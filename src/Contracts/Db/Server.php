<?php

namespace Chukdo\Contracts\Db;

use Chukdo\Contracts\Json\Json as JsonInterface;

/**
 * Interface server de donnée.
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
interface Server
{
	/**
	 * Server constructor.
	 *
	 * @param string|null $dsn
	 * @param string|null $database
	 */
	public function __construct( string $dsn = null, string $database = null );

	/**
	 * @return mixed
	 */
	public function client();

	/**
	 * @return bool
	 */
	public function ping(): bool;

	/**
	 * @return JsonInterface
	 */
	public function status(): JsonInterface;

	/**
	 * @return string|null
	 */
	public function version(): ?string;

	/**
	 * @return JsonInterface
	 */
	public function databases(): JsonInterface;

	/**
	 * @param string      $collection
	 * @param string|null $database
	 *
	 * @return mixed
	 */
	public function collection( string $collection, string $database = null );

	/**
	 * @param string|null $database
	 *
	 * @return mixed
	 */
	public function database( string $database = null );
}