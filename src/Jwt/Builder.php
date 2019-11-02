<?php

namespace Chukdo\Jwt;

use Chukdo\Contracts\Json\Json as JsonInterface;
use Chukdo\Json\Json;
use Chukdo\Helper\Arr;
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
    use TraitEncode;

    /**
     * @var array
     */
    protected $claims = [];

    /**
     * @var string
     */
    protected $secret;

    protected $keywords = [ 'iss',
                            'iat',
                            'aud',
                            'nbf',
                            'jti', ];

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
     * @return string
     */
    public function getSecret(): string
    {
        return $this->secret;
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
     * @return string
     */
    public function token(): string
    {
        // b64 header sujet ALG
        // b64 claim
        // encode package
    }
}