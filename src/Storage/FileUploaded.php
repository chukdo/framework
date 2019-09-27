<?php

namespace Chukdo\Storage;

use Chukdo\Helper\Str;

/**
 * Gestion des fichiers uploadés.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class FileUploaded
{
	/**
	 * @var string|null
	 */
	protected $name = null;

	/**
	 * @var int|null
	 */
	protected $maxFileSize = null;

	/**
	 * @var string|null
	 */
	protected $allowedMimeTypes = null;

	/**
	 * @var array
	 */
	protected $uploadedFile = [];

	/**
	 * FileUploaded constructor.
	 *
	 * @param string      $name
	 * @param string|null $allowedMimeTypes
	 * @param int|null    $maxFileSize
	 */
	public function __construct( string $name, string $allowedMimeTypes = null, int $maxFileSize = null )
	{
		$uploadedFiles = $this->normalizeUploadedFiles();

		if ( isset( $uploadedFiles[ $name ] ) ) {
			$this->name         = $name;
			$this->uploadedFile = $uploadedFiles[ $name ];

			$this->setMaxFileSize( $maxFileSize );
			$this->setAllowedMimeTypes( $allowedMimeTypes );

		} else {
			throw new FileUploadedException( sprintf( 'Uploaded file [%s] does not exist', $name ) );
		}
	}

	/**
	 * @return array
	 */
	private static function normalizeUploadedFiles(): array
	{
		$uploadedFiles = [];

		foreach ( $_FILES as $name => $file ) {
			foreach ( $file as $type => $value ) {
				if ( is_array( $value ) ) {
					foreach ( self::__normalizeUploadedFiles( $value ) as $nName => $nValue ) {
						$uploadedFiles[ $name . '.' . $nName ][ $type ] = $nValue;
					}
				} else {
					$uploadedFiles[ $name ][ $type ] = $value;
				}
			}
		}

		return $uploadedFiles;
	}

	/**
	 * @param array $array
	 *
	 * @return array
	 */
	private static function __normalizeUploadedFiles( array $array ): array
	{
		$uploadedFiles = [];

		foreach ( $array as $k => $v ) {
			if ( is_array( $v ) ) {
				foreach ( self::__normalizeUploadedFiles( $v ) as $_k => $_v ) {
					$uploadedFiles[ $k . '.' . $_k ] = $_v;
				}
			} else {
				$uploadedFiles[ $k ] = $v;
			}
		}

		return $uploadedFiles;
	}

	/**
	 * @param int|null $maxFileSize
	 *
	 * @return FileUploaded
	 */
	public function setMaxFileSize( int $maxFileSize = null ): self
	{
		$this->maxFileSize = $maxFileSize;

		return $this;
	}

	/**
	 * @param string|null $allowedMimeTypes
	 *
	 * @return FileUploaded
	 */
	public function setAllowedMimeTypes( string $allowedMimeTypes = null ): self
	{
		$this->allowedMimeTypes = $allowedMimeTypes;

		return $this;
	}

	/**
	 * @return string
	 */
	public function name(): string
	{
		return (string) $this->uploadedFile[ 'name' ];
	}

	/**
	 * @return string
	 */
	public function extension(): string
	{
		return Str::extension( $this->uploadedFile[ 'name' ] );
	}

	/**
	 * @return string
	 */
	public function error(): string
	{
		return (string) $this->uploadedFile[ 'error' ];
	}

	/**
	 * @param $path
	 *
	 * @return bool
	 */
	public function store( $path ): bool
	{
		if ( $this->isValid() ) {
			if ( move_uploaded_file( $this->path(), $path ) ) {
				return true;
			} else {
				throw new FileUploadedException( sprintf( 'Can\'t store uploaded file [%s] to [%s]',
					$this->name,
					$path ) );
			}
		}

		throw new FileUploadedException( sprintf( 'Uploaded file [%s] is not valid',
			$this->name ) );
	}

	/**
	 * @return bool
	 */
	public function isValid(): bool
	{
		return $this->isValidSize() && $this->isValidMimeType();
	}

	/**
	 * @return bool
	 */
	public function isValidSize(): bool
	{
		if ( $this->maxFileSize ) {
			return $this->size() < $this->maxFileSize;
		}

		return true;
	}

	/**
	 * @return int
	 */
	public function size(): int
	{
		return (int) $this->uploadedFile[ 'size' ];
	}

	/**
	 * @return bool
	 */
	public function isValidMimeType(): bool
	{
		if ( $this->allowedMimeTypes ) {
			foreach ( str::split( $this->allowedMimeTypes, ',' ) as $allowedMimeType ) {
				if ( preg_match( "#$allowedMimeType#i", $this->mimeType() ) ) {
					return true;
				}
			}

			return false;
		}

		return true;
	}

	/**
	 * @return string
	 */
	public function mimeType(): string
	{
		return (string) $this->uploadedFile[ 'type' ];
	}

	/**
	 * @return string
	 */
	public function path(): string
	{
		return (string) $this->uploadedFile[ 'tmp_name' ];
	}
}
