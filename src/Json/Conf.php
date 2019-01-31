<?php namespace Chukdo\Json;

/**
 * Gestion des exceptions
 *
 * @package 	Exception
 * @version 	1.0.0
 * @copyright 	licence MIT, Copyright (C) 2019 Domingo
 * @since 		08/01/2019
 * @author 		Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class ConfException extends \Exception {}

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
    public function loadConf(string $file): bool
    {
        if (file_exists($file)) {
            $load = new Json(file_get_contents($file));
            $this->merge($load->toFlatJson());
            return true;
        }

        return false;
    }
}
