<?php

namespace Chukdo\Conf;

use Throwable;
use Chukdo\Bootstrap\AppException;
use Chukdo\Json\Json;
use Chukdo\Storage\Storage;

/**
 * Gestion des fichiers de configuration.
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Conf extends Json
{
    /**
     * @param string $dir
     *
     * @return $this
     */
    public function loadDir( string $dir ): self
    {
        $storage = new Storage();
        $files   = $storage->files( $dir, '/\.json$/' );

        if ( count( $files ) === 0 ) {
            throw new AppException( sprintf( 'Conf dir [%s] has no files', $dir ) );
        }

        foreach ( $files as $file ) {
            if ( !$this->loadFile( $file ) ) {
                throw new AppException( sprintf( 'Conf file [%s] no exist', $file ) );
            }
        }

        return $this;
    }

    /**
     * @param string $file
     *
     * @return $this
     */
    public function loadFile( string $file ): self
    {
        $storage = new Storage();

        if ( $storage->exists( $file ) ) {
            $load = new self( $storage->get( $file ) );
            $this->merge( $load->to2d(), true );

            return $this;
        }

        throw new AppException( sprintf( 'Conf file [%s] no exist', $file ) );
    }

    /**
     * @param string      $path
     * @param string|null $env
     * @param string|null $channel
     *
     * @return $this
     */
    public function loadDefault( string $path, string $env = null, string $channel = null ): self
    {
        $path    = rtrim( $path, '/' ) . '/';
        $env     = trim( $env, '/' );
        $channel = trim( $channel, '/' );

        $this->loadFile( $path . 'default.json' );

        try {
            if ( $env ) {
                $this->loadFile( $path . $env . '.json' );
            }
            if ( $channel ) {
                $this->loadFile( $path . $channel . '/default.json' );
                if ( $env ) {
                    $this->loadFile( $path . $channel . '/' . $env . '.json' );
                }
            }
        }
        catch ( Throwable $e ) {

        }

        return $this;
    }
}
