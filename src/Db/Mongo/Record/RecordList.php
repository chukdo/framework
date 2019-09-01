<?php

namespace Chukdo\Db\Mongo\Record;

use Chukdo\Json\Json;
use Chukdo\Db\Mongo\Collection;

/**
 * Mongo RecordList.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class RecordList extends Json
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * Index constructor.
     * @param Collection $collection
     */
    public function __construct( Collection $collection )
    {
        $this->collection = $collection;

        parent::__construct([], false);
    }

    /**
     * @return Collection
     */
    public function collection(): Collection
    {
        return $this->collection;
    }
}