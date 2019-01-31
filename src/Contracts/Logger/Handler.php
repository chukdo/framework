<?php namespace Chukdo\Contracts\Logger;

/**
 * Interface de Gestionnaires de logs
 *
 * @package 	Contracts
 * @version 	1.0.0
 * @copyright 	licence MIT, Copyright (C) 2019 Domingo
 * @since 		08/01/2019
 * @author 		Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
interface Handler
{
    /**
     * Test si l'enregistrement sera géré ou non par le gestionnaire de log
     *
     * @param  array  $record
     * @return bool
     */
    public function isHandling(array $record): bool;

    /**
     * Gere un enregistrement
     *
     * @param  array  $record
     * @return bool
     */
    public function handle(array $record): bool;

    /**
     * Ajoute un processeur de modification des enregistrements de log
     *
     * @param Processor $processor
     * @return Handler
     */
    public function pushProcessor(Processor $processor): self;

    /**
     * Defini le formatteur de données.
     *
     * @param Formatter $formatter
     * @return Handler
     */
    public function setFormatter(Formatter $formatter): self;
}