<?php namespace Chukdo\Contracts\Logger;

/**
 * Interface de Gestion des logs
 *
 * @package 	Contracts
 * @version 	1.0.0
 * @copyright 	licence MIT, Copyright (C) 2019 Domingo
 * @since 		08/01/2019
 * @author 		Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
interface Logger
{

    /**
     * Ajoute un enregistrement d'alerte dans le journal
     *
     * @param  string  $message
     * @param  array  $context
     * @return void
     */
    public function alert(string $message, array $context = []): void;

    /**
     * Ajoute un enregistrement critique dans le journal
     *
     * @param  string  $message
     * @param  array  $context
     * @return void
     */
    public function critical(string $message, array $context = []): void;

    /**
     * Ajoute un enregistrement d'erreur dans le journal
     *
     * @param  string  $message
     * @param  array  $context
     * @return void
     */
    public function error(string $message, array $context = []): void;

    /**
     * Ajoute un enregistrement d'avertissement dans le journal
     *
     * @param  string  $message
     * @param  array  $context
     * @return void
     */
    public function warning(string $message, array $context = []): void;

    /**
     * Ajoute un enregistrement de notice dans le journal
     *
     * @param  string  $message
     * @param  array  $context
     * @return void
     */
    public function notice(string $message, array $context = []): void;

    /**
     * Ajoute un enregistrement d'information dans le journal
     *
     * @param  string  $message
     * @param  array  $context
     * @return void
     */
    public function info(string $message, array $context = []): void;

    /**
     * Ajoute un enregistrement de debug dans le journal
     *
     * @param  string  $message
     * @param  array  $context
     * @return void
     */
    public function debug(string $message, array $context = []): void;

    /**
     * Ajoute un enregistrement dans le journal
     *
     * @param  string  $level
     * @param  string  $message
     * @param  array  $context
     * @return void
     */
    public function log(string $level, $message, array $context = []): void;
}