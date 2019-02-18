<?php namespace Chukdo\Json;

use Chukdo\Xml\Xml;
use Closure;
use ArrayObject;

use \Chukdo\Helper\Is;
use \Chukdo\Helper\Str;
use \Chukdo\Helper\To;

/**
 * Manipulation des données
 *
 * @package     Json
 * @version 	1.0.0
 * @copyright 	licence MIT, Copyright (C) 2019 Domingo
 * @since 		08/01/2019
 * @author Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Json extends \ArrayObject
{
    /**
     * json constructor.
     * @param mixed $data
     */
    public function __construct($data = null)
    {
        parent::__construct([]);

        if (Is::arr($data)) {
            foreach ($data as $k => $v) {
                $this->offsetSet($k, $v);
            }
        } else if (Is::json($data)) {
            foreach (json_decode($data, true) as $k => $v) {
                $this->offsetSet($k, $v);
            }
        }
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
        $this->reset(array_values($this->getArrayCopy()));

        return $this;
    }

    /**
     * @param array $reset
     * @return Json
     */
    public function reset(array $reset = []): self
    {
        parent::__construct([]);

        foreach ($reset as $key => $value) {
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
     * @return mixed
     */
    public function getLast()
    {
        return $this->getIndex($this->count() - 1);
    }

    /**
     * @param int $key
     * @param mixed $default
     * @return mixed|null
     */
    public function getIndex(int $key = 0, $default = null)
    {
        $index = 0;

        if ($this->count() > 0) {
            foreach($this as $value) {
                if ($key == $index) {
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
     * @return int|mixed|string|null
     */
    public function getKeyIndex(int $index = 0, $default = null)
    {
        if ($this->count() > 0) {
            foreach($this as $key => $value) {
                if ($key == $index) {
                    return $key;
                }
            }
        }

        return $default;
    }

    /**
     * @param string $key
     * @param int $order
     * @return Json
     */
    public function sort(string $key, int $order = SORT_ASC): self
    {
        $array  = $this->getArrayCopy();
        $toSort = [];

        foreach ($array as $k => $v) {
            $toSort[$k] = $v[$key];
        }

        array_multisort($toSort, $order, $array);

        $this->reset($array);

        return $this;
    }

    /**
     * @param mixed $value
     * @return Json
     */
    public function append($value): self
    {
        if (Is::arr($value)) {
            parent::append(new Json($value));
        } else {
            parent::append($value);
        }

        return $this;
    }

    /**
     * @param $value
     * @return bool
     */
    public function in($value): bool
    {
        foreach ($this as $v) {
            if ($v === $value) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param mixed $key
     * @param mixed $value
     * @return Json
     */
    public function offsetSet($key, $value): self
    {
        if (Is::arr($value)) {
            parent::offsetSet($key, new Json($value));
        } else {
            parent::offsetSet($key, $value);
        }

        return $this;
    }

    /**
     * @param mixed $key
     * @param null $default
     * @return mixed|null
     */
    public function offsetGet($key, $default = null)
    {
        if ($this->offsetExists($key)) {
            return parent::offsetGet($key);
        }

        return $default;
    }

    /**
     * @param mixed $key
     * @return mixed|null
     */
    public function offsetUnset($key)
    {
        if ($this->offsetExists($key)) {
            $offset = parent::offsetGet($key);
            parent::offsetUnset($key);
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
        les sous elements data_array en array */
        foreach ($array as $k => $v) {
            if ($v instanceof arrayObject) {
                $array[$k] = $v->getArrayCopy();
            }
        }

        return $array;
    }

    /**
     * @param iterable $merge
     * @param bool|null $overwrite
     * @return Json
     */
    public function merge(iterable $merge, bool $overwrite = null): self
    {
        foreach ($merge as $k => $v) {
            if ($overwrite || !$this->offsetExists($k)) {
                $this->offsetSet($k, $v);
            }
        }

        return $this;
    }

    /**
     * @param iterable $merge
     * @param bool|null $overwrite
     * @return Json
     */
    public function mergeRecursive(iterable $merge, bool $overwrite = null): self
    {
        foreach ($merge as $k => $v) {

            /** Les deux sont iterables on boucle en recursif */
            if (is_iterable($v) && $this->offsetGet($k) instanceof Json) {
                $this->offsetGet($k)->mergeRecursive($v, $overwrite);
                continue;
            }

            if ($overwrite || !$this->offsetExists($k)) {
                $this->offsetSet($k, $v);
            }
        }

        return $this;
    }

    /**
     * @param iterable $data
     * @return Json
     */
    public function addToSet(iterable $data): self
    {
        foreach ($data as $k => $v) {
            if (!$this->in($v)) {
                $this->append($v);
            }
        }

        return $this;
    }

    /**
     * @param iterable $hydrate
     * @param Json $reference
     * @param bool|null $typehint
     * @param bool|null $matchOnly
     * @return Json
     */
    public static function hydrator(
        iterable $hydrate,
        Json $reference,
        bool $typehint = null,
        bool $matchOnly = null): Json
    {
        $data = new Json();

        foreach ($reference as $refKey => $refValue) {
            $match = false;

            foreach ($hydrate as $hydrateKey => $hydrateValue) {
                if ($refKey == $hydrateKey) {
                    if ($refValue instanceof Json) {

                        /** Tableau vide */
                        if ($refValue->isEmpty()) {
                            $data->offsetSet($hydrateKey, array_values((array) $hydrateValue));

                            /** Tableau avec un element de structure */
                        } else if ($refValue->count() === 1 && $refValue->getKeyIndex(0) === 0) {
                            $refValueClone  = reset($refValue->getArrayCopy());
                            $refValueClones = new Json();

                            foreach ($hydrateValue as $v) {
                                $refValueClones->append($refValueClone);
                            }

                            $data->offsetSet($hydrateKey, self::hydrator($hydrateValue, $refValueClones, $typehint, $matchOnly));

                        /** Objet */
                        } else {
                            $data->offsetSet($hydrateKey, self::hydrator($hydrateValue, $refValue, $typehint, $matchOnly));
                        }
                    } else {

                        /** Typage */
                        if ($typehint == true) {
                            $hydrateValue = To::type(gettype($refValue), $hydrateValue);
                        }

                        $data->offsetSet($hydrateKey, $hydrateValue);
                    }

                    $match = true;
                    break;
                }
            }

            /** Hydrate or populate */
            if (!$match && !$matchOnly) {
                $data->offsetSet($refKey,
                    $refValue instanceof Json ?
                        $refValue->getArrayCopy() :
                        $refValue
                );
            }
        }

        return $data;
    }

    /**
     * @param iterable $populate
     * @param bool|null $typehint
     * @return Json
     */
    public function hydrate(iterable $populate, bool $typehint = null): Json
    {
        return self::hydrator($populate, $this, $typehint, false);
    }

    /**
     * @param iterable $populate
     * @param bool|null $typehint
     * @return Json
     */
    public function populate(iterable $populate, bool $typehint = null): Json
    {
        return self::hydrator($populate, $this, $typehint, true);
    }

    /**
     * Applique une fonction aux resultats
     *
     * @param Closure $closure
     * @return Json
     */
    public function filter(Closure $closure): self
    {
        foreach ($this as $k => $v) {
            $this->offsetSet($k, $closure($k, $v));
        }

        return $this;
    }

    /**
     * Applique une fonction aux resultats de maniere recursive
     *
     * @param closure $closure
     * @return Json
     */
    public function filterRecursive(Closure $closure): self
    {
        foreach ($this as $k => $v) {
            if ($v instanceof Json) {
                $v->filterRecursive($closure);
            } else {
                $this->offsetSet($k, $closure($k, $v));
            }
        }

        return $this;
    }

    /**
     * Appel d'une methode callback
     *
     * @param $callback
     * @return Json
     */
    public function callBack($callback): self
    {
        $toCall = [];

        if (is_callable($callback)) {
            foreach ($this as $key => $value) {
                $toCall[$key] = $value;
            }

            foreach ($toCall as $key => $value) {
                $callback($this, $key, $value);
            }
        }

        return $this;
    }

    /**
     * @param mixed ...$offsets
     * @return Json
     */
    public function only(...$offsets): Json
    {
        $only = new Json();

        foreach ($offsets as $offsetList) {
            foreach ((array) $offsetList as $offset) {
                $only->set($offset, $this->get($offset));
            }
        }

        return $only;
    }

    /**
     * @param mixed ...$offsets
     * @return Json
     */
    public function except(...$offsets): Json
    {
        $except = new Json($this->getArrayCopy());

        foreach ($offsets as $offsetList) {
            foreach ((array) $offsetList as $offset) {
                $except->unset($offset);
            }
        }

        return $except;
    }

    /**
     * @param string $path
     * @return bool
     */
    public function filled(string $path): bool
    {
        $value = $this->get($path);

        if ($value != null && $value != '') {
            return true;
        }

        return false;
    }

    /**
     * @param string $path
     * @return bool
     */
    public function exists(string $path): bool
    {
        $value = $this->get($path);

        if ($value !== null) {
            return true;
        }

        return false;
    }

    /**
     * @param string $path
     * @param null $default
     * @return Json|mixed|null
     */
    public function get(string $path, $default = null)
    {
        $dot        = Str::contain($path, '.');
        $wildcard   = Str::contain($path, '*');

        /** Pas de chemin, retourne offsetGet */
        if (!$dot && !$wildcard) {
            return $this->offsetGet($path, $default);
        }

        /** Mise à plat des données */
        $simpleArray = $this->toSimpleArray();

        /** recherche de valeurs multiples */
        $pattern    = '/^'.str_replace(['.', '*'], ['\.', '.*?'], $path).'/';
        $values     = new Json();

        foreach ($simpleArray as $key => $v) {
            if (Str::match($pattern, $key)) {
                $values->set($key, $simpleArray[$key]);
            }
        }

        return $values;
    }

    /**
     * @param string $path
     * @return Json
     */
    public function unset(string $path): self
    {
        $dot        = Str::contain($path, '.');
        $wildcard   = Str::contain($path, '*');

        /** Pas de chemin, retourne offsetGet */
        if (!$dot && !$wildcard) {
            $this->offsetUnset($path);
            return true;
        }

        $json = $this->get($path);

        foreach ($json as $key => $value) {
            // split key
                // loop
                    // à la fin offsetUnset
        }

        return $this;
    }

    /**
     * @param string $path
     * @param $value
     * @return Json
     */
    public function set(string $path, $value): self
    {
        $path = Str::split($path, '.');
        $end  = array_pop($path);
        $key  = $this;

        foreach ($path as $name) {
            if (($get = $key->offsetGet($name)) instanceof Json) {
                $key = $get;
            } else {
                $key->offsetSet($name, []);
                $key = $key->offsetGet($name);
            }
        }

        $key->offsetSet($end, $value);

        return $this;
    }

    /**
     * Retourne l'objet sous forme d'un tableau a 2 dimensions (path => value)
     *
     * @param string|null $path chemin de depart null par défaut
     * @return array
     */
    public function toSimpleArray(string $path = null): array
    {
        $mixed = [];

        foreach ($this as $k => $v) {
            $k = trim($path . '.' . $k, '.');

            if ($v instanceof Json) {
                $mixed = array_merge($mixed, $v->toSimpleArray($k));
            } else {
                $mixed[$k] = $v;
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
     * @return string
     */
    public function toJson(bool $prettyfy = false): string
    {
        return json_encode(
            $this->getArrayCopy(),
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
        $xml->import($this->getArrayCopy());

        return $xml;
    }

    /**
     * @param string|null $title
     * @param string|null $color
     * @return string
     */
    public function toHtml(string $title = null, string $color = null): string
    {
        $html = "<table id=\"JsonTableRender\" style=\"border-spacing:0;border-collapse:collapse;font-family:Helvetica;\">";

        if ($title) {
            $color = $color ?: '#499cef';
            $html .= "<thead style=\"color: #fff;background: $color;\">"
                . "<tr>"
                . "<th colspan=\"2\" style=\"padding:10px;\">$title</th>"
                . "</tr>"
                . "</thead>";
        }

        foreach ($this as $k => $v) {
            $v     = $v instanceof Json ? $v->toHtml() : $v;
            $html .= "<tr>"
                . "<td style=\"background:#eee;padding:8px;border:1px solid #eee;\">$k</td>"
                . "<td  style=\"padding:8px;border:1px solid #eee;\">$v</td>"
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
        $tree->setData($this->toArray());
        $tree->setRenderer(new \cli\tree\Ascii);
        $tree->display();
    }

    /**
     * @param mixed ...$param
     * @return mixed
     */
    public function to(...$param)
    {
        $function = array_shift($param);
        $param[0] = $this->get($param[0]);

        return call_user_func_array(['\Chukdo\Helper\To', $function], $param);
    }

    /**
     * @param mixed ...$param
     * @return mixed
     */
    public function is(...$param)
    {
        $function = array_shift($param);
        $param[0] = $this->get($param[0]);

        return call_user_func_array(['\Chukdo\Helper\Is', $function], $param);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function __isset(string $key): bool
    {
        return $this->offsetExists($key);
    }

    /**
     * @param string $key
     * @param $value
     */
    public function __set(string $key, $value): void
    {
        $this->offsetSet($key, $value);
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function __get(string $key)
    {
        return $this->offsetExists($key) ? $this->offsetGet($key) : null;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function __unset(string $key): bool
    {
        return (bool) $this->offsetUnset($key);
    }
}
