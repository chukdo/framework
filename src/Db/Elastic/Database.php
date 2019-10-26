<?php

Namespace Chukdo\DB\Elastic;

use Chukdo\Helper\Str;
use Chukdo\Json\Json;
use Elasticsearch\Client;
use Chukdo\Contracts\Json\Json as JsonInterface;
use Chukdo\Contracts\Db\Server as ServerInterface;
use Chukdo\Contracts\Db\Database as DatabaseInterface;
use Chukdo\Contracts\Db\Collection as CollectionInterface;
use Throwable;

/**
 * Server Server Database.
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Database implements DatabaseInterface
{
	/**
	 * @var Server
	 */
	protected $server;
	
	/**
	 * @var Client
	 */
	protected $client;
	
	/**
	 * @var string|null
	 */
	protected $database = null;
	
	/**
	 * Database constructor.
	 *
	 * @param Server      $server
	 * @param string|null $database
	 */
	public function __construct( Server $server, string $database = null )
	{
		$this->database = $database;
		$this->server   = $server;
		$this->client   = $server->client();
	}
	
	/**
	 * @return string|null
	 */
	public function prefixName(): ?string
	{
		return $this->name() !== null
			? $this->name() . '_'
			: null;
	}
	
	/**
	 * @return string|null
	 */
	public function name(): ?string
	{
		return $this->database;
	}
	
	/**
	 * @return ServerInterface
	 */
	public function server(): ServerInterface
	{
		return $this->server;
	}
	
	/**
	 * @return bool
	 */
	public function drop(): bool
	{
		$drop = true;
		foreach ( $this->collections() as $collection ) {
			$drop .= $this->collection( $collection )
			              ->drop();
		}
		
		return $drop;
	}
	
	/**
	 * @return Client
	 */
	public function client(): Client
	{
		return $this->client;
	}
	
	/**
	 * @param string $collection
	 *
	 * @return CollectionInterface
	 */
	public function collection( string $collection ): CollectionInterface
	{
		return new Collection( $this, $collection );
	}
	
	/**
	 * @param string $collection
	 *
	 * @return CollectionInterface
	 */
	public function createCollection( string $collection ): CollectionInterface
	{
		if ( !$this->collectionExist( $collection ) ) {
			$this->client()
			     ->indices()
			     ->create( [ 'index' => $collection ] );
		}
		
		return $this->collection( $collection );
	}
	
	/**
	 * @param string $collection
	 *
	 * @return bool
	 */
	public function collectionExist( string $collection ): bool
	{
		foreach ( $this->collections() as $coll ) {
			if ( $coll === $collection ) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * @return JsonInterface
	 */
	public function collections(): JsonInterface
	{
		$list    = new Json();
		$indices = $this->client()
		                ->cat()
		                ->indices();
		foreach ( $indices as $indice ) {
			if ( $this->name() === null ||
			     ( $this->name() !== null && Str::startWith( $indice[ 'index' ], $this->name() . '_' ) ) ) {
				$list->append( $indice[ 'index' ] );
			}
		}
		
		return $list;
	}
	
	/**
	 * @param string $collection
	 *
	 * @return DatabaseInterface
	 */
	public function dropCollection( string $collection ): DatabaseInterface
	{
		try {
			$this->client()
			     ->indices()
			     ->delete( [ 'index' => $this->prefixName() . $collection ] );
		} catch ( Throwable $e ) {
		}
		
		return $this;
	}
	
	/**
	 * @return JsonInterface
	 */
	public function info(): JsonInterface
	{
		$info  = new Json();
		$stats = new Json( $this->client()
		                        ->indices()
		                        ->stats( [ 'index' => '*' ] ) );
		foreach ( $stats->offsetGet( 'indices' ) as $key => $indice ) {
			if ( $indice instanceof JsonInterface && Str::startWith( $key, $this->name() ) ) {
				$info->offsetSet( $key, $indice->offsetGet( 'total' ) );
			}
		}
		
		return $info;
	}
}