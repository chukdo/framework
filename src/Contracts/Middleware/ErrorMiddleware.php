<?php

namespace Chukdo\Contracts\Middleware;

use Chukdo\Json\Message;

/**
 * Interface des middlewares.
 *
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
interface ErrorMiddleware extends Middleware
{
	/**
	 * @param Message $errors
	 *
	 * @return ErrorMiddleware
	 */
	public function errorMessage( Message $errors ): self;
}
