<?php namespace Chukdo\Helper;

/**
 * Stream
 *
 * @package 	Helper
 * @version 	1.0.0
 * @copyright 	licence MIT, Copyright (C) 2019 Domingo
 * @since 		08/01/2019
 * @author 		Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
final class Stream
{  
    /**
     * Constructeur privé, empeche l'intanciation de la classe statique
     */
    private function __construct() {}

    /**
     * Enregistre un gestionnaire d'URL
     *
     * @param 	string  $name Le nom du gestionnaire à enregistrer.
     * @param 	string	$class La classe qui implémente le gestionnaire
     * @return 	void
     */
    public static function register(string $name, string $class): void
    {
        if (self::exists($name)) {
            stream_wrapper_unregister($name);
        }
        stream_wrapper_register($name, $class);
    }

    /**
     * Retourne si un gestionnaire d'URL existe
     *
     * @param 	string  $name Le nom du gestionnaire à trouver.
     * @return 	bool
     */
    public static function exists(string $name): bool
    {
        return (bool) in_array($name, stream_get_wrappers());
    }
}