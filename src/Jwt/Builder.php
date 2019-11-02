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
class Builder
{
    /**
     * @var array
     */
    protected $claims = [];

    protected $keywords = [ 'iss',
                            'iat',
                            'aud',
                            'nbf',
                            'jti', ];

    /**
     * @param string      $jwt
     * @param string      $secret
     * @param string|null $validIssuer
     * @param string|null $validAudience
     *
     * @return bool
     */
    public function parse( string $jwt, string $secret, string $validIssuer = null, string $validAudience = null ): bool
    {
        $tokenParts = $this->getTokensParts( $jwt );

        if ( Arr::empty( $tokenParts ) ) {
            return false;
        }

        $header    = $this->headerDecode( $tokenParts[ 'header' ] );
        $payload   = $this->payloadDecode( $tokenParts[ 'payload' ] );
        $signature = $this->signatureEncode( $header, $payload, $secret );

        $time = time();
        $nbf  = $payload[ 'nbf' ] ?? null;
        $exp  = $payload[ 'exp' ] ?? null;
        $iss  = $payload[ 'iss' ] ?? null;
        $aud  = (array)( $payload[ 'aud' ] ?? [] );

        $this->setAll( $payload );

        if ( $signature !== $tokenParts[ 'signature' ] ) {
            return false;
        }

        if ( $nbf && $time <= $nbf ) {
            return false;
        }

        if ( $exp && $time >= $exp ) {
            return false;
        }

        if ( $validIssuer && $iss !== $validIssuer ) {
            return false;
        }

        if ( $validAudience && !Arr::in( $validAudience, $aud ) ) {
            return false;
        }

        return true;
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

    /**
     * @param iterable $values
     *
     * @return $this
     */
    public function setAll( iterable $values ): self
    {
        foreach ( $values as $key => $value ) {
            $this->set( $key, $value );
        }

        return $this;
    }

    /**
     * @param string $key
     * @param        $value
     *
     * @return $this
     */
    public function set( string $key, $value ): self
    {
        $this->claims[ $key ] = $value;

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
     * @return string|null
     */
    public function getIssuer(): ?string
    {
        return $this->claims[ 'iss' ] ?? null;
    }

    /**
     * @param mixed ...$url
     *
     * @return $this
     */
    public function audience( ... $url ): self
    {
        if ( !isset( $this->claims[ 'aud' ] ) ) {
            $this->claims[ 'aud' ] = [];
        }

        Arr::push( $this->claims[ 'aud' ], Arr::spreadArgs( $url ), true );

        return $this;
    }

    /**
     * @return array
     */
    public function getAudience(): array
    {
        return $this->claims[ 'aud' ] ?? [];
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
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->claims[ 'jti' ] ?? null;
    }

    /**
     * @param int  $time
     * @param bool $relative
     *
     * @return $this
     */
    public function issuedAt( int $time = 0, bool $relative = true ): self
    {
        $this->claims[ 'iat' ] = ( $relative
                ? time()
                : 0 ) + $time;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getIssuedAt(): ?int
    {
        return $this->claims[ 'iat' ] ?? null;
    }

    /**
     * @param int  $time
     * @param bool $relative
     *
     * @return $this
     */
    public function expiresAt( int $time = 3600, bool $relative = true ): self
    {
        $this->claims[ 'exp' ] = ( $relative
                ? time()
                : 0 ) + $time;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getExpiresAt(): ?int
    {
        return $this->claims[ 'exp' ] ?? null;
    }

    /**
     * @param int  $time
     * @param bool $relative
     *
     * @return $this
     */
    public function validAt( int $time = 3600, bool $relative = true ): self
    {
        $this->claims[ 'nbf' ] = ( $relative
                ? time()
                : 0 ) + $time;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getValidAt(): ?int
    {
        return $this->claims[ 'nbf' ] ?? null;
    }

    /**
     * @param string $role
     *
     * @return $this
     */
    public function role( string $role ): self
    {
        $this->claims[ 'rol' ] = $role;
    }

    /**
     * @param string $client
     *
     * @return $this
     */
    public function client( string $client ): self
    {
        $this->claims[ 'clt' ] = $client;
    }

    /**
     * @return JsonInterface
     */
    public function getAll(): JsonInterface
    {
        $all = [];

        foreach ( $this->claims as $key => $value ) {
            if ( $this->get( $key ) ) {
                $all[ $key ] = $value;
            }
        }

        return new Json( $all );
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function get( string $key )
    {
        if ( !Arr::in( $key, $this->keywords ) ) {
            return $this->claims[ $key ];
        }

        return null;
    }

    /**
     * @param string $secret
     * @param string $crypto
     *
     * @return string
     */
    public function token( string $secret, string $crypto = 'HS256' ): string
    {
    }
}