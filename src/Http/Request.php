<?php namespace Chukdo\Http;

use Chukdo\Helper\Str;
Use \Chukdo\Json\Json;
Use \Chukdo\Helper\Http;
use Chukdo\Storage\FileUploaded;

/**
 * Gestion de requete HTTP entrante
 *
 * @package     http
 * @version 	1.0.0
 * @copyright 	licence MIT, Copyright (C) 2019 Domingo
 * @since 		08/01/2019
 * @author Domingo Jean-Pierre <jp.domingo@gmail.com>
 */

class Request
{
    /**
     * @param Header $header
     */
    protected $header;

    /**
     * @param Url $url
     */
    protected $url;

    /**
     * @param Json $inputs
     */
    protected $inputs;

    /**
     * @param String $method
     */
    protected $method;

    /**
     * Response constructor.
     */
    public function __construct()
    {
        $this->header   = new Header();
        $this->url      = new Url(Http::server('SCRIPT_URI'));
        $this->inputs   = new Json($_REQUEST, true);
        $this->method   = Http::request('httpverb') ?: Http::server('REQUEST_METHOD');

        $this->header->setHeader('Content-Type', Http::server('CONTENT_TYPE', ''));
        $this->header->setHeader('Content-Length', Http::server('CONTENT_LENGTH', ''));

        foreach ($_SERVER as $key => $value) {
            if ($name = Str::match('/^HTTP_(.*)/', $key)) {
                switch ($name) {
                    case 'HOST' :
                    case 'COOKIE' :
                        break;
                    default :
                        $this->header->setHeader($name, $value);
                }
            }
        }
    }

    /**
     * @return Header
     */
    public function header(): Header
    {
        return $this->header;
    }

    /**
     * @return Url
     */
    public function url(): Url
    {
        return $this->url;
    }

    /**
     * @param string $name
     * @param string|null $allowedMimeTypes
     * @param int|null $maxFileSize
     * @return FileUploaded
     */
    public function file(string $name, string $allowedMimeTypes = null, int $maxFileSize = null): FileUploaded
    {
        return new FileUploaded($name, $allowedMimeTypes, $maxFileSize = null);
    }

    /**
     * @return Json
     */
    public function inputs(): Json
    {
        return $this->inputs;
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function input(string $name)
    {
        return $this->inputs->get($name);
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function wildcard(string $name)
    {
        return $this->inputs->wildcard($name);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function exists(string $name): bool
    {
        return $this->inputs->exists($name);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function filled(string $name): bool
    {
        return $this->inputs->filled($name);
    }

    /**
     * @param mixed ...$offsets
     * @return Json
     */
    public function only(...$offsets): Json
    {
        return $this->inputs->only($offsets);
    }

    /**
     * @param mixed ...$offsets
     * @return Json
     */
    public function except(...$offsets): Json
    {
        return $this->inputs->except($offsets);
    }

    /**
     * @return string|null
     */
    public function from(): ?string
    {
        return parse_url(
            Http::server('HTTP_ORIGIN') ?:
                Http::server('HTTP_REFERER') ?:
                Http::server('REMOTE_ADDR'),
            PHP_URL_HOST);
    }

    /**
     * @return bool
     */
    public function ajax(): bool
    {
        return $this->header->getHeader('X-Requested-with') === 'XMLHttpRequest';
    }

    /**
     * @return string|null
     */
    public function method(): ?string
    {
        return $this->method;
    }

    /**
     * @return array
     */
    public function userAgent(): array
    {
        return Http::getUserAgent(Http::server('HTTP_USER_AGENT'));
    }

    /**
     * @return bool
     */
    public function secured(): bool
    {
        return
            Http::server('HTTPS') ||
            Http::server('SERVER_PORT') == '443' ||
            Http::server('REQUEST_SCHEME') == 'https';
    }
}