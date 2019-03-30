<?php

namespace Chukdo\Json;

use Chukdo\Storage\Storage;
use Chukdo\Helper\Is;

/**
 * Gestion des fichiers de langues.
 *
 * @version    1.0.0
 *
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 *
 * @since        08/01/2019
 *
 * @author Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Lang extends Json
{
    /**
     * @param string $dir
     *
     * @return bool
     */
    public function load(string $dir): bool
    {
        $storage = new Storage();
        $files = $storage->files(
            $dir,
            '/\.json$/'
        );

        if (count($files) == 0) {
            return false;
        }

        foreach ($files as $file) {
            if (!$this->loadFile($file)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $file
     *
     * @return bool
     */
    protected function loadFile(string $file): bool
    {
        $storage = new Storage();
        $name = basename(
            $file,
            '.json'
        );
        $json = $storage->get($file);
        $root = $this->offsetGetOrSet(
            $name,
            []
        );

        if (!Is::json($json)) {
            return false;
        }

        foreach (json_decode(
            $json,
            true
        ) as $k => $v) {
            $root->offsetSet(
                $k,
                $v
            );
        }

        return true;
    }
}
