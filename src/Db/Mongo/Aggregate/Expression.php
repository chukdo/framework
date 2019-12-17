<?php

namespace Chukdo\Db\Mongo\Aggregate;

use Chukdo\Helper\Is;

/**
 * Server Aggregate Expression.
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Expression
{
    /**
     * @var string
     */
    protected string $name;

    /**
     * @var Expression|string|array
     */
    protected $expression;

    /**
     * Expression constructor.
     *
     * @param string                  $name
     * @param Expression|string|array $expression
     */
    public function __construct( string $name, $expression )
    {
        $this->name       = $name;
        $this->expression = $expression;
    }

    /**
     * @param Expression|string|array $expression
     *
     * @return string|array|null
     */
    public static function parseExpression( $expression )
    {
        $parsed = null;
        if ( $expression instanceof Expression ) {
            $parsed = $expression->projection();
        } else {
            if ( Is::arr( $expression ) ) {
                $parsed = [];
                foreach ( $expression as $key => $exp ) {
                    $parsed[ $key ] = self::parseExpression( $exp );
                }
            } else {
                if ( Is::string( $expression ) ) {
                    $parsed = '$' . $expression;
                } else {
                    $parsed = $expression;
                }
            }
        }

        return $parsed;
    }

    /**
     * @return array
     */
    public function projection(): array
    {
        return [ '$' . $this->name => $this->parseExpression( $this->expression ) ];
    }
}