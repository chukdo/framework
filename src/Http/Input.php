<?php

namespace Chukdo\Http;

use Chukdo\Contracts\Http\Input as InputInterface;
use Chukdo\Helper\To;
use Chukdo\Json\Json;
use Chukdo\Helper\HttpRequest;
use Chukdo\Storage\FileUploaded;
use Throwable;

/**
 * Class Input
 *
 * @package Chukdo\Http
 */
class Input extends Json implements InputInterface
{
    /**
     * Input constructor.
     *
     * @param iterable|null $data
     */
    public function __construct( iterable $data = null )
    {
        $data = To::arr( $data
                             ?: HttpRequest::all() );

        /** Trim all input */
        array_walk_recursive( $data, static function( &$v, $k )
        {
            if ( is_string( $v ) ) {
                $v = trim( $v );
            }
        } );

        parent::__construct( $data );
    }

    /**
     * @param string      $name
     * @param string|null $allowedMimeTypes
     * @param int|null    $maxFileSize
     *
     * @return FileUploaded|null
     */
    public function file( string $name, string $allowedMimeTypes = null, int $maxFileSize = null ): ?FileUploaded
    {
        try {
            return new FileUploaded( $name, $allowedMimeTypes, $maxFileSize );
        }
        catch ( Throwable $e ) {
            return null;
        }
    }
}
