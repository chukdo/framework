<?php

namespace Chukdo\Db\Mongo\Aggregate;

use Chukdo\Helper\Arr;

/**
 * Aggregate BucketAuto.
 * https://docs.mongodb.com/manual/reference/operator/aggregation/bucketAuto/
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Facet extends Stage
{
	/**
	 * @var array
	 */
	protected $pipe = [];
	
	/**
	 * @param string $field
	 * @param        $stage
	 *
	 * @return $this
	 */
	public function set( string $field, $stage ): self
	{
		//$this->pipe[ $field ] = Expression::parseExpression( $expression );
		
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function projection(): array
	{
		return [ '$facet' => $this->pipe ];
	}
}