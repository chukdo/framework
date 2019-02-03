<?php namespace Chukdo\Logger\Handlers;

use \Chukdo\Helper\File;
use \Chukdo\Http\Url;

/**
 * Gestionnaire des flux fichier
 *
 * @package 	Logger
 * @version 	1.0.0
 * @copyright 	licence MIT, Copyright (C) 2019 Domingo
 * @since 		08/01/2019
 * @author 		Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class StreamHandler extends AbstractHandler
{
    /**
     * @var object Chukdo\Http\Url
     */
    protected $url;

    /**
     * @var object
     */
    protected $stream;

    /**
     * Constructeur
     *
     * @param   string  $url
     * @param   string  $dateFormat (dateformat d, dmY etc..)
     * @param   int     $filePermission
     */
    public function __construct($url, $dateFormat = '', $filePermission = 0777)
    {
        $this->setUrl($url);
        $this->getUrl()->setMeta('basename', $this->getUrl()->getFile())
            ->setMeta('date', $dateFormat)
            ->setMeta('permission', $filePermission);

        File::mkDir($this->getUrl()->getDir());
        parent::__construct();
    }

    /**
     * Destructeur
     *
     * @return void
     */
    public function __destruct()
    {
        $this->closeFile();
    }

    /**
     * @param string $record
     * @return bool
     */
    public function write(string $record): bool
    {
        $this->handleFile();
        $write = $this->getStream()->fwrite($record."\r\n");

        return $write > 0 ? true : false;
    }

    /**
     * @param string $url
     */
    private function setUrl(string $url): void
    {
        $this->url = new Url($url);
    }

    /**
     * @return Url
     */
    private function getUrl(): Url
    {
        return $this->url;
    }

    /**
     * Retourne le gestionnaire de fichier
     *
     * @return  string
     */
    private function getStream()
    {
        return $this->stream;
    }

    /**
     * Ouvre un pointeur de fichier
     *
     * @return  void
     */
    private function handleFile()
    {
        $url = $this->getUrl();

        if ($url->getMeta('date')) {
            $url->setFile(date($url->getMeta('date')).'_'.$url->getMeta('basename'));
        }

        if ($this->getStream()) {
            if ($url->getFile() != basename($this->getStream()->getPathname())) {
                $this->closeFile();
                $this->openFile();
            }
        } else {
            $this->openFile();
        }
    }

    /**
     * Ouvre un gestionnaire de fichier et defini les permissions sur le fichier
     *
     * @return  void
     */
    private function openFile()
    {
        $url            = $this->getUrl();
        $file           =  $url->buildUrl();
        $this->stream   = new \SplFileObject($file, 'a+');

        File::chmod($file, $url->getMeta('permission'));
    }

    /**
     * Ferme le pointeur de fichier
     *
     * @return  void
     */
    private function closeFile()
    {
        $this->stream = '';
    }
}