<?php

namespace Chukdo\Logger\Formatters;

use Chukdo\Contracts\Logger\Formatter as FormatterInterface;
use Chukdo\Json\Json;

/**
 * Formatter de log par defaut.
 *
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class JsonStringFormatter implements FormatterInterface
{
	/**
	 * @param array $record
	 *
	 * @return mixed
	 */
	public function formatRecord( array $record )
	{
		$json = new Json( [
			                  'date'    => date( 'd/m/Y H:i:s', $record[ 'date' ] ),
			                  'name'    => $record[ 'channel' ] . '.' . $record[ 'levelname' ],
			                  'message' => str_replace( [
				                                            "\r\n",
				                                            "\r",
				                                            "\n",
			                                            ], ' ', $record[ 'message' ] ),
			                  'extra'   => $record[ 'extra' ],
			                  'time'    => $record[ 'date' ],
			                  'level'   => $record[ 'level' ],
		                  ] );
		
		return $json->toJson();
	}
}
