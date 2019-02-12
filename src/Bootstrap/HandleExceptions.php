<?php namespace Chukdo\Bootstrap;

Use \Throwable;
Use \Exception;
Use \ErrorException;

/**
 * Gestion des exception
 *
 * @package 	Contracts
 * @version 	1.0.0
 * @copyright 	licence MIT, Copyright (C) 2019 Domingo
 * @since 		08/01/2019
 * @author 		Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class HandleExceptions
{
    /**
     * @var App $app
     */
    protected $app;

    /**
     * HandleExceptions constructor.
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;

        error_reporting(-1);
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleShutdown']);
        ini_set('display_errors', 'Off');
    }

    /**
     * @param int $level
     * @param string $message
     * @param string $file
     * @param int $line
     * @throws ErrorException
     */
    public function handleError(int $level, string $message, string $file = '', int $line = 0): void
    {
        if (error_reporting() & $level) {
            throw new ErrorException($message, 0, $level, $file, $line);
        }
    }

    /**
     * @param Throwable $e
     * @throws ServiceException
     * @throws \ReflectionException
     */
    public function handleException(Throwable $e)
    {
        $exceptionHandler = $this->getExceptionHandler();

        $exceptionHandler->report($e);

        if ($this->app->runningInConsole()) {
            $exceptionHandler->renderForConsole($e);
        } else {
            $exceptionHandler->render($e);
        }
    }

    /**
     * @throws ServiceException
     * @throws \ReflectionException
     */
    public function handleShutdown(): void
    {
        if (! is_null($error = error_get_last()) && $this->isFatal($error['type'])) {
            $this->handleException($this->fatalExceptionFromError($error));
        }
    }

    /**
     * @param array $error
     * @return ErrorException
     */
    protected function fatalExceptionFromError(array $error): ErrorException
    {
        return new ErrorException(
            $error['message'], 0, $error['type'], $error['file'], $error['line']
        );
    }

    /**
     * @param int $type
     * @return bool
     */
    protected function isFatal(int $type): bool
    {
        return in_array($type, [E_COMPILE_ERROR, E_CORE_ERROR, E_ERROR, E_PARSE]);
    }

    /**
     * @return mixed|object|null
     * @throws ServiceException
     * @throws \ReflectionException
     */
    protected function getExceptionHandler()
    {
        return $this->app->make('\Chukdo\Bootstrap\ExceptionHandler');
    }
}