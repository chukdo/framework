<?php

namespace Chukdo\Contracts\Db;

use Chukdo\Contracts\Json\Json as JsonInterface;
use Chukdo\DB\Mongo\Collection;
use Chukdo\Db\Mongo\Index;
use Chukdo\Db\Mongo\Schema\Schema;
use MongoDB\Driver\Session as MongoSession;

/**
 * Interface de gestion des documents JSON.
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
interface Record extends JsonInterface
{
    /**
     * @return Collection
     */
    public function collection(): Collection;

    /**
     * Initialise le modele en injectant le schema et les index
     */
    public function init();

    /**
     * Création des index
     */
    public function initIndex();

    /**
     * Création des schema de validation des données
     */
    public function initSchema();

    /**
     * @return Index
     */
    public function index(): Index;

    /**
     * @return Schema
     */
    public function schema(): Schema;

    /**
     * @return string|null
     */
    public function id(): ?string;

    /**
     * @param MongoSession|null $session
     * @return Record
     */
    public function save( MongoSession $session = null ): self;

    /**
     * @return JsonInterface
     */
    public function record(): JsonInterface;

    /**
     * @param MongoSession|null $session
     * @return Record
     */
    public function delete( MongoSession $session = null ): self;

    /**
     * @param string $collection
     * @return mixed
     */
    public function moveTo( string $collection ): self;
}