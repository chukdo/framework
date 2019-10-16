<?php

namespace App\Model;

use Chukdo\Db\Record\Record;

class Agence extends Record
{
	/**
	 * @var bool
	 */
	protected $autoDateRecord = true;

	/**
	 * @param $adresse
	 *
	 * @return Agence
	 */
	public function setAdresse( $adresse ): self
	{
		list( $cp, $ville ) = array_pad( explode( ' ', $adresse ), 2, '' );

		$this->offsetSet( 'cp', $cp );
		$this->offsetSet( 'ville', $ville );

		return $this;
	}
}