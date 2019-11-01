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
Class Facet extends PipelineStage
{
	/**
	 * @return array
	 */
	public function projection(): array
	{
		$projection = [];
		
		foreach ( $this->pipe as $key => $stage ) {
			$projection[][ $key ] = $stage->projection();
		}
		
		return $projection;
	}
}