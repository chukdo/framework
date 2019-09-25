<?php

namespace Chukdo\Db\Elastic;

/**
 * Server Where.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Where
{
    /**
     * @var array
     */
    protected $where = [];

    /**
     * @param string $field
     * @param string $operator
     * @param        $value
     * @param null   $value2
     *
     * @return $this
     */
    public function where( string $field, string $operator, $value, $value2 = null ): self
    {
        $keyword = 'must';

        switch ( $operator ) {
            case '==' :
                $keyword = 'filter';
                break;
            case '!==' :
            case '!=' :
            case '!in' :
                $keyword = 'must_not';
                break;
        }

        $this->where = array_merge_recursive(
            $this->where, [
            $keyword => $this->subQuery( $field, $operator, $value, $value2 ),
        ] );

        return $this;
    }

    /**
     * @param string $field
     * @param string $operator
     * @param        $value
     * @param null   $value2
     *
     * @return array
     */
    protected function subQuery( string $field, string $operator, $value, $value2 = null ): array
    {
        switch ( $operator ) {
            case '==' :
            case '!==' :
            case '=' :
            case '!=' :
                return [
                    'term' => [
                        $field => Collection::filterIn( $field, $value ),
                    ],
                ];
                break;
            case '>' :
                return [
                    'range' => [
                        $field => [
                            'gt' => Collection::filterIn( $field, $value ),
                        ],
                    ],
                ];
                break;
            case '>=':
                return [
                    'range' => [
                        $field => [
                            'gte' => Collection::filterIn( $field, $value ),
                        ],
                    ],
                ];
                break;
            case '<':
                return [
                    'range' => [
                        $field => [
                            'lt' => Collection::filterIn( $field, $value ),
                        ],
                    ],
                ];
                break;
            case '<=':
                return [
                    'range' => [
                        $field => [
                            'lte' => Collection::filterIn( $field, $value ),
                        ],
                    ],
                ];
                break;
            case '<>' :
                return [
                    'range' => [
                        $field => [
                            'gt' => Collection::filterIn( $field, $value ),
                            'lt' => Collection::filterIn( $field, $value ),
                        ],
                    ],
                ];
                break;
            case '<=>' :
                return [
                    'range' => [
                        $field => [
                            'gte' => Collection::filterIn( $field, $value ),
                            'lte' => Collection::filterIn( $field, $value ),
                        ],
                    ],
                ];
                break;
            case 'in':
            case '!in':
                $in = [];

                foreach ( $value as $k => $v ) {
                    $in[ $k ] = Collection::filterIn( $field, $v );
                }

                return [
                    'terms' => [
                        $field => $in,
                    ],
                ];
                break;
            case 'size':
                return [
                    '
                script' => [
                        'script' => 'doc[\'' . $field . '\']values.size() = ' . $value,
                    ],
                ];
                break;
            case 'exist':
                return [
                    'exists' => [
                        $field => $value,
                    ],
                ];
                break;
            case 'regex':
                return [
                    'regexp' => [
                        $field => [
                            'value' => $value,
                            'flags' => $value2 ?: 'ALL',
                        ],
                    ],
                ];
                break;
            case 'match':
                return [
                    'match' => [
                        $field => $value,
                    ],
                ];
                break;
            default :
                throw new ElasticException( sprintf( "Unknown operator [%s]", $operator ) );
        }
    }

    /**
     * @param string $field
     * @param string $operator
     * @param        $value
     * @param null   $value2
     *
     * @return $this
     */
    public function orWhere( string $field, string $operator, $value, $value2 = null ): self
    {
        $keyword = 'should';

        switch ( $operator ) {
            case '==' :
                $keyword = 'filter';
                break;
            case '!==' :
            case '!=' :
            case '!in' :
                $keyword = 'should_not';
                break;
        }

        $this->where = array_merge_recursive(
            $this->where, [
            $keyword => $this->subQuery( $field, $operator, $value, $value2 ),
        ] );

        return $this;
    }

    /**
     * @return array
     */
    public function filter(): array
    {
        return  $this->where;
    }
}