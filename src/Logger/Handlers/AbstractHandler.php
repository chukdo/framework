<?php namespace Chukdo\Logger\Handlers;

use \Chukdo\Logger\Logger;
use \Chukdo\Contracts\Logger\Handler as HandlerInterface;
use \Chukdo\Contracts\Logger\Processor as ProcessorInterface;
use \Chukdo\Contracts\Logger\Formatter as FormatterInterface;
use \Chukdo\Logger\Formatters\DefaultFormatter;

/**
 * Gestionaire de log
 *
 * @copyright 	licence MIT, Copyright (C) 2015 Domingo
 * @author 		Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
abstract class AbstractHandler implements HandlerInterface
{
    /**
     * @var FormatterInterface
     */
    protected $formatter;

    /**
     * @var Array
     */
    protected $levels;

    /**
     * Processeurs de modifications des enregistrements
     *
     * @var ProcessorInterface[]
     */
    protected $processors = [];

    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->setLevels(array_keys(Logger::getLevels()));
    }

    /**
     * Ecriture de l'enregistrement
     *
     * @param  array    $record
     * @return bool     true si l'operation reussi false sinon
     */
    abstract protected function write(array $record);

    /**
     * Défini les niveaux qui declenche l'enregistrement d'un log
     *
     * @param  array  $levels
     * @return void
     */
    public function setLevels($levels)
    {
        $this->levels = (array) $levels;
    }

    /**
     * Test si l'enregistrement sera géré ou non par le gestionnaire de log
     *
     * @param  array  $record
     * @return bool
     */
    public function isHandling(array $record)
    {
        return in_array($record['level'], $this->levels);
    }

    /**
     * Gere un enregistrement
     *
     * @param  array  $record
     * @return bool
     */
    public function handle(array $record)
    {
        if ($this->isHandling($record)) {
            $record = $this->processRecord($record);

            if (!$this->formatter) {
                $this->setFormatter(new DefaultFormatter());
            }

            $record['formatted'] = $this->formatter->formatRecord($record);

            return $this->write($record);
        }

        return false;
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
     * Ajoute un processeur de modification des enregistrements de log
     *
     * @param   ProcessorInterface  $processor
     * @return  $this
     */
    public function pushProcessor(ProcessorInterface $processor)
    {
        array_push($this->processors, $processor);

        return $this;
    }

    /**
     * Defini le formatteur de données.
     *
     * @param  FormatterInterface   $formatter
     * @return self
     */

    public function setFormatter(FormatterInterface $formatter)
    {
        $this->formatter = $formatter;

        return $this;
    }
}