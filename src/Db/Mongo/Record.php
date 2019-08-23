<?php

namespace Chukdo\Db\Mongo;

use Chukdo\Json\Json;

/**
 * Mongo Record.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Record extends Json
{
    /**
     * Json constructor.
     * @param null $data
     * @param null $preFilter
     */
    public function __construct( $data = null)
    {
        parent::__construct($data, function( $k, $v )
        {
            return Collection::filterOut($k, $v);
        });
    }
}