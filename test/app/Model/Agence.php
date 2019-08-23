<?php

namespace App\Model;

use Chukdo\Db\Mongo\Model;

class Agence extends Model
{
    public function createIndex()
    {
        $this->collection()
            ->index()
            ->set('nom')
            ->set('date_created', 'desc')
            ->set('codeage', 'asc', true)
            ->save();
    }

    public function createSchema()
    {
        $this->collection()
            ->schema()
            ->set('nom', 'string', true)
            ->set('codeage', 'int', true, [
                'min' => 300,
                'max' => 600,
            ])
            ->save();
    }


    //save
    //delete
    //softDelete
    // conf > collection / date / history
    // extend find ()
    //  -> find() renvoyer liste de modeles et non json ?!
}