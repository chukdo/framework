<?php namespace Chukdo\Contracts\Validation;

/**
 * Interface des regles de validation
 *
 * @package 	Contracts
 * @version 	1.0.0
 * @copyright 	licence MIT, Copyright (C) 2019 Domingo
 * @since 		08/01/2019
 * @author 		Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
interface Validate
{
    /**
     * @return string
     */
    public function name(): string;

    /**
     * @param mixed ...$data
     * @return bool
     */
    public function validate(...$data): bool;
}