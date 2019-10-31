<?php

namespace Chukdo\Db\Mongo\Aggregate;

/**
 * Aggregate Facet.
 * https://docs.mongodb.com/manual/reference/operator/aggregation/facet/
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Facet extends Stage
{
	/**
	 * @var string
	 */
	protected $field;
	
	/**
	 * Facet constructor.
	 *
	 * @param string $field
	 */
	public function __construct( string $field )
	{
		$this->field = $field;
	}
	
	/**
	 * @return array
	 */
	public function projection(): array
	{
		return [ $this->field => $this->pipe ];
	}
}