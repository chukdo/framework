<?php

namespace Chukdo\Db\Record;

use Chukdo\Contracts\Db\RecordList as RecordListInterface;
use Chukdo\Contracts\Db\Collection as CollectionInterface;
use Chukdo\Json\Json;

/**
 * Server RecordList.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class RecordList extends Json implements RecordListInterface
{
	/**
	 * @var CollectionInterface
	 */
	protected $collection;

	/**
	 * RecordList constructor.
	 *
	 * @param CollectionInterface $collection
	 * @param null                $data
	 */
	public function __construct( CollectionInterface $collection, $data = null )
	{
		$this->collection = $collection;

		parent::__construct( $data, false );
	}

	/**
	 * @return CollectionInterface
	 */
	public function collection(): CollectionInterface
	{
		return $this->collection;
	}
}