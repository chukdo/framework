<?php

namespace Chukdo\Jwt;

use Chukdo\Helper\To;

/**
 * Json Web Token.
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
trait TraitEncode
{
    /**
     * @param array  $header
     * @param array  $payload
     * @param string $secret
     *
     * @return string|null
     */
    protected function signatureEncode( array $header, array $payload, string $secret ): ?string
    {
        $alg            = $header[ 'alg' ] ?? null;
        $headerEncoded  = $this->headerEncode( $header );
        $payLoadEncoded = $this->payloadEncode( $payload );

        switch ( $alg ) {
            case 'HS256' :
                return To::base64UrlEncode( hash_hmac( 'sha256', $headerEncoded . '.' . $payLoadEncoded, $secret, true ) );
            case 'HS384' :
                return To::base64UrlEncode( hash_hmac( 'sha384', $headerEncoded . '.' . $payLoadEncoded, $secret, true ) );
            case 'HS512' :
                return To::base64UrlEncode( hash_hmac( 'sha512', $headerEncoded . '.' . $payLoadEncoded, $secret, true ) );
        }

        return null;
    }

    /**
     * @param array $header
     *
     * @return string
     */
    protected function headerEncode( array $header ): string
    {
        return To::base64UrlEncode( $this->jsonEncode( $header ) );
    }

    /**
     * @param $value
     *
     * @return string
     */
    protected function jsonEncode( $value ): string
    {
        return json_encode( $value, JSON_THROW_ON_ERROR, 512 );
    }

    /**
     * @param array $payload
     *
     * @return string
     */
    protected function payloadEncode( array $payload ): string
    {
        return To::base64UrlEncode( $this->jsonEncode( $payload ) );
    }
}