<?php

namespace Chukdo\Json;

use Chukdo\Contracts\Json\Json as JsonInterface;
use ArrayObject;
use Chukdo\Helper\Cli;
use Chukdo\Helper\Is;
use Chukdo\Helper\Str;
use Chukdo\Helper\To;
use Chukdo\Xml\Xml;
use Closure;
use League\CLImate\CLImate;
use Throwable;

/**
 * Manipulation des données.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Json extends ArrayObject implements JsonInterface
{
    /**
     * @var Closure|null
     */
    protected $preFilter = null;

    /**
     * Json constructor.
     * @param null $data
     * @param null $preFilter
     */
    public function __construct( $data = null, $preFilter = null )
    {
        parent::__construct([]);

        $this->preFilter = $preFilter;

        if ( Is::iterable($data) ) {
            foreach ( $data as $k => $v ) {
                $this->offsetSet($k, $v);
            }
        }
        elseif ( Is::json($data) ) {
            foreach ( json_decode($data,
                true) as $k => $v ) {
                $this->offsetSet($k, $v);
            }
        }
    }

    /**
     * @param mixed $key
     * @param mixed $value
     * @return self
     */
    public function offsetSet( $key, $value ): self
    {
        if ( Is::iterable($value) ) {
            parent::offsetSet($key, new Json($value));
        }
        else {
            parent::offsetSet($key, $this->preFilter instanceof Closure
                ? ( $this->preFilter )($key, $value)
                : $value);
        }

        return $this;
    }

    /**
     * @return Collect
     */
    public function collect(): Collect
    {
        return new Collect($this);
    }

    /**
     * @return $this
     */
    public function all()
    {
        return $this;
    }

    /**
     * @return array
     */
    public function getArrayCopy(): array
    {
        $array = parent::getArrayCopy();

        /* Iteration de l'objet pour convertir
         * les sous elements data_array en array */
        foreach ( $array as $k => $v ) {
            if ( $v instanceof arrayObject ) {
                $array[ $k ] = $v->getArrayCopy();
            }
        }

        return $array;
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->count() == 0
            ? true
            : false;
    }

    /**
     * @return mixed
     */
    public function getFirst()
    {
        return $this->getIndex(0);
    }

    /**
     * @param int   $key
     * @param mixed $default
     * @return mixed|null
     */
    public function getIndex( int $key = 0, $default = null )
    {
        $index = 0;

        if ( $this->count() > 0 ) {
            foreach ( $this as $value ) {
                if ( $key == $index ) {
                    return $value;
                }

                ++$index;
            }
        }

        return $default;
    }

    /**
     * @return mixed
     */
    public function getLast()
    {
        return $this->getIndex($this->count() - 1);
    }

    /**
     * @return mixed|null
     */
    public function getKeyFirst()
    {
        foreach ( $this as $key => $unused ) {
            return $key;
        }

        return null;
    }

    /**
     * @return mixed|null
     */
    public function getKeyLast()
    {
        $last = null;

        foreach ( $this as $key => $unused ) {
            $last = $key;
        }

        return $last;
    }

    /**
     * @param int  $index
     * @param null $default
     * @return int|mixed|string|null
     */
    public function getKeyIndex( int $index = 0, $default = null )
    {
        if ( $this->count() > 0 ) {
            foreach ( $this as $key => $value ) {
                if ( $key == $index ) {
                    return $key;
                }
            }
        }

        return $default;
    }

    /**
     * @param array $keys
     * @param null  $default
     * @return mixed|null
     */
    public function offsetGetFirstInList( array $keys, $default = null )
    {
        foreach ( $keys as $key ) {
            if ( $get = $this->offsetGet($key) ) {
                return $get;
            }
        }

        return $default;
    }

    /**
     * @param mixed $key
     * @param null  $default
     * @return mixed|null
     */
    public function offsetGet( $key, $default = null )
    {
        if ( $this->offsetExists($key) ) {
            return parent::offsetGet($key);
        }

        return $default;
    }

    /**
     * @param iterable|null $merge
     * @param bool|null     $overwrite
     * @return Json
     */
    public function merge( iterable $merge = null, bool $overwrite = null ): self
    {
        if ( $merge ) {
            foreach ( $merge as $k => $v ) {
                if ( $overwrite || !$this->offsetExists($k) ) {
                    $this->offsetSet($k, $v);
                }
            }
        }

        return $this;
    }

    /**
     * @param string ...$names
     * @return Json
     */
    public function map( string ... $names ): Json
    {
        $json = new Json();

        foreach ( $this as $k => $v ) {
            if (in_array($k, $names)) {
                $json->offsetSet($k, $v);
            }
        }

        return $json;
    }

    /**
     * @param Closure $closure
     * @return Json
     */
    public function filter( Closure $closure ): Json
    {
        $json = new Json();

        foreach ( $this as $k => $v ) {
            $r = $closure($k, $v);

            if ($r !== null) {
                $json->offsetSet($k, $r);
            }
        }

        return $json;
    }

    /**
     * @param Closure $closure
     * @return Json
     */
    public function filterRecursive( Closure $closure ): Json
    {
        $json = new Json();

        foreach ( $this as $k => $v ) {
            if ( $v instanceof Json) {
                $r = $v->filterRecursive($closure);

                if ($r->count() > 0) {
                    $json->offsetSet($k, $r);
                }
            }
            else {
                $r = $closure($k, $v);

                if ($r !== null) {
                    $json->offsetSet($k, $r);
                }
            }
        }

        return $json;
    }

    /**
     * @param mixed ...$offsets
     * @return Json
     */
    public function with( ...$offsets ): Json
    {
        $only = new Json();

        foreach ( $offsets as $offsetList ) {
            foreach ( (array) $offsetList as $offset ) {
                $only->set($offset, $this->get( $offset ));
            }
        }

        return $only;
    }

    /**
     * @param mixed ...$offsets
     * @return Json
     */
    public function without( ...$offsets ): Json
    {
        $except = new Json($this->toArray());

        foreach ( $offsets as $offsetList ) {
            foreach ( (array) $offsetList as $offset ) {
                $except->unset($offset);
            }
        }

        return $except;
    }

    /**
     * @param iterable|null $merge
     * @param bool|null     $overwrite
     * @return Json
     */
    public function mergeRecursive( iterable $merge = null, bool $overwrite = null ): self
    {
        if ( $merge ) {
            foreach ( $merge as $k => $v ) {
                /* Les deux sont iterables on boucle en recursif */
                if ( is_iterable($v)
                     && $this->offsetGet($k) instanceof JsonInterface ) {
                    $this->offsetGet($k)
                        ->mergeRecursive($v,
                            $overwrite);
                    continue;
                }

                if ( $overwrite || !$this->offsetExists($k) ) {
                    $this->offsetSet($k, $v);
                }
            }
        }

        return $this;
    }

    /**
     * @return Json
     */
    public function clean(): Json
    {
        $json = new Json();

        foreach ( $this as $k => $v ) {
            if ( $v !== null && $v !== '') {
                $json->offsetSet($k, $v);
            }
        }

        return $json;
    }

    /**
     * @param iterable $data
     * @return Json
     */
    public function addToSet( iterable $data ): self
    {
        foreach ( $data as $k => $v ) {
            if ( !$this->in($v) ) {
                $this->append($v);
            }
        }

        return $this;
    }

    /**
     * @param $value
     * @return bool
     */
    public function in( $value ): bool
    {
        foreach ( $this as $v ) {
            if ( $v === $value ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param mixed $value
     * @return Json
     */
    public function append( $value ): self
    {
        if ( Is::arr($value) ) {
            parent::append(new Json($value));
        }
        else {
            parent::append($value);
        }

        return $this;
    }

    /**
     * @param string|null $path
     * @param null        $default
     * @return mixed|null
     */
    public function get( ?string $path, $default = null )
    {
        if ($path == null) {
            return $default;
        }

        if ( Str::notContain($path, '.') ) {
            return $this->offsetGet($path, $default);
        }

        $arr       = new Arr(Str::split($path, '.'));
        $firstPath = $arr->getFirstAndRemove();
        $endPath   = $arr->join('.');
        $get       = $this->offsetGet($firstPath);

        if ( $get instanceof JsonInterface ) {
            return $get->get($endPath);
        }

        return $default;
    }

    /**
     * @param string $path
     * @param        $value
     * @return Json
     */
    public function set( string $path, $value ): self
    {
        if ( Str::notContain($path, '.') ) {
            return $this->offsetSet($path, $value);
        }

        $arr       = new Arr(Str::split($path, '.'));
        $firstPath = $arr->getFirstAndRemove();
        $endPath   = $arr->join('.');

        return $this->offsetGetOrSet($firstPath)
            ->set($endPath,
                $value);
    }

    /**
     * @param       $key
     * @param mixed $value
     * @return mixed
     */
    public function offsetGetOrSet( $key, $value = null )
    {
        if ( $this->offsetExists($key) ) {
            return parent::offsetGet($key);
        }
        else {
            return $this->offsetSet($key,
                $value
                    ?: [])
                ->offsetGet($key);
        }
    }

    /**
     * @param string $path
     * @return mixed|null
     */
    public function unset( string $path )
    {
        if ( Str::notContain($path, '.') ) {
            return $this->offsetUnset($path);
        }

        $arr       = new Arr(Str::split($path, '.'));
        $firstPath = $arr->getFirstAndRemove();
        $endPath   = $arr->join('.');
        $get       = $this->offsetGet($firstPath);

        if ( $get instanceof JsonInterface ) {
            return $get->unset($endPath);
        }

        return null;
    }

    /**
     * @param mixed $key
     * @return mixed|null
     */
    public function offsetUnset( $key )
    {
        if ( $this->offsetExists($key) ) {
            $offset = parent::offsetGet($key);
            parent::offsetUnset($key);

            return $offset;
        }

        return null;
    }

    /**
     * @param string $path
     * @return bool
     */
    public function filled( string $path ): bool
    {
        $value = $this->get($path);

        if ( $value != null && $value != '' ) {
            return true;
        }

        return false;
    }

    /**
     * @param string $path
     * @return bool
     */
    public function exists( string $path ): bool
    {
        $value = $this->get($path);

        if ( $value !== null ) {
            return true;
        }

        return false;
    }

    /**
     * @param string $path
     * @param bool   $scalarResultOnly
     * @return Json
     */
    public function wildcard( string $path, $scalarResultOnly = false ): self
    {
        $path      = rtrim($path, '.*');
        $arr       = new Arr(Str::split($path, '.'));
        $firstPath = $arr->getFirstAndRemove();
        $emptyPath = $arr->empty();
        $endPath   = $arr->join('.');
        $json      = new Json([]);
        $get       = $this->offsetGet($firstPath);

        if ( $firstPath == '*' ) {
            foreach ( $this as $key => $value ) {
                if ( $value instanceof JsonInterface ) {
                    if ( ( $get = $value->wildcard($endPath,
                        $scalarResultOnly) )->count() ) {
                        $json->offsetSet($key,
                            $get);
                    }
                }
            }
        }
        elseif ( $get instanceof JsonInterface && !$emptyPath ) {
            $json->offsetSet($firstPath,
                $get->wildcard($endPath,
                    $scalarResultOnly));
        }
        elseif ( $get && $emptyPath && ( ( is_scalar($get) && $scalarResultOnly ) || !$scalarResultOnly ) ) {
            $json->offsetSet($firstPath,
                $get);
        }

        return $json;
    }

    /**
     * @param string|null $prefix
     * @return array
     */
    public function toSimpleArray( string $prefix = null ): array
    {
        $mixed = [];

        foreach ( $this as $k => $v ) {
            $k = trim($prefix . '.' . $k,
                '.');

            if ( $v instanceof JsonInterface ) {
                $mixed = array_merge($mixed,
                    $v->toSimpleArray($k));
            }
            else {
                $mixed[ $k ] = $v;
            }
        }

        return $mixed;
    }

    /**
     * @param string|null $title
     * @param string|null $color
     * @return string
     */
    public function toHtml( string $title = null, string $color = null ): string
    {
        return To::html($this, $title, $color, true);
    }

    /**
     * @param string|null $title
     * @param string|null $color
     * @return string
     */
    public function toConsole( string $title = null, string $color = null ): string
    {
        if ( !Cli::runningInConsole() ) {
            throw new JsonException('You can call json::toConsole only in CLI mode.');
        }

        $climate = new CLImate();
        $climate->output->defaultTo('buffer');

        if ( $title ) {
            $climate->border();
            $climate->style->addCommand('colored', $color
                ?: 'green');
            $climate->colored(ucfirst($title
                ?: $this->name));
            $climate->border();
        }

        $climate->json($this->toArray());

        return $climate->output->get('buffer')
            ->get();
    }    /**
     * @return Xml
     */
    public function toXml(): Xml
    {
        $xml = new Xml();
        $xml->import($this->toArray());

        return $xml;
    }

    /**
     * @param mixed ...$param
     * @return mixed
     */
    public function to( ...$param )
    {
        $function   = array_shift($param);
        $param[ 0 ] = $this->get($param[ 0 ]);

        return call_user_func_array([
            '\Chukdo\Helper\To',
            $function,
        ],
            $param);
    }

    /**
     * @param mixed ...$param
     * @return mixed
     */
    public function is( ...$param )
    {
        $function   = array_shift($param);
        $param[ 0 ] = $this->get($param[ 0 ]);

        return call_user_func_array([
            '\Chukdo\Helper\Is',
            $function,
        ],
            $param);
    }    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->getArrayCopy();
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->toJson(true);
    }

    /**
     * @param bool $prettyfy
     * @return string
     */
    public function toJson( bool $prettyfy = false ): string
    {
        return json_encode($this->toArray(),
            $prettyfy
                ? JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
                : JSON_UNESCAPED_SLASHES);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function __isset( string $key ): bool
    {
        return $this->offsetExists($key);
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function __get( string $key )
    {
        return $this->offsetExists($key)
            ? $this->offsetGet($key)
            : null;
    }

    /**
     * @param string $key
     * @param        $value
     */
    public function __set( string $key, $value ): void
    {
        $this->offsetSet($key, $value);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function __unset( string $key ): bool
    {
        return (bool) $this->offsetUnset($key);
    }






}
