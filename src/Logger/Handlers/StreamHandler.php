<?php namespace Chukdo\Logger\Handlers;

use \Chukdo\Helper\File;
use \Chukdo\Http\Url;

/**
 * Gestionaire de fichier
 *
 * @copyright 	licence MIT, Copyright (C) 2014 Domingo
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
     * Ecriture de l'enregistrement
     *
     * @param  array    $record
     * @return bool     true si l'operation reussi false sinon
     */
    public function write(array $record)
    {
        $this->handleFile();
        $write = $this->getStream()->fwrite($record['formatted']."\r\n");

        return $write > 0 ? true : false;
    }

    /**
     * defini l'url
     *
     * @param   string  $url
     * @return  void
     */
    private function setUrl($url)
    {
        $this->url = new Url($url);
    }

    /**
     * Retourne l'objet url
     *
     * @return object   Chukdo\Http\Url
     */
    private function getUrl()
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