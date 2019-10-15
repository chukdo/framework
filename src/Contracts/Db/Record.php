<?php

namespace Chukdo\Contracts\Db;

use Chukdo\Contracts\Json\Json as JsonInterface;

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
	 * @return mixed
	 */
	public function collection();

	/**
	 * @return string|null
	 */
	public function id(): ?string;

	/**
	 * @return Record
	 */
	public function save(): self;

	/**
	 * @return Record
	 */
	public function insert(): self;

	/**
	 * @return Record
	 */
	public function update(): self;

	/**
	 * @return JsonInterface
	 */
	public function record(): JsonInterface;

	/**
	 * @return Record
	 */
	public function delete(): self;

	/**
	 * @param string $collection
	 *
	 * @return mixed
	 */
	public function moveTo( string $collection ): self;
}