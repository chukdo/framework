<?php

namespace Chukdo\Helper;

use Chukdo\Http\Url as UrlClass;

/**
 * Gestion des Url.
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
final class Url
{
    /**
     * HttpRequest constructor.
     */
    private function __construct()
    {
    }

    /**
     * @param string $url
     * @param array  $params
     *
     * @return string
     */
    public static function build( string $url, array $params = [] ): string
    {
        $urlBuilder = new UrlClass( $url, 'https' );

        foreach ( $params as $key => $value ) {
            $urlBuilder->setInput( $key, $value );
        }

        return $urlBuilder->buildUrl();
    }
}