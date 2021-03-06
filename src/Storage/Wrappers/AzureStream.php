<?php

namespace Chukdo\Storage\Wrappers;

use Chukdo\Storage\ServiceLocator;
use Exception;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use Throwable;

/**
 * Azure streamWrapper.
 *
 * @copyright     licence MIT, Copyright (C) 2015 Domingo
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class AzureStream extends AbstractStream
{
    /**
     * @var object|null
     */
    protected ?object $stream;

    /**
     * @var string|null
     */
    private ?string $streamContent = null;

    /**
     * @var int
     */
    private int $streamLength = 0;

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
        if ( $this->streamContent === null ) {
            $this->streamGet();
        }

        if ( $offset >= $this->streamLength ) {
            return null;
        }

        return substr( (string) $this->streamContent, $offset, $length );
    }

    /**
     * Retourne le contenu du fichier.
     *
     * @return string|null
     * @throws StreamException
     */
    public function streamGet(): ?string
    {
        if ( $this->streamContent === null ) {
            $get = stream_get_contents( $this->getStream()
                                             ->getBlob( $this->getHost(), $this->getPath() )
                                             ->getContentStream() );
            if ( $get !== false ) {
                $this->streamContent = $get;
                $this->streamLength  = strlen( $this->streamContent );
            }
        }

        return $this->streamContent;
    }

    /**
     * Lit les informations sur une ressource de fichier.
     *
     * @return BlobRestProxy
     * @throws StreamException
     */
    protected function getStream(): BlobRestProxy
    {
        if ( $this->stream instanceof BlobRestProxy ) {
            return $this->stream;
        }

        return $this->stream = $this->initStream();
    }

    /**
     * @return BlobRestProxy
     * @throws StreamException
     */
    public function initStream(): BlobRestProxy
    {
        $scheme = $this->getScheme();
        try {
            $stream = ServiceLocator::getInstance()
                                    ->getResource( $scheme );
        }
        catch ( Exception $e ) {
            throw new StreamException( sprintf( '[%s] is not a registred resource', $scheme ), $e->getCode(), $e );
        }
        if ( !( $stream instanceof BlobRestProxy ) ) {
            throw new StreamException( sprintf( 'service [%s] is not a azure BlobRestProxy instance', $scheme ) );
        }

        return $stream;
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
        throw new StreamException( '[streamGetRange] not implemented' );
    }

    /**
     * Ajoute du contenu au debut du fichier.
     *
     * @param string $content
     *
     * @return bool
     * @throws StreamException
     */
    public function streamSet( string $content ): bool
    {
        return (bool) $this->getStream()
                           ->createBlockBlob( $this->getHost(), $this->getPath(), $content );
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
                           ->appendBlock( $this->getHost(), $this->getPath(), $content );
    }

    /**
     * Retourne si le fichier existe.
     *
     * @return bool
     */
    public function streamExists(): bool
    {
        try {
            return (bool) $this->streamSize() > 0;
        }
        catch ( Throwable $e ) {
        }

        return false;
    }

    /**
     * Retourne la taille du fichier.
     *
     * @return int
     * @throws StreamException
     */
    public function streamSize(): int
    {
        if ( $this->streamLength === 0 ) {
            $this->streamLength = (int) $this->getStream()
                                             ->getBlob( $this->getHost(), $this->getPath() )
                                             ->getProperties()
                                             ->getContentLength();
        }

        return $this->streamLength;
    }

    /**
     * Supprime fichier.
     *
     * @return bool
     * @throws StreamException
     */
    public function streamDelete(): bool
    {
        $this->getStream()
             ->deleteBlob( $this->getHost(), $this->getPath() );

        return true;
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
        $this->getStream()
             ->copyBlob( $this->getHost(), $path, $this->getHost(), $this->getPath() );
        $this->getStream()
             ->deleteBlob( $this->getHost(), $this->getPath() );

        return true;
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
        $path  = $this->getPath();
        $blobs = $this->getStream()
                      ->listBlobs( $this->getHost() )
                      ->getBlobs();
        $list  = [];
        foreach ( $blobs as $blob ) {
            $name = $blob->getName();
            if ( $path ) {
                if ( strpos( $name, $path ) === 0 ) {
                    $list[] = trim( substr( $name, strlen( $path ) ), '/' );
                }
            }
            else {
                $list[] = $name;
            }
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
     */
    public function streamAccessTime( $time = false ): int
    {
        return 0;
    }

    /**
     * Defini ou retourne la date de creation du fichier.
     *
     * @param bool $time
     *
     * @return int
     */
    public function streamCreatedTime( $time = false ): int
    {
        return 0;
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
        if ( $time ) {
            return time();
        }

        return (int) $this->getStream()
                          ->getBlob( $this->getHost(), $this->getPath() )
                          ->getProperties()
                          ->getLastModified()
                          ->getTimestamp();
    }

    /**
     * Libere le flux.
     *
     * @return bool
     */
    public function streamClose(): bool
    {
        $this->stream = null;

        return true;
    }
}
