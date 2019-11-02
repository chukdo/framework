<?php

namespace Chukdo\Db\Mongo\Aggregate;

use Chukdo\Db\Mongo\TraitWhere;
use Chukdo\Contracts\Db\Stage as StageInterface;

/**
 * Server Match.
 * https://docs.mongodb.com/manual/reference/operator/aggregation/match/
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Match implements StageInterface
{
	use TraitPipelineStage, TraitWhere {
		TraitWhere::where insteadof TraitPipelineStage;
	}
	
	/**
	 * @var array
	 */
	protected $pipe = [];
	
	/**
	 * @var stageInterface
	 */
	protected $stage;
	
	/**
	 * Stage constructor.
	 *
	 * @param PipelineStage $stage
	 */
	public function __construct( PipelineStage $stage )
	{
		$this->stage = $stage;
	}
	
	/**
	 * @return PipelineStage
	 */
	public function stage(): PipelineStage
	{
		return $this->stage;
	}
	
	/**
	 * @return array
	 */
	public function projection(): array
	{
		return $this->filter();
	}
	
	/**
	 * @param $pipe
	 *
	 * @return StageInterface
	 */
	public function pipeStage( $pipe ): StageInterface
	{
		return $this->stage()
		            ->pipeStage( $pipe );
	}
}