<?php

namespace Chukdo\Mail;

use Chukdo\Contracts\Mail\Transport as TransportInterface;

/**
 * SMTP.
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Smtp implements TransportInterface
{
    /**
     * History Log
     *
     * @param array $log
     */
    public array $log = [];
    /**
     * Socket
     *
     * @param resource $sock
     */
    protected $sock;
    /**
     * Dsn
     *
     * @param array $dsn
     */
    protected array $dsn = [];

    /**
     * Smtp constructor.
     *
     * @param string $dsn
     * @param int    $timeout
     */
    public function __construct( string $dsn, int $timeout = 10 )
    {
        $this->dsn = parse_url( $dsn );

        $host   = $this->dsn[ 'host' ];
        $port   = $this->dsn[ 'port' ];
        $scheme = $this->dsn[ 'scheme' ];
        $sock   = $scheme === 'ssl'
            ? "$scheme://$host:$port"
            : "$host:$port";

        $this->dsn[ 'pass' ] = rawurldecode( $this->dsn[ 'pass' ] );
        $this->dsn[ 'user' ] = rawurldecode( $this->dsn[ 'user' ] );

        $this->sock = stream_socket_client( $sock, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, stream_context_create() );

        if ( $this->sock === false ) {
            throw new MailException( sprintf( 'SMTP [%s %s]', $errno, $errstr ) );
        }

        if ( ( $code = $this->read() ) !== 220 ) {
            throw new MailException( sprintf( 'SMTP Connexion [%s]', $code ) );
        }

        stream_set_timeout( $this->sock, $timeout, 0 );
    }

    /**
     * @return int
     */
    protected function read(): int
    {
        $s = '';

        if ( !is_resource( $this->sock ) ) {
            throw new MailException( 'Error SMTP: Lost connection' );
        }

        while ( is_resource( $this->sock ) && !feof( $this->sock ) ) {
            $g = @fgets( $this->sock, 515 );
            $s .= $g;

            if ( $g[ 3 ] !== '-' ) {
                break;
            }

            $i = stream_get_meta_data( $this->sock );

            if ( $i[ 'timed_out' ] ) {
                break;
            }
        }

        $this->log[] = 'S: ' . $s;

        return (int) substr( $s, 0, 3 );
    }

    /**
     * @param string      $from
     * @param array       $to
     * @param string      $message
     * @param string      $headers
     * @param string|null $host
     *
     * @return bool
     */
    public function sendMail( string $from, array $to, string $message, string $headers, string $host = null ): bool
    {
        $c = true;

        if ( $this->hello( $host ?? 'localhost' ) ) {
            if ( ( $this->dsn[ 'scheme' ] === 'tls' ) && !$this->starttls() ) {
                $c = false;
            }

            if ( isset( $this->dsn[ 'user' ], $this->dsn[ 'pass' ] ) && $c && !$this->auth() ) {
                $c = false;
            }

            if ( $c && $this->mailFrom( $from ) ) {
                foreach ( (array) $to as $mail ) {
                    $this->mailTo( $mail );
                }

                if ( $r = $this->data( $message, $headers ) ) {
                    $this->quit();

                    return $r;
                }
            }
        }

        $this->quit();

        throw new MailException( sprintf( 'SMTP [%s]', end( $this->log ) ) );
    }

    /**
     * @param string $host
     *
     * @return bool
     */
    public function hello( string $host = 'localhost' ): bool
    {
        return !( ( $this->write( "EHLO $host" ) !== 250 ) && $this->write( "HELO $host" ) !== 250 );
    }

    /**
     * @param string $c
     * @param bool   $r
     *
     * @return int|null
     */
    protected function write( string $c, bool $r = true )
    {
        if ( $r ) {
            $c           .= "\r\n";
            $this->log[] = 'C: ' . $c;
        }

        if ( !is_resource( $this->sock ) ) {
            throw new MailException( 'Error SMTP: Lost connection' );
        }

        @fwrite( $this->sock, $c );


        return $r
            ? $this->read()
            : null;
    }

    /**
     * @return bool
     */
    public function starttls(): bool
    {
        return ( $this->write( 'STARTTLS' ) === 220 ) && @stream_socket_enable_crypto( $this->sock, true, STREAM_CRYPTO_METHOD_TLS_CLIENT );
    }

    /**
     * @param string $mode
     *
     * @return bool
     */
    public function auth( string $mode = 'LOGIN' ): bool
    {
        switch ( $mode ) {
            case 'PLAIN' :
                $b64e = base64_encode( "\0" . $this->dsn[ 'user' ] . "\0" . $this->dsn[ 'pass' ] );

                return ( $this->write( 'AUTH PLAIN' ) === 334 ) && $this->write( $b64e ) === 235;
            case 'LOGIN' :
                $b64u = base64_encode( $this->dsn[ 'user' ] );
                $b64p = base64_encode( $this->dsn[ 'pass' ] );

                return ( $this->write( 'AUTH LOGIN' ) === 334 ) && ( $this->write( $b64u ) === 334 ) && $this->write( $b64p ) === 235;
            default :
                return false;
        }
    }

    /**
     * @param string $from
     *
     * @return bool
     */
    public function mailFrom( string $from ): bool
    {
        return $this->write( "MAIL FROM:<$from>" ) === 250;
    }

    /**
     * @param string $to
     *
     * @return bool
     */
    public function mailTo( string $to ): bool
    {
        return $this->write( "RCPT TO:<$to>" ) === 250;
    }

    /**
     * @param string $data
     * @param string $headers
     *
     * @return bool
     */
    public function data( string $data, string $headers ): bool
    {
        if ( $this->write( 'DATA' ) === 354 ) {
            $headerList = explode( "\n", trim( $headers ) );

            foreach ( $headerList as $header ) {
                $this->write( $header . "\r\n", false );
            }

            $this->write( "\r\n", false );

            $datas = str_split( $data, 900 );

            foreach ( $datas as $chunkData ) {
                $this->write( ( strpos( $chunkData, '.' ) === 0
                                  ? '.'
                                  : '' ) . $chunkData, false );
            }

            if ( $this->write( "\r\n." ) === 250 ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function quit(): bool
    {
        return $this->write( 'QUIT' ) === 221;
    }

    /**
     * @return bool
     */
    public function noop(): bool
    {
        return $this->write( 'NOOP' ) === 250;
    }

    /**
     * @return bool
     */
    public function reset(): bool
    {
        return $this->write( 'RSET' ) === 250;
    }

    /**
     * Détruit la connexion au serveur Smtp
     *
     * @return void
     */
    public function __destruct()
    {
        fclose( $this->sock );
    }

    /**
     * @return string|null
     */
    public function messageId(): ?string
    {
        foreach ( array_reverse( $this->log ) as $log ) {
            if ( strpos( $log, 'S: 250 Ok' ) === 0 ) {
                return trim( substr( $log, 9 ) );
            }
        }

        return null;
    }
}