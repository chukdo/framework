<?php namespace Chukdo\Logger\Handlers;

/**
 * Gestionnaire des logs pour fichier
 *
 * @package 	Logger
 * @version 	1.0.0
 * @copyright 	licence MIT, Copyright (C) 2019 Domingo
 * @since 		08/01/2019
 * @author 		Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class FileHandler extends AbstractHandler
{
    /**
     * @var string
     */
    protected $file;

    /**
     * FileHandler constructor.
     * @param string $file
     */
    public function __construct(string $file)
    {
        $this->file = $file;

        parent::__construct();
    }

    /**
     * Destructeur
     *
     * @return void
     */
    public function __destruct()
    {
        $this->file = null;
    }

    /**
     * @param string $record
     * @return bool
     */
    public function write(string $record): bool
    {
        $fp = fopen($this->file, 'a');
        $r  = fwrite($fp, $record."\n");

        fclose($fp);

        return (bool) $r;
    }
}