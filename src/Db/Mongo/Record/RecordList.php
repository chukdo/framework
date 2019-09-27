<?php

namespace Chukdo\Db\Mongo\Record;

use Chukdo\Contracts\Db\RecordList as RecordListInterface;
use Chukdo\Json\Json;
use Chukdo\Db\Mongo\Collection;

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
	 * @var Collection
	 */
	protected $collection;

	/**
	 * RecordList constructor.
	 *
	 * @param Collection $collection
	 * @param null       $data
	 */
	public function __construct( Collection $collection, $data = null )
	{
		$this->collection = $collection;

		parent::__construct( $data, false );
	}

	/**
	 * @return Collection
	 */
	public function collection(): Collection
	{
		return $this->collection;
	}
}