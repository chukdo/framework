<?php namespace Chukdo\Json;

use Chukdo\Storage\Storage;
use Chukdo\Helper\Is;

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
        $storage    = new Storage();
        $files      = $storage->files($dir, '/\.json$/');

        if (count($files) == 0) {
            return false;
        }

        foreach ($files as $file) {
            $json = $storage->get($file);

            if (Is::json($json)) {
                foreach (json_decode($json, true) as $k => $v) {
                    $this->offsetSet($k, $v);
                }
            } else {
                return false;
            }
        }

        return true;
    }
}
