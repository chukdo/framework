<?php

namespace Chukdo\Contracts\Db;

use Chukdo\Contracts\Json\Json as JsonInterface;

/**
 * Interface schema des données.
 *
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
interface Schema
{
	/**
	 * @return Collection
	 */
	public function collection(): Collection;
	
	/**
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function get( string $name ): ?Property;
	
	/**
	 * @param array $properties
	 *
	 * @return Schema
	 */
	public function setAll( array $properties ): Schema;
	
	/**
	 * @param string $name
	 * @param null   $type
	 * @param array  $options
	 *
	 * @return Schema
	 */
	public function set( string $name, $type = null, array $options = [] ): Schema;
	
	/**
	 * @param string $name
	 *
	 * @return Schema
	 */
	public function unset( string $name ): Schema;
	
	/**
	 * @return Property
	 */
	public function property(): Property;
	
	/**
	 * @return JsonInterface
	 */
	public function properties(): JsonInterface;
	
	/**
	 * @return bool
	 */
	public function drop(): bool;
	
	/**
	 * @return bool
	 */
	public function save(): bool;
	
	/**
	 * @return array
	 */
	public function toArray(): array;
}