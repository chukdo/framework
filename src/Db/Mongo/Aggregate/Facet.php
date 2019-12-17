<?php

namespace Chukdo\Db\Mongo\Aggregate;

use Chukdo\Contracts\Db\Stage as StageInterface;

/**
 * Aggregate FacetPipelineStage.
 * https://docs.mongodb.com/manual/reference/operator/aggregation/facet/
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Facet implements StageInterface
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
     * Match constructor.
     *
     * @param PipelineStage $stage
     */
    public function __construct( PipelineStage $stage )
    {
        $this->stage = $stage;
    }

    /**
     * @param string $field
     *
     * @return FacetPipelineStage
     */
    public function facetPipelineStage( string $field ): FacetPipelineStage
    {
        return $this->pipe[ $field ] = new FacetPipelineStage( $this->stage() );
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
        $projection = [];

        foreach ( $this->pipe as $key => $stage ) {
            $projection[ $key ] = $stage->projection();
        }

        return $projection;
    }
}