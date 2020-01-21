<?php

namespace Chukdo\Http;

use Chukdo\Helper\Arr;
use Chukdo\Helper\Http;
use Chukdo\Helper\Str;
use Chukdo\Json\Json;
use Chukdo\Jwt\JwtException;
use Chukdo\Xml\Xml;

/**
 * RequestApi.
 *
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class ResponseApi
{
    /**
     * @var string|null
     */
    protected ?string $raw;

    /**
     * @var resource
     */
    protected $curl;

    /**
     * @var Header
     */
    protected Header $header;

    /**
     * ResponseApi constructor.
     *
     * @param RequestApi $request
     * @param array      $options
     */
    public function __construct( RequestApi $request, array $options = [] )
    {
        $this->curl   = curl_init();
        $this->header = new Header();

        $this->setOption( CURLOPT_URL, $request->getUrl() );

        switch ( $request->method() ) {
            case 'POST' :
                $this->setOption( CURLOPT_POST, true );

                if ( $request->hasInputs() ) {
                    $this->setOption( CURLOPT_POSTFIELDS, $request->getInputs() );
                }

                break;
            case 'PUT' :
                $this->setOption( CURLOPT_CUSTOMREQUEST, 'PUT' );

                if ( $request->hasInputs() ) {
                    $this->setOption( CURLOPT_POSTFIELDS, $request->getInputs() );
                }

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

        $this->setOption( CURLOPT_SSL_VERIFYPEER, false )
             ->setOption( CURLOPT_AUTOREFERER, true )
             ->setOption( CURLOPT_FOLLOWLOCATION, true )
             ->setOption( CURLOPT_RETURNTRANSFER, true )
             ->setOption( CURLINFO_HEADER_OUT, true )
             ->setOption( CURLOPT_HTTPHEADER, (array) $request->header()
                                                              ->getHeaders() )
             ->setOption( CURLOPT_HEADER, false )
             ->setOption( CURLOPT_HEADERFUNCTION, fn( $h, $header ) => $this->header->parseHeaders( $header ) )
             ->setOptions( $options );

        $this->raw = (string) curl_exec( $this->curl );
    }

    /**
     * @param int $key
     * @param     $value
     *
     * @return $this
     */
    protected function setOption( int $key, $value ): self
    {
        curl_setopt( $this->curl, $key, $value );

        return $this;
    }

    /**
     * @param iterable $options
     *
     * @return $this
     */
    protected function setOptions( iterable $options ): self
    {
        foreach ( $options as $key => $value ) {
            $this->setOption( $key, $value );
        }

        return $this;
    }

    /**
     * @return array
     */
    public function debug(): array
    {
        return curl_getinfo( $this->curl );
    }

    /**
     * @return mixed
     */
    public function content()
    {
        if ( $this->hasError() ) {
            throw new HttpException( $this->error() );
        }

        $content     = $this->raw();
        $contentType = $this->header()
                            ->getHeader( 'Content-Type' );

        if ( $contentType !== null ) {
            $type = Http::contentTypeToExt( $contentType );

            switch ( $type ) {
                case 'json':
                    return $this->json();
                    break;
                case 'html':
                    return $this->html();
                    break;
                case 'xml':
                    return $this->xml();
                    break;
            }
        }

        /** Auto detect Json */
        if ( strpos( $content, '{' ) === 0 ) {
            return $this->json();
        }

        /** Auto detect Html */
        if ( strpos( $content, '<' ) === 0 ) {
            return $this->xml();
        }

        throw new HttpException( sprintf( 'Can\'t decode response [%s]', $content ) );
    }

    /**
     * @return bool
     */
    public function hasError(): bool
    {
        return curl_errno( $this->curl ) || $this->header->getStatus() > 400 || $this->raw === null;
    }

    /**
     * @return string
     */
    public function error(): string
    {
        $status = $this->header->getStatus();
        $errno  = curl_errno( $this->curl );

        /** Curl Error */
        if ( $errno ) {
            return curl_error( $this->curl );
        }

        /** Bad http header status */
        if ( $status >= 400 ) {
            return sprintf( 'Curl return bad http status [%s] message [%s]', $status, $this->raw );
        }

        /** Empty response */
        if ( $this->raw === null ) {
            return 'Curl has empty response';
        }

        return '';
    }

    /**
     * @return string
     */
    public function raw(): string
    {
        if ( $this->raw === null ) {
            throw new JwtException( 'Curl has empty response' );
        }

        return $this->raw;
    }

    /**
     * @return Header
     */
    public function header(): Header
    {
        return $this->header;
    }

    /**
     * @return Json
     */
    public function json(): Json
    {
        return new Json( json_decode( $this->raw(), true, 512, JSON_THROW_ON_ERROR ) );
    }

    /**
     * @return Xml
     */
    public function html(): Xml
    {
        return Xml::loadFromString( $this->raw(), true );
    }

    /**
     * @return Xml
     */
    public function xml(): Xml
    {
        return Xml::loadFromString( $this->raw(), false );
    }

    /**
     *
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     *
     */
    public function close(): void
    {
        curl_close( $this->curl );
    }
}
