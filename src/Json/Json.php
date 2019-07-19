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

        if ( $preFilter instanceof Closure ) {
            $this->preFilter = $preFilter;
        }

        if ( Is::iterable($data) ) {
            foreach ( $data as $k => $v ) {
                $this->offsetSet($k, $v);
            }
        }
        elseif ( Is::jsonString($data) ) {
            foreach ( json_decode($data, true) as $k => $v ) {
                $this->offsetSet($k, $v);
            }
        }
    }

    /**
     * @param mixed $key
     * @param mixed $value
     * @return JsonInterface
     */
    public function offsetSet( $key, $value ): JsonInterface
    {
        if ( Is::iterable($value) ) {
            parent::offsetSet($key, new Json($value, $this->preFilter));
        }
        else {
            parent::offsetSet($key, $this->preFilter instanceof Closure
                ? ( $this->preFilter )($key, $value)
                : $value);
        }

        return $this;
    }

    /**
     * @param JsonInterface $json
     * @return JsonInterface
     */
    public function intersect( JsonInterface $json ): JsonInterface
    {
        $intersect = new Json();

        foreach ( $this as $key => $value ) {
            if ( $json->offsetExists($key) ) {
                $intersect->offsetSet($key, $value);
            }
        }

        return $intersect;
    }

    /**
     * @param JsonInterface $json
     * @return JsonInterface
     */
    public function diff( JsonInterface $json ): JsonInterface
    {
        $diff = new Json();

        foreach ( $this as $key => $value ) {
            if ( !$json->offsetExists($key) ) {
                $diff->offsetSet($key, $value);
            }
        }

        return $diff;
    }

    /**
     * @param mixed $value
     * @return JsonInterface
     */
    public function append( $value ): JsonInterface
    {
        if ( Is::arr($value) ) {
            parent::append(new Json($value, $this->preFilter));
        }
        else {
            parent::append($this->preFilter instanceof Closure
                ? ( $this->preFilter )(null, $value)
                : $value);
        }

        return $this;
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
     * @param mixed $key
     * @return bool
     */
    public function offsetExists( $key ): bool
    {
        return parent::offsetExists($key);
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
     * @param string $key
     * @return bool
     */
    public function __isset( string $key ): bool
    {
        return $this->offsetExists($key);
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
     * @return array
     */
    public function toArray(): array
    {
        return $this->getArrayCopy();
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
     * @param string $key
     * @return bool
     */
    public function __unset( string $key ): bool
    {
        return (bool) $this->offsetUnset($key);
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
     * @param iterable $data
     * @return JsonInterface
     */
    public function addToSet( iterable $data ): JsonInterface
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
     * @return $this
     */
    public function all()
    {
        return $this;
    }

    /**
     * @param mixed $value
     * @return JsonInterface
     */
    public function appendIfNoExist( $value ): JsonInterface
    {
        foreach ( $this as $k => $v ) {
            if ( $v === $value ) {
                return $this;
            }
        }

        return $this->append($value);
    }

    /**
     * @return JsonInterface
     */
    public function clean(): JsonInterface
    {
        $json = new Json();

        foreach ( $this as $k => $v ) {
            if ( $v !== null && $v !== '' ) {
                $json->offsetSet($k, $v);
            }
        }

        return $json;
    }

    /**
     * @return Collect
     */
    public function collect(): Collect
    {
        return new Collect($this);
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
     * @param string|null $path
     * @param null        $default
     * @return mixed|null
     */
    public function get( ?string $path, $default = null )
    {
        if ( $path == null ) {
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
            return $get->get($endPath, $default);
        }

        return $default;
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
     * @param Closure $closure
     * @return JsonInterface
     */
    public function filter( Closure $closure ): JsonInterface
    {
        $json = new Json();

        foreach ( $this as $k => $v ) {
            $r = $closure($k, $v);

            if ( $r !== null ) {
                $json->offsetSet($k, $r);
            }
        }

        return $json;
    }

    /**
     * @param Closure $closure
     * @return JsonInterface
     */
    public function filterRecursive( Closure $closure ): JsonInterface
    {
        $json = new Json();

        foreach ( $this as $k => $v ) {
            if ( $v instanceof Json ) {
                $r = $v->filterRecursive($closure);

                if ( $r->count() > 0 ) {
                    $json->offsetSet($k, $r);
                }
            }
            else {
                $r = $closure($k, $v);

                if ( $r !== null ) {
                    $json->offsetSet($k, $r);
                }
            }
        }

        return $json;
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
     * @return int
     */
    public function count(): int
    {
        return parent::count();
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
     * @return mixed
     */
    public function getLast()
    {
        return $this->getIndex($this->count() - 1);
    }

    /**
     * @param $value
     * @return mixed
     */
    public function indexOf( $value )
    {
        foreach ( $this as $k => $v ) {
            if ( $v === $value ) {
                return $k;
            }
        }

        return null;
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
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->count() == 0
            ? true
            : false;
    }

    /**
     * @param string ...$names
     * @return JsonInterface
     */
    public function map( string ... $names ): JsonInterface
    {
        $json = new Json();

        foreach ( $this as $k => $v ) {
            if ( in_array($k, $names) ) {
                $json->offsetSet($k, $v);
            }
        }

        return $json;
    }

    /**
     * @param string ...$offsets
     * @return JsonInterface
     */
    public function with( string ...$offsets ): JsonInterface
    {
        $only = new Json();

        foreach ( $offsets as $offsetList ) {
            foreach ( (array) $offsetList as $offset ) {
                $only->set($offset, $this->get($offset));
            }
        }

        return $only;
    }

    /**
     * @param iterable|null $merge
     * @param bool|null     $overwrite
     * @return JsonInterface
     */
    public function merge( iterable $merge = null, bool $overwrite = null ): JsonInterface
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
     * @return JsonInterface
     */
    public function mergeRecursive( iterable $merge = null, bool $overwrite = null ): JsonInterface
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
     * @param string|null $title
     * @param string|null $color
     * @return string
     */
    public function toHtml( string $title = null, string $color = null ): string
    {
        return To::html($this, $title, $color, true);
    }

    /**
     * @param string|null $prefix
     * @return JsonInterface
     */
    public function to2d( string $prefix = null ): JsonInterface
    {
        $mixed = new Json();

        foreach ( $this as $k => $v ) {
            $k = trim($prefix . '.' . $k,
                '.');

            if ( $v instanceof JsonInterface ) {
                $mixed->merge($v->to2d($k));
            }
            else {
                $mixed->offsetSet($k, $v);
            }
        }

        return $mixed;
    }

    /**
     * @return Xml
     */
    public function toXml(): Xml
    {
        $xml = new Xml();
        $xml->import($this->toArray());

        return $xml;
    }

    /**
     * @param string ...$offsets
     * @return JsonInterface
     */
    public function without( string ... $offsets ): JsonInterface
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
     * @param string $path
     * @param        $value
     * @return JsonInterface
     */
    public function set( string $path, $value ): JsonInterface
    {
        if ( Str::notContain($path, '.') ) {
            return $this->offsetSet($path, $value);
        }

        $arr       = new Arr(Str::split($path, '.'));
        $firstPath = $arr->getFirstAndRemove();
        $endPath   = $arr->join('.');

        return $this->offsetGetOrSet($firstPath)
            ->set($endPath, $value);
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
     * @param array $arr
     * @return JsonInterface
     */
    public function reset( array $arr = [] ): JsonInterface
    {
        parent::__construct($arr);

        return $this;
    }

    /**
     * @return JsonInterface
     */
    public function resetKeys(): JsonInterface
    {
        $arr = array_values($this->toArray());

        return $this->reset($arr);
    }

    /**
     * @param string $path
     * @param bool   $scalarResultOnly
     * @return JsonInterface
     */
    public function wildcard( string $path, $scalarResultOnly = false ): JsonInterface
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


}
