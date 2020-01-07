<?php

namespace Chukdo\Http;

use Chukdo\Helper\Http;
use Chukdo\Json\Json;
use Chukdo\Xml\Xml;

/**
 * RequestApi.
 *
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class RequestApi
{
    /**
     * @var Header
     */
    protected Header $header;

    /**
     * @var Url
     */
    protected Url $url;

    /**
     * @var array
     */
    protected array $inputs = [];

    /**
     * @var string|null
     */
    protected ?string $rawInput = null;

    /**
     * @var string
     */
    protected string $method = 'GET';

    /**
     * RequestApi constructor.
     *
     * @param string|null $method
     * @param string|null $url
     * @param iterable    $inputs
     */
    public function __construct( string $method = null, string $url = null, iterable $inputs = [] )
    {
        $this->header = new Header();

        $this->setMethod( $method )
             ->setUrl( $url )
             ->setInputs( $inputs );
    }

    /**
     * @param string $method
     *
     * @return $this
     */
    public function setMethod( string $method = 'GET' ): self
    {
        $this->method = strtoupper( trim( $method ) );

        return $this;
    }

    /**
     * @param array $options
     *
     * @return ResponseApi
     */
    public function send( array $options = [] ): ResponseApi
    {
        switch ( $this->method() ) {
            case 'GET' :
            case 'DELETE' :
            case 'HEAD' :
                $this->url()
                     ->setInputs( $this->inputs );
                break;
        }

        return new ResponseApi( $this, $options );
    }

    /**
     * @return string
     */
    public function method(): string
    {
        return $this->method;
    }

    /**
     * @return Url
     */
    public function url(): Url
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url()
                    ->buildCompleteUrl();
    }

    /**
     * @param string|null $url
     *
     * @return $this
     */
    public function setUrl( string $url = null ): self
    {
        $this->url = new Url( $url );

        return $this;
    }

    /**
     * @param iterable $header
     *
     * @return $this
     */
    public function setHeaders( iterable $header ): self
    {
        foreach ( $header as $key => $value ) {
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
        $this->header()
             ->setHeader( $key, $value );

        return $this;
    }

    /**
     * @return Header
     */
    public function header(): Header
    {
        return $this->header;
    }

    /**
     * @param string $token
     *
     * @return $this
     */
    public function setBearer( string $token ): self
    {
        $this->header()
             ->setBearer( $token );

        return $this;
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType( string $type ): self
    {
        $this->header()
             ->setType( $type );

        return $this;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->header()
                    ->getType();
    }

    /**
     * @return bool
     */
    public function hasInputs(): bool
    {
        return count( $this->inputs ) > 0;
    }

    /**
     * @param string $key
     * @param        $value
     *
     * @return $this
     */
    public function setInput( string $key, $value ): self
    {
        $this->inputs[ $key ] = $value;

        return $this;
    }

    /**
     * @param string $raw
     *
     * @return $this
     */
    public function setRawInput( string $raw ): self
    {
        $this->rawInput = $raw;

        return $this;
    }

    /**
     * @return array|string|null
     */
    public function getInputs()
    {
        if ( $raw = $this->rawInput ) {
            return $raw;
        }

        switch ( $this->getType() ) {
            case 'json' :
                $json = new Json( $this->inputs );

                return $json->toJson( false );
            case 'xml' :
            case 'html' :
                $xml = new Xml();

                return $xml->import( $this->inputs )
                           ->toXmlString( $this->getType() === 'html' );
            default :
                return $this->inputs;

        }
    }

    /**
     * @param iterable $inputs
     *
     * @return $this
     */
    public function setInputs( iterable $inputs ): self
    {
        foreach ( $inputs as $key => $value ) {
            $this->setInput( $key, $value );
        }

        return $this;
    }
}
