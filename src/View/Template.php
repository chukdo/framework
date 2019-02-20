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

    public function render()
    {
        
    }
}