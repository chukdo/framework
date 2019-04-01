<?php

namespace Chukdo\Logger;

use Chukdo\Contracts\Logger\Logger as LoggerInterface;
use Chukdo\Contracts\Logger\Handler as HandlerInterface;
use Chukdo\Contracts\Logger\Processor as ProcessorInterface;

/**
 * Gestion des logs.
 *
 * @version    1.0.0
 *
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 *
 * @since        08/01/2019
 *
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Logger implements LoggerInterface
{
    /**
     * RFC 5424.
     */
    const DEBUG = 100;
    const INFO = 200;
    const NOTICE = 250;
    const WARNING = 300;
    const ERROR = 400;
    const CRITICAL = 500;
    const ALERT = 550;
    const EMERGENCY = 600;

    /**
     * RFC 5424.
     *
     * @var array
     */
    public static $levels
        = [
            100 => 'Debug',
            200 => 'Info',
            250 => 'Notice',
            300 => 'Warning',
            400 => 'Error',
            500 => 'Critical',
            550 => 'Alert',
            600 => 'Emergency',
        ];

    /**
     * @var string
     */
    protected $name;

    /**
     * Pile des gestionnaires de logs.
     *
     * @var array
     */
    protected $handlers = [];

    /**
     * Processeurs de modifications des enregistrements.
     *
     * @var array
     */
    protected $processors = [];

    /**
     * Constructeur
     * Initialise l'objet.
     *
     * @param string $name nom de l'instance de log
     * @param array $handlers liste des gestionnaires
     * @param array $processors liste des processus
     */
    public function __construct( $name, array $handlers = [], array $processors = [] )
    {
        $this->name = $name;

        foreach( $handlers as $handler ) {
            $this->pushHandler($handler);
        }

        foreach( $processors as $processor ) {
            $this->pushProcessor($processor);
        }
    }

    /**
     * Retourne la liste des niveaux de la RFC 5424.
     *
     * @return array
     */
    public static function getLevels(): array
    {
        return self::$levels;
    }

    /**
     * @param int $level
     *
     * @return string
     */
    public static function getLevel( int $level ): string
    {
        return self::$levels[ $level ];
    }

    /**
     * @param string $name
     *
     * @return Logger
     */
    public function setName( string $name ): self
    {
        $this->name = ucfirst(strtolower($name));

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Ajoute un gestionnaire de log à la pile des gestionnaires de logs.
     *
     * @param HandlerInterface $handler
     *
     * @return $this
     */
    public function pushHandler( HandlerInterface $handler ): self
    {
        array_push(
            $this->handlers,
            $handler
        );

        return $this;
    }

    /**
     * Ajoute un processeur de modification des enregistrements de log à la pile des processeurs de logs.
     *
     * @param ProcessorInterface $processor
     *
     * @return $this
     */
    public function pushProcessor( ProcessorInterface $processor ): self
    {
        array_push(
            $this->processors,
            $processor
        );

        return $this;
    }

    /**
     * @param string $message
     * @param array $context
     *
     * @return bool
     *
     * @throws LoggerException
     */
    public function alert( string $message, array $context = [] ): bool
    {
        return $this->log(
            self::ALERT,
            $message,
            $context
        );
    }

    /**
     * @param string $message
     * @param array $context
     *
     * @return bool
     *
     * @throws LoggerException
     */
    public function critical( string $message, array $context = [] ): bool
    {
        return $this->log(
            self::CRITICAL,
            $message,
            $context
        );
    }

    /**
     * @param string $message
     * @param array $context
     *
     * @return bool
     *
     * @throws LoggerException
     */
    public function emergency( string $message, array $context = [] ): bool
    {
        return $this->log(
            self::EMERGENCY,
            $message,
            $context
        );
    }

    /**
     * @param string $message
     * @param array $context
     *
     * @return bool
     *
     * @throws LoggerException
     */
    public function error( string $message, array $context = [] ): bool
    {
        return $this->log(
            self::ERROR,
            $message,
            $context
        );
    }

    /**
     * @param string $message
     * @param array $context
     *
     * @return bool
     *
     * @throws LoggerException
     */
    public function warning( string $message, array $context = [] ): bool
    {
        return $this->log(
            self::WARNING,
            $message,
            $context
        );
    }

    /**
     * @param string $message
     * @param array $context
     *
     * @return bool
     *
     * @throws LoggerException
     */
    public function notice( string $message, array $context = [] ): bool
    {
        return $this->log(
            self::NOTICE,
            $message,
            $context
        );
    }

    /**
     * @param string $message
     * @param array $context
     *
     * @return bool
     *
     * @throws LoggerException
     */
    public function info( string $message, array $context = [] ): bool
    {
        return $this->log(
            self::INFO,
            $message,
            $context
        );
    }

    /**
     * @param string $message
     * @param array $context
     *
     * @return bool
     *
     * @throws LoggerException
     */
    public function debug( string $message, array $context = [] ): bool
    {
        return $this->log(
            self::DEBUG,
            $message,
            $context
        );
    }

    /**
     * @param int $level
     * @param string $message
     * @param array $context
     *
     * @return bool
     *
     * @throws LoggerException
     */
    public function log( int $level, string $message, array $context = [] ): bool
    {
        if( empty($this->handlers) ) {
            throw new LoggerException('You tried to log record from an empty handler stack.');
        }

        if( !isset(self::$levels[ $level ]) ) {
            throw new LoggerException("You tried to log record with unknown level [$level]");
        }

        $record = [
            'message'   => $this->interpolate(
                $message,
                $context
            ),
            'level'     => $level,
            'levelname' => $this->getLevel($level),
            'channel'   => $this->getName(),
            'date'      => time(),
            'extra'     => [],
            'formatted' => null,
        ];

        return $this->handleRecord($this->processRecord($record));
    }

    /**
     * @param string $message
     * @param array $context
     *
     * @return string
     */
    public function interpolate( string $message, array $context = [] ): string
    {
        $replace = [];

        foreach( $context as $key => $val ) {
            if( !is_array($val)
                && (!is_object($val)
                    || method_exists(
                        $val,
                        '__toString'
                    )) ) {
                $replace[ '{' . $key . '}' ] = $val;
            }
        }

        return strtr(
            $message,
            $replace
        );
    }

    /**
     * Modifie / ajoute des données à un enregistrement.
     *
     * @param array $record
     *
     * @return array
     */
    public function processRecord( array $record ): array
    {
        foreach( $this->processors as $processor ) {
            $record = $processor->processRecord($record);
        }

        return $record;
    }

    /**
     * Envoi l'enregistrement aupres des gestionnaires de logs.
     *
     * @param array $record
     *
     * @return bool true si un gestionnaire à traiter l'enregistrement false sinon
     */
    public function handleRecord( array $record ): bool
    {
        $handle = 0;

        foreach( $this->handlers as $handler ) {
            $handle += (int) $handler->handle($record);
        }

        return $handle > 0;
    }
}
