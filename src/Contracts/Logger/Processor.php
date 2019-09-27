<?php

namespace Chukdo\Contracts\Logger;

/**
 * Interface des processus.
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
interface Processor
{
	/**
	 * Modifie / ajoute des données à un enregistrement.
	 *
	 * @param array $record
	 *
	 * @return array
	 */
	public function processRecord( array $record ): array;
}
