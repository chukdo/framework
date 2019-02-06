<?php namespace Chukdo\Storage;

Use \Closure;
Use \Chukdo\Support\Singleton;

/**
 * Annuaire de ressource pour les flux de données
 *
 * @package 	Storage
 * @version 	1.0.0
 * @copyright 	licence MIT, Copyright (C) 2019 Domingo
 * @since 		08/01/2019
 * @author 		Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class ServiceLocator extends Singleton
{
    /**
     * @var array
     */
    private $cache = [];

    /**
     * @var array
     */
    private $resources = [];

    /**
     * Ajoute un service à l'annuaire
     *
     * @param $scheme
     * @param Closure $closure
     */
    public function setService($scheme, Closure $closure): void
    {
        $this->resources[$scheme] = $closure;
    }

    /**
     * Retourne un service de l'annuaire
     *
     * @param   string  $scheme
     * @return  closure
     * @throws  ServiceLocatorException
     */
    public function getService($scheme): Closure
    {
        if (!isset($this->resources[$scheme])) {
            throw new ServiceLocatorException(sprintf('[%s] is not a registered service', $scheme));
        }

        return $this->resources[$scheme];
    }

    /**
     * Retourne une ressource à l'annuaire
     *
     * @param   string  $scheme
     * @return  object
     * @throws  ServiceLocatorException
     */
    public function getResource(string $scheme)
    {
        if ($cache = $this->getCacheResource($scheme)) {
            return $cache;
        }

        $service  = $this->getService($scheme);
        $resource = call_user_func($service);

        $this->cacheResource($scheme, $resource);

        return $resource;
    }

    /**
     * Cache une ressource
     *
     * @param   string  $scheme
     * @param   $resource
     * @return  void
     */
    public function cacheResource(string $scheme, $resource): void
    {
        $this->cache[$scheme] = $resource;
    }

    /**
     * Retourne une ressource en cache
     *
     * @param string $scheme
     * @return mixed
     */
    public function getCacheResource(string $scheme)
    {
        return isset($this->cache[$scheme]) ?
            $this->cache[$scheme] :
            null;
    }

    /**
     * Supprime une ressource en cache
     *
     * @param   string  $scheme
     * @return  bool    true si le cache a été detruit false si le cache n'existait pas
     */
    public function unsetCacheResource(string $scheme): bool
    {
        if (isset($this->cache[$scheme])) {
            unset($this->cache[$scheme]);
            return true;
        }

        return false;
    }
}