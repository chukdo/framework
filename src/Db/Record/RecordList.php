<?php

namespace Chukdo\Db\Record;

use Chukdo\Contracts\Db\Collection as CollectionInterface;
use Chukdo\Contracts\Json\Json as JsonInterface;
use Chukdo\DB\Elastic\Collection;
use Chukdo\Json\Json;

/**
 * Server RecordList.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class RecordList extends Json
{
	/**
	 * @var CollectionInterface|Collection
	 */
	protected $collection;

	/**
	 * @var bool
	 */
	protected $hiddenId = false;

	/**
	 * @var bool
	 */
	protected $idAsKey = false;

	/**
	 * RecordList constructor.
	 *
	 * @param CollectionInterface $collection
	 * @param JsonInterface       $json
	 * @param bool                $idAsKey
	 * @param bool                $hiddenId
	 */
	public function __construct( CollectionInterface $collection, JsonInterface $json, bool $idAsKey = false, bool $hiddenId = false )
	{
		$this->collection = $collection;
		$this->hiddenId   = $hiddenId;
		$this->idAsKey    = $idAsKey;

		parent::__construct( [], false );

		foreach ( $json as $k => $v ) {
			$this->append( $v );
		}
	}

	/**
	 * @param mixed $value
	 *
	 * @return JsonInterface
	 */
	public function append( $value ): JsonInterface
	{
		parent::append( $this->collection()
							 ->record( $value, $this->hiddenId ) );

		return $this;
	}

	/**
	 * @return CollectionInterface|Collection
	 */
	public function collection(): CollectionInterface
	{
		return $this->collection;
	}
}