<?php

namespace Chukdo\Http;

use Chukdo\Helper\Http;
use Chukdo\Helper\Str;
use Chukdo\Json\Json;
use Chukdo\Xml\Xml;

/**
 * Gestion des entetes HTTP.
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
    protected $header;

    /**
     * @param string
     */
    protected $content = null;

    /**
     * @param string $file
     */
    protected $file = null;

    /**
     * @param bool $deleteFileAfterSend
     */
    protected $deleteFileAfterSend = false;

    /**
     * Response constructor.
     */
    public function __construct() {
        $this->header = new Header();
        $this->header->setStatus(200)
            ->setDate(time())
            ->setServer('Apache')
            ->setConnection('close')
            ->setCacheControl(false,
                false,
                'no-store, no-cache, must-revalidate');
    }

    /**
     * @param int $status
     * @return Response
     */
    public function status( int $status ): self {
        $this->header->setStatus($status);

        return $this;
    }

    /**
     * @param string $name
     * @param string $header
     * @return Response
     */
    public function header( string $name, string $header ): self {
        $this->header->setHeader($name,
            $header);

        return $this;
    }

    /**
     * @param iterable $headers
     * @return Response
     */
    public function headers( iterable $headers ): self {
        $this->header->setHeaders($headers);

        return $this;
    }

    /**
     * @param string $name
     * @param string $cookie
     * @return Response
     */
    public function cookie( string $name, string $cookie ): self {
        $this->header->setCookie($name,
            $cookie);

        return $this;
    }

    /**
     * @param iterable $cookies
     * @return Response
     */
    public function cookies( iterable $cookies ): self {
        $this->header->setCookies($cookies);

        return $this;
    }

    /**
     * @param string      $file
     * @param string|null $name
     * @param string|null $type
     * @return Response
     */
    public function download( string $file, string $name = null, string $type = null ): self {
        $name = $name
            ?: basename($file);
        $type = $type
            ?: Http::mimeContentType($name);

        $this->file = $file;
        $this->header->setHeader('Content-Disposition',
            'attachment; filename="' . $name . '"')
            ->setHeader('Content-Type',
                $type);

        return $this;
    }

    /**
     * @param string      $file
     * @param string|null $name
     * @param string|null $type
     * @return Response
     */
    public function file( string $file, string $name = null, string $type = null ): self {
        $name = $name
            ?: basename($file);
        $type = $type
            ?: Http::mimeContentType($name);

        $this->file = $file;
        $this->header->setHeader('Content-Disposition',
            'inline; filename="' . $name . '"')
            ->setHeader('Content-Type',
                $type);

        return $this;
    }

    /**
     * @param string $content
     * @return Response
     */
    public function html( string $content ): self {
        $this->header->setHeader('Content-Type',
            'text/html; charset=utf-8');

        $this->content($content);

        return $this;
    }

    /**
     * @param string $content
     * @return Response
     */
    public function content( string $content ): self {
        $this->content = $content;

        return $this;
    }

    /**
     * @param $content
     * @return Response
     */
    public function text( $content ): self {
        $this->header->setHeader('Content-Type',
            'text/plain; charset=utf-8');

        $this->content((new Json($content))->toJson());

        return $this;
    }

    /**
     * @param $content
     * @return Response
     */
    public function json( $content ): self {
        $this->header->setHeader('Content-Type',
            'application/json; charset=utf-8');

        $this->content((new Json($content))->toJson());

        return $this;
    }

    /**
     * @param      $content
     * @param bool $html
     * @return Response
     */
    public function xml( $content, bool $html = false ): self {
        $this->header->setHeader('Content-Type',
            'text/xml; charset=utf-8');

        $this->content((new Xml())->import($content)
            ->toXmlString($html,
                true));

        return $this;
    }

    /**
     * @param string $url
     * @param int    $code
     * @return Response
     */
    public function redirect( string $url, int $code = 307 ): self {
        $this->header->setLocation($url,
            $code);
        $this->send();

        return $this;
    }

    /**
     * @return Response
     */
    public function send(): self {
        $hasContent = $this->content != null;
        $hasFile    = $this->file != null;

        if( $hasContent ) {
            $this->sendContentResponse();
        }
        elseif( $hasFile ) {
            $this->sendDownloadResponse();
        }
        else {
            $this->sendHeaderResponse();
        }

        if( $this->deleteFileAfterSend ) {
            unlink($this->file);
        }

        return $this;
    }

    /**
     * @return Response
     */
    protected function sendContentResponse(): self {
        $content = $this->content;

        if( Str::contain(Http::server('HTTP_ACCEPT_ENCODING'),
            'deflate') ) {
            $this->header->setHeader('Content-Encoding',
                'deflate');
            $content = gzdeflate($this->content);
        }
        elseif( Str::contain(Http::server('HTTP_ACCEPT_ENCODING'),
            'gzip') ) {
            $this->header->setHeader('Content-Encoding',
                'gzip');
            $content = gzencode($this->content);
        }

        if( $this->header->getHeader('Transfer-Encoding') == 'chunked' ) {
            $this->sendHeaderResponse();

            foreach( str_split($content,
                4096) as $c ) {
                $l = dechex(strlen($c));

                echo "$l\r\n$c\r\n";
            }

            echo "0\r\n";
        }
        else {
            $this->header->setHeader('Content-Length',
                strlen($content));
            $this->sendHeaderResponse();

            echo $content;
        }

        return $this;
    }

    /**
     * @return Response
     */
    protected function sendHeaderResponse(): self {
        if( headers_sent() ) {
            return $this;
        }

        header_remove();

        foreach( explode("\n",
            $this->header->send()) as $header ) {
            header($header,
                true);
        }

        return $this;
    }

    /**
     * @return Response
     */
    protected function sendDownloadResponse(): self {
        if( $this->header->getHeader('Transfer-Encoding') == 'chunked' ) {
            $this->sendHeaderResponse();

            $f = fopen($this->file,
                'rb');

            while( !feof($f) ) {
                $c = fread($f,
                    131072);
                $l = dechex(strlen($c));

                echo "$l\r\n$c\r\n";
            }

            fclose($f);

            echo "0\r\n";
        }
        else {
            $this->header->setHeader('Content-Length',
                filesize($this->file));
            $this->sendHeaderResponse();
            readfile($this->file);
        }

        return $this;
    }

    public function end() {
        exit;
    }

    /**
     * @return Response
     */
    public function deleteFileAfterSend(): self {
        $this->deleteFileAfterSend = true;

        return $this;
    }
}
