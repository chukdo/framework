<?php namespace Chukdo\Json;

use Chukdo\Xml\Xml;
use Closure;
use ArrayObject;

use Chukdo\Helper\Is;
use Chukdo\Helper\Str;

/**
 * Manipulation des données
 *
 * @package     Json
 * @version    1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Json extends \ArrayObject
{
    /**
     * json constructor.
     *
     * @param mixed $data
     */
    public function __construct( $data = null )
    {
        parent::__construct( [] );

        if ( Is::arr( $data ) ) {
            foreach ( $data as $k => $v ) {
                $this->offsetSet( $k, $v );
            }
        } else if ( Is::json( $data ) ) {
            foreach ( json_decode( $data, true ) as $k => $v ) {
                $this->offsetSet( $k, $v );
            }
        }
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
        return new Json( $this->getArrayCopy() );
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->count() == 0 ? true : false;
    }

    /**
     * @return $this
     */
    public function resetKeys(): Json
    {
        $this->reset( array_values( $this->getArrayCopy() ) );

        return $this;
    }

    /**
     * @param array $reset
     *
     * @return Json
     */
    public function reset( array $reset = [] ): self
    {
        parent::__construct( [] );

        foreach ( $reset as $key => $value ) {
            $this->offsetSet( $key, $value );
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFirst()
    {
        return $this->getIndex( 0 );
    }

    /**
     * @return mixed
     */
    public function getLast()
    {
        return $this->getIndex( $this->count() - 1 );
    }

    /**
     * @param int $key
     * @param mixed $default
     *
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

                $index++;
            }
        }

        return $default;
    }

    /**
     * @param int $index
     * @param null $default
     *
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
     * @param int $order
     *
     * @return Json
     */
    public function sort( string $key, int $order = SORT_ASC ): self
    {
        $array = $this->getArrayCopy();
        $toSort = [];

        foreach ( $array as $k => $v ) {
            $toSort[ $k ] = $v[ $key ];
        }

        array_multisort( $toSort, $order, $array );

        $this->reset( $array );

        return $this;
    }

    /**
     * @param mixed $value
     *
     * @return Json
     */
    public function append( $value ): self
    {
        if ( Is::arr( $value ) ) {
            parent::append( new Json( $value ) );
        } else {
            parent::append( $value );
        }

        return $this;
    }

    /**
     * @param $value
     *
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
     * @param $key
     * @param mixed $value
     *
     * @return mixed
     */
    public function offsetGetOrSet( $key, $value = null )
    {
        if ( $this->offsetExists( $key ) ) {
            return parent::offsetGet( $key );
        } else {
            return $this->offsetSet( $key, $value ?: [] )->offsetGet( $key );
        }
    }

    /**
     * @param mixed $key
     * @param mixed $value
     *
     * @return Json
     */
    public function offsetSet( $key, $value ): self
    {
        if ( Is::arr( $value ) ) {
            parent::offsetSet( $key, new self( $value ) );
        } else {
            parent::offsetSet( $key, $value );
        }

        return $this;
    }

    /**
     * @param mixed $key
     * @param null $default
     *
     * @return mixed|null
     */
    public function offsetGet( $key, $default = null )
    {
        if ( $this->offsetExists( $key ) ) {
            return parent::offsetGet( $key );
        }

        return $default;
    }

    /**
     * @param mixed $key
     *
     * @return mixed|null
     */
    public function offsetUnset( $key )
    {
        if ( $this->offsetExists( $key ) ) {
            $offset = parent::offsetGet( $key );
            parent::offsetUnset( $key );
            return $offset;
        }

        return null;
    }

    /**
     * @return array
     */
    public function getArrayCopy(): array
    {
        $array = parent::getArrayCopy();

        /** Iteration de l'objet pour convertir
         * les sous elements data_array en array */
        foreach ( $array as $k => $v ) {
            if ( $v instanceof arrayObject ) {
                $array[ $k ] = $v->getArrayCopy();
            }
        }

        return $array;
    }

    /**
     * @param iterable|null $merge
     * @param bool|null $overwrite
     *
     * @return Json
     */
    public function merge( iterable $merge = null, bool $overwrite = null ): self
    {
        if ( $merge ) {
            foreach ( $merge as $k => $v ) {
                if ( $overwrite || !$this->offsetExists( $k ) ) {
                    $this->offsetSet( $k, $v );
                }
            }
        }

        return $this;
    }

    /**
     * @param iterable|null $merge
     * @param bool|null $overwrite
     *
     * @return Json
     */
    public function mergeRecursive( iterable $merge = null, bool $overwrite = null ): self
    {
        if ( $merge ) {
            foreach ( $merge as $k => $v ) {

                /** Les deux sont iterables on boucle en recursif */
                if ( is_iterable( $v ) && $this->offsetGet( $k ) instanceof Json ) {
                    $this->offsetGet( $k )->mergeRecursive( $v, $overwrite );
                    continue;
                }

                if ( $overwrite || !$this->offsetExists( $k ) ) {
                    $this->offsetSet( $k, $v );
                }
            }
        }

        return $this;
    }

    /**
     * @param iterable $data
     *
     * @return Json
     */
    public function addToSet( iterable $data ): self
    {
        foreach ( $data as $k => $v ) {
            if ( !$this->in( $v ) ) {
                $this->append( $v );
            }
        }

        return $this;
    }

    /**
     * Applique une fonction aux resultats
     *
     * @param Closure $closure
     *
     * @return Json
     */
    public function filter( Closure $closure ): self
    {
        foreach ( $this as $k => $v ) {
            $this->offsetSet( $k, $closure( $k, $v ) );
        }

        return $this;
    }

    /**
     * Applique une fonction aux resultats de maniere recursive
     *
     * @param closure $closure
     *
     * @return Json
     */
    public function filterRecursive( Closure $closure ): self
    {
        foreach ( $this as $k => $v ) {
            if ( $v instanceof Json ) {
                $v->filterRecursive( $closure );
            } else {
                $this->offsetSet( $k, $closure( $k, $v ) );
            }
        }

        return $this;
    }

    /**
     * Appel d'une methode callback
     *
     * @param $callback
     *
     * @return Json
     */
    public function callBack( $callback ): self
    {
        $toCall = [];

        if ( is_callable( $callback ) ) {
            foreach ( $this as $key => $value ) {
                $toCall[ $key ] = $value;
            }

            foreach ( $toCall as $key => $value ) {
                $callback( $this, $key, $value );
            }
        }

        return $this;
    }

    /**
     * @param mixed ...$offsets
     *
     * @return Json
     */
    public function only( ...$offsets ): self
    {
        $only = new self();

        foreach ( $offsets as $offsetList ) {
            foreach ( (array) $offsetList as $offset ) {
                $only->set( $offset, $this->get( $offset ) );
            }
        }

        return $only;
    }

    /**
     * @param mixed ...$offsets
     *
     * @return Json
     */
    public function except( ...$offsets ): self
    {
        $except = new self( $this->getArrayCopy() );

        foreach ( $offsets as $offsetList ) {
            foreach ( (array) $offsetList as $offset ) {
                $except->unset( $offset );
            }
        }

        return $except;
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    public function filled( string $path ): bool
    {
        $value = $this->get( $path );

        if ( $value != null && $value != '' ) {
            return true;
        }

        return false;
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    public function exists( string $path ): bool
    {
        $value = $this->get( $path );

        if ( $value !== null ) {
            return true;
        }

        return false;
    }

    /**
     * @param string $path
     *
     * @return Json
     */
    public function wildcard( string $path ): self
    {
        $path = rtrim( $path, '.*' );
        $arr = new Arr( Str::split( $path, '.' ) );
        $firstPath = $arr->getFirstAndRemove();
        $emptyPath = $arr->empty();
        $endPath = $arr->join( '.' );
        $json = new self();
        $get = $this->offsetGet( $firstPath );

        if ( $firstPath == '*' ) {
            foreach ( $this as $key => $value ) {
                if ( $value instanceof Json ) {
                    if ( ( $get = $value->wildcard( $endPath ) )->count() ) {
                        $json->offsetSet( $key, $get );
                    }
                }
            }
        } else if ( $get instanceof Json && !$emptyPath ) {
            $json->offsetSet( $firstPath, $get->wildcard( $endPath ) );

        } else if ( $get && $emptyPath ) {
            $json->offsetSet( $firstPath, $get );
        }

        return $json;
    }

    /**
     * @param string $path
     * @param null $default
     *
     * @return Json|mixed|null
     */
    public function get( string $path, $default = null )
    {
        if ( Str::notContain( $path, '.' ) ) {
            return $this->offsetGet( $path, $default );
        }

        $arr = new Arr( Str::split( $path, '.' ) );
        $firstPath = $arr->getFirstAndRemove();
        $endPath = $arr->join( '.' );
        $get = $this->offsetGet( $firstPath );

        if ( $get instanceof Json ) {
            return $get->get( $endPath );
        }

        return $default;
    }

    /**
     * @param string $path
     *
     * @return mixed|null
     */
    public function unset( string $path )
    {
        if ( Str::notContain( $path, '.' ) ) {
            return $this->offsetUnset( $path );
        }

        $arr = new Arr( Str::split( $path, '.' ) );
        $firstPath = $arr->getFirstAndRemove();
        $endPath = $arr->join( '.' );
        $get = $this->offsetGet( $firstPath );

        if ( $get instanceof Json ) {
            return $get->unset( $endPath );
        }

        return null;
    }

    /**
     * @param string $path
     * @param $value
     *
     * @return Json
     */
    public function set( string $path, $value ): self
    {
        if ( Str::notContain( $path, '.' ) ) {
            return $this->offsetSet( $path, $value );
        }

        $arr = new Arr( Str::split( $path, '.' ) );
        $firstPath = $arr->getFirstAndRemove();
        $endPath = $arr->join( '.' );

        return $this->offsetGetOrSet( $firstPath )->set( $endPath, $value );
    }

    /**
     * Retourne l'objet sous forme d'un tableau a 2 dimensions (path => value)
     *
     * @param string|null $path chemin de depart null par défaut
     *
     * @return array
     */
    public function toSimpleArray( string $path = null ): array
    {
        $mixed = [];

        foreach ( $this as $k => $v ) {
            $k = trim( $path . '.' . $k, '.' );

            if ( $v instanceof Json ) {
                $mixed = array_merge( $mixed, $v->toSimpleArray( $k ) );
            } else {
                $mixed[ $k ] = $v;
            }
        }

        return $mixed;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->getArrayCopy();
    }

    /**
     * @param bool $prettyfy
     *
     * @return string
     */
    public function toJson( bool $prettyfy = false ): string
    {
        return json_encode(
            $this->toArray(),
            $prettyfy ?
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES :
                JSON_UNESCAPED_SLASHES
        );
    }

    /**
     * @return Xml
     * @throws \Chukdo\Xml\NodeException
     * @throws \Chukdo\Xml\XmlException
     */
    public function toXml()
    {
        $xml = new Xml();
        $xml->import( $this->toArray() );

        return $xml;
    }

    /**
     * @param string|null $title
     * @param string|null $color
     * @param string|null $widthFirstCol
     *
     * @return string
     */
    public function toHtml( string $title = null, string $color = null, string $widthFirstCol = null ): string
    {
        $html = "<table id=\"JsonTableRender\" style=\"border-spacing:0;border-collapse:collapse;font-family:Helvetica;width:100%;word-break:break-word;\">";

        if ( $title ) {
            $color = $color ?: '#499cef';
            $html .= "<thead style=\"color: #fff;background: $color;\">"
                . "<tr>"
                . "<th colspan=\"2\" style=\"padding:20px;font-size:30px;\">$title</th>"
                . "</tr>"
                . "</thead>";
        }

        foreach ( $this as $k => $v ) {
            $v = $v instanceof Json ? $v->toHtml( null, null, $widthFirstCol ) : $v;
            $html .= "<tr>"
                . "<td style=\"background:#eee;padding:6px;border:1px solid #eee;width:$widthFirstCol;\">$k</td>"
                . "<td  style=\"padding:6px;border:1px solid #eee;\">$v</td>"
                . "</tr>";
        }

        return $html . '</table>';
    }

    /**
     *
     */
    public function toConsole(): void
    {
        $tree = new \cli\Tree;
        $tree->setData( $this->toArray() );
        $tree->setRenderer( new \cli\tree\Ascii );
        $tree->display();
    }

    /**
     * @param mixed ...$param
     *
     * @return mixed
     */
    public function to( ...$param )
    {
        $function = array_shift( $param );
        $param[ 0 ] = $this->get( $param[ 0 ] );

        return call_user_func_array( [ '\Chukdo\Helper\To', $function ], $param );
    }

    /**
     * @param mixed ...$param
     *
     * @return mixed
     */
    public function is( ...$param )
    {
        $function = array_shift( $param );
        $param[ 0 ] = $this->get( $param[ 0 ] );

        return call_user_func_array( [ '\Chukdo\Helper\Is', $function ], $param );
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->toJson( true );
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function __isset( string $key ): bool
    {
        return $this->offsetExists( $key );
    }

    /**
     * @param string $key
     * @param $value
     */
    public function __set( string $key, $value ): void
    {
        $this->offsetSet( $key, $value );
    }

    /**
     * @param string $key
     *
     * @return mixed|null
     */
    public function __get( string $key )
    {
        return $this->offsetExists( $key ) ? $this->offsetGet( $key ) : null;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function __unset( string $key ): bool
    {
        return (bool) $this->offsetUnset( $key );
    }
}
