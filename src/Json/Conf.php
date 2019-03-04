<?php namespace Chukdo\Json;

use Chukdo\Storage\Storage;

/**
 * Gestion des fichiers de configuration
 *
 * @package     Json
 * @version 	1.0.0
 * @copyright 	licence MIT, Copyright (C) 2019 Domingo
 * @since 		08/01/2019
 * @author Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Conf extends Json
{
    /**
     * @param string $file
     * @return bool
     */
    public function load(string $file): bool
    {
        $storage = new Storage();

        if ($storage->exists($file)) {
            $load = new Json($storage->get($file));
            $this->merge($load->toSimpleArray(), true);
            return true;
        }

        return false;
    }
}
