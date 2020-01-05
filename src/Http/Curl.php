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
    protected $handle;

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
     * @param string      $url
     * @param array       $options
     * @param Header|null $headers
     */
    public function __construct( string $url, array $options = [], Header $headers = null )
    {
        $this->handle  = curl_init();
        $this->headers = new Header();

        $this->setUrl( $url )
             ->setOption( CURLOPT_SSL_VERIFYPEER, false )
             ->setOption( CURLOPT_RETURNTRANSFER, true )
             ->setOption( CURLOPT_FOLLOWLOCATION, true )
             ->setOptions( $options )
             ->setOption( CURLOPT_HEADER, false )
             ->setOption( CURLOPT_HEADERFUNCTION, fn( $h, $header ) => $this->headers->parseHeaders( $header ) );

        if ( $headers ) {
            $this->setHeaders( $headers );
        }
    }

    /**
     *
     */
    public function execute(): void
    {
        if ( $this->raw === null ) {
            $this->raw = curl_exec( $this->handle );
            $status    = $this->headers->getStatus();
            $errno     = curl_errno( $this->handle );

            /** Curl Error */
            if ( $errno ) {
                throw new HttpException( curl_error( $this->handle ) );
            }

            /** Bad http header status */
            if ( $status >= 400 ) {
                throw new HttpException( sprintf( 'Curl return bad http status [%s]', $status ) );
            }

            /** Empty response */
            if ( $this->raw === null ) {
                throw new HttpException( 'Curl has empty response' );
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
     * @param string $url
     *
     * @return $this
     */
    public function setUrl( string $url ): self
    {
        curl_setopt( $this->handle, CURLOPT_URL, $url );

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
