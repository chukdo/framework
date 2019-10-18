<?php

namespace Chukdo\Db\Record;

use Chukdo\Db\Elastic\ElasticException;
use Exception;
use DateTime;
use Chukdo\Json\Json;
use Chukdo\Contracts\Db\Collection as CollectionInterface;
use Chukdo\Contracts\Json\Json as JsonInterface;

/**
 * Server Record.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Record extends Json
{
	/**
	 * @var CollectionInterface
	 */
	protected $collection;

	/**
	 * @var mixed|null
	 */
	protected $id = null;

	/**
	 * Record constructor.
	 *
	 * @param CollectionInterface $collection
	 * @param null                $data
	 */
	public function __construct( CollectionInterface $collection, $data = null )
	{
		$json     = new Json( $data );
		$filtered = $json->filterRecursive( static function( $k, $v ) use ( $collection ) {
			return $collection->filterOut( $k, $v );
		} );

		$this->collection = $collection;
		$this->id         = $filtered->offsetUnset( '_id' );

		parent::__construct( $filtered, false );
	}

	/**
	 * @return Record
	 */
	public function delete(): Record
	{
		$write = $this->collection()
					  ->write();

		if ( ( $id = $this->id() ) !== null ) {
			$write->where( '_id', '=', $id );

			$write->deleteOne();

			return $this;
		}

		throw new ElasticException( 'No ID to delete Record' );
	}

	/**
	 * @return CollectionInterface
	 */
	public function collection(): CollectionInterface
	{
		return $this->collection;
	}

	/**
	 * @return string|null
	 */
	public function id(): ?string
	{
		return $this->id;
	}

	/**
	 * @param string $collection
	 *
	 * @return Record
	 * @throws Exception
	 */
	public function moveTo( string $collection ): Record
	{
		$write = $this->collection()
					  ->write();

		if ( ( $id = $this->id() ) !== null ) {
			$write->where( '_id', '=', $id );

			$this->collection()
				 ->database()
				 ->collection( $collection )
				 ->write()
				 ->setAll( $write->deleteOneAndGet() )
				 ->set( 'date_archived', new DateTime() )
				 ->insert();

			return $this;
		}

		throw new ElasticException( 'No ID to move Record' );
	}

	/**
	 * @return Record
	 */
	public function save(): Record
	{
		/** Insert */
		if ( $this->id() === null ) {
			$this->insert();

		} /** Update */
		else {
			$this->update();
		}

		return $this;
	}

	/**
	 * @return Record
	 */
	public function insert(): Record
	{
		$write = $this->collection()
					  ->write()
					  ->setAll( $this->record() );

		/** Insert */
		$this->id = $write->insert();

		return $this;
	}

	/**
	 * @return JsonInterface
	 */
	public function record(): JsonInterface
	{

		return $this->filterRecursive( static function( $k, $v ) {
			return $v instanceof Record
				? null
				: $v;
		} );
	}

	/**
	 * @return Record
	 */
	public function update(): Record
	{
		$write = $this->collection()
					  ->write()
					  ->setAll( $this->record() );

		/** Update */
		$write->where( '_id', '=', $this->id() )
			  ->updateOne();

		return $this;
	}
}