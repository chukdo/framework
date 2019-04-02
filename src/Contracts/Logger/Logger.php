<?php

namespace Chukdo\Contracts\Logger;

/**
 * Interface de Gestion des logs.
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
interface Logger
{
    /**
     * @param string $message
     * @param array  $context
     * @return bool
     */
    public function alert( string $message, array $context = [] ): bool;

    /**
     * @param string $message
     * @param array  $context
     * @return bool
     */
    public function critical( string $message, array $context = [] ): bool;

    /**
     * @param string $message
     * @param array  $context
     * @return bool
     */
    public function emergency( string $message, array $context = [] ): bool;

    /**
     * @param string $message
     * @param array  $context
     * @return bool
     */
    public function error( string $message, array $context = [] ): bool;

    /**
     * @param string $message
     * @param array  $context
     * @return bool
     */
    public function warning( string $message, array $context = [] ): bool;

    /**
     * @param string $message
     * @param array  $context
     * @return bool
     */
    public function notice( string $message, array $context = [] ): bool;

    /**
     * @param string $message
     * @param array  $context
     * @return bool
     */
    public function info( string $message, array $context = [] ): bool;

    /**
     * @param string $message
     * @param array  $context
     * @return bool
     */
    public function debug( string $message, array $context = [] ): bool;

    /**
     * @param int    $level
     * @param string $message
     * @param array  $context
     * @return bool
     */
    public function log( int $level, string $message, array $context = [] ): bool;
}
