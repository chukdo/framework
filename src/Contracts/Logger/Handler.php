<?php

namespace Chukdo\Contracts\Logger;

/**
 * Interface de Gestionnaires de logs.
 *
 * @version       1.0.0
 *
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 *
 * @since         08/01/2019
 *
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
interface Handler
{
    /**
     * @param array $record
     *
     * @return bool
     */
    public function isHandling( array $record ): bool;

    /**
     * @param array $record
     *
     * @return bool
     */
    public function handle( array $record ): bool;

    /**
     * @param Processor $processor
     *
     * @return Handler
     */
    public function pushProcessor( Processor $processor ): Handler;

    /**
     * @param Formatter $formatter
     *
     * @return Handler
     */
    public function setFormatter( Formatter $formatter ): Handler;
}
