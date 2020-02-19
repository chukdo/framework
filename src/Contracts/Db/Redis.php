<?php

namespace Chukdo\Contracts\Db;

use Countable;
use Iterator;

/**
 * Interface de la base de donnée NOSQL Redis basé sur son protocole unifié.
 *
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
interface Redis extends Iterator, Countable
{
    /**
     *
     */
    public function __destruct();

    /**
     * Lecture d'une reponse du serveur.
     *
     * @return mixed
     */
    public function read();

    /**
     * Ecriture d'une commande basé sur le protocol unifié de Redis.
     *
     * @param string $c command
     */
    public function write( string $c );

    /**
     * Formate une commande Redis (protocol unifié de Redis).
     *
     * @param array $args arguments
     *
     * @return string
     */
    public function command( array $args );

    /**
     * Ecriture de commandes dans un pipeline (gain de performance).
     *
     * @param array $commands
     *
     * @return mixed
     */
    public function pipe( array $commands );

    /**
     * Retourne les informations sur le serveur Redis.
     *
     * @param string|null $key information que l'on souhaite recuperer
     *
     * @return mixed
     */
    public function info( string $key = null );

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function exists( string $key );

    /**
     * @param int $key
     *
     * @return mixed
     */
    public function expire( int $key );

    /**
     * @param string $key
     * @param        $value
     *
     * @return mixed
     */
    public function set( string $key, $value );

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function get( string $key );

    /**
     * @param string $key
     * @param int    $offset
     * @param int    $length
     *
     * @return mixed
     */
    public function getRange( string $key, int $offset, int $length );

    /**
     * @param string $key
     * @param int    $offset
     * @param string $content
     *
     * @return mixed
     */
    public function setRange( string $key, int $offset, string $content );

    /**
     * @param string $key
     * @param string $value
     *
     * @return mixed
     */
    public function append( string $key, string $value );

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function strlen( string $key );

    /**
     * @param string $key
     * @param        $newKey
     *
     * @return mixed
     */
    public function rename( string $key, $newKey );

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function keys( string $key );

    /**
     * @param string $name
     * @param string $key
     * @param string $value
     *
     * @return mixed
     */
    public function hset( string $name, string $key, string $value );

    /**
     * @param string $name
     * @param string $key
     *
     * @return mixed
     */
    public function hget( string $name, string $key );

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function del( string $key );

    /**
     * @param string $key
     * @param string $value
     *
     * @return mixed
     */
    public function rpush( string $key, string $value );

    /**
     * @param string $name
     * @param array  $args
     *
     * @return mixed
     */
    public function __call( string $name, array $args );
}
