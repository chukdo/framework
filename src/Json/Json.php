<?php namespace Chukdo\Json;

use Chukdo\Xml\Xml;
use Closure;
use ArrayObject;

use \Chukdo\Helper\Test;
use \Chukdo\Helper\Data;
use \Chukdo\Helper\Convert;

/**
 * Gestion des exceptions
 *
 * @package 	Exception
 * @version 	1.0.0
 * @copyright 	licence MIT, Copyright (C) 2019 Domingo
 * @since 		08/01/2019
 * @author 		Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class JsonException extends \Exception {}


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
     * Defini si l'objet est défini ou non
     *
     * @var bool
     */
    protected $undefined = true;

    /**
     * json constructor.
     * @param mixed $data
     * @param bool $clean
     */
    public function __construct($data = null, bool $clean = false)
    {
        parent::__construct([]);

        $this->undefined = true;

        if (Test::isArray($data)) {
            foreach ($data as $k => $v) {
                $this->offsetSet($k, $v, $clean);
            }
        } else if (Test::isJson($data)) {
            foreach (json_decode($data, true) as $k => $v) {
                $this->offsetSet($k, $v, $clean);
            }
        }
    }

    /**
     * @return bool
     */
    public function isUndefined(): bool
    {
        return $this->isEmpty() && $this->undefined;
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
    public function getIndexKey(int $index = 0, $default = null)
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
        if (Test::isArray($value)) {
            parent::append(new Json($value));
        } else {
            parent::append($value);
        }

        $this->undefined = true;

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
     * @param bool $clean
     * @return Json
     */
    public function offsetSet($key, $value, bool $clean = false): self
    {
        if ($clean) {
            if ($value === '' || $value === null) {
                return $this;
            }
        }

        if (Test::isArray($value)) {
            parent::offsetSet($key, new Json($value, $clean));
        } else {
            parent::offsetSet($key, $value);
        }

        $this->undefined = false;

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
     * @return mixed|void|null
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
     * @param string $key
     * @param $value
     * @return Json
     */
    public function set(string $key, $value): self
    {
        return $this->offsetSet($key, $value);
    }

    /**
     * @param string $key
     * @param bool $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return $this->offsetGet($key, $default);
    }

    /**
     * @param iterable $merge
     * @param bool $overwrite
     * @return Json
     */
    public function merge(iterable $merge, bool $overwrite = true): self
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
     * @param bool $overwrite
     * @return Json
     */
    public function mergeRecursive(iterable $merge, bool $overwrite = true): self
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
     * @param bool $typehint
     * @param bool $matchOnly
     * @return Json
     */
    public static function hydrator(
        iterable $hydrate,
        Json $reference,
        bool $typehint = true,
        bool $matchOnly = false): Json
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
                        } else if ($refValue->count() === 1 && $refValue->getIndexKey(0) === 0) {
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
                            $hydrateValue = Convert::toType(gettype($refValue), $hydrateValue);
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
     * @param bool $typehint
     * @return Json
     */
    public function hydrate(iterable $populate, bool $typehint = true): Json
    {
        return self::hydrator($populate, $this, $typehint, false);
    }

    /**
     * @param iterable $populate
     * @param bool $typehint
     * @return Json
     */
    public function populate(iterable $populate, bool $typehint = true): Json
    {
        return self::hydrator($populate, $this, $typehint, true);
    }

    /**
     * Supprime les doublons de l'objet
     *
     * @return Json
     */
    public function unique(): self
    {
        $this->reset(array_unique($this->getArrayCopy()));

        return $this;
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
     * Supprime en recursif tous les champs vides
     *
     * @return Json
     */
    public function removeEmpty(): self
    {
        $toremove = [];

        foreach ($this as $k => $v) {
            if ($v instanceof Json) {
                $v->removeEmpty();

                if ($v->count() == 0) {
                    $toremove[] = $k;
                }
            } else {
                if ($v === '' || $v === null) {
                    $toremove[] = $k;
                }
            }
        }

        foreach ($toremove as $remove) {
            $this->offsetUnset($remove);
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
     * Retourne l'objet sous forme d'un tableau a 2 dimensions (path => value)
     *
     * @param mixed $path chemin de depart
     * @return array
     */
    public function toFlatJson($path = false)
    {
        $mixed = [];

        foreach ($this as $k => $v) {
            $k = ($path ? $path : '').'/'.ltrim($k, '/');

            if ($v instanceof Json) {
                $mixed = array_merge($mixed, $v->toFlatJson($k));
            } else {
                $mixed[$k] = $v;
            }
        }

        return $mixed;
    }


    /**
     * @param string $path
     * @param bool $assoc
     * @return Json|mixed|null
     */
    public function getPathMatch(string $path, bool $assoc = false)
    {
        /** Pas de chemin,  retour de l'offset */
        if (strpos($path, '/') === false) {
            return $this->offsetGet($path);
        }

        /** Mise à plat des données */
        $data = $this->toFlatJson();

        /** recherche une seule valeur */
        if (strpos($path, '*') === false) {
            if (isset($data[$path])) {
                return $data[$path];
            }

            return null;
        }

        /** recherche de valeurs multiples */
        $path   = trim($path, '/');
        $match  = '/^\/'.str_replace(['*', '/'], ['.*?', '\/'], $path).'$/';
        $values = new Json();

        foreach (Data::match($match, array_keys($data)) as  $value) {
            if ($assoc == true) {
                $values->offsetSet($value, $data[$value]);
            } else {
                $values->append($data[$value]);
            }
        }

        return $values;
    }

    /**
     * @param string $path
     * @return mixed|null
     */
    public function getPath(string $path)
    {
        /** Pas de chemin,  retour de l'offset */
        if (strpos($path, '/') === false) {
            return $this->offsetGet($path);
        }

        /** Mise à plat des données */
        $data = $this->toFlatJson();

        /** recherche une seule valeur */
        if (isset($data[$path])) {
            return $data[$path];
        }

        return null;
    }

    /**
     * @param string $path
     * @param $value
     * @return Json
     */
    public function setPath(string $path, $value): self
    {
        $path = explode('/', trim($path, '/'));
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
     * @return array
     */
    public function toArray(): array
    {
        return $this->getArrayCopy();
    }

    /**
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->getArrayCopy(), JSON_PRETTY_PRINT);
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

    /**
     * Delegation de methode
     *
     * @param $name
     * @param array $params
     * @throws JsonException
     */
    public function __call(string $name, array $params = [])
    {
        throw new JsonException("La methode $name n'existe pas");
    }
}
