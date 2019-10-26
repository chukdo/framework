<?php

namespace Chukdo\Contracts\Exception;

use Throwable;

/**
 * Interface de Gestionnaires des exception.
 *
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
interface Handler
{
	/**
	 * @param Throwable $e
	 */
	public function report( Throwable $e ): void;
	
	/**
	 * @param Throwable $e
	 */
	public function render( Throwable $e ): void;
}
