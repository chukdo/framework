<?php

namespace App\Model;

use Chukdo\Db\Mongo\Record\Record;
use Chukdo\Helper\Is;

class Contrat extends Record
{
    public function createIndex()
    {
        $this->collection()
            ->index()
            ->set('_agence')
            ->set('_modele')
            ->save();
    }

    public function createSchema()
    {
        $this->collection()
            ->schema()
            ->set('_agence', 'string', true)
            ->set('_modele', 'string', true)
            ->save();
    }

    public function setReference($ref)
    {
        if (Is::arr($ref)) {
            $this->offsetSet('reference', implode('|', $ref));
        } else {
            $this->offsetSet('reference', $ref);
        }
    }

    //save
    //delete
    //softDelete
    // conf > collection / date / history
    // extend find ()
    //  -> find() renvoyer liste de modeles et non json ?!
}