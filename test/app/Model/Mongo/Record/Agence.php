<?php

namespace App\Model\Mongo\Record;

use Chukdo\Db\Record\Record;

class Agence extends Record
{
    /**
     * @var bool
     */
    protected bool $autoDateRecord = true;

    /**
     * @param $adresse
     *
     * @return Agence
     */
    public function setAdresse( $adresse ): self
    {
        [
            $cp,
            $ville,
        ] = array_pad( explode( ' ', $adresse ), 2, '' );

        $this->offsetSet( 'cp', $cp );
        $this->offsetSet( 'ville', $ville );

        return $this;
    }
}