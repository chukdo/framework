<?php namespace Chukdo\Logger\Formatters;

use Chukdo\Contracts\Logger\Formatter as FormatterInterface;
use Chukdo\Json\Json;

/**
 * Formatter de log par defaut
 *
 * @package 	Logger
 * @version 	1.0.0
 * @copyright 	licence MIT, Copyright (C) 2019 Domingo
 * @since 		08/01/2019
 * @author 		Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class NullFormatter implements FormatterInterface
{
    /**
     * @param array $record
     * @return mixed
     */
    public function formatRecord(array $record)
    {
        return $record;
    }
}