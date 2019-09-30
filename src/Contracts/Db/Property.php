<?php

namespace Chukdo\Contracts\Db;

use Chukdo\Contracts\Json\Json as JsonInterface;

/**
 * Interface propriété des données.
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
interface Property
{
	/**
	 * Property constructor.
	 *
	 * @param array       $property
	 * @param string|null $name
	 */
	public function __construct( Array $property = [], string $name = null );

	/**
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function get( string $name );

	/**
	 * @param array $properties
	 *
	 * @return mixed
	 */
	public function setAll( array $properties );

	/**
	 * @param string $name
	 * @param null   $type
	 * @param array  $options
	 *
	 * @return mixed
	 */
	public function set( string $name, $type = null, array $options = [] );

	/**
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function unset( string $name );

	/**
	 * @return JsonInterface
	 */
	public function properties(): JsonInterface;

	/**
	 * @return mixed
	 */
	public function type();

	/**
	 * @return string|null
	 */
	public function name(): ?string;

	/**
	 * @return array
	 */
	public function toArray(): array;
}