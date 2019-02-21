<?php namespace Chukdo\Json;

Use \Throwable;
Use \Exception;
Use \Chukdo\Bootstrap\AppException;

/**
 * Intégration d'une exception dans Json
 *
 * @package 	Exception
 * @version 	1.0.0
 * @copyright 	licence MIT, Copyright (C) 2019 Domingo
 * @since 		08/01/2019
 * @author 		Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class JsonException extends Json
{
    /**
     * @param Throwable $e
     * @return JsonException
     */
    public function loadException(Throwable $e): self
    {
        if (!$e instanceof Exception) {
            $e = new AppException($e->getMessage(), $e->getCode(), $e);
        }

        $backTrace = [];

        foreach ($e->getTrace() as $trace) {
            $trace       = new Json($trace);
            $backTrace[] = [
                'Call' => $trace->offsetGet('class') . $trace->offsetGet('type') . $trace->offsetGet('function') . '()',
                'File' => $trace->offsetGet('file'),
                'Line' => $trace->offsetGet('line')
            ];
        }

        $this
            ->set('Code', $e->getCode())
            ->set('Message', $e->getMessage())
            ->set('File', $e->getFile())
            ->set('Line', $e->getLine())
            ->set('Trace', $backTrace);

        return $this;
    }

    /**
     * @param string|null $title
     * @param string|null $code
     * @return string
     */
    public function toHtml(string $title = null, string $code = null): string
    {
        return parent::toHtml(($title ?: 'Error') . ' (' . ($code ?: '500') . ')', 'red');
    }

    /**
     * @param string|null $title
     */
    public function toConsole(string $title = null): void
    {
        $table = new \cli\Table();
        $table->setHeaders(['%R' . strtoupper($title ?: 'Exception') . '%n']);
        $table->setRenderer(new \cli\table\Ascii([80]));
        $table->display();

        $table = new \cli\Table();
        $table->setHeaders(['%YCode%n', '%YMessage%n', '%YFile%n', '%YLine%n']);
        $table->addRow([
            $this->get('Code'),
            $this->get('Message'),
            $this->get('File'),
            $this->get('Line')
        ]);

        $table->setRenderer(new \cli\table\Ascii([5, 30, 40, 5]));
        $table->display();

        $backTrace = $this->get('Trace');

        if ($backTrace instanceof Json) {
            $table = new \cli\Table();
            $table->setHeaders(['%YFile%n', '%YLine%n', '%YCall%n']);

            foreach ($backTrace as $trace) {
                $table->addRow([
                    $trace->get('File'),
                    $trace->get('Line'),
                    $trace->get('Call')
                ]);
            }

            $table->setRenderer(new \cli\table\Ascii([40, 5, 35]));
            $table->display();
        }
    }
}