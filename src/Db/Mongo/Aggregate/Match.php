<?php

namespace Chukdo\Db\Mongo\Aggregate;

use Chukdo\Contracts\Db\Stage as StageInterface;
use Chukdo\Db\Mongo\TraitWhereOperation;

/**
 * Server Match.
 * https://docs.mongodb.com/manual/reference/operator/aggregation/match/
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Match extends Stage
{
    use TraitWhereOperation;

    /**
     * @var array
     */
    protected array $where = [];

    /**
     * @var array
     */
    protected array $orWhere = [];

    /**
     * @param string     $field
     * @param string     $operator
     * @param mixed|null $value
     * @param mixed|null $value2
     *
     * @return Match
     */
    public function where( string $field, string $operator, $value = null, $value2 = null ): Match
    {
        $this->where[ $field ] = $this->whereOperator( $field, $operator, $value, $value2 );

        return $this;
    }

    /**
     * @param string     $field
     * @param string     $operator
     * @param mixed|null $value
     * @param mixed|null $value2
     *
     * @return Match
     */
    public function orWhere( string $field, string $operator, $value = null, $value2 = null ): Match
    {
        $this->orWhere[ $field ] = $this->whereOperator( $field, $operator, $value, $value2 );

        return $this;
    }

    /**
     * @return array
     */
    public function projection(): array
    {
        $filter = [];
        if ( !empty( $this->where ) ) {
            $filter[ '$and' ] = [ $this->where ];
        }
        if ( !empty( $this->orWhere ) ) {
            $filter[ '$or' ] = [ $this->orWhere ];
        }

        return $filter;
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