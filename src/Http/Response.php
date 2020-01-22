<?php

namespace Chukdo\Http;

use DateTime;
use Chukdo\Helper\Http;
use Chukdo\Helper\HttpRequest;
use Chukdo\Helper\Str;
use Chukdo\Json\Json;
use Chukdo\Xml\Xml;

/**
 * Gestion des entetes HTTP.
 * Le output_buffering de php doit être à Off dans le php.ini: output_buffering = Off
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Response
{
    /**
     * @param Header $header
     */
    protected Header $header;

    /**
     * @param string
     */
    protected ?string $content;

    /**
     * @param string $file
     */
    protected ?string $file;

    /**
     * @param bool $deleteFileAfterSend
     */
    protected bool $deleteFileAfterSend = false;

    /**
     * @return Response
     */
    public function instance(): self
    {
        return $this;
    }

    /**
     * @return Response
     */
    public function reset(): self
    {
        $this->__construct();

        return $this;
    }

    /**
     * ResponseApi constructor.
     */
    public function __construct()
    {
        $this->content = null;
        $this->file    = null;
        $this->header  = new Header();
        $this->header->setStatus( 200 )
                     ->setDate( new DateTime() )
                     ->setHeader( 'Server', 'Apache' )
                     ->setHeader( 'Connection', 'close' )
                     ->setCacheControl( 0, true, 'no-store, no-cache, must-revalidate' );
    }

    /**
     * @param int $status
     *
     * @return Response
     */
    public function status( int $status ): self
    {
        $this->header->setStatus( $status );

        return $this;
    }

    /**
     * @param string $name
     * @param string $header
     *
     * @return Response
     */
    public function header( string $name, string $header ): self
    {
        $this->header->setHeader( $name, $header );

        return $this;
    }

    /**
     * @param iterable $headers
     *
     * @return Response
     */
    public function headers( iterable $headers ): self
    {
        $this->header->setHeaders( $headers );

        return $this;
    }

    /**
     * @return Header
     */
    public function getHeaders(): Header
    {
        return $this->header;
    }

    /**
     * @return string|null
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * @return string|null
     */
    public function getFile(): ?string
    {
        return $this->file;
    }

    /**
     * @param string $name
     * @param string $cookie
     *
     * @return Response
     */
    public function cookie( string $name, string $cookie ): self
    {
        $this->header->setCookie( $name, $cookie );

        return $this;
    }

    /**
     * @param iterable $cookies
     *
     * @return Response
     */
    public function cookies( iterable $cookies ): self
    {
        $this->header->setCookies( $cookies );

        return $this;
    }

    /**
     * @param string      $file
     * @param string|null $name
     * @param string|null $type
     *
     * @return Response
     */
    public function download( string $file, string $name = null, string $type = null ): self
    {
        $name       ??= basename( $file );
        $type       ??= Http::extToContentType( $name );
        $this->file = $file;
        $this->header->setHeader( 'Content-Disposition', 'attachment; filename="' . $name . '"' )
                     ->setHeader( 'Content-Type', $type );

        return $this;
    }

    /**
     * @param string      $file
     * @param string|null $name
     * @param string|null $type
     *
     * @return Response
     */
    public function file( string $file, string $name = null, string $type = null ): self
    {
        $name       ??= basename( $file );
        $type       ??= Http::extToContentType( $name );
        $this->file = $file;
        $this->header->setHeader( 'Content-Disposition', 'inline; filename="' . $name . '"' )
                     ->setHeader( 'Content-Type', $type );

        return $this;
    }

    /**
     * @param string $content
     *
     * @return Response
     */
    public function html( string $content ): self
    {
        $this->header->setHeader( 'Content-Type', 'text/html; charset=utf-8' );
        $this->content( $content );

        return $this;
    }

    /**
     * @param string $content
     *
     * @return Response
     */
    public function content( string $content ): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @param string $content
     *
     * @return Response
     */
    public function append( string $content ): self
    {
        $this->content .= $content;

        return $this;
    }

    /**
     * @param string $content
     *
     * @return Response
     */
    public function prepend( string $content ): self
    {
        $this->content = $content . $this->content;

        return $this;
    }

    /**
     * @param $content
     *
     * @return Response
     */
    public function text( $content ): self
    {
        $this->header->setHeader( 'Content-Type', 'text/plain; charset=utf-8' );
        $this->content( ( new Json( $content ) )->toJson() );

        return $this;
    }

    /**
     * @param $content
     *
     * @return Response
     */
    public function json( $content ): self
    {
        $this->header->setHeader( 'Content-Type', 'Application/json; charset=utf-8' );
        $this->content( ( new Json( $content ) )->toJson() );

        return $this;
    }

    /**
     * @param      $content
     * @param bool $html
     *
     * @return Response
     */
    public function xml( $content, bool $html = false ): self
    {
        $this->header->setHeader( 'Content-Type', 'text/xml; charset=utf-8' );
        $this->content( ( new Xml() )->import( $content )
                                     ->toXmlString( $html ) );

        return $this;
    }

    /**
     * @param string $url
     * @param int    $code
     *
     * @return Response
     */
    public function redirect( string $url, int $code = 307 ): self
    {
        $this->header->setLocation( $url, $code );

        return $this->send();
    }

    /**
     * @return Response
     */
    public function send(): self
    {
        if ( headers_sent( $filename, $linenum ) || error_get_last() !== null ) {
            throw new HttpException( sprintf( 'Headers already sent from file %s at line %s', $filename, $linenum ) );
        }

        if ( $this->content !== null ) {
            $this->sendContentResponse();
        }
        elseif ( $this->file !== null ) {
            $this->sendDownloadResponse();

            if ( $this->deleteFileAfterSend ) {
                unlink( $this->file );
            }
        }
        else {
            $this->sendHeaderResponse();
        }

        return $this;
    }

    /**
     * @return Response
     */
    protected function sendContentResponse(): self
    {
        if ( $this->content === null ) {
            throw new HttpException( 'Response content empty' );
        }

        $content = $this->content;

        if ( $encoding = HttpRequest::server( 'HTTP_ACCEPT_ENCODING' ) ) {
            if ( Str::contain( $encoding, 'deflate' ) ) {
                $this->header->setHeader( 'Content-Encoding', 'deflate' );
                $content = (string) gzdeflate( $content );

            }
            elseif ( Str::contain( $encoding, 'gzip' ) ) {
                $this->header->setHeader( 'Content-Encoding', 'gzip' );
                $content = (string) gzencode( $content );
            }
        }

        if ( $this->header->getHeader( 'Transfer-Encoding' ) === 'chunked' ) {
            $this->sendHeaderResponse();

            foreach ( str_split( $content, 4096 ) as $c ) {
                $l = dechex( strlen( $c ) );
                echo "$l\r\n$c\r\n";
            }
            echo "0\r\n";

        }
        else {
            $this->header->setHeader( 'Content-Length', (string) strlen( $content ) );
            $this->sendHeaderResponse();
            echo $content;
        }

        return $this;
    }

    /**
     * @return Response
     */
    protected function sendHeaderResponse(): self
    {
        header_remove();
        foreach ( explode( "\n", $this->header->send() ) as $header ) {
            header( $header, true );
        }

        return $this;
    }

    /**
     * @return Response
     */
    protected function sendDownloadResponse(): self
    {
        $file = (string) $this->file;

        if ( !file_exists( $file ) ) {
            throw new HttpException( sprintf( 'File [%s] no exists', $file ) );
        }

        if ( $this->header->getHeader( 'Transfer-Encoding' ) === 'chunked' ) {
            $this->sendHeaderResponse();

            $f = fopen( $file, 'rb' );

            if ( $f === false ) {
                throw new HttpException( sprintf( 'Can\'t load file [%s]', $file ) );
            }

            while ( !feof( $f ) ) {
                if ( $c = fread( $f, 131072 ) ) {
                    $l = dechex( strlen( $c ) );
                    echo "$l\r\n$c\r\n";
                }
            }

            fclose( $f );
            echo "0\r\n";
        }
        else {
            $this->header->setHeader( 'Content-Length', (string) filesize( $file ) );
            $this->sendHeaderResponse();
            readfile( $file );
        }

        return $this;
    }

    /**
     *
     */
    public function end(): void
    {
        exit;
    }

    /**
     * @return Response
     */
    public function deleteFileAfterSend(): self
    {
        $this->deleteFileAfterSend = true;

        return $this;
    }
}
