<?php namespace Chukdo\Logger\Formatters;

use \Chukdo\Contracts\Logger\Formatter as FormatterInterface;
use \Chukdo\Json\Json;

/**
 * Formatter de log par defaut (json)
 *
 * @copyright 	licence MIT, Copyright (C) 2015 Domingo
 * @author 		Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class DefaultFormatter implements FormatterInterface
{

    /**
     * @var bool
     */
    protected $pretty;

    /**
     * Constructeur
     *
     * @param   bool    $pretty
     */
    public function __construct($pretty = false)
    {
        $this->pretty = $pretty;
    }

    /**
     * Formatte un enregistrement
     *
     * @param  array  $record
     * @return mixed
     */
    public function formatRecord(array $record)
    {
        $json = new Json(array(
            'date'      => date('d/m/Y H:i:s', $record['time']),
            'name'      => $record['channel'].'.'.$record['levelname'],
            'message'   => str_replace(array("\r\n", "\r", "\n"), ' ', $record['message']),
            'context'   => $record['context'],
            'extra'     => $record['extra'],
            'time'      => $record['time'],
            'level'     => $record['level']
        ));

        return $json->stringify($this->pretty);
    }
}