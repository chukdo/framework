<?php namespace Chukdo\Logger\Handlers;

use \Chukdo\Contracts\Db\Redis as RedisInterface;

/**
 * Gestionnaire des flux Redis
 *
 * @package 	Logger
 * @version 	1.0.0
 * @copyright 	licence MIT, Copyright (C) 2019 Domingo
 * @since 		08/01/2019
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
     * RedisHandler constructor.
     * @param RedisInterface $redis
     * @param string $key
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
     * @param array $record
     * @return bool
     */
    public function write(array $record): bool
    {
        try {
            $this->redis->rpush($this->key, $record['formatted']);
            return true;

        } catch (\Exception $e) {
            return false;
        }
    }
}