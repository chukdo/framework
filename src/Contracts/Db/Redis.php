<?php

namespace Chukdo\Contracts\Db;

/**
 * Interface de la base de donnée NOSQL Redis basé sur son protocole unifié.
 *
 * @version    1.0.0
 *
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 *
 * @since        08/01/2019
 *
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
interface Redis extends \Iterator, \Countable
{
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
    public function write(string $c);

    /**
     * Formate une commande Redis (protocol unifié de Redis).
     *
     * @param array $args arguments
     *
     * @return string
     */
    public function command(array $args);

    /**
     * Ecriture de commandes dans un pipeline (gain de performance).
     *
     * @param array $commands
     *
     * @return mixed
     */
    public function pipe(array $commands);

    /**
     * Retourne les informations sur le serveur Redis.
     *
     * @param string|null $key information que l'on souhaite recuperer
     *
     * @return mixed
     */
    public function info(string $key = null);

    /**
     * @param string $name
     * @param array  $args
     *
     * @return mixed
     */
    public function __call(string $name, array $args);
}
