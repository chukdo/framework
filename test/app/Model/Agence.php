<?php

namespace App\Model;

use Chukdo\Db\Mongo\Model;

class Agence extends Model
{
    public function index()
    {
        $this->collection()
            ->index()
            ->set('nom')
            ->set('date_created', 'desc')
            ->set('codeage', 'asc', true)
            ->save();
    }

    public function schema()
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
}