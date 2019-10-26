<?php

namespace App\Model\Mongo\Record;

use Chukdo\Db\Record\Record;
use Chukdo\Helper\Is;

class Contrat extends Record
{
	public function setReference( $ref )
	{
		if ( Is::arr( $ref ) ) {
			$this->offsetSet( 'reference', implode( '|', $ref ) );
		} else {
			$this->offsetSet( 'reference', $ref );
		}
	}
	
	//save
	//delete
	//softDelete
	// conf > collection / date / history
	// extend find ()
	//  -> find() renvoyer liste de modeles et non json ?!
}