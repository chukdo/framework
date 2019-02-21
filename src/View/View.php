<?php namespace Chukdo\View;

Use \Closure;
Use \Chukdo\Helper\Str;
Use Chukdo\Json\Json;

/**
 * Moteur de template
 *
 * @package     View
 * @version 	1.0.0
 * @copyright 	licence MIT, Copyright (C) 2019 Domingo
 * @since 		08/01/2019
 * @author Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class View
{
    /**
     * @var array
     */
    protected $folders = [];

    /**
     * @var string
     */
    protected $defaultFolder = null;

    /**
     * @var array
     */
    protected $sharedData = [];

    /**
     * @var array
     */
    protected $sharedTemplateData = [];

    /**
     * @var array
     */
    protected $functions = [];

    /**
     * View constructor.
     * @param string $folder
     */
    public function __construct(string $folder = null)
    {
        $this->setDefaultFolder($folder);
    }

    /**
     * @param string|null $folder
     */
    protected function setDefaultFolder(string $folder = null): void
    {
        $this->defaultFolder = rtrim($folder, '/');
    }

    /**
     * @param string $name
     * @param string $folder
     * @return View
     */
    public function addFolder(string $name, string $folder): self
    {
        $this->folders[$name] = rtrim($folder, '/');

        return $this;
    }

    /**
     * @param iterable $data
     * @param array|string|null $templates
     * @return View
     */
    public function addData(Iterable $data, $templates = null): self
    {
        if ($templates == null) {
            $this->sharedData = $data;
        } else {
            foreach ((array) $templates as $template) {
                $this->sharedTemplateData[$template] = $data;
            }
        }

        return $this;
    }

    /**
     * @param string|null $template
     * @return iterable|null
     */
    protected function getData(string $template = null): ?iterable
    {
        if ($template == null) {
            return $this->sharedData;

        } else if (isset($this->sharedTemplateData[$template])) {
            return $this->sharedTemplateData[$template];

        } else {
            return null;
        }
    }

    /**
     * @param string $name
     * @param Closure $closure
     * @return View
     */
    public function registerFunction(string $name, Closure $closure): self
    {
        $this->functions[$name] = $closure;

        return $this;
    }

    /**
     * @return array
     */
    protected function getRegisteredFunctions(): array
    {
        return $this->functions;
    }

    /**
     * @param string $template
     * @param iterable|null $data
     * @return Template
     */
    public function make(string $template, iterable $data = null): Template
    {
        list ($folder, $name) = Str::split($template, '::', 2);

        $file = $name ?
            $this->folders[$folder] . '/' . $name . '.html' :
            $this->defaultFolder . '/' . $folder . '.html';

        $data = new Json($data);
        $data->mergeRecursive($name ?
            $this->getData($folder) :
            $this->getData()
        );

        if (file_exists($file)) {
            return new Template($file, $data, $this->getRegisteredFunctions());
        }

        throw new ViewException(sprintf('Template file [%s] does not exist', $file));
    }

    /**
     * @param string $template
     * @param iterable|null $data
     */
    public function render(string $template, iterable $data = null)
    {
        $this->make($template, $data)->render();
    }
}