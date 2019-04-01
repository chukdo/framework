<?php

namespace Chukdo\Logger\Processors;

use Chukdo\Contracts\Logger\Processor as ProcessorInterface;

/**
 * Ajoute le debug_backtrace au log.
 *
 * @version    1.0.0
 *
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 *
 * @since        08/01/2019
 *
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class BacktraceProcessor implements ProcessorInterface
{
    /**
     * @param array $record
     *
     * @return array
     */
    public function processRecord( array $record ): array
    {
        $debug                            = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $record[ 'extra' ][ 'backtrace' ] = [];

        array_shift($debug);

        foreach( $debug as $v ) {
            array_push(
                $record[ 'extra' ][ 'backtrace' ],
                '' . (isset($v[ 'file' ])
                    ? $v[ 'file' ]
                    : null) . (isset($v[ 'line' ])
                    ? '(' . $v[ 'line' ] . ') : '
                    : null) . (isset($v[ 'class' ])
                    ? $v[ 'class' ]
                    : null) . (isset($v[ 'type' ])
                    ? $v[ 'type' ]
                    : null) . (isset($v[ 'function' ])
                    ? $v[ 'function' ] . '()'
                    : null)
            );
        }

        return $record;
    }
}
