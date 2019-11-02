<?php

namespace Chukdo\Db\Mongo\Aggregate;

use Chukdo\Contracts\Db\Stage as StageInterface;

/**
 * Aggregate PipelineStage.
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class PipelineStage implements StageInterface
{
    use TraitPipelineStage;

    /**
     * @var array
     */
    protected $pipe = [];

    /**
     * @return $this
     */
    public function stage(): self
    {
        return $this;
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

    /**
     * @param $pipe
     *
     * @return StageInterface
     */
    public function pipeStage( $pipe ): StageInterface
    {
        $key = '$' . $pipe;

        if ( isset( $this->pipe[ $key ] ) ) {
            return $this->pipe[ $key ];
        }

        $class = '\Chukdo\DB\Mongo\Aggregate\\' . ucfirst( $pipe );

        return $this->pipe[ $key ] = new $class( $this );
    }
}