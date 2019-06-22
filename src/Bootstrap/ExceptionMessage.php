<?php

namespace Chukdo\Bootstrap;

use Chukdo\Helper\Http;
use Chukdo\Helper\HttpRequest;
use Chukdo\Helper\To;
use Chukdo\Http\Response;
use Chukdo\Json\Json;
use Chukdo\Xml\Xml;
use League\CLImate\CLImate;
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
     * @param string    $env
     */
    public function __construct( Throwable $e, string $env = '0' )
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
        $render   = HttpRequest::render();
        $response = new Response();

        /* Dev mode */
        if ( $this->env != 0 ) {
            $this->message = [ 'Error' => 'Error happened' ];
        }

        switch ( $render ) {
            case 'cli':
                $contentType = Http::mimeContentType('text');
                $content     = $this->renderCli($this->message);
                break;
            case 'xml':
                $contentType = Http::mimeContentType('xml');
                $content     = $this->renderXml($this->message);
                break;
            case 'json':
                $contentType = Http::mimeContentType('json');
                $content     = $this->renderJson($this->message);
                break;
            case 'html':
            default:
                $contentType = Http::mimeContentType('html');
                $content     = $this->renderHtml($this->message);;
        }

        try {
            $response->status(500)
                ->header('Content-Type', $contentType . '; charset=utf-8')
                ->content($content)
                ->send()
                ->end();
        } catch (Throwable $e) {
            die($content);
        }

    }

    /**
     * @param array $message
     * @return string
     */
    protected function renderXml(array $message): string
    {
        return ( new Xml() )->import($message)
            ->toXml()
            ->toXmlString();
    }

    /**
     * @param array $message
     * @return string
     */
    protected function renderJson(array $message): string
    {
        return ( new Json($message) )->toJson(true);
    }

    /**
     * @param array $message
     * @return string
     */
    protected function renderHtml(array $message): string
    {
        $title = $message[ 'Call' ];
        unset($message[ 'Call' ]);

        return To::html($message, $title, '#B30000');
    }

    /**
     * @param array $message
     * @return string
     */
    protected function renderCli(array $message): string
    {
        $climate = new CLImate();
        $climate->output->defaultTo('buffer');
        $climate->border();
        $climate->red()
            ->out(strtoupper($message[ 'Call' ]
                ?: 'Exception'));
        $climate->border();
        $padding = $climate->padding(7);
        $padding->label('Code')
            ->result($message[ 'Code' ]);
        $padding->label('Message')
            ->result($message[ 'Error' ]);
        $padding->label('File')
            ->result($message[ 'File' ]);
        $padding->label('Line')
            ->result($message[ 'Line' ]);

        $backTrace = $message[ 'Trace' ];

        if ( is_array($backTrace) ) {
            foreach ( $backTrace as $k => $trace ) {
                unset($backTrace[ $k ][ 'Php' ]);
            }
            $padding->label('Trace');
            $climate->json($backTrace);
            $climate->border();
        }

        return $climate->output->get('buffer')
            ->get();
    }
}
