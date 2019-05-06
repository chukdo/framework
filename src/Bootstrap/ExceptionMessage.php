<?php

namespace Chukdo\Bootstrap;

use Chukdo\Helper\Cli;
use Chukdo\Helper\Http;
use Chukdo\Helper\Str;
use Chukdo\Helper\To;
use Chukdo\Http\Response;
use Chukdo\Json\Json;
use Chukdo\Xml\Xml;
use SplFileObject;
use Throwable;

/**
 * Message d'exception
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class ExceptionMessage
{
    /**
     * @var array
     */
    protected $message = [];

    /**
     * @var int
     */
    protected $env;

    /**
     * ExceptionMessage constructor.
     * @param Throwable $e
     * @param int       $env
     */
    public function __construct( Throwable $e, int $env = 0 )
    {
        $this->env = $env;
        $backTrace = [];

        if ( $previous = $e->getPrevious() ) {
            $e = $previous;
        }

        foreach ( $e->getTrace() as $trace ) {
            $trace = array_merge([
                'file'     => null,
                'line'     => null,
                'class'    => null,
                'type'     => null,
                'function' => null,
            ],
                $trace);
            $file  = $trace[ 'file' ];
            $line  = $trace[ 'line' ];

            $backTrace[] = [
                'Call' => $trace[ 'class' ] . $trace[ 'type' ] . $trace[ 'function' ] . '()',
                'File' => $file,
                'Line' => $line,
                'Php'  => $file && $line
                    ? $this->getCode($trace[ 'file' ], $trace[ 'line' ])
                    : '',
            ];
        }

        $this->message[ 'Call' ]  = get_class($e);
        $this->message[ 'Error' ] = $e->getMessage();
        $this->message[ 'Code' ]  = $e->getCode();
        $this->message[ 'File' ]  = $e->getFile();
        $this->message[ 'Line' ]  = $e->getLine();
        $this->message[ 'Php' ]   = $this->getCode($e->getFile(), $e->getLine());
        $this->message[ 'Trace' ] = $backTrace;
    }

    /**
     * @param string $file
     * @param int    $line
     * @return string
     */
    protected function getCode( string $file, int $line ): string
    {
        $code = '';
        $spl  = new SplFileObject($file);

        for ( $i = -7 ; $i < 3 ; ++$i ) {
            try {
                $spl->seek($line + $i);
                $code .= ( $line + $i + 1 ) . ( $i == -1
                        ? '> '
                        : ': ' ) . $spl->current() . "\n";
            } catch ( Throwable $e ) {
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
     *
     */
    public function render(): void
    {
        if ( Cli::runningInConsole() ) {
            $this->renderForConsole();
        }
        else {
            $this->renderForResponse();
        }
    }

    /**
     * @param string $title
     */
    public function renderForConsole(): void
    {
        $table = new \cli\Table();
        $table->setHeaders([
            '%R' . strtoupper($this->message[ 'Call' ]
                ?: 'Exception') . '%n',
        ]);
        $table->setRenderer(new \cli\table\Ascii([ 89 ]));
        $table->display();

        $table = new \cli\Table();
        $table->setHeaders([
            '%YCode%n',
            '%YMessage%n',
            '%YFile%n',
            '%YLine%n',
        ]);
        $table->addRow([
            $this->message[ 'Code' ],
            $this->message[ 'Error' ],
            $this->message[ 'File' ],
            $this->message[ 'Line' ],
        ]);

        $table->setRenderer(new \cli\table\Ascii([
            5,
            30,
            40,
            5,
        ]));
        $table->display();

        $backTrace = $this->message[ 'Trace' ];

        if ( is_array($backTrace) ) {
            $table = new \cli\Table();
            $table->setHeaders([
                '%YFile%n',
                '%YLine%n',
                '%YCall%n',
            ]);

            foreach ( $backTrace as $trace ) {
                $table->addRow([
                    $trace[ 'File' ],
                    $trace[ 'Line' ],
                    $trace[ 'Call' ],
                ]);
            }

            $table->setRenderer(new \cli\table\Ascii([
                40,
                5,
                38,
            ]));
            $table->display();
        }

        exit;
    }

    /**
     *
     */
    public function renderForResponse(): void
    {
        $render   = Str::extension(Http::server('SCRIPT_URI'));
        $response = new Response();

        /* Dev mode */
        if ( $this->env != 0 ) {
            $this->message = [ 'Error' => 'Error happened' ];
        }

        switch ( $render ) {
            case 'xml':
                $contentType = Http::mimeContentType('xml');
                $content     = ( new Xml() )->import($this->message)
                    ->toXml()
                    ->toXmlString();

                break;
            case 'json':
                $contentType = Http::mimeContentType('json');
                $content     = ( new Json($this->message) )->toJson(true);
                break;
            case 'html':
            default:
                $title = $this->message[ 'Call' ];
                unset($this->message[ 'Call' ]);

                $contentType = Http::mimeContentType('html');
                $content     = To::html($this->message, $title, '#B30000');
        }

        $response->status(500)
            ->header('Content-Type', $contentType . '; charset=utf-8')
            ->content($content)
            ->send()
            ->end();
    }
}
