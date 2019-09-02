<?php

namespace App\Model;

use Chukdo\Db\Mongo\Record\Record;

class Agence extends Record
{
    /**
     * @var bool
     */
    protected $autoDateRecord = true;

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

    /**
     * @param $adresse
     * @return Agence
     */
    public function setAdresse($adresse): self
    {
        list($cp, $ville) = array_pad(explode(' ', $adresse), 2, '');

        $this->offsetSet('cp', $cp);
        $this->offsetSet('ville', $ville);

        return $this;
    }
}