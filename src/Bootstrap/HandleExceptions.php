<?php

namespace Chukdo\Bootstrap;

use ReflectionException;
use Throwable;
use Exception;
use ErrorException;

/**
 * Gestion des exception.
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class HandleExceptions
{
	/**
	 * @var App
	 */
	protected $app;

	/**
	 * HandleExceptions constructor.
	 *
	 * @param App $app
	 */
	public function __construct( App $app )
	{
		$this->app = $app;

		error_reporting( -1 );
		set_error_handler( [
			$this,
			'handleError',
		] );
		set_exception_handler( [
			$this,
			'handleException',
		] );
		register_shutdown_function( [
			$this,
			'handleShutdown',
		] );
		ini_set( 'display_errors', 'Off' );
	}

	/**
	 * @param int    $level
	 * @param string $message
	 * @param string $file
	 * @param int    $line
	 *
	 * @throws ErrorException
	 */
	public function handleError( int $level, string $message, string $file = '', int $line = 0 ): void
	{
		if ( error_reporting() & $level ) {
			throw new ErrorException( $message, 0, $level, $file, $line );
		}
	}

	/**
	 * @throws ServiceException
	 * @throws ReflectionException
	 */
	public function handleShutdown(): void
	{
		if ( ( $error = error_get_last() ) !== null && $this->isFatal( $error[ 'type' ] ) ) {
			$this->handleException( $this->fatalExceptionFromError( $error ) );
		}
	}

	/**
	 * @param int $type
	 *
	 * @return bool
	 */
	protected function isFatal( int $type ): bool
	{
		return in_array( $type,
			[
				E_COMPILE_ERROR,
				E_CORE_ERROR,
				E_ERROR,
				E_PARSE,
			], true );
	}

	/**
	 * @param Throwable $e
	 *
	 * @throws ServiceException
	 * @throws ReflectionException
	 */
	public function handleException( Throwable $e ): void
	{
		if ( !$e instanceof Exception ) {
			$e = new AppException( $e->getMessage(), $e->getCode(), $e );
		}

		$exceptionHandler = $this->getExceptionHandler();

		try {
			$exceptionHandler->report( $e );
		} catch ( Throwable $e ) {
		}

		$exceptionHandler->render( $e );
	}

	/**
	 * @return mixed|object|null
	 * @throws ServiceException
	 * @throws ReflectionException
	 */
	protected function getExceptionHandler()
	{
		return $this->app->make( 'Chukdo\Bootstrap\ExceptionHandler' );
	}

	/**
	 * @param array $error
	 *
	 * @return ErrorException
	 */
	protected function fatalExceptionFromError( array $error ): ErrorException
	{
		return new ErrorException( $error[ 'message' ], 0, $error[ 'type' ], $error[ 'file' ], $error[ 'line' ] );
	}
}
