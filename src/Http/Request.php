<?php namespace Chukdo\Http;

use Chukdo\Bootstrap\App;
use Chukdo\Helper\Str;
use Chukdo\Helper\Http;
use Chukdo\Json\Json;
use Chukdo\Json\JsonInput;
use Chukdo\Validation\Validator;
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
     * @param JsonInput
     */
    protected $inputs;

    /**
     * @param Header
     */
    protected $header;

    /**
     * @param Url
     */
    protected $url;

    /**
     * @param String
     */
    protected $method;

    /**
     * Request constructor.
     * @param App $app
     * @throws \Chukdo\Bootstrap\ServiceException
     * @throws \ReflectionException
     */
    public function __construct(App $app)
    {
        $this->inputs   = $app->make('Chukdo\Json\JsonInput');
        $this->header   = new Header();
        $this->url      = new Url(Http::server('SCRIPT_URI'));
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

    // function rules()
    // function messages()
    // function filters()

    /**
     * @param iterable $rules
     * @return Validator
     */
    public function validate(Iterable $rules): Validator
    {
        return $this->inputs->validate($rules);
    }

    /**
     * @param string $name
     * @param string|null $allowedMimeTypes
     * @param int|null $maxFileSize
     * @return FileUploaded
     */
    public function file(string $name, string $allowedMimeTypes = null, int $maxFileSize = null): FileUploaded
    {
        return $this->inputs->file($name, $allowedMimeTypes, $maxFileSize);
    }

    /**
     * @return JsonInput
     */
    public function inputs(): JsonInput
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
     * @param string $path
     * @return bool
     */
    public function filled(string $path): bool
    {
        return $this->inputs->filled($path);
    }

    /**
     * @param string $path
     * @return bool
     */
    public function exists(string $path): bool
    {
        return $this->inputs->exists($path);
    }

    /**
     * @param string $path
     * @return Json
     */
    public function wildcard(string $path): Json
    {
        return $this->inputs->wildcard($path);
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