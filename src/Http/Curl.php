<?php

namespace Chukdo\Http;

/**
 * Curl.
 *
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Curl
{
    /**
     * @var resource
     */
    protected $handle;

    /**
     * @var mixed
     */
    protected $data = null;

    /**
     * Curl constructor.
     *
     * @param array       $options
     * @param Header|null $headers
     */
    public function __construct( array $options = [], Header $headers = null )
    {
        $this->handle = curl_init();

        $this->setOption( CURLOPT_FOLLOWLOCATION, true )
             ->setOption( CURLOPT_HEADER, true )
             ->setOptions( $options );

        if ( $headers ) {
            $this->setHeaders( $headers );
        }


        $status = $this->status();

        /** Curl Error */
        if ( $error = $this->error() ) {
            throw new HttpException( $error );
        }

        /** Bad http header status */
        if ( $status >= 400 ) {
            throw new HttpException( sprintf( 'Curl return bad http status [%s]', $status ) );
        }
    }

    /**
     *
     */
    public function execute(): void
    {
        if ( $this->data === null ) {
            $this->data = curl_exec( $this->handle );
            $status     = $this->status();
            $error      = $this->error();

            /** Curl Error */
            if ( $error ) {
                throw new HttpException( $error );
            }

            /** Bad http header status */
            if ( $status >= 400 ) {
                throw new HttpException( sprintf( 'Curl return bad http status [%s]', $status ) );
            }
        }
    }

    /**
     * @param Header $headers
     *
     * @return $this
     */
    public function setHeaders( Header $headers ): self
    {
        curl_setopt( $this->handle, CURLOPT_HTTPHEADER, (array) $headers->getHeaders() );

        return $this;
    }

    /**
     * @param string $key
     * @param        $value
     *
     * @return $this
     */
    public function setOption( string $key, $value ): self
    {
        curl_setopt( $this->handle, $key, $value );

        return $this;
    }

    /**
     * @param array $options
     *
     * @return $this
     */
    public function setOptions( array $options ): self
    {
        foreach ( $options as $key => $value ) {
            $this->setOption( $key, $value );
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function error(): ?string
    {
        $this->execute();

        if ( curl_errno( $this->handle ) ) {
            return curl_error( $this->handle );
        }

        return null;
    }

    /**
     * @return bool|mixed|string
     */
    public function data()
    {
        $this->execute();

        return $this->data;
    }

    /**
     * @return int
     */
    public function contentLength(): int
    {
        $this->execute();

        return (int) curl_getinfo( $this->handle, CURLINFO_CONTENT_LENGTH_DOWNLOAD );
    }

    /**
     * @return int
     */
    public function status(): int
    {
        $this->execute();

        return (int) curl_getinfo( $this->handle, CURLINFO_HTTP_CODE );
    }

    /**
     *
     */
    public function close(): void
    {
        curl_close( $this->handle );
    }

    /**
     *
     */
    public function __destruct()
    {
        $this->close();
    }
}
