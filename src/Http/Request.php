<?php namespace Chukdo\Http;

Use \Chukdo\Json\Json;
Use \Chukdo\Xml\Xml;
Use \Chukdo\Helper\Http;
use Symfony\Component\Console\Helper\Helper;

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
     * Response constructor.
     */
    public function __construct()
    {
        $this->header   = new Header();
        $this->url      = new Url($_SERVER['uri']);
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
     * @return string
     */
    public function method(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }
}