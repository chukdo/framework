<?php

namespace Chukdo\Json;

use ArrayObject;
use Chukdo\Helper\Cli;
use Chukdo\Helper\Is;
use Chukdo\Helper\Str;
use Chukdo\Helper\To;
use Chukdo\Xml\Xml;
use Closure;
use League\CLImate\CLImate;

/**
 * Manipulation des données.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Json extends \ArrayObject
{
    /**
     * @var Closure
     */
    protected $setFilter;

    /**
     * @var Closure
     */
    protected $getFilter;

    /**
     * Json constructor.
     * @param array|iterable|null $data
     * @param Closure|null $setFilter
     * @param Closure|null $getFilter
     */
    public function __construct( $data = null, $setFilter = null, $getFilter = null )
    {
        parent::__construct([]);

        $this->setFilter = $setFilter;
        $this->getFilter = $getFilter;

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
            parent::offsetSet($key, $this->newParentClass($value));
        }
        else {
            parent::offsetSet($key, $this->setFilter($value));
        }

        return $this;
    }

    /**
     * @param mixed ...$params
     * @return mixed
     */
    protected function newParentClass( ... $params )
    {
        try {
            $rc = new \ReflectionClass(get_called_class());
            $rc->newInstanceArgs($params);

            return call_user_func_array([
                $rc,
                'newInstance',
            ],
                $params);
        } catch ( \Throwable $e ) {
        }
    }

    /**
     * @param $value
     * @return mixed
     */
    protected function setFilter( $value )
    {
        if ( $this->setFilter instanceof Closure ) {
            return ( $this->setFilter )($value);
        }

        return $value;
    }

    /**
     * @param $value
     * @return mixed
     */
    protected function getFilter( $value )
    {
        if ( $this->getFilter instanceof Closure ) {
            return ( $this->getFilter )($value);
        }

        return $value;
    }

    /**
     * @return Json
     */
    public function clean(): self
    {
        foreach ( $this->getArrayCopy() as $k => $v ) {
            if ( $v === false ) {
                $this->offsetUnset($k);
            }
        }

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
     * @return $this
     */
    public function all()
    {
        return $this;
    }

    /**
     * @return Json
     */
    public function clone(): Json
    {
        return $this->newParentClass($this->getArrayCopy());
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
     * @return $this
     */
    public function resetKeys(): Json
    {
        $this->reset(array_values($this->getArrayCopy()));

        return $this;
    }

    /**
     * @param array $reset
     * @return Json
     */
    public function reset( array $reset = [] ): self
    {
        parent::__construct([]);

        foreach ( $reset as $key => $value ) {
            $this->offsetSet($key, $value);
        }

        return $this;
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
     * @param string $key
     * @param int    $order
     * @return Json
     */
    public function sort( string $key, int $order = SORT_ASC ): self
    {
        $array  = $this->getArrayCopy();
        $toSort = [];

        foreach ( $array as $k => $v ) {
            $toSort[ $k ] = $v[ $key ];
        }

        array_multisort($toSort, $order, $array);

        $this->reset($array);

        return $this;
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
            return $this->getFilter(parent::offsetGet($key));
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
                     && $this->instanceOfJson($this->offsetGet($k)) ) {
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
     * @param $object
     * @return bool
     */
    protected function instanceOfJson( $object ): bool
    {
        return $object instanceof Json || is_subclass_of($object, 'Chukdo\Json\Json');
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
            parent::append($this->newParentClass($value));
        }
        else {
            parent::append($value);
        }

        return $this;
    }

    /**
     * Applique une fonction aux resultats.
     * @param Closure $closure
     * @return Json
     */
    public function filter( Closure $closure ): self
    {
        foreach ( $this as $k => $v ) {
            $this->offsetSet($k, $closure($k, $v));
        }

        return $this;
    }

    /**
     * Applique une fonction aux resultats de maniere recursive.
     * @param closure $closure
     * @return Json
     */
    public function filterRecursive( Closure $closure ): self
    {
        foreach ( $this as $k => $v ) {
            if ( $this->instanceOfJson($v) ) {
                $v->filterRecursive($closure);
            }
            else {
                $this->offsetSet($k, $closure($k, $v));
            }
        }

        return $this;
    }

    /**
     * Appel d'une methode callback.
     * @param $callback
     * @return Json
     */
    public function callBack( $callback ): self
    {
        $toCall = [];

        if ( is_callable($callback) ) {
            foreach ( $this as $key => $value ) {
                $toCall[ $key ] = $value;
            }

            foreach ( $toCall as $key => $value ) {
                $callback($this,
                    $key,
                    $value);
            }
        }

        return $this;
    }

    /**
     * @param mixed ...$offsets
     * @return Json
     */
    public function only( ...$offsets ): self
    {
        $only = $this->newParentClass([]);

        foreach ( $offsets as $offsetList ) {
            foreach ( (array) $offsetList as $offset ) {
                $only->set($offset,
                    $this->get($offset));
            }
        }

        return $only;
    }

    /**
     * @param string $path
     * @param null   $default
     * @return Json|mixed|null
     */
    public function get( string $path, $default = null )
    {
        if ( Str::notContain($path, '.') ) {
            return $this->offsetGet($path, $default);
        }

        $arr       = new Arr(Str::split($path, '.'));
        $firstPath = $arr->getFirstAndRemove();
        $endPath   = $arr->join('.');
        $get       = $this->offsetGet($firstPath);

        if ( $this->instanceOfJson($get) ) {
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
     * @param mixed ...$offsets
     * @return Json
     */
    public function except( ...$offsets ): self
    {
        $except = $this->newParentClass($this->getArrayCopy());

        foreach ( $offsets as $offsetList ) {
            foreach ( (array) $offsetList as $offset ) {
                $except->unset($offset);
            }
        }

        return $except;
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

        if ( $this->instanceOfJson($get) ) {
            return $get->unset($endPath);
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
        $json      = $this->newParentClass([]);
        $get       = $this->offsetGet($firstPath);

        if ( $firstPath == '*' ) {
            foreach ( $this as $key => $value ) {
                if ( $this->instanceOfJson($value) ) {
                    if ( ( $get = $value->wildcard($endPath,
                        $scalarResultOnly) )->count() ) {
                        $json->offsetSet($key,
                            $get);
                    }
                }
            }
        }
        elseif ( $this->instanceOfJson($get) && !$emptyPath ) {
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

            if ( $this->instanceOfJson($v) ) {
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
     * @return Xml
     * @throws \Chukdo\Xml\NodeException
     * @throws \Chukdo\Xml\XmlException
     */
    public function toXml()
    {
        $xml = new Xml();
        $xml->import($this->toArray());

        return $xml;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->getArrayCopy();
    }

    /**
     * @param string|null $title
     * @param string|null $color
     * @param string|null $widthFirstCol
     * @return string
     */
    public function toHtml( string $title = null, string $color = null, string $widthFirstCol = null ): string
    {
        return To::html($this, $title, $color, $widthFirstCol);
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
