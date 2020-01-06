<?php

namespace Chukdo\Http;

use Chukdo\Json\Json;
use Chukdo\Xml\Xml;

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
    protected $curl;

    /**
     * @var Header
     */
    protected Header $curlHeaders;

    /**
     * @var string|null
     */
    protected ?string $raw = null;

    /**
     * @var Header
     */
    protected Header $headers;

    /**
     * Curl constructor.
     *
     * @param string|null $method
     * @param string|null $url
     * @param array       $headers
     * @param array       $options
     */
    public function __construct( string $method = null, string $url = null, array $headers = [], array $options = [] )
    {
        $this->curl        = curl_init();
        $this->headers     = new Header();
        $this->curlHeaders = new Header();

        $this->setMethod( $method )
             ->setUrl( $url )
             ->setOption( CURLOPT_SSL_VERIFYPEER, false )
             ->setOption( CURLOPT_RETURNTRANSFER, true )
             ->setOption( CURLOPT_FOLLOWLOCATION, true )
             ->setOptions( $options )
             ->setOption( CURLOPT_HEADER, false )
             ->setOption( CURLOPT_HEADERFUNCTION, fn( $h, $header ) => $this->headers->parseHeaders( $header ) )
             ->setHeaders( $headers );
    }

    /**
     *
     */
    public function execute(): void
    {
        if ( $this->raw === null ) {
            curl_setopt( $this->curl, CURLOPT_HTTPHEADER, (array) $this->curlHeaders->getHeaders() );

            $this->raw = curl_exec( $this->curl );
            $status    = $this->headers->getStatus();
            $errno     = curl_errno( $this->curl );

            /** Curl Error */
            if ( $errno ) {
                throw new HttpException( curl_error( $this->curl ) );
            }

            /** Bad http header status */
            if ( $status >= 400 ) {
                throw new HttpException( sprintf( 'Curl return bad http status [%s] message [%s]', $status, $this->raw ) );
            }

            /** Empty response */
            if ( $this->raw === null ) {
                throw new HttpException( 'Curl has empty response' );
            }
        }
    }

    /**
     * @param $inputs
     *
     * @return $this
     */
    public function setInputs( $inputs ): self
    {
        return $this->setOption( CURLOPT_POSTFIELDS, $inputs );
    }

    /**
     * @param string $method
     *
     * @return $this
     */
    public function setMethod( string $method = 'GET' ): self
    {
        $method = strtoupper( trim( $method ) );

        switch ( $method ) {
            case 'POST' :
                $this->setOption( CURLOPT_POST, true );
                break;
            case 'PUT' :
                $this->setOption( CURLOPT_CUSTOMREQUEST, 'PUT' );
                break;
            case 'GET' :
                $this->setOption( CURLOPT_HTTPGET, true );
                break;
            case 'DELETE' :
                $this->setOption( CURLOPT_CUSTOMREQUEST, 'DELETE' );
                break;
            case 'HEAD' :
                $this->setOption( CURLOPT_HTTPGET, true )
                     ->setOption( CURLOPT_NOBODY, true );
                break;
        }

        return $this;
    }

    /**
     * @param string $token
     *
     * @return $this
     */
    public function setBearer( string $token ): self
    {
        $this->curlHeaders->setHeader( 'Authorization', 'Bearer ' . $token );

        return $this;
    }

    /**
     * @param array $headers
     *
     * @return $this
     */
    public function setHeaders( array $headers = [] ): self
    {
        foreach ( $headers as $key => $value ) {
            $this->setHeader( $key, $value );
        }

        return $this;
    }

    /**
     * @param string $key
     * @param string $value
     *
     * @return $this
     */
    public function setHeader( string $key, string $value ): self
    {
        $this->curlHeaders->setHeader( $key, $value );

        return $this;
    }

    /**
     * @param string|null $url
     *
     * @return $this
     */
    public function setUrl( string $url = null ): self
    {
        if ( $url ) {
            curl_setopt( $this->curl, CURLOPT_URL, $url );
        }

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
        curl_setopt( $this->curl, $key, $value );

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
     * @return bool|mixed|string
     */
    public function raw()
    {
        $this->execute();

        return $this->raw;
    }

    /**
     * @return Json|Xml|string|null
     */
    public function content()
    {
        $content = $this->raw();

        if ( strpos( $content, '{' ) === 0 ) {
            return new Json( json_decode( $content, true, 512, JSON_THROW_ON_ERROR ) );
        }

        if ( strpos( $content, '<' ) === 0 ) {
            return Xml::loadFromString( $content );
        }

        return $content;
    }

    /**
     * @return Header
     */
    public function headers(): Header
    {
        $this->execute();

        return $this->headers;
    }

    /**
     *
     */
    public function close(): void
    {
        curl_close( $this->curl );
    }

    /**
     *
     */
    public function __destruct()
    {
        $this->close();
    }
}
