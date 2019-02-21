<?php namespace Chukdo\View;

use \Closure;
use Chukdo\Json\Json;

/**
 * Moteur de template
 *
 * @package     View
 * @version 	1.0.0
 * @copyright 	licence MIT, Copyright (C) 2019 Domingo
 * @since 		08/01/2019
 * @author Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Template
{
    /**
     * @var String
     */
    protected $file = '';

    /**
     * @var Json
     */
    protected $data;

    /**
     * @var array
     */
    protected $functions = [];

    /**
     * Template constructor.
     * @param string $file
     * @param Json $data
     * @param array $functions
     */
    public function __construct(string $file, Json $data, array $functions)
    {
        $this->file         = $file;
        $this->data         = $data;
        $this->functions    = $functions;
    }

    /**
     * @param iterable $data
     * @return Template
     */
    public function data(Iterable $data): self
    {
        $this->data->mergeRecursive($data, true);

        return $this;
    }

    /**
     * @param string $key
     * @param string|null $functions
     */
    public function e(string $key, string $functions = null): void
    {
        $e = isset(${$key}) ? ${$key} : $this->j($key);

        echo $e;
    }

    /**
     * @param string $key
     * @return Json|mixed|null
     */
    public function j(string $key)
    {
        return $this->data->get($key);
    }

    /**
     * @param string $key
     * @return Json
     */
    public function w(string $key)
    {
        return $this->data->wildcard($key);
    }

    /**
     *
     */
    public function render()
    {
        ob_start();
        include $this->file;
        $render = ob_get_clean();

        echo $render;
    }
}