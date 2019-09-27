<?php

namespace Chukdo\Conf;

use Chukdo\Bootstrap\AppException;
use Chukdo\Contracts\Json\Json as JsonInterface;
use Chukdo\Storage\Storage;

/**
 * Gestion des fichiers de langues.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Lang extends Conf
{
	/**
	 * @param string $file
	 *
	 * @return JsonInterface
	 */
	public function loadFile( string $file ): JsonInterface
	{
		$storage = new Storage();
		$name    = basename( $file, '.json' );

		if ( $storage->exists( $file ) ) {
			$load = new Conf( $storage->get( $file ) );

			$this->merge( $load->to2d( $name ),
				true );

			return $this;
		}

		throw new AppException( sprintf( 'Lang file [%s] no exist', $file ) );
	}
}
