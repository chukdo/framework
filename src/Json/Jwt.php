<?php

namespace Chukdo\Json;

use Chukdo\Helper\Arr;
use Chukdo\Helper\To;

/**
 * Json Web Token.
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Jwt
{
	/**
	 * @var array
	 */
	protected $claims = [];
	
	protected $keywords
	                  = [
			'iss',
			'iat',
			'aud',
			'nbf',
			'jti',
		];
	
	/**
	 * @param string      $jwt
	 * @param string      $secret
	 * @param string|null $checkIssuer
	 * @param string|null $checkAudience
	 *
	 * @return bool
	 */
	public function parse( string $jwt, string $secret, string $checkIssuer = null, string $checkAudience = null ): bool
	{
		$tokenParts = explode( '.', $jwt );
		
		if ( count( $tokenParts ) !== 3 ) {
			return false;
		}
		
		$header    = To::base64Decode( $tokenParts[ 0 ] ); // {...}
		$payload   = To::base64Decode( $tokenParts[ 1 ] );
		$signature = $tokenParts[ 2 ];
		
		// string en 3 point
		// 3 elems string
		// 3 elems base64 decode
		// 3 json array
		
		// header [alg] encode -> header  + payload
		
		//$header['alg']
		
		// decode
		// a b c et en rencode pour check si ok
		// on parse
		// si iss check =
		// si aud check =
		// is exp & nbf check >< (gestion du time setable
		
		return true;
	}
	
	/**
	 * @param string $url
	 *
	 * @return $this
	 */
	public function issuer( string $url ): self
	{
		$this->claims[ 'iss' ] = $url;
		
		return $this;
	}
	
	/**
	 * @return string|null
	 */
	public function getIssuer(): ?string
	{
		return $this->claims[ 'iss' ] ?? null;
	}
	
	/**
	 * @param mixed ...$url
	 *
	 * @return $this
	 */
	public function audience( ... $url ): self
	{
		if ( !isset( $this->claims[ 'aud' ] ) ) {
			$this->claims[ 'aud' ] = [];
		}
		
		Arr::push( $this->claims[ 'aud' ], Arr::spreadArgs( $url ), true );
		
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function getAudience(): array
	{
		return $this->claims[ 'aud' ] ?? [];
	}
	
	/**
	 * @param string $id
	 *
	 * @return $this
	 */
	public function id( string $id ): self
	{
		$this->claims[ 'jti' ] = $id;
		
		return $this;
	}
	
	/**
	 * @return string|null
	 */
	public function getId(): ?string
	{
		return $this->claims[ 'jti' ] ?? null;
	}
	
	/**
	 * @param int  $time
	 * @param bool $relative
	 *
	 * @return $this
	 */
	public function issuedAt( int $time = 0, bool $relative = true ): self
	{
		$this->claims[ 'iat' ] = ( $relative
				? time()
				: 0 ) + $time;
		
		return $this;
	}
	
	/**
	 * @return int|null
	 */
	public function getIssuedAt(): ?int
	{
		return $this->claims[ 'iat' ] ?? null;
	}
	
	/**
	 * @param int  $time
	 * @param bool $relative
	 *
	 * @return $this
	 */
	public function expiresAt( int $time = 3600, bool $relative = true ): self
	{
		$this->claims[ 'exp' ] = ( $relative
				? time()
				: 0 ) + $time;
		
		return $this;
	}
	
	/**
	 * @return int|null
	 */
	public function getExpiresAt(): ?int
	{
		return $this->claims[ 'exp' ] ?? null;
	}
	
	/**
	 * @param int  $time
	 * @param bool $relative
	 *
	 * @return $this
	 */
	public function validAt( int $time = 3600, bool $relative = true ): self
	{
		$this->claims[ 'nbf' ] = ( $relative
				? time()
				: 0 ) + $time;
		
		return $this;
	}
	
	/**
	 * @return int|null
	 */
	public function getValidAt(): ?int
	{
		return $this->claims[ 'nbf' ] ?? null;
	}
	
	/**
	 * @param string $role
	 *
	 * @return $this
	 */
	public function role( string $role ): self
	{
		$this->claims[ 'rol' ] = $role;
	}
	
	/**
	 * @param string $client
	 *
	 * @return $this
	 */
	public function client( string $client ): self
	{
		$this->claims[ 'clt' ] = $client;
	}
	
	/**
	 * @param string $key
	 * @param        $value
	 *
	 * @return $this
	 */
	public function set( string $key, $value ): self
	{
		if ( !Arr::in( $key, $this->keywords ) ) {
			$this->claims[ $key ] = $value;
		}
		
		return $this;
	}
	
	/**
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function get( string $key )
	{
		if ( !Arr::in( $key, $this->keywords ) ) {
			return $this->claims[ $key ];
		}
		
		return null;
	}
	
	/**
	 * @return array
	 */
	public function getAll(): array
	{
		$all = [];
		
		foreach ( $this->claims as $key => $value ) {
			if ( $this->get( $key ) ) {
				$all[ $key ] = $value;
			}
		}
		
		return $all;
	}
	
	/**
	 * @param array $values
	 *
	 * @return $this
	 */
	public function setAll( array $values ): self
	{
		foreach ( $values as $key => $value ) {
			$this->set( $key, $value );
		}
		
		return $this;
	}
	
	/**
	 * @param string $secret
	 * @param string $crypto
	 *
	 * @return string
	 */
	public function token( string $secret, string $crypto = 'HS256' ): string
	{
	}
	
	/**
	 * @param string $value
	 *
	 * @return string
	 */
	protected function jsonEncode( string $value ): string
	{
		return json_encode( $value );
	}
	
	protected function jsonDecode( string $value )
	{
		//return json_decode($value, true);
		//json_decode( $value, true, 512, JSON_THROW_ON_ERROR );
		
		$a = 'ok';
		
		if ( 1 == $a ) {
			return 'ok';
		}
	}
	
	/**
	 * @param string $jwt
	 *
	 * @return array
	 */
	protected function getTokensParts( string $jwt ): array
	{
		$tokenParts = explode( '.', $jwt );
		
		if ( count( $tokenParts ) !== 3 ) {
			return [];
		}
		
		return [
			'header'    => $tokenParts[ 0 ],
			'payload'   => $tokenParts[ 1 ],
			'signature' => $tokenParts[ 2 ],
		];
	}
	
	/**
	 * @param string $header
	 * @param string $payload
	 * @param string $secret
	 *
	 * @return string|null
	 */
	protected function encodeSignature( string $header, string $payload, string $secret ): ?string
	{
		$alg = $header[ 'alg' ] ?? null; // header string Json !!!
		
		switch ( $alg ) {
			case 'H256' :
				return hash_hmac( 'sha256', To::base64UrlEncode( $header ) . '.' . To::base64UrlEncode( $payload ),
				                  $secret, true );
		}
		
		return null;
	}
}