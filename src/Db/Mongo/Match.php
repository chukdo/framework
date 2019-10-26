<?php

namespace Chukdo\Db\Mongo;

use Chukdo\Db\Mongo\Aggregate\Aggregate;

/**
 * Server Match.
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Match extends Where
{
	/**
	 * @var Collection
	 */
	protected $collection;
	
	/**
	 * @var Aggregate
	 */
	protected $aggregate;
	
	/**
	 * Match constructor.
	 *
	 * @param Aggregate  $aggregate
	 * @param Collection $collection
	 */
	public function __construct( Aggregate $aggregate, Collection $collection )
	{
		parent::__construct( $collection );
		$this->aggregate = $aggregate;
	}
	
	public function pipe(): Aggregate
	{
		return $this->aggregate;
	}
}