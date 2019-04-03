<?php

namespace Chukdo\Json;

use Chukdo\Storage\Storage;
use Chukdo\Helper\Is;

/**
 * Gestion des fichiers de langues.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Lang extends Conf
{
    /**
     * @param string $file
     * @return bool
     */
    public function loadFile( string $file ): bool
    {
        $storage = new Storage();
        $name    = basename($file, '.json');

        if( $storage->exists($file) ) {
            $load = new Conf($storage->get($file));

            $this->merge($load->toSimpleArray($name),
                true);

            return true;
        }

        return false;
    }
}
