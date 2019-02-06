<?php namespace Chukdo\Bootstrap;

Use \Throwable;
Use \Exception;
Use \ErrorException;

class HandleExceptions
{
    /**
     * @var App $app
     */
    protected $app;

    /**
     * @param App $app
     */
    public function bootstrap(App $app): void
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
     */
    public function handleException(Throwable $e)
    {
        try {
            $e->report();
        } catch (Exception $e) {}

        if ($this->app->runningInConsole()) {
            $this->renderForConsole($e);
        } else {
            $this->renderHttpResponse($e);
        }

        if (!headers_sent()) {
            header("HTTP/1.1 500 Internal Server Error");
            header("Content-Type: text/html; charset=utf-8");
        }

        die('<h1>ERROR 500 Internal Server Error</h1><h4>Exception: '.$e->getMessage().' in '.$e->getFile().'('.$e->getLine().')</h4>');
    }

    /**
     *
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
     * Get an instance of the exception handler.
     *
     * @return \Illuminate\Contracts\Debug\ExceptionHandler
     */
    protected function getExceptionHandler()
    {
        return $this->app->make(ExceptionHandler::class);
    }
}