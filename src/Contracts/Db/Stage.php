<?php

namespace Chukdo\Contracts\Db;

use Chukdo\Db\Mongo\Aggregate\PipelineStage;

/**
 * Aggregate PipelineStage.
 *
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
interface Stage
{
	/**
	 * @return mixed
	 */
	public function projection();
	
	/**
	 * @return PipelineStage
	 */
	public function stage(): PipelineStage;
}