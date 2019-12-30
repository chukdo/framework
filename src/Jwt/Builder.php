<?php

namespace Chukdo\Jwt;

use Chukdo\Json\Json;
use Chukdo\Helper\Arr;

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
    protected array $claims = [];

    /**
     * @var array
     */
    protected array $headers = [ 'typ' => 'JWT' ];

    /**
     * @var array
     */
    protected array $keywords = [ 'iss',
                                  'iat',
                                  'aud',
                                  'nbf',
                                  'jti',
                                  'sub' ];

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
     * @param string $subject
     *
     * @return $this
     */
    public function subject( string $subject ): self
    {
        $this->claims[ 'sub' ] = $subject;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSubject(): ?string
    {
        return $this->claims[ 'sub' ] ?? null;
    }

    /**
     * @param mixed ...$url
     *
     * @return $this
     */
    public function audience( ...$url ): self
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
    public function startAt( int $time = 3600, bool $relative = true ): self
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
     * @return Json
     */
    public function claims(): Json
    {
        $all = [];

        foreach ( $this->claims as $key => $value ) {
            if ( Arr::in( $key, $this->keywords ) ) {
                $all[ $key ] = $value;
            }
        }

        return new Json( $all );
    }

    /**
     * @return Json
     */
    public function all(): Json
    {
        $all = [];

        foreach ( $this->claims as $key => $value ) {
            if ( !Arr::in( $key, $this->keywords ) ) {
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
     * @param iterable $headers
     *
     * @return $this
     */
    public function headers( iterable $headers ): self
    {
        foreach ( $headers as $key => $value ) {
            $this->header( $key, $value );
        }

        return $this;
    }

    /**
     * @param string $key
     * @param        $value
     *
     * @return $this
     */
    public function header( string $key, $value ): self
    {
        $this->headers[ $key ] = $value;

        return $this;
    }

    /**
     * @return Json
     */
    public function getHeaders(): Json
    {
        return new Json( $this->headers );
    }

    /**
     * @param string      $secret
     * @param string|null $alg
     *
     * @return string
     */
    public function token( string $secret, string $alg = null ): string
    {
        if ( !$this->getHeader( 'alg' ) ) {
            $this->header( 'alg', $alg ?? 'HS256' );
        }

        ksort( $this->headers );

        $headerEncoded  = $this->headerEncode( $this->headers );
        $payloadEncoded = $this->payloadEncode( $this->claims );
        $signature      = $this->signatureEncode( $this->headers, $this->claims, $secret );

        return $headerEncoded . '.' . $payloadEncoded . '.' . $signature;
    }

    /**
     * @param string $key
     *
     * @return mixed|null
     */
    public function getHeader( string $key )
    {
        return $this->headers[ $key ] ?? null;
    }
}