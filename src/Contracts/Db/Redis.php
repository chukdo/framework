<?php namespace Chukdo\Contracts\Db;

/**
 * Interface de la base de donnée NOSQL Redis basé sur son protocole unifié
 *
 * @package 	Contracts
 * @version 	1.0.0
 * @copyright 	licence MIT, Copyright (C) 2019 Domingo
 * @since 		08/01/2019
 * @author 		Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Interface Redis extends \Iterator, \Countable
{
    /**
     * Lecture d'une reponse du serveur
     *
     * @return 	mixed
     */
    public function read();

    /**
     * Ecriture d'une commande basé sur le protocol unifié de Redis
     *
     * @param 	string 	$c command
     * @return void
     */
    public function write(string $c);

    /**
     * Formate une commande Redis (protocol unifié de Redis)
     *
     * @param 	array 	$args arguments
     * @return 	string
     */
    public function command(array $args);

    /**
     * Ecriture de commandes dans un pipeline (gain de performance)
     *
     * @param 	array 	$commands
     * @return 	mixed
     */
    public function pipe(array $commands);

    /**
     * Retourne les informations sur le serveur Redis
     *
     * @param 	string 	$key    information que l'on souhaite recuperer
     * @return 	mixed 	string si $key defini array sinon
     */
    public function info(string $key = '');

    /**
     * Appel des commandes redis au travers de la surcharge magique de PHP
     *
     * @param 	string	$name de la fonction redis à invoquer
     * @param 	array 	$args arguments à passer à la fonction
     * @return	 mixed
     */
    public function __call(string $name, array $args);
}