<?php namespace Chukdo\Http;

Use \Closure;
Use \Chukdo\Json\Json;
Use \Chukdo\Xml\Xml;
Use \Chukdo\Helper\Data;

/**
 * Gestion des entetes HTTP
 *
 * @package     http
 * @version 	1.0.0
 * @copyright 	licence MIT, Copyright (C) 2019 Domingo
 * @since 		08/01/2019
 * @author Domingo Jean-Pierre <jp.domingo@gmail.com>
 */

class Response
{
    /**
     * Données
     *
     * @param Header $data
     */
    protected $header;

    /**
     * @param string
     */
    protected $content;

    /**
     * @param string $file
     */
    protected $file;

    /**
     * @param Closure $stream
     */
    protected $stream;

    /**
     * Response constructor.
     */
    public function __construct()
    {
        $this->header   = new Header();
        $this->data     = new Json();
        $this->files    = new Json();

        $this->header
            ->setStatus(200)
            ->setDate(time())
            ->setServer('Apache')
            ->setConnection('close')
            ->setCacheControl(false, false, 'no-store, no-cache, must-revalidate');
    }

    /**
     * @param string $name
     * @param string $header
     * @return Response
     */
    public function header(string $name, string $header): self
    {
        $this->header->setHeader($name, $header);

        return $this;
    }

    /**
     * @param iterable $headers
     * @return Response
     */
    public function headers(iterable $headers): self
    {
        $this->header->setHeaders($headers);

        return $this;
    }

    /**
     * @param string $name
     * @param string $cookie
     * @return Response
     */
    public function cookie(string $name, string $cookie): self
    {
        $this->header->setCookie($name, $cookie);

        return $this;
    }

    /**
     * @param iterable $cookies
     * @return Response
     */
    public function cookies(iterable $cookies): self
    {
        $this->header->setCookies($cookies);

        return $this;
    }

    /**
     * @param Closure $closure
     * @param string $name
     * @param string|null $type
     * @return Response
     */
    public function stream(Closure $closure, string $name, string $type = null): self
    {
        $type = $type ?: 'application/octet-stream';

        $this->stream = $closure;
        $this->header
            ->setHeader('Content-Disposition', 'attachment; filename="'.$name.'"')
            ->setHeader('Content-Type', $type);

        return $this;
    }

    /**
     * @param string $file
     * @param string|null $name
     * @param string|null $type
     * @return Response
     */
    public function download(string $file, string $name = null, string $type = null): self
    {
        $name = $name ?: basename($file);
        $type = $type ?: 'application/octet-stream';

        $this->file = $file;
        $this->header
            ->setHeader('Content-Disposition', 'attachment; filename="'.$name.'"')
            ->setHeader('Content-Type', $type);

        return $this;
    }

    /**
     * @param string $file
     * @param string|null $name
     * @param string|null $type
     * @return Response
     */
    public function file(string $file, string $name = null, string $type = null): self
    {
        $name = $name ?: basename($file);
        $type = $type ?: 'application/octet-stream';

        $this->file = $file;
        $this->header
            ->setHeader('Content-Disposition', 'inline; filename="'.$name.'"')
            ->setHeader('Content-Type', $type);

        return $this;
    }

    public function content($content): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @param $content
     * @return Response
     */
    public function json($content): self
    {
        $this->header->setHeader('Content-Type', 'application/json');

        $this->content((new Json($content))->toJson());

        return $this;
    }

    /**
     * @param $content
     * @param bool $html
     * @return Response
     */
    public function xml($content, bool $html = false): self
    {
        $this->header->setHeader('Content-Type', 'text/xml');

        $this->content((new Xml())->import($content)->toXmlString($html, true));

        return $this;
    }

    /**
     * @param string $url
     * @param int $code
     * @return Response
     */
    public function redirect(string $url, int $code = 307): self
    {
        $this->header->setLocation($url, $code);
        $this->send();

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
     * Envoi la reponse HTTP à la sortie standard
     */
    public function send()
    {
        $count = $this->data->count() + $this->files->count();

        switch($count) {
            case 0  :
                $this->sendHeaderResponse(true);
                break;
            case 1  :
                $this->sendSimpleResponse(true);
                break;
            default :
                $this->sendMultipartResponse(true);
        }
    }

    /**
     * @return Response
     */
    public function sendHeaderResponse(): self
    {
        if (headers_sent()) {
            return;
        }

        header_remove();

        foreach (explode("\n", $this->header->send()) as $header) {
            header($header, true);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function sendSimpleResponse()
    {
        if ($this->data->count()) {
            $this->sendSimpleResponseData($this->data->getIndex(0));

        } else if ($this->files->count()) {
            foreach ($this->files as $name => $file) {
                $this->sendSimpleResponseFile($file, $name);
                break;
            }
        }

        return $this;
    }

    /**
     * @param $file
     * @param $name
     * @param $header
     * @throws Exception
     */
    public function sendSimpleResponseFile($file, $name, $header)
    {
        $this->unsetContentEncoding();

        /** Définition du Content-Type si non défini */
        if (!$this->getContentType()) {
            $this->setContentType(helper_file::getFileContentType($file));
        }

        /** Download */
        $this->setContentDisposition($this->disposition.'; filename="'.$name.'"');

        /** Transfert-Encoding > chunked !! */
        if ($this->getTransferEncoding() == 'chunked') {
            $this->sendHeaderResponse($header);

            $f = fopen($file, 'rb');

            while (!feof($f)) {
                $c = fread($f, 4096);
                $l = dechex(strlen($c));

                echo "$l\r\n$c\r\n";
            }

            fclose($f);

            echo "0\r\n";

            /** Envoi simple sans Content-Encoding ni Transfert-Encoding spécifique */
        } else {
            $scheme = parse_url($file, PHP_URL_SCHEME);

            if ($scheme == null) {
                $this->setContentLength(filesize($file));
            }

            $this->sendHeaderResponse($header);
            readfile($file);
        }
    }

    /**
     * @param $data
     * @param $header
     * @throws Exception
     */
    public function sendSimpleResponseData($data, $header)
    {
        $content = $this->getContentEncoding();

        /** Définition du Content-Type si non défini */
        if (!$this->getContentType()) {
            $this->setContentType(helper_data::getDataContentType($data));
        }

        /** Content-Encoding */
        if ($content) {

            /** Compression GZIP || ZLIB */
            switch ($content) {
                case 'gzip'    : $data = gzencode($data); break;
                case 'deflate' : $data = gzdeflate($data); break;
            }
        }

        /** Transfert-Encoding > chunked !! */
        if ($this->getTransferEncoding() == 'chunked') {
            $this->sendHeaderResponse($header);

            foreach (str_split($data, 4096) as $c) {
                $l = dechex(strlen($c));

                echo "$l\r\n$c\r\n";
            }

            echo "0\r\n";

            /** Envoi simple sans Content-Encoding ni Transfert-Encoding spécifique */
        } else {
            $this->setContentLength(strlen($data));
            $this->sendHeaderResponse($header);
            echo $data;
        }
    }

    /**
     * Envoi la reponse HTTP ne contenant plusieurs données et / ou fichiers
     *
     * @param bool $header envoi les entetes via la fonction 'header()' ou à la sortie standard
     * @throws Exception
     */
    public function sendMultipartResponse($header)
    {
        $bound = md5(uniqid('', true));

        /** Definition des entetes */
        $this->header->unsetContentEncoding();
        $this->header->setContentType('multipart/related; boundary='.$bound);

        /** Envoi des entetes */
        $this->sendHeaderResponse($header);

        /** Envoi du multipart Data */
        foreach ($this->data as $value) {
            $type = Data::getDataContentType($value);

            echo "--$bound\r\nContent-Type: $type\r\n\r\n$value\r\n";
        }

        /** Envoi du multipart Fichiers */
        foreach ($this->files as $name => $file) {
            $type = File::getFileContentType($file);

            echo "--$bound\r\n"
                ."Content-Disposition: inline; filename=\"$name\"\r\n"
                ."Content-Type: $type\r\n\r\n";

            readfile($file);

            echo "\r\n";
        }

        echo "--$bound--";
    }
}