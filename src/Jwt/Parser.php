<?php

namespace Chukdo\Jwt;

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
    protected array $header = [];

    /**
     * @var array
     */
    protected array $payload = [];

    /**
     * @var string|null
     */
    protected ?string $signature = null;

    /**
     * @var array
     */
    protected array $claims = [];

    /**
     * @var string|null
     */
    protected ?string $err = null;

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

        return [
            'header'    => $tokenParts[ 0 ],
            'payload'   => $tokenParts[ 1 ],
            'signature' => $tokenParts[ 2 ],
        ];
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
     * @param string $value
     *
     * @return mixed
     */
    protected function jsonDecode( string $value )
    {
        return json_decode( $value, true, 512, JSON_THROW_ON_ERROR );
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
     * @param string   $secret
     * @param int|null $time
     *
     * @return bool
     */
    public function hasValidToken( string $secret, int $time = null ): bool
    {
        if ( !$this->hasValidSignature( $secret ) ) {
            $this->err = 'The JWT signature is not a valid';

            return false;
        }

        if ( !$this->hasValidStart( $time ) ) {
            $this->err = 'The JWT is not yet valid';

            return false;
        }

        if ( !$this->hasValidExpired( $time ) ) {
            $this->err = 'The JWT has expired';

            return false;
        }

        if ( !$this->hasValidId() ) {
            $this->err = 'The JWT claim:jti is not a valid';

            return false;
        }

        if ( !$this->hasValidIssuer() ) {
            $this->err = 'The JWT claim:iss is not a valid';

            return false;
        }

        if ( !$this->hasValidAudience() ) {
            $this->err = 'The JWT claim:aud is not a valid';

            return false;
        }

        return true;
    }

    /**
     * @param string $secret
     *
     * @return bool
     */
    public function hasValidSignature( string $secret ): bool
    {
        $signature = $this->signatureEncode( $this->header, $this->payload, $secret );

        return !( $signature !== $this->signature );
    }

    /**
     * @param int|null $time
     *
     * @return bool
     */
    public function hasValidStart( int $time = null ): bool
    {
        $nbf  = $this->payload[ 'nbf' ] ?? null;
        $time ??= time();

        return !( $nbf && $time >= $nbf );
    }

    /**
     * @param int|null $time
     *
     * @return bool
     */
    public function hasValidExpired( int $time = null ): bool
    {
        $exp  = $this->payload[ 'exp' ] ?? null;
        $time ??= time();

        return !( $exp && $time >= $exp );
    }

    /**
     * @return bool
     */
    public function hasValidId(): bool
    {
        $jti = $this->payload[ 'jti' ] ?? null;

        return !( isset( $this->claims[ 'jti' ] ) && $jti !== $this->claims[ 'jti' ] );
    }

    /**
     * @return bool
     */
    public function hasValidIssuer(): bool
    {
        $iss = $this->payload[ 'iss' ] ?? null;

        return !( isset( $this->claims[ 'iss' ] ) && $iss !== $this->claims[ 'iss' ] );
    }

    /**
     * @return bool
     */
    public function hasValidAudience(): bool
    {
        $aud = $this->payload[ 'aud' ] ?? null;

        return !( isset( $this->claims[ 'aud' ] ) && $aud !== $this->claims[ 'aud' ] );
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
     * @return Json
     */
    public function header(): Json
    {
        return new Json( $this->header );
    }

    /**
     * @return Json
     */
    public function payload(): Json
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
}