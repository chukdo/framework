<?php namespace Chukdo\Support;

/**
 * Singleton
 *
 * @package    support
 * @version    1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Singleton
{
    /**
     * Tableau des instances
     *
     * @var object
     */
    private static $_instance = null;

    /**
     * Constructeur verouillé pour ne pas etre instancié depuis l'exterieur de la classe
     */
    private function __construct()
    {
    }

    /**
     * Méthode qui crée l'unique instance de la classe
     * si elle n'existe pas encore puis la retourne.
     *
     * @return    object Singleton $this
     */
    public static function getInstance()
    {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new static();
        }

        return self::$_instance;
    }

    /**
     * Bloque le clonage de l'objet
     */
    final private function __clone()
    {
    }

    /**
     * invoque des méthodes inaccessibles dans un contexte statique.
     *
     * @param string $name
     * @param array $args
     *
     * @return mixed
     */
    public static function __callStatic( $name, $args )
    {
        return call_user_func_array( [ self::getInstance(), trim( $name, '_' ) ], $args );
    }
}