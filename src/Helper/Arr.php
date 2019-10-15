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
	 * @param array $pushTo
	 * @param array $toPush
	 *
	 * @return array
	 */
	public static function push( array $pushTo, array $toPush ): array
	{
		foreach ( $toPush as $push ) {
			$pushTo[] = $push;
		}

		return $pushTo;
	}
}
