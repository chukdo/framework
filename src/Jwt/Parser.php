<?php

namespace Chukdo\Jwt;

use Chukdo\Contracts\Json\Json as JsonInterface;
use Chukdo\Helper\Arr;
use Chukdo\Helper\To;
use iterable;

/**
 * Json Web Token.
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Parser
{
    /**
     * @var array
     */
    protected $claims = [];

    /**
     * @var string
     */
    protected $secret;

    /**
     * @var string
     */
    protected $jwt;

    /**
     * @return Builder
     */
    public function parse(): Builder
    {
        $tokenParts = $this->getTokensParts( $this->jwt );
        $payload    = $this->payloadDecode( $tokenParts[ 'payload' ] ?? [] );
        $builder    = new Builder();
        $builder->setAll( $payload );
        $builder->secret( $this->secret );

        return $builder;
    }

    /**
     * @return bool
     */
    public function signedToken(): bool
    {
        $tokenParts = $this->getTokensParts( $this->jwt );

        if ( Arr::empty( $tokenParts ) ) {
            return false;
        }

        $header    = $this->headerDecode( $tokenParts[ 'header' ] );
        $payload   = $this->payloadDecode( $tokenParts[ 'payload' ] );
        $signature = $this->signatureEncode( $header, $payload, $this->secret );

        $time = time();
        $nbf  = $payload[ 'nbf' ] ?? null;
        $exp  = $payload[ 'exp' ] ?? null;
        $iss  = $payload[ 'iss' ] ?? null;
        $jti  = $payload[ 'jti' ] ?? null;
        $aud  = (array)( $payload[ 'aud' ] ?? [] );

        if ( $signature !== $tokenParts[ 'signature' ] ) {
            return false;
        }

        if ( $nbf && $time <= $nbf ) {
            return false;
        }

        if ( $exp && $time >= $exp ) {
            return false;
        }

        if ( $this->claims[ 'jti' ] && $jti !== $this->claims[ 'jti' ] ) {
            return false;
        }

        if ( $this->claims[ 'iss' ] && $iss !== $this->claims[ 'iss' ] ) {
            return false;
        }

        if ( $this->claims[ 'aud' ] && !Arr::in( $this->claims[ 'aud' ], $aud ) ) {
            return false;
        }

        return true;
    }

    /**
     * @param string $jwt
     *
     * @return $this
     */
    public function token( string $jwt ): self
    {
        $this->jwt = $jwt;
    }

    /**
     * @param string $secret
     *
     * @return $this
     */
    public function secret( string $secret ): self
    {
        $this->secret = $secret;
    }

    /**
     * @param string $url
     *
     * @return $this
     */
    public function issuer( string $url ): self
    {
        $this->claims[ 'iss' ] = $url;

        return $this;
    }

    /**
     * @param string $url
     *
     * @return $this
     */
    public function audience( string $url ): self
    {
        $this->claims[ 'aud' ] = $url;

        return $this;
    }

    /**
     * @param string $id
     *
     * @return $this
     */
    public function id( string $id ): self
    {
        $this->claims[ 'jti' ] = $id;

        return $this;
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
     * @param string $value
     *
     * @return mixed
     */
    protected function jsonDecode( string $value )
    {
        return json_decode( $value, true, 512, JSON_THROW_ON_ERROR );
    }

    /**
     * @param string $jwt
     *
     * @return array
     */
    protected function getTokensParts( string $jwt ): array
    {
        $tokenParts = explode( '.', $jwt );

        if ( count( $tokenParts ) !== 3 ) {
            return [];
        }

        return [ 'header'    => $tokenParts[ 0 ],
                 'payload'   => $tokenParts[ 1 ],
                 'signature' => $tokenParts[ 2 ], ];
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
     * @param string $header
     *
     * @return array
     */
    protected function headerDecode( string $header ): array
    {
        return $this->jsonDecode( To::base64Decode( $header ) );
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

    /**
     * @param string $payload
     *
     * @return array
     */
    protected function payloadDecode( string $payload ): array
    {
        return $this->jsonDecode( To::base64Decode( $payload ) );
    }

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
            case 'H256' :
                return To::base64UrlEncode( hash_hmac( 'sha256', $headerEncoded . '.' . $payLoadEncoded, $secret, true ) );
        }

        return null;
    }
}