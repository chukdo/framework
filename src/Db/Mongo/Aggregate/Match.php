<?php

namespace Chukdo\Db\Mongo\Aggregate;

use Chukdo\Db\Mongo\Where;
use Chukdo\Contracts\Db\Stage as StageInterface;

/**
 * Server Match.
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Match extends Where implements StageInterface
{
	/**
	 * @var array
	 */
	protected $pipe = [];
	
	/**
	 * @var stageInterface
	 */
	protected $stage;
	
	/**
	 * Set constructor.
	 *
	 * @param StageInterface $stage
	 */
	public function __construct( StageInterface $stage )
	{
		$this->stage = $stage;
	}
	
	/**
	 * @return StageInterface
	 */
	public function stage(): StageInterface
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
}