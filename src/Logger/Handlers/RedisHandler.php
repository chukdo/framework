<?php

namespace Chukdo\Logger\Handlers;

use Chukdo\Contracts\Db\Redis as RedisInterface;
use Exception;

/**
 * Gestionnaire de log pour Redis.
 *
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
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
	 *
	 * @param RedisInterface|null $redis
	 * @param string|null         $key
	 */
	public function __construct( ?RedisInterface $redis, string $key = null )
	{
		$this->redis = $redis;
		$this->key   = $key ?? 'log';
		parent::__construct();
	}
	
	/**
	 * Destructeur.
	 */
	public function __destruct()
	{
		$this->redis->__destruct();
		$this->redis = null;
	}
	
	/**
	 * @param string $record
	 *
	 * @return bool
	 */
	public function write( $record ): bool
	{
		try {
			$this->redis->rpush( $this->key, $record );
			
			return true;
		} catch ( Exception $e ) {
			return false;
		}
	}
}
