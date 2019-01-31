<?php namespace Chukdo\Logger\Handlers;

use \Chukdo\Contracts\Db\Redis as RedisInterface;

/**
 * Gestionaire de db nosql redis
 *
 * @copyright 	licence MIT, Copyright (C) 2015 Domingo
 * @author 		Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class RedisHandler extends AbstractHandler
{
    /**
     * @var object
     */
    protected $redis;

    /**
     * @var string
     */
    protected $key;

    /**
     * Constructeur
     *
     * @param   \Chukdo\Contracts\Db\Redis  $redis RedisInterface
     * @param   string  $key cle de stockage dans redis
     */
    public function __construct(RedisInterface $redis, $key = 'log')
    {
        $this->redis = $redis;
        $this->key   = $key;

        parent::__construct();
    }

    /**
     * Destructeur
     *
     * @return void
     */
    public function __destruct()
    {
        $this->redis->__destruct();
        $this->redis = '';
    }

    /**
     * Ecriture de l'enregistrement
     *
     * @param  array    $record
     * @return bool     true si l'operation reussi false sinon
     */
    public function write(array $record)
    {
        try {
            $this->redis->rpush($this->key, $record['formatted']);
            return true;

        } catch (\Exception $e) {
            return false;
        }
    }
}