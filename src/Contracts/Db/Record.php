<?php

namespace Chukdo\Contracts\Db;

use Chukdo\Contracts\Json\Json as JsonInterface;
use Chukdo\DB\Mongo\Collection;
use Chukdo\Db\Mongo\Index;
use Chukdo\Db\Mongo\Schema\Schema;

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
     * @return Index
     */
    public function index(): Index;

    /**
     * Création des index
     */
    public function createIndex();

    /**
     * @return Schema
     */
    public function schema(): Schema;

    /**
     * Création des schema de validation des données
     */
    public function createSchema();

    /**
     * @return string|null
     */
    public function id(): ?string;

    /**
     * @return mixed
     */
    public function save();
}