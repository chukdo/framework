<?php

namespace Chukdo\Db\Redis;

use Chukdo\Contracts\Db\Redis as RedisInterface;

/**
 * Gestion de la base de donnée NOSQL Redis basé sur son protocole unifié.
 *
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Redis implements RedisInterface
{
    /**
     * Socket.
     *
     * @var resource
     */
    protected $sock;

    /**
     * Pointer SCAN pour iteration.
     *
     * @var int
     */
    protected int $pointer = 0;

    /**
     * Pile de stockage SCAN.
     *
     * @var array
     */
    protected array $stack = [];

    /**
     * Arguements par defaut pour le SCAN.
     *
     * @var array
     */
    protected array $args = [];

    /**
     * type de SCAN (SCAN, HSCAN, SSCAN).
     *
     * @var string
     */
    protected ?string $type = null;

    /**
     * Redis constructor.
     *
     * @param string|null $dsn
     * @param int|null    $timeout
     *
     * @throws RedisException
     */
    public function __construct( string $dsn = null, int $timeout = null )
    {
        $urlParsed = parse_url( $dsn ?? 'redis://127.0.0.1:6379' );

        if ( $urlParsed === false ) {
            throw new RedisException( sprintf( 'Can\'t connect to dsn [%s]', $dsn ) );
        }

        $host = $urlParsed[ 'host' ];
        $port = $urlParsed[ 'port' ];

        if ( $urlParsed[ 'scheme' ] === 'ssl' ) {
            $host = 'ssl://' . $host;
        }

        if ( $urlParsed[ 'scheme' ] === 'tls' ) {
            $host = 'tls://' . $host;
        }

        $sock = fsockopen( $host, $port, $errno, $errstr, $timeout ?? 5 );

        if ( $sock === false ) {
            throw new RedisException( "[$errno $errstr]" );
        }

        $this->sock = $sock;

        if ( isset( $urlParsed[ 'pass' ] ) && !$this->__call( 'AUTH', [ $urlParsed[ 'pass' ] ] ) ) {
            throw new RedisException( 'Wrong password' );
        }

        $this->setTypeIterator( 'scan' );
    }

    /**
     * Appel des commandes redis au travers de la surcharge magique de PHP.
     *
     * @param string $name
     * @param array  $args
     *
     * @return mixed
     * @throws RedisException
     */
    public function __call( string $name, array $args )
    {
        array_unshift( $args, str_replace( '_', ' ', strtoupper( $name ) ) );
        $this->write( $this->command( $args ) );

        return $this->read();
    }

    /**
     * Ecriture d'une commande basé sur le protocol unifié de Redis.
     *
     * @param string $c command
     *
     * @throws RedisException
     */
    public function write( string $c ): void
    {
        $length = mb_strlen( $c );
        for ( $written = 0; $written < $length; $written += $fwrite ) {
            $fwrite = fwrite( $this->sock, mb_substr( $c, $written ) );

            if ( $fwrite === false || $fwrite <= 0 ) {
                throw new RedisException( 'Stream write error' );
            }
        }
    }

    /**
     * Formate une commande Redis (protocol unifié de Redis).
     *
     * @param array $args arguments
     *
     * @return string
     */
    public function command( array $args ): string
    {
        $c = '*' . count( $args ) . "\r\n";

        foreach ( $args as $arg ) {
            $c .= '$' . mb_strlen( $arg ) . "\r\n" . $arg . "\r\n";
        }

        return $c;
    }

    /**
     * Lecture d'une reponse du serveur.
     *
     * @return mixed
     * @throws RedisException
     */
    public function read()
    {
        $get = stream_get_line( $this->sock, 512, "\r\n" );

        if ( $get === false ) {
            throw new RedisException( 'Failed to read type of response from stream' );
        }

        $reply = substr( $get, 1 );

        switch ( $get[ 0 ] ) {
            case '-':
                /** Error */ throw new RedisException( $reply );
                break;
            case '+':
                /** Inline */ $s = $reply;
                if ( $s === 'OK' ) {
                    $s = true;
                }
                break;
            case ':':
                /** Integer */ $s = (int) $reply;
                break;
            case '$':
                /** Bulk */ $s = null;
                if ( $reply === '-1' ) {
                    break;
                }
                $size = (int) $reply;
                $read = 0;
                if ( $size > 0 ) {
                    while ( $read < $size ) {
                        $len  = min( 1024, $size - $read );
                        $read += $len;

                        if ( ( $r = stream_get_line( $this->sock, $len ) ) !== false ) {
                            $s .= $r;
                        }
                        else {
                            throw new RedisException( 'Failed to read response from stream' );
                        }
                    }
                }

                /** \r\n */
                stream_get_line( $this->sock, 2 );
                break;

            case '*':
                /** Multi Bulk */ $s = null;
                if ( $reply === '*-1' ) {
                    break;
                }
                $c = (int) $reply;
                $s = [];
                for ( $i = 0; $i < $c; ++$i ) {
                    $s[] = $this->read();
                }
                break;
            default:
                throw new RedisException( sprintf( "Unknow response [%s]", $reply ) );
                break;
        }

        return $s;
    }

    /**
     * Défini le type de commande SCAN lors d'une itération Redis
     * Ex. SCAN SSCAN HSCAN.
     *
     * @param string $type
     */
    public function setTypeIterator( string $type ): void
    {
        $this->type = strtoupper( $type );
    }

    /**
     * Retourne le nombre d'enregistrement dans la base redis.
     *
     * @return int|mixed
     * @throws RedisException
     */
    public function count()
    {
        $this->write( $this->command( [ 'dbsize' ] ) );

        return $this->read();
    }

    /**
     * Initialise l'iteration.
     *
     * @throws RedisException
     */
    public function rewind(): void
    {
        /** Reset */
        $this->stack   = [];
        $this->pointer = 0;
        /** command SCAN */
        $scan = $this->getIterator( $this->pointer );
        if ( count( $scan ) === 2 ) {
            $this->pointer = (int) $scan[ 0 ];
            $this->stack   = (array) $scan[ 1 ];
        }
    }

    /**
     * Ecris la commande SCAN lors d'une itération Redis
     * Ex. SCAN SSCAN HSCAN.
     *
     * @param int $pointer
     *
     * @return mixed
     * @throws RedisException
     */
    protected function getIterator( int $pointer )
    {
        $this->write( $this->command( array_merge( [
                                                       $this->type,
                                                       $pointer,
                                                   ], $this->args ) ) );

        return $this->read();
    }

    /**
     * @return bool|mixed
     * @throws RedisException
     */
    public function current()
    {
        $current = false;
        if ( isset( $this->stack[ 0 ] ) ) {
            $key = $this->stack[ 0 ];
            switch ( $this->__call( 'TYPE', [ $key ] ) ) {
                case 'string':
                case 'set':
                    $current = $this->get( $key );
                    break;
                case 'list':
                    $current = $this->__call( 'LRANGE', [
                        $key,
                        '0',
                        '-1',
                    ] );
                    break;
                case 'zset':
                    $current = $this->__call( 'ZRANGE', [
                        $key,
                        '0',
                        '-1',
                    ] );
                    break;
                case 'hash':
                    $current = $this->__call( 'HGETALL', [ $key ] );
                    break;
            }
        }

        return $current;
    }

    /**
     * @param string $key
     *
     * @return mixed
     * @throws RedisException
     */
    public function get( string $key )
    {
        return $this->__call( 'GET', [ $key ] );
    }

    /**
     * Retourne la cle de l'element courant.
     *
     * @return string
     */
    public function key(): string
    {
        return $this->stack[ 0 ] ?? '';
    }

    /**
     * Pointe sur l'element suivant.
     *
     * @throws RedisException
     */
    public function next(): void
    {
        if ( !empty( $this->stack ) ) {
            array_shift( $this->stack );
        }
        else {
            if ( $this->pointer !== 0 ) {
                /** command SCAN */
                $scan = $this->getIterator( $this->pointer );
                if ( count( $scan ) === 2 ) {
                    $this->pointer = (int) $scan[ 0 ];
                    $this->stack   = (array) $scan[ 1 ];
                }
            }
        }
    }

    /**
     * Verifie si il y a un element apres l'element courant
     * apres l'appel de rewind() ou next().
     *
     * @return bool
     */
    public function valid(): bool
    {
        return !( $this->pointer === 0 && empty( $this->stack ) );
    }

    /**
     * Ecriture de commandes dans un pipeline (gain de performance).
     *
     * @param array $commands
     *
     * @return array
     * @throws RedisException
     */
    public function pipe( array $commands ): array
    {
        $s = [];
        $c = '';
        $i = 0;
        foreach ( $commands as $command ) {
            $args = str_getcsv( $command, ' ', '"' );
            $c    .= $this->command( $args );
            ++$i;
        }
        $this->write( $c );
        for ( $j = 0; $j < $i; ++$j ) {
            $s[ $j ] = $this->read();
        }

        return $s;
    }

    /**
     * Retourne les informations sur le serveur Redis.
     *
     * @param string $key information precise que l'on souhaite recuperer
     *
     * @return array|bool|mixed
     * @throws RedisException
     */
    public function info( string $key = null )
    {
        $info  = [];
        $items = explode( "\r\n", $this->__call( 'info', [] ) );
        foreach ( $items as $item ) {
            $item = explode( ':', $item );
            if ( isset( $item[ 1 ] ) ) {
                $info[ $item[ 0 ] ] = $item[ 1 ];
            }
        }
        if ( $key ) {
            return $info[ $key ] ?? false;
        }

        return $info;
    }

    /**
     * Arguments à ajouter à la commande SCAN lors d'une itération Redis
     * Ex. scan 0 MATCH *11*.
     *
     * @param array $args
     */
    public function setArgsIterator( array $args ): void
    {
        $this->args = $args;
    }

    /**
     * Détruit la connexion au serveur Redis.
     */
    public function __destruct()
    {
        unset( $this->sock );
    }

    /**
     * @param string $key
     *
     * @return mixed
     * @throws RedisException
     */
    public function exists( string $key )
    {
        return $this->__call( 'EXISTS', [ $key ] );
    }

    /**
     * @param int $key second
     *
     * @return mixed
     * @throws RedisException
     */
    public function expire( int $key )
    {
        return $this->__call( 'EXPIRE', [ $key ] );
    }

    /**
     * @param string $key
     * @param        $value
     *
     * @return mixed
     * @throws RedisException
     */
    public function set( string $key, $value )
    {
        return $this->__call( 'SET', [
            $key,
            $value,
        ] );
    }

    /**
     * @param string $key
     *
     * @return bool
     * @throws RedisException
     */
    public function del( string $key ): bool
    {
        return (bool) $this->__call( 'DEL', [ $key ] );
    }

    /**
     * @param string $key
     *
     * @return mixed
     * @throws RedisException
     */
    public function __isset( string $key )
    {
        return $this->__call( 'EXISTS', [ $key ] );
    }

    /**
     * @param string $key
     *
     * @return mixed
     * @throws RedisException
     */
    public function __get( string $key )
    {
        return $this->__call( 'GET', [ $key ] );
    }

    /**
     * @param string $key
     * @param        $value
     *
     * @return mixed
     * @throws RedisException
     */
    public function __set( string $key, $value )
    {
        return $this->__call( 'SET', [
            $key,
            $value,
        ] );
    }

    /**
     * @param string $key
     */
    public function __unset( string $key ): void
    {
        $this->__call( 'DEL', [ $key ] );
    }

    /**
     * @param string $key
     * @param string $value
     *
     * @return mixed
     */
    public function rpush( string $key, string $value )
    {
        return $this->__call( 'RPUSH', [
            $key,
            $value,
        ] );
    }

    /**
     * @param string $key
     * @param int    $offset
     * @param int    $length
     *
     * @return mixed
     */
    public function getRange( string $key, int $offset, int $length )
    {
        return $this->__call( 'GETRANGE', [
            $key,
            $offset,
            $length,
        ] );
    }

    /**
     * @param string $key
     * @param int    $offset
     * @param string $content
     *
     * @return mixed
     */
    public function setRange( string $key, int $offset, string $content )
    {
        return $this->__call( 'SETRANGE', [
            $key,
            $offset,
            $content,
        ] );
    }

    /**
     * @param string $key
     * @param string $value
     *
     * @return mixed
     */
    public function append( string $key, string $value )
    {
        return $this->__call( 'APPEND', [
            $key,
            $value,
        ] );
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function strlen( string $key )
    {
        return $this->__call( 'STRLEN', [ $key ] );
    }

    /**
     * @param string $key
     * @param        $newKey
     *
     * @return mixed
     */
    public function rename( string $key, $newKey )
    {
        return $this->__call( 'RENAME', [
            $key,
            $newKey,
        ] );
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function keys( string $key )
    {
        return $this->__call( 'KEYS', [ $key ] );
    }

    /**
     * @inheritDoc
     */
    public function hset( string $name, string $key, string $value )
    {
        return $this->__call( 'HSET', [
            $name,
            $key,
            $value,
        ] );
    }

    /**
     * @inheritDoc
     */
    public function hget( string $name, string $key )
    {
        return $this->__call( 'HGET', [
            $name,
            $key,
        ] );
    }
}
