<?php

namespace Chukdo\Storage\Wrappers;

use Chukdo\Contracts\Db\Redis as RedisInterface;
use Chukdo\Storage\ServiceLocator;
use Exception;

/**
 * Redis streamWrapper.
 *
 * @copyright     licence MIT, Copyright (C) 2015 Domingo
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class RedisStream extends AbstractStream
{
    /**
     * @var object StreamInterface
     */
    protected object $stream;

    /**
     * Retourne le contenu du fichier.
     *
     * @return mixed
     * @throws StreamException
     */
    public function streamGet()
    {
        return $this->getStream()
                    ->get( $this->getPath() );
    }

    /**
     * Lit les informations sur une ressource de fichier.
     *
     * @return RedisInterface
     * @throws StreamException
     */
    protected function getStream(): RedisInterface
    {
        if ( $this->stream instanceof RedisInterface ) {
            return $this->stream;
        }

        return $this->stream = $this->initStream();
    }

    /**
     * @return RedisInterface
     * @throws StreamException
     */
    public function initStream(): RedisInterface
    {
        $scheme = $this->getScheme();
        $host   = (int) $this->getHost();

        try {
            $stream = ServiceLocator::getInstance()
                                    ->getResource( $scheme );
            $stream->select( $host );
        }
        catch ( Exception $e ) {
            throw new StreamException( sprintf( '[%s] is not a registred resource', $scheme ), $e->getCode(), $e );
        }

        if ( !( $stream instanceof RedisInterface ) ) {
            throw new StreamException( sprintf( 'service [%s] is not a redis interface', $scheme ) );
        }

        return $stream;
    }

    /**
     * Retourne une portion du contenu du fichier.
     *
     * @param int $offset
     * @param int $length
     *
     * @return string|null
     * @throws StreamException
     */
    public function streamGetRange( int $offset, int $length ): ?string
    {
        if ( $length > 0 ) {
            return $this->getStream()
                        ->getRange( $this->getPath(), $offset, --$length );
        }

        return null;
    }

    /**
     * Ecris une portion de contenu en commencant à l'offset défini.
     *
     * @param int    $offset
     * @param string $content
     *
     * @return bool
     * @throws StreamException
     */
    public function streamSetRange( int $offset, string $content ): bool
    {
        return (bool) $this->getStream()
                           ->setRange( $this->getPath(), $offset, $content );
    }

    /**
     * Ajoute du contenu au debut du fichier.
     *
     * @param string|null $content
     *
     * @return bool
     * @throws StreamException
     */
    public function streamSet( ?string $content ): bool
    {
        return (bool) $this->getStream()
                           ->set( $this->getPath(), $content );
    }

    /**
     * Ajoute du contenu à la fin du fichier.
     *
     * @param string $content
     *
     * @return bool
     * @throws StreamException
     */
    public function streamAppend( string $content ): bool
    {
        return (bool) $this->getStream()
                           ->append( $this->getPath(), $content );
    }

    /**
     * Retourne si le fichier existe.
     *
     * @return bool
     * @throws StreamException
     */
    public function streamExists(): bool
    {
        return (bool) $this->getStream()
                           ->exists( $this->getPath() );
    }

    /**
     * Retourne la taille du fichier.
     *
     * @return int
     * @throws StreamException
     */
    public function streamSize(): int
    {
        return (int) $this->getStream()
                          ->strlen( $this->getPath() );
    }

    /**
     * Supprime fichier.
     *
     * @return bool
     * @throws StreamException
     */
    public function streamDelete(): bool
    {
        return (bool) $this->getStream()
                           ->del( $this->getPath() );
    }

    /**
     * Renomme le fichier ou le dossier.
     *
     * @param string $path
     *
     * @return bool
     * @throws StreamException
     */
    public function streamRename( string $path ): bool
    {
        if ( (bool) $this->getStream()
                         ->rename( $this->getPath(), $path ) ) {
            return true;
        }

        return false;
    }

    /**
     * Crée un dossier.
     *
     * @param bool $recursive
     *
     * @return bool
     */
    public function streamSetDir( bool $recursive ): bool
    {
        return true;
    }

    /**
     * Supprime un dossier.
     *
     * @return bool
     */
    public function streamDeleteDir(): bool
    {
        return true;
    }

    /**
     * Retourne si le fichier est un dossier.
     *
     * @return bool
     * @throws StreamException
     */
    public function streamIsDir(): bool
    {
        return false;
    }

    /**
     * Retourne la liste des fichiers present dans le dossier.
     *
     * @return array
     * @throws StreamException
     */
    public function streamListDir(): array
    {
        $path = $this->getPath();
        $list = $this->getStream()
                     ->keys( $path . '/*' );
        foreach ( $list as $k => $v ) {
            $list[ $k ] = trim( str_replace( $path, '', $v ), '/' );
        }
        natcasesort( $list );

        return $list;
    }

    /**
     * Defini ou retourne la derniere date d'acces au fichier.
     *
     * @param bool $time
     *
     * @return int
     * @throws StreamException
     */
    public function streamAccessTime( $time = false ): int
    {
        return (int) $this->streamInfo( $this->getPath(), 'atime', $time
            ? time()
            : null );
    }

    /**
     * Defini des meta données d'information sur le fichier.
     *
     * @param string $path
     * @param string $name
     * @param null   $value
     *
     * @return mixed
     * @throws StreamException
     */
    protected function streamInfo( string $path, string $name, $value = null )
    {
        $path = 'info::' . $path;
        if ( $value !== null ) {
            $this->getStream()
                 ->hset( $path, $name, $value );

            return $value;
        }

        return $this->getStream()
                    ->hget( $path, $name );
    }

    /**
     * Defini ou retourne la date de creation du fichier.
     *
     * @param bool $time
     *
     * @return int
     * @throws StreamException
     */
    public function streamCreatedTime( $time = false ): int
    {
        return (int) $this->streamInfo( $this->getPath(), 'ctime', $time
            ? time()
            : null );
    }

    /**
     * Defini ou retourne la derniere date de modification au fichier.
     *
     * @param bool $time
     *
     * @return int
     * @throws StreamException
     */
    public function streamModifiedTime( $time = false ): int
    {
        return (int) $this->streamInfo( $this->getPath(), 'mtime', $time
            ? time()
            : null );
    }

    /**
     * Libere le flux.
     *
     * @return bool
     */
    public function streamClose(): bool
    {
        unset( $this->stream );

        return true;
    }
}
