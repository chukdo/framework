<?php

namespace Chukdo\Helper;

/**
 * Classe Arr
 * Fonctionnalités des tableaux.
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
final class Arr
{
	/**
	 * @param array $args
	 *
	 * @return array
	 */
	public static function spreadArgs( array $args ): array
	{
		if ( isset( $args[ 0 ] ) && is_array( $args[ 0 ] ) ) {
			return $args[ 0 ];
		}

		return $args;
	}

	/**
	 * @param array $mergeTo
	 * @param array $toMerge
	 *
	 * @return array
	 */
	public static function merge( array $mergeTo, array $toMerge ): array
	{
		foreach ( $toMerge as $key => $merge ) {
			$mergeTo[ $key ] = $merge;
		}

		return $mergeTo;
	}

	/**
	 * @param array $array
	 *
	 * @return bool
	 */
	public static function hasContent( array $array ): bool
	{
		return !empty( $array );
	}

	/**
	 * @param       $value
	 * @param array $array
	 *
	 * @return bool
	 */
	public static function in( $value, array $array ): bool
	{
		return in_array( $value, $array, true );
	}

	/**
	 * @param       $value
	 * @param array $array
	 * @param bool  $unique
	 *
	 * @return array
	 */
	public static function append( $value, array $array, bool $unique = true ): array
	{
		if ( $unique === false || ( $unique === true && !self::in( $value, $array ) ) ) {
			$array[] = $value;
		}

		return $array;
	}

	/**
	 * @param array $pushTo
	 * @param array $toPush
	 *
	 * @param bool  $unique
	 *
	 * @return array
	 */
	public static function push( array $pushTo, array $toPush, bool $unique = true ): array
	{
		foreach ( $toPush as $push ) {
			if ( $unique === false || ( $unique === true && !self::in( $push, $pushTo ) ) ) {
				$pushTo[] = $push;
			}
		}

		return $pushTo;
	}
}
