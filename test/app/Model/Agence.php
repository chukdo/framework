<?php

namespace App\Model;

use Chukdo\Db\Mongo\Model;

class Agence extends Model
{
    public function index()
    {
        $this->collection()
            ->index()
            ->create('nom')
            ->create('date_created', 'desc')
            ->create('codeage', 'asc', true);
    }

    public function schema()
    {
        $schema = $this->collection()->schema();
        $schema->set('nom', 'string', true);
        $schema->set('codeage', 'int', true)->setMin(300)->setMax(600);
        $schema->save();
    }
}