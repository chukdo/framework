<?php namespace Chukdo\Logger;

use \Chukdo\Event\Event;
use \Chukdo\Json\Json;
use \Chukdo\Contracts\Logger\Logger as LoggerInterface;
use \Chukdo\Contracts\Logger\Handler as HandlerInterface;
use \Chukdo\Contracts\Logger\Processor as ProcessorInterface;
use \Chukdo\Contracts\Logger\Formatter as FormatterInterface;

/**
 * Gestion des exceptions
 *
 * @copyright 	licence MIT, Copyright (C) 2014 Domingo
 * @author 		Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class LoggerException extends \Exception {}

/**
 * Gestion des logs
 *
 * @copyright 	licence MIT, Copyright (C) 2014 Domingo
 * @author 		Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Logger implements LoggerInterface
{
    /**
     * RFC 5424
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
     * RFC 5424
     *
     * @var array $levels
     */
    public static $levels = array(
        100 => 'DEBUG',
        200 => 'INFO',
        250 => 'NOTICE',
        300 => 'WARNING',
        400 => 'ERROR',
        500 => 'CRITICAL',
        550 => 'ALERT',
        600 => 'EMERGENCY',
    );

    /**
     * @var string
     */
    protected $name;

    /**
     * Pile des gestionnaires de logs
     *
     * @var HandlerInterface[]
     */
    protected $handlers = [];

    /**
     * Processeurs de modifications des enregistrements
     *
     * @var ProcessorInterface[]
     */
    protected $processors = [];

    /**
     * Constructeur
     * Initialise l'objet
     *
     * @param 	string 	$name nom de l'instance de log
     * @param   array   $handlers liste des gestionnaires
     * @param   array   $processors liste des processus
     * @return 	void
     */
    public function __construct($name, array $handlers = [], array $processors = [])
    {
        $this->name = $name;

        foreach ($handlers as $handler) {
            $this->pushHandler($handler);
        }

        foreach ($processors as $processor) {
            $this->pushProcessor($processor);
        }
    }

    /**
     * Retourne la liste des niveaux de la RFC 5424
     *
     * @return string
     */
    public static function getLevels()
    {
        return self::$levels;
    }

    /**
     * Retourne le nom de l'instance de log
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Ajoute un gestionnaire de log à la pile des gestionnaires de logs
     *
     * @param   HandlerInterface $handler
     * @return  $this
     */
    public function pushHandler(HandlerInterface $handler)
    {
        array_push($this->handlers, $handler);

        return $this;
    }

    /**
     * Ajoute un processeur de modification des enregistrements de log à la pile des processeurs de logs
     *
     * @param   ProcessorInterface $processor
     * @return  $this
     */
    public function pushProcessor(ProcessorInterface $processor)
    {
        array_push($this->processors, $processor);

        return $this;
    }

    /**
     * Ajoute un enregistrement d'alerte dans le journal
     *
     * @param  string  $message
     * @param  array  $context
     * @return bool
     */
    public function alert($message, array $context = [])
    {
        return $this->log(self::ALERT, $message, $context);
    }

    /**
     * Ajoute un enregistrement critique dans le journal
     *
     * @param  string  $message
     * @param  array  $context
     * @return bool
     */
    public function critical($message, array $context = [])
    {
        return $this->log(self::CRITICAL, $message, $context);
    }

    /**
     * Ajoute un enregistrement d'erreur dans le journal
     *
     * @param  string  $message
     * @param  array  $context
     * @return bool
     */
    public function error($message, array $context = [])
    {
        return $this->log(self::ERROR, $message, $context);
    }

    /**
     * Ajoute un enregistrement d'avertissement dans le journal
     *
     * @param  string  $message
     * @param  array  $context
     * @return bool
     */
    public function warning($message, array $context = [])
    {
        return $this->log(self::WARNING, $message, $context);
    }

    /**
     * Ajoute un enregistrement de notice dans le journal
     *
     * @param  string  $message
     * @param  array  $context
     * @return bool
     */
    public function notice($message, array $context = [])
    {
        return $this->log(self::NOTICE, $message, $context);
    }

    /**
     * Ajoute un enregistrement d'information dans le journal
     *
     * @param  string  $message
     * @param  array  $context
     * @return bool
     */
    public function info($message, array $context = [])
    {
        return $this->log(self::INFO, $message, $context);
    }

    /**
     * Ajoute un enregistrement de debug dans le journal
     *
     * @param  string  $message
     * @param  array  $context
     * @return bool
     */
    public function debug($message, array $context = [])
    {
        return $this->log(self::DEBUG, $message, $context);
    }

    /**
     * Ajoute un enregistrement dans le journal
     *
     * @param  int  $level
     * @param  string  $message
     * @param  array  $context
     * @return bool     true si une ecriture a eu lieu false non
     */
    public function log($level, $message, array $context = [])
    {
        if (empty($this->handlers)) {
            throw new LoggerException('You tried to log record from an empty handler stack.');
        }

        if (!isset(self::$levels[$level])) {
            throw new LoggerException("You tried to log record with unknown level [$level]");
        }

        $record = [
            'message'   => $message,
            'context'   => $context,
            'level'     => $level,
            'levelname' => ucfirst(strtolower(self::$levels[$level])),
            'channel'   => ucfirst(strtolower($this->name)),
            'time'      => time(),
            'extra'     => [],
            'formatted' => null
        ];

        return $this->handleRecord($this->processRecord($record));
    }

    /**
     * Modifie / ajoute des données à un enregistrement
     *
     * @param  array  $record
     * @return array
     */
    public function processRecord(array $record)
    {
        foreach ($this->processors as $processor) {
            $record = $processor->processRecord($record);
        }

        return $record;
    }

    /**
     * Envoi l'enregistrement aupres des gestionnaires de logs
     *
     * @param  array    $record
     * @return bool     true si un gestionnaire à traiter l'enregistrement false sinon
     */
    public function handleRecord(array $record)
    {
        $handle = 0;

        foreach ($this->handlers as $handler) {
            $handle += (int) $handler->handle($record);
        }

        return $handle > 0;
    }
}