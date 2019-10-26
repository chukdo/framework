<?php

namespace Chukdo\Contracts\Db;

use Chukdo\Contracts\Json\Json as JsonInterface;

/**
 * Interface propriété des données.
 *
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
	 * @return Property|null
	 */
	public function get( string $name ): ?Property;
	
	/**
	 * @param array $properties
	 *
	 * @return Property
	 */
	public function setAll( array $properties ): Property;
	
	/**
	 * @param string $name
	 * @param null   $type
	 * @param array  $options
	 *
	 * @return Property
	 */
	public function set( string $name, $type = null, array $options = [] ): Property;
	
	/**
	 * @param array $value
	 *
	 * @return Property
	 */
	public function setProperties( array $value ): Property;
	
	/**
	 * @param $value
	 *
	 * @return Property
	 */
	public function setType( $value ): Property;
	
	/**
	 * @param string $name
	 *
	 * @return Property
	 */
	public function unset( string $name ): Property;
	
	/**
	 * @return JsonInterface
	 */
	public function properties(): JsonInterface;
	
	/**
	 * @return string|null
	 */
	public function type(): ?string;
	
	/**
	 * @return string|null
	 */
	public function name(): ?string;
	
	/**
	 * @return array
	 */
	public function toArray(): array;
}