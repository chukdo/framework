<?php

namespace Chukdo\Logger\Handlers;

use Chukdo\Logger\Logger;
use Chukdo\Contracts\Logger\Handler as HandlerInterface;
use Chukdo\Contracts\Logger\Processor as ProcessorInterface;
use Chukdo\Contracts\Logger\Formatter as FormatterInterface;
use Chukdo\Logger\Formatters\JsonStringFormatter;

/**
 * Abstract class.
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
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
    protected $levels = [];

    /**
     * @var array
     */
    protected $processors = [];

    /**
     * Constructeur.
     */
    public function __construct()
    {
        $this->setLevels(array_keys(Logger::getLevels()));
    }

    /**
     * @param $levels
     */
    public function setLevels( $levels ): void
    {
        $this->levels = (array) $levels;
    }

    /**
     * @param array $record
     * @return bool
     */
    public function handle( array $record ): bool
    {
        if( $this->isHandling($record) ) {
            $record = $this->processRecord($record);

            if( !$this->formatter ) {
                $this->setFormatter(new JsonStringFormatter());
            }

            return $this->write($this->formatter->formatRecord($record));
        }

        return false;
    }

    /**
     * @param array $record
     * @return bool
     */
    public function isHandling( array $record ): bool
    {
        return in_array($record[ 'level' ],
            $this->levels);
    }

    /**
     * @param array $record
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
     * @param FormatterInterface $formatter
     * @return HandlerInterface
     */
    public function setFormatter( FormatterInterface $formatter ): HandlerInterface
    {
        $this->formatter = $formatter;

        return $this;
    }

    /**
     * @param $record
     * @return bool
     */
    abstract protected function write( $record ): bool;

    /**
     * @param ProcessorInterface $processor
     * @return HandlerInterface
     */
    public function pushProcessor( ProcessorInterface $processor ): HandlerInterface
    {
        array_push($this->processors,
            $processor);

        return $this;
    }
}
