<?php namespace Chukdo\Json;

use Chukdo\Storage\Storage;

/**
 * Gestion des fichiers de langues
 *
 * @package     Json
 * @version 	1.0.0
 * @copyright 	licence MIT, Copyright (C) 2019 Domingo
 * @since 		08/01/2019
 * @author Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class JsonLang extends Json
{
    /**
     * @param string $dir
     * @return bool
     */
    public function load(string $dir): bool
    {
        $r      = false;
        $files  = (new Storage())->files($dir, '/\.json$/');

        foreach ($files as $file) {
            $load = new Json(file_get_contents($file));
            $this->merge($load->toSimpleArray(), true);
            $r = true;
        }

        return $r;
    }
}
