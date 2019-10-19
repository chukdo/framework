<?php

namespace Chukdo\Db\Mongo\Schema;

use Chukdo\Contracts\Db\Collection as CollectionInterface;
use Chukdo\Contracts\Db\Schema as SchemaInterface;
use Chukdo\Contracts\Db\Property as PropertyInterface;
use Chukdo\Contracts\Json\Json as JsonInterface;
use Chukdo\DB\Mongo\Collection;
use Chukdo\Json\Json;

/**
 * Server Schema.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Schema implements SchemaInterface
{
	/**
	 * @var Collection
	 */
	protected $collection;

	/**
	 * @var Property
	 */
	protected $property;

	/**
	 * Schema constructor.
	 *
	 * @param Collection $collection
	 */
	public function __construct( Collection $collection )
	{
		$this->collection = $collection;
		$db               = $collection->database();
		$json             = $db->server()
							   ->command( [
								   'listCollections' => 1,
								   'filter'          => [ 'name' => $collection->name() ],
							   ], $db->name() );

		$this->property = new Property( $json->getJson( '0.options.validator.$jsonSchema' )
											 ->toArray() );
	}

	/**
	 * @return CollectionInterface
	 */
	public function collection(): CollectionInterface
	{
		return $this->collection;
	}

	/**
	 * @return bool
	 */
	public function drop(): bool
	{
		$schema = [
			'validator'        => [
				'$jsonSchema' => [ 'bsonType' => 'object' ],
			],
			'validationLevel'  => 'strict',
			'validationAction' => 'error',
		];

		$save = new Json( $this->collection()
							   ->database()
							   ->client()
							   ->modifyCollection( $this->collection()
														->name(), $schema ) );

		if ( $save->offsetGet( 'ok' ) === 1 ) {
			$this->property = new Property();

			return true;
		}

		return false;
	}

	/**
	 * @param string $name
	 *
	 * @return PropertyInterface|null
	 */
	public function get( string $name ): ?PropertyInterface
	{
		return $this->property()
					->get( $name );
	}

	/**
	 * @param string $name
	 *
	 * @return SchemaInterface
	 */
	public function unset( string $name ): SchemaInterface
	{
		$this->property()
			 ->unset( $name );

		return $this;
	}

	/**
	 * @param array $properties
	 *
	 * @return SchemaInterface
	 */
	public function setAll( array $properties ): SchemaInterface
	{
		$this->property()
			 ->setAll( $properties );

		return $this;
	}

	/**
	 * @param string $name
	 * @param null   $type
	 * @param array  $options
	 *
	 * @return SchemaInterface
	 */
	public function set( string $name, $type = null, array $options = [] ): SchemaInterface
	{
		$this->property()
			 ->set( $name, $type, $options );

		return $this;
	}

	/**
	 * @return Property
	 */
	public function property(): PropertyInterface
	{
		return $this->property;
	}

	/**
	 * @return JsonInterface
	 */
	public function properties(): JsonInterface
	{
		return $this->property()
					->properties();
	}

	/**
	 * @return bool
	 */
	public function save(): bool
	{
		$schema = [
			'validator'        => [
				'$jsonSchema' => $this->toArray(),
			],
			'validationLevel'  => 'strict',
			'validationAction' => 'error',
		];

		$save = new Json( $this->collection()
							   ->database()
							   ->client()
							   ->modifyCollection( $this->collection()
														->name(), $schema ) );

		return $save->offsetGet( 'ok' ) === 1;
	}

	/**
	 * @return array
	 */
	public function toArray(): array
	{
		return $this->property()
					->toArray();
	}


}