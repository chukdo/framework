<?php

namespace Chukdo\Json;

use Chukdo\Storage\Storage;

/**
 * Gestion des fichiers de configuration.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Conf extends Json
{
    /**
     * @param string $dir
     * @return bool
     */
    public function loadDir( string $dir ): bool
    {
        $storage = new Storage();
        $files   = $storage->files($dir,
            '/\.json$/');

        if ( count($files) == 0 ) {
            return false;
        }

        foreach ( $files as $file ) {
            if ( !$this->loadFile($file) ) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $file
     * @return bool
     */
    public function loadFile( string $file ): bool
    {
        $storage = new Storage();

        if ( $storage->exists($file) ) {
            $load = new Conf($storage->get($file));

            $this->merge($load->toSimpleArray(),
                true);

            return true;
        }

        return false;
    }
}
