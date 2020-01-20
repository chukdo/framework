<?php

namespace Chukdo\Db\Mongo\Aggregate;

use Chukdo\Contracts\Db\Stage as StageInterface;

/**
 * Aggregate AddFields.
 * https://docs.mongodb.com/manual/reference/operator/aggregation/addFields/
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Stage extends AbstractPipelineStage
{
    /**
     * @var array
     */
    protected array $pipe = [];

    /**
     * @var PipelineStage
     */
    protected PipelineStage $stage;

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
     * @return array|mixed
     */
    public function projection()
    {
        return $this->pipe;
    }

    /**
     * @param $pipe
     *
     * @return StageInterface
     */
    public function pipeStage( $pipe ): StageInterface
    {
        return $this->stage()
                    ->stage()
                    ->pipeStage( $pipe );
    }

    /**
     * @return PipelineStage
     */
    public function stage(): PipelineStage
    {
        return $this->stage;
    }
}