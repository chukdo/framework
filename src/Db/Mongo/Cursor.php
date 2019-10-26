<?php

namespace Chukdo\Db\Mongo;

use MongoDB\Driver\Cursor as MongoDbCursor;
use Iterator;
use IteratorIterator;
use Traversable;

/**
 * Mongodb cursor.
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Cursor implements Iterator
{
	/**
	 * @var Collection
	 */
	protected $collection;
	
	/**
	 * @var Find
	 */
	protected $find;
	
	/**
	 * @var MongoDbCursor
	 */
	protected $cursor;
	
	/**
	 * @var Iterator
	 */
	protected $iterator;
	
	/**
	 * Cursor constructor.
	 *
	 * @param Collection  $collection
	 * @param Traversable $cursor
	 */
	public function __construct( Collection $collection, Traversable $cursor )
	{
		$this->collection = $collection;
		$this->cursor     = $cursor;
		$this->cursor->setTypeMap( [
			                           'root'     => 'array',
			                           'document' => 'array',
			                           'array'    => 'array',
		                           ] );
		$this->iterator = new IteratorIterator( $this->cursor );
		$this->iterator->rewind();
	}
	
	/**
	 * @return MongoDbCursor
	 */
	public function cursor(): MongoDbCursor
	{
		return $this->cursor;
	}
	
	/**
	 * Return the current element
	 *
	 * @link  https://php.net/manual/en/iterator.current.php
	 * @return mixed Can return any type.
	 * @since 5.0.0
	 */
	public function current()
	{
		return $this->collection->record( $this->iterator->current() );
	}
	
	/**
	 * Return the key of the current element
	 *
	 * @link  https://php.net/manual/en/iterator.key.php
	 * @return mixed scalar on success, or null on failure.
	 * @since 5.0.0
	 */
	public function key()
	{
		return $this->iterator->key();
	}
	
	/**
	 * Move forward to next element
	 *
	 * @link  https://php.net/manual/en/iterator.next.php
	 * @return void Any returned value is ignored.
	 * @since 5.0.0
	 */
	public function next()
	{
		$this->iterator->next();
	}
	
	/**
	 * Rewind the Iterator to the first element
	 *
	 * @link  https://php.net/manual/en/iterator.rewind.php
	 * @return void Any returned value is ignored.
	 * @since 5.0.0
	 */
	public function rewind()
	{
		$this->iterator->rewind();
	}
	
	/**
	 * Checks if current position is valid
	 *
	 * @link  https://php.net/manual/en/iterator.valid.php
	 * @return boolean The return value will be casted to boolean and then evaluated.
	 * Returns true on success or false on failure.
	 * @since 5.0.0
	 */
	public function valid()
	{
		return $this->iterator->valid();
	}
}