<?php

namespace Chukdo\Jwt;

use Chukdo\Contracts\Json\Json as JsonInterface;
use Chukdo\Json\Json;
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
    protected $header = [];

    /**
     * @var array
     */
    protected $payload = [];

    /**
     * @var string|null
     */
    protected $signature = null;

    /**
     * @var array
     */
    protected $claims = [];

    /**
     * @var string|null
     */
    protected $err = null;

    /**
     * @param string $jwt
     *
     * @return $this
     */
    public function parse( string $jwt ): self
    {
        $tokenParts = $this->getTokensParts( $jwt );

        if ( Arr::empty( $tokenParts ) ) {
            return $this;
        }

        $this->header    = $this->headerDecode( $tokenParts[ 'header' ] );
        $this->payload   = $this->payloadDecode( $tokenParts[ 'payload' ] );
        $this->signature = $tokenParts[ 'signature' ];

        return $this;
    }

    /**
     * @param string $secret
     *
     * @return bool
     */
    public function signed( string $secret ): bool
    {
        $time      = time();
        $signature = $this->signatureEncode( $this->header, $this->payload, $secret );
        $nbf       = $this->payload[ 'nbf' ] ?? null;
        $exp       = $this->payload[ 'exp' ] ?? null;
        $iss       = $this->payload[ 'iss' ] ?? null;
        $jti       = $this->payload[ 'jti' ] ?? null;
        $aud       = (array)( $this->payload[ 'aud' ] ?? [] );

        if ( $signature !== $this->signature ) {
            $this->err = 'The JWT signature is not a valid';
            return false;
        }

        if ( $nbf && $time <= $nbf ) {
            $this->err = 'The JWT is not yet valid';
            return false;
        }

        if ( $exp && $time >= $exp ) {
            $this->err = 'The JWT has expired';
            return false;
        }

        if ( isset( $this->claims[ 'jti' ] ) && $jti !== $this->claims[ 'jti' ] ) {
            $this->err = 'The JWT claim:jti is not a valid';
            return false;
        }

        if ( isset( $this->claims[ 'iss' ] ) && $iss !== $this->claims[ 'iss' ] ) {
            $this->err = 'The JWT claim:iss is not a valid';
            return false;
        }

        if ( isset( $this->claims[ 'aud' ] ) && !Arr::in( $this->claims[ 'aud' ], $aud ) ) {
            $this->err = 'The JWT claim:aud is not a valid';
            return false;
        }

        return true;
    }

    public function builder(): Builder
    {
        $builder = new Builder();
        $builder->headers( $this->header )
                ->setAll( $this->payload );

        return $builder;
    }

    /**
     * @return string|null
     */
    public function error(): ?string
    {
        return $this->err;
    }

    /**
     * @return JsonInterface
     */
    public function header(): JsonInterface
    {
        return new Json( $this->header );
    }

    /**
     * @return JsonInterface
     */
    public function payload(): JsonInterface
    {
        return new Json( $this->payload );
    }

    /**
     * @return string|null
     */
    public function signature(): ?string
    {
        return $this->signature;
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