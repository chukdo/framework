<?php

namespace Chukdo\Jwt;

use Chukdo\Helper\Arr;
use Chukdo\Helper\To;

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
    use TraitEncode;

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

        if ( Arr::empty( $tokenParts ) ) {
            throw new JwtException( 'The token is not a valid JWT' );
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
            throw new JwtException( 'The JWT signature is not a valid' );
        }

        if ( $nbf && $time <= $nbf ) {
            throw new JwtException( 'The JWT is not yet valid' );
        }

        if ( $exp && $time >= $exp ) {
            throw new JwtException( 'The JWT has expired' );
        }

        if ( $this->claims[ 'jti' ] && $jti !== $this->claims[ 'jti' ] ) {
            throw new JwtException( 'The JWT claim:jti is not a valid' );
        }

        if ( $this->claims[ 'iss' ] && $iss !== $this->claims[ 'iss' ] ) {
            throw new JwtException( 'The JWT claim:iss is not a valid' );
        }

        if ( $this->claims[ 'aud' ] && !Arr::in( $this->claims[ 'aud' ], $aud ) ) {
            throw new JwtException( 'The JWT claim:aud is not a valid' );
        }

        $builder = new Builder();
        $builder->setAll( $payload );
        $builder->secret( $this->secret );

        return $builder;
    }

    /**
     * @param string $jwt
     *
     * @return $this
     */
    public function token( string $jwt ): self
    {
        $this->jwt = $jwt;

        return $this;
    }

    /**
     * @param string $secret
     *
     * @return $this
     */
    public function secret( string $secret ): self
    {
        $this->secret = $secret;

        return $this;
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
     * @param string $header
     *
     * @return array
     */
    protected function headerDecode( string $header ): array
    {
        return $this->jsonDecode( To::base64Decode( $header ) );
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
}