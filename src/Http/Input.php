<?php

namespace Chukdo\Http;

use Chukdo\Contracts\Http\Input as InputInterface;
use Chukdo\Json\Json;
use Chukdo\Helper\Cli;
use Chukdo\Storage\FileUploaded;
use Throwable;

/**
 * Gestion des inputs.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Input extends Json implements InputInterface
{
    /**
     * Input constructor.
     *
     * @param null $data
     */
    public function __construct( $data = null )
    {
        $data = $data
            ?: ( Cli::runningInConsole()
                ? Cli::inputs()
                : $_REQUEST );

        /* Trim all input */
        array_walk_recursive( $data,
            function( &$v, $k ) {
                if ( is_scalar( $v ) ) {
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
        } catch ( Throwable $e ) {
            return null;
        }
    }
}
