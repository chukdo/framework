<?php namespace Chukdo\View;

use Chukdo\Json\Json;
use Chukdo\Helper\Str;

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
     * @param $data
     * @param string|null $functions
     * @return mixed
     */
    public function v($data, string $functions = null)
    {
        if($functions) {
            foreach (Str::split($functions, '|') as $function) {
                $data = $this->$function($data);
            }
        }

        return $data;
    }

    /**
     * @param string $key
     * @param string|null $functions
     * @return Json|mixed|null
     */
    public function j(string $key, string $functions = null)
    {
        return $this->v($this->data->get($key), $functions);
    }

    /**
     * @param string $key
     * @param string|null $functions
     * @return mixed
     */
    public function w(string $key, string $functions = null)
    {
        return $this->v($this->data->wildcard($key), $functions);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        ob_start();
        include($this->file);
        return ob_get_clean();
    }

    /**
     * @param string $name
     * @param array|null $arguments
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        $data = reset($arguments);

        if (isset($this->functions[$name])) {
            return $this->functions[$name]($data);

        } else if (is_callable($name)) {
            return $name($data);
        }

        throw new ViewException(sprintf('Method [%s] is not a template registered function', $name));
    }

    /**
     *
     */
    public function render()
    {
        // $response   = $this->app->make('\Chukdo\Http\Response')
        echo $this->__toString();
    }
}