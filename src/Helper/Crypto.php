<?php

namespace Chukdo\Helper;

use Exception;

/**
 * Classe Str
 * Fonctionnalités de filtre sur les données.
 *
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
final class Crypto
{
	/**
	 * @param int|null $duration
	 * @param string   $salt
	 *
	 * @return string
	 */
	public static function encodeCsrf( int $duration, string $salt ): string
	{
		return self::encrypt( json_encode( [
			                                   'time'     => time(),
			                                   'duration' => $duration ?? 60,
		                                   ], JSON_THROW_ON_ERROR, 512 ), $salt );
	}
	
	/**
	 * @param string $data
	 * @param string $salt
	 *
	 * @return string
	 */
	public static function encrypt( string $data, string $salt ): string
	{
		$encrypted = openssl_encrypt( $data, 'aes-256-ecb', $salt, true );
		
		return base64_encode( $encrypted );
	}
	
	/**
	 * @param string $token
	 * @param string $salt
	 *
	 * @return bool
	 */
	public static function decodeCsrf( string $token, string $salt ): bool
	{
		/** URI Decode */
		if ( Str::contain( $token, '%' ) ) {
			$token = rawurldecode( $token );
		}
		
		/** Hack decoding link ex. Outlook */
		$token = str_replace( ' ', '+', $token );
		$json  = json_decode( self::decrypt( $token, $salt ), false, 512, JSON_THROW_ON_ERROR );
		
		return $json && ( $json->time + $json->duration >= time() );
	}
	
	/**
	 * @param string $data
	 * @param string $salt
	 *
	 * @return string
	 */
	public static function decrypt( string $data, string $salt ): string
	{
		$data = base64_decode( $data );
		
		return openssl_decrypt( $data, 'aes-256-ecb', $salt, true );
	}
	
	/**
	 * @param int|null $length
	 *
	 * @return string
	 */
	public static function password( int $length = null ): string
	{
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?';
		
		return substr( str_shuffle( $chars ), 0, $length ?? 8 );
	}
	
	/**
	 * @param int  $length
	 * @param bool $readable
	 *
	 * @return string
	 * @throws Exception
	 */
	public static function code( int $length, bool $readable = true ): string
	{
		$token        = '';
		$codeAlphabet = $readable
			? 'abcdefghjkmnpqrstuvwxyz123456789'
			: '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$max          = strlen( $codeAlphabet );
		
		for ( $i = 0; $i < $length; ++$i ) {
			$token .= $codeAlphabet[ random_int( 0, $max - 1 ) ];
		}
		
		return $token;
	}
	
	/**
	 * @return string
	 * @throws Exception
	 */
	public static function secret(): string
	{
		return bin2hex( random_bytes( 32 ) );
	}
	
	/**
	 * Hash un fichier et retourne son chemin de stockage.
	 *
	 * @param string $name      nom du fichier
	 * @param int    $hashlevel nombre de sous repertoire pour le stockage du fichier
	 *
	 * @return string chemin complet du fichier à stocker
	 */
	public static function hash( string $name, int $hashlevel = 2 ): string
	{
		$file = crc32( $name );
		$path = '';
		$hash = str_split( hash( 'crc32', $file ), 2 );
		
		/** Hashlevel */
		for ( $i = 0; $i < $hashlevel; ++$i ) {
			$path .= $hash[ $i ] . '/';
		}
		
		return $path . $file;
	}
}
