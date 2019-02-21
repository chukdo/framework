<?php namespace Chukdo\View;

Use \Closure;
Use \Chukdo\Helper\Str;
Use \Chukdo\Http\Response;
Use \Chukdo\Contracts\View\Functions;

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
     * @var Response
     */
    protected $response;

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
     * @param string|null $folder
     * @param Response $response
     */
    public function __construct(string $folder = null, Response $response = null)
    {
        $this->setDefaultFolder($folder);
        $this->setResponseHandler($response);
    }

    /**
     * @param Response|null $response
     */
    public function setResponseHandler(Response $response = null)
    {
        $this->response = $response;
    }

    /**
     * @return Response|null
     */
    public function getResponseHandler(): ?Response
    {
        return $this->response;
    }

    /**
     * @param string|null $folder
     */
    public function setDefaultFolder(string $folder = null): void
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
     * @param string $template
     * @return bool
     */
    public function exists(string $template): bool
    {
        return $this->path($template)['exists'];
    }

    /**
     * @param string $template
     * @return array|null
     */
    public function path(string $template): ?array
    {
        list ($folder, $name) = Str::split($template, '::', 2);

        $r = [
            'folder'    => null,
            'name'      => null,
            'file'      => null,
            'exists'    => false
        ];

        if ($name) {
            $r['folder']    = $folder;
            $r['name']      = $name;

            if (isset($this->folders[$folder])) {
                $r['file']      = $this->folders[$folder] . '/' . $name . '.html';
                $r['exists']    = file_exists($r['file']);
            }
        } else {
            $r['name'] = $folder;

            if ($this->defaultFolder) {
                $r['file']      = $this->defaultFolder . '/' . $folder . '.html';
                $r['exists']    = file_exists($r['file']);
            }
        }

        return $r;
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
    public function getData(string $template = null): ?iterable
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
     * @param Functions $functions
     */
    public function loadFunction(Functions $functions): void
    {
        $functions->register($this);
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
    public function getRegisteredFunctions(): array
    {
        return $this->functions;
    }

    /**
     * @param string $function
     * @return bool
     */
    public function isRegisteredFunction(string $function): bool
    {
        return isset($this->functions[$function]);
    }

    /**
     * @param string $function
     * @return bool
     */
    public function getRegisteredFunction(string $function)
    {
        if ($this->isRegisteredFunction($function)) {
            return $this->functions[$function];
        }

        throw new ViewException(sprintf('Method [%s] is not a template registered function', $function));
    }

    /**
     * @param string $template
     * @param iterable|null $data
     * @return Template
     */
    public function make(string $template, iterable $data = null): Template
    {
        return new Template($template, $data, $this);
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