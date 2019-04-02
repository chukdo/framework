<?php

namespace Chukdo\Json;

use SplFileObject;
use Throwable;

/**
 * Intégration d'une exception dans Json.
 *
 * @version       1.0.0
 *
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 *
 * @since         08/01/2019
 *
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class JsonException extends Json
{
    /**
     * @param Throwable $e
     *
     * @return JsonException
     */
    public function loadException( Throwable $e ): self {
        $backTrace = [];

        if( $previous = $e->getPrevious() ) {
            $e = $previous;
        }

        foreach( $e->getTrace() as $trace ) {
            $trace = new Json($trace);
            $file  = $trace->offsetGet('file');
            $line  = $trace->offsetGet('line');

            $backTrace[] = [
                'Call' => $trace->offsetGet('class') . $trace->offsetGet('type') . $trace->offsetGet('function') . '()',
                'File' => $file,
                'Line' => $line,
                'Php'  => $file && $line
                    ? $this->getCode($trace->offsetGet('file'),
                        $trace->offsetGet('line'))
                    : '',
            ];
        }

        $this->offsetSet('Error',
            $e->getMessage())
            ->offsetSet('Code',
                $e->getCode())
            ->offsetSet('File',
                $e->getFile())
            ->offsetSet('Line',
                $e->getLine())
            ->offsetSet('Php',
                $this->getCode($e->getFile(),
                    $e->getLine()))
            ->offsetSet('Trace',
                $backTrace,
                false);

        return $this;
    }

    /**
     * @param string $file
     * @param int    $line
     *
     * @return string
     */
    protected function getCode( string $file, int $line ): string {
        $code = '';
        $spl  = new SplFileObject($file);

        for( $i = -7 ; $i < 3 ; ++$i ) {
            try {
                $spl->seek($line + $i);
                $code .= ($line + $i + 1) . ($i == -1
                        ? '> '
                        : ': ') . $spl->current() . "\n";
            } catch( Throwable $e ) {
            }
        }

        $code = highlight_string('<?php ' . $code,
            true);
        $code = str_replace('&lt;?php&nbsp;',
            '',
            $code);
        $code = '<span style="line-height:0.6rem">' . $code . '</span>';

        return $code;
    }

    /**
     * @param string|null $title
     * @param string|null $code
     * @param string|null $widthFirstCol
     *
     * @return string
     */
    public function toHtml( string $title = null, string $code = null, string $widthFirstCol = null ): string {
        return parent::toHtml(($title && $code)
            ? $title . '(' . $code . ')'
            : null,
            'red',
            '45px');
    }

    /**
     * @param string|null $title
     */
    public function toConsole( string $title = null ): void {
        $table = new \cli\Table();
        $table->setHeaders([
                '%R' . strtoupper($title
                    ?: 'Exception') . '%n',
            ]);
        $table->setRenderer(new \cli\table\Ascii([ 80 ]));
        $table->display();

        $table = new \cli\Table();
        $table->setHeaders([
                '%YCode%n',
                '%YMessage%n',
                '%YFile%n',
                '%YLine%n',
            ]);
        $table->addRow([
                $this->get('Code'),
                $this->get('Message'),
                $this->get('File'),
                $this->get('Line'),
            ]);

        $table->setRenderer(new \cli\table\Ascii([
                    5,
                    30,
                    40,
                    5,
                ]));
        $table->display();

        $backTrace = $this->get('Trace');

        if( $backTrace instanceof Json ) {
            $table = new \cli\Table();
            $table->setHeaders([
                    '%YFile%n',
                    '%YLine%n',
                    '%YCall%n',
                ]);

            foreach( $backTrace as $trace ) {
                $table->addRow([
                        $trace->get('File'),
                        $trace->get('Line'),
                        $trace->get('Call'),
                    ]);
            }

            $table->setRenderer(new \cli\table\Ascii([
                        40,
                        5,
                        35,
                    ]));
            $table->display();
        }
    }
}
