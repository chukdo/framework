<?php

namespace Chukdo\Db\Mongo\Schema;

/**
 * Mongo Schema validation.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Schema extends Property
{
    /**
     * @return array
     */
    public function get(): array
    {
        return [
            'validator'        => [
                '$jsonSchema' => parent::get(),
            ],
            'validationLevel'  => 'strict',
            'validationAction' => 'error',
        ];
    }
}