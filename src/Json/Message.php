<?php

namespace Chukdo\Json;

use Chukdo\Helper\Cli;
use Chukdo\Helper\To;
use League\CLImate\CLImate;

/**
 * Gestion des messages.
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Message extends Json
{
    /**
     * @var string
     */
    protected string $name;

    /**
     * Message constructor.
     *
     * @param string $name
     */
    public function __construct( string $name )
    {
        $this->name = $name;
        parent::__construct( [] );
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'result'   => $this->name,
            'messages' => $this->getArrayCopy(),
        ];
    }

    /**
     * @param string|null $title
     * @param string      $color
     *
     * @return string
     */
    public function toConsole( string $title = null, string $color = '' ): string
    {
        if ( !Cli::runningInConsole() ) {
            throw new JsonException( 'You can call json::toConsole only in CLI mode.' );
        }

        $title   = ucfirst( $title ?? $this->name );
        $climate = new CLImate();
        $climate->output->defaultTo( 'buffer' );
        $climate->border();

        switch ( strtolower( $color ) ) {
            case 'red' :
                $climate->red( $title );
                break;
            case 'blue' :
                $climate->blue( $title );
                break;
            case 'green':
                $climate->green( $title );
                break;
        }

        $climate->border();
        $padding = $climate->padding( 15 );

        foreach ( $this as $k => $v ) {
            $padding->label( $k )
                    ->result( $v );
        }

        $climate->border();

        return $climate->output->get( 'buffer' )
                               ->get();
    }

    /**
     * @param string|null $title
     * @param string|null $color
     *
     * @return string
     */
    public function toHtml( string $title = null, string $color = null ): string
    {
        return To::html( $this, $title
            ?: $this->name, $color );
    }
}
