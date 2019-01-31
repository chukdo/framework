<?php namespace Chukdo\Logger\Processors;

use \Chukdo\Contracts\Logger\Processor as ProcessorInterface;

/**
 * Ajoute le debug_backtrace au log
 *
 * @copyright 	licence MIT, Copyright (C) 2015 Domingo
 * @author 		Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class BacktraceProcessor implements ProcessorInterface
{
    /**
     * Modifie / ajoute des données à un enregistrement
     *
     * @param  array  $record
     * @return array
     */
    public function processRecord(array $record)
    {
        $debug = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $record['extra']['backtrace'] = array();

        array_shift($debug);

        foreach ($debug as $v) {
            array_push($record['extra']['backtrace'], ''
                .(isset($v['file']) ? $v['file'] : null)
                .(isset($v['line']) ? '('.$v['line'].') : ' : null)
                .(isset($v['class']) ? $v['class'] : null)
                .(isset($v['type']) ? $v['type'] : null)
                .(isset($v['function']) ? $v['function'].'()' : null));
        }

        return $record;
    }
}