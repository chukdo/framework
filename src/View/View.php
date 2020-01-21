<?php

namespace Chukdo\View;

use Closure;
use Chukdo\Helper\Str;
use Chukdo\Http\Response;
use Chukdo\Contracts\View\Functions;

/**
 * Moteur de template.
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class View
{
    /**
     * @var Response
     */
    protected Response $response;

    /**
     * @var array
     */
    protected array $folders = [];

    /**
     * @var string
     */
    protected string $defaultFolder;

    /**
     * @var array
     */
    protected iterable $sharedData = [];

    /**
     * @var array
     */
    protected array $sharedTemplateData = [];

    /**
     * @var array
     */
    protected array $functions = [];

    /**
     * View constructor.
     *
     * @param string|null   $folder
     * @param Response|null $response
     */
    public function __construct( string $folder = null, Response $response = null )
    {
        if ( $folder !== null ) {
            $this->setDefaultFolder( $folder );
        }

        $this->setResponseHandler( $response ?? new Response() );
    }

    /**
     * @param string $folder
     *
     * @return $this
     */
    public function setDefaultFolder( string $folder ): self
    {
        $this->defaultFolder = rtrim( (string) $folder, '/' );

        return $this;
    }

    /**
     * @param Response $response
     *
     * @return $this
     */
    public function setResponseHandler( Response $response ): self
    {
        $this->response = $response;

        return $this;
    }

    /**
     * @return View
     */
    public function instance(): self
    {
        return $this;
    }

    /**
     * @return Response
     */
    public function getResponseHandler(): Response
    {
        return $this->response;
    }

    /**
     * @param string $name
     * @param string $folder
     *
     * @return View
     */
    public function addFolder( string $name, string $folder ): self
    {
        $this->folders[ $name ] = rtrim( $folder, '/' );

        return $this;
    }

    /**
     * @param string $template
     *
     * @return bool
     */
    public function exists( string $template ): bool
    {
        return $this->path( $template )[ 'exists' ] ?? false;
    }

    /**
     * @param string $template
     *
     * @return array
     */
    public function path( string $template ): array
    {
        [
            $folder,
            $name,
        ] = Str::split( $template, '::', 2 );

        $r = [
            'folder' => null,
            'name'   => null,
            'file'   => null,
            'exists' => false,
        ];

        if ( $name ) {
            $r[ 'folder' ] = $folder;
            $r[ 'name' ]   = $name;

            if ( isset( $this->folders[ $folder ] ) ) {
                $r[ 'file' ]   = $this->folders[ $folder ] . '/' . $name . '.html';
                $r[ 'exists' ] = file_exists( $r[ 'file' ] );
            }
        }
        else {
            $r[ 'name' ] = $folder;

            if ( $this->defaultFolder ) {
                $r[ 'file' ]   = $this->defaultFolder . '/' . $folder . '.html';
                $r[ 'exists' ] = file_exists( $r[ 'file' ] );
            }
        }

        return $r;
    }

    /**
     * @param iterable          $data
     * @param array|string|null $templates
     *
     * @return View
     */
    public function addData( Iterable $data, $templates = null ): self
    {
        if ( $templates === null ) {
            $this->sharedData = $data;
        }
        else {
            foreach ( (array) $templates as $template ) {
                $this->sharedTemplateData[ $template ] = $data;
            }
        }

        return $this;
    }

    /**
     * @param string|null $template
     *
     * @return iterable|null
     */
    public function getData( string $template = null ): ?iterable
    {
        if ( $template === null ) {
            return $this->sharedData;
        }

        return $this->sharedTemplateData[ $template ] ?? null;
    }

    /**
     * @param Functions $functions
     *
     * @return View
     */
    public function loadFunction( Functions $functions ): self
    {
        $functions->register( $this );

        return $this;
    }

    /**
     * @param string  $name
     * @param Closure $closure
     *
     * @return View
     */
    public function registerFunction( string $name, Closure $closure ): self
    {
        $this->functions[ $name ] = $closure;

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
     *
     * @return Closure
     */
    public function callRegisteredFunction( string $function ): Closure
    {
        if ( $this->isRegisteredFunction( $function ) ) {
            return $this->functions[ $function ];
        }
        throw new ViewException( sprintf( 'Method [%s] is not a template registered function', $function ) );
    }

    /**
     * @param string $function
     *
     * @return bool
     */
    public function isRegisteredFunction( string $function ): bool
    {
        return isset( $this->functions[ $function ] );
    }

    /**
     * @param string        $template
     * @param iterable|null $data
     *
     * @return string
     */
    public function renderToString( string $template, iterable $data = [] ): string
    {
        return (string) $this->make( $template, $data );
    }

    /**
     * @param string        $template
     * @param iterable|null $data
     *
     * @return Template
     */
    public function make( string $template, iterable $data = [] ): Template
    {
        return new Template( $template, $data, $this );
    }

    /**
     * @param string        $template
     * @param iterable|null $data
     *
     * @return Response
     */
    public function render( string $template, iterable $data = [] ): Response
    {
        return $this->make( $template, $data )
                    ->render();
    }
}
