<?php

namespace Chukdo\Support;

/**
 * Singleton.
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Singleton
{
    /**
     * Tableau des instances.
     * @var object
     */
    private static $singletonInstance = null;

    /**
     * Constructeur verouillé pour ne pas etre instancié depuis l'exterieur de la classe.
     */
    private function __construct()
    {
    }

    /**
     * invoque des méthodes inaccessibles dans un contexte statique.
     * @param string $name
     * @param array  $args
     * @return mixed
     */
    public static function __callStatic( $name, $args )
    {
        return call_user_func_array([
            self::getInstance(),
            trim($name,
                '_'),
        ],
            $args);
    }

    /**
     * Méthode qui crée l'unique instance de la classe
     * si elle n'existe pas encore puis la retourne.
     * @return object Singleton $this
     */
    public static function getInstance()
    {
        if( is_null(self::$singletonInstance) ) {
            self::$singletonInstance = new static();
        }

        return self::$singletonInstance;
    }

    /**
     * Bloque le clonage de l'objet.
     */
    private function __clone()
    {
    }
}
