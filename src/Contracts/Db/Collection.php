<?php

namespace Chukdo\Contracts\Db;

use Chukdo\Contracts\Json\Json as JsonInterface;

/**
 * Interface database de donnée.
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
interface Collection
{
	/**
	 * @return mixed
	 */
	public function client();

	/**
	 * @return string
	 */
	public function name(): string;

	/**
	 * @return JsonInterface
	 */
	public function info(): JsonInterface;

	/**
	 * @return mixed
	 */
	public function database();

	/**
	 * @return bool
	 */
	public function drop(): bool;

	/**
	 * @param string      $collection
	 * @param string|null $database
	 *
	 * @return mixed
	 */
	public function rename( string $collection, string $database = null );

	/**
	 * @return mixed
	 */
	public function schema();

	/**
	 * @return mixed
	 */
	public function write();

	/**
	 * @return mixed
	 */
	public function find();

	/**
	 * @return mixed
	 */
	public function id();
}