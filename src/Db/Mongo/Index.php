<?php

namespace Chukdo\Db\Mongo;

use Chukdo\Json\Json;
use Chukdo\Contracts\Json\Json as JsonInterface;
use Exception;

Class Index
{
	/**
	 * @var Collection
	 */
	protected $collection;
	
	/**
	 * @var array
	 */
	protected $index;
	
	/**
	 * Index constructor.
	 *
	 * @param Collection $collection
	 */
	public function __construct( Collection $collection )
	{
		$this->collection = $collection;
	}
	
	/**
	 * @return JsonInterface
	 */
	public function get(): JsonInterface
	{
		$indexes = new Json();
		foreach ( $this->collection()
		               ->client()
		               ->listIndexes() as $index ) {
			$indexes->offsetSet( $index[ 'name' ], $index[ 'key' ] );
		}
		
		return $indexes;
	}
	
	/**
	 * @param string $field
	 * @param int    $sort
	 * @param bool   $unique
	 *
	 * @return $this
	 */
	public function set( string $field, int $sort = SORT_DESC, bool $unique = false ): self
	{
		$name                  = $unique
			? $field . '_unique'
			: $field;
		$orderby               = $sort === SORT_ASC
			? 1
			: -1;
		$this->index[ $field ] = [
			'name'   => $name,
			'order'  => $orderby,
			'field'  => $field,
			'unique' => $unique,
		];
		$this->collection()
		     ->client()
		     ->createIndex( [ $field => $orderby ], [
			     'unique' => $unique,
			     'name'   => $name,
		     ] );
		
		return $this;
	}
	
	/**
	 * @return bool
	 */
	public function save(): bool
	{
		try {
			foreach ( $this->index as $index ) {
				$this->collection()
				     ->client()
				     ->createIndex( [ $index[ 'field' ] => $index[ 'order' ] ], [
					     'unique' => $index[ 'unique' ],
					     'name'   => $index[ 'name' ],
				     ] );
			}
			
			return true;
		} catch ( Exception $e ) {
			return false;
		}
	}
	
	/**
	 * @return bool
	 */
	public function drop(): bool
	{
		try {
			$this->collection()
			     ->client()
			     ->dropIndexes();
			
			return true;
		} catch ( Exception $e ) {
			return false;
		}
	}
	
	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function delete( string $name ): bool
	{
		try {
			$this->collection()
			     ->client()
			     ->dropIndex( $name );
			
			return true;
		} catch ( Exception $e ) {
			return false;
		}
	}
	
	/**
	 * @return Collection
	 */
	protected function collection(): Collection
	{
		return $this->collection;
	}
}
