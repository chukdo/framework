<?php namespace Chukdo\Storage\Wrappers;


Use \Chukdo\Contracts\Storage\StreamWrapper as StreamWrapperInterface;
Use \Chukdo\Contracts\Storage\Stream as StreamInterface;
Use \Chukdo\Http\Url;

/**
 * Gestion des exceptions
 *
 * @package 	Exception
 * @version 	1.0.0
 * @copyright 	licence MIT, Copyright (C) 2019 Domingo
 * @since 		08/01/2019
 * @author 		Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class StreamException extends \Exception {}

/**
 * Abstraction de la classe PHP StreamWrapper (File First Vision)
 *
 * @package 	Stream
 * @version 	1.0.0
 * @copyright 	licence MIT, Copyright (C) 2019 Domingo
 * @since 		08/01/2019
 * @author 		Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
abstract class AbstractStream implements StreamWrapperInterface, StreamInterface
{
    /**
     * @var \Chukdo\Http\Url
     */
    protected $url = null;

    /**
     * @var string
     */
    protected $mode = null;

    /**
     * @var int
     */
    protected $tell = 0;

    /**
     * @var bool
     */
    protected $eof = false;

    /**
     * @var array
     */
    protected $dir;

    /**
     * @return mixed
     */
    abstract function initStream();

    /**
     * @param string $url
     */
    private function setUrl(string $url): void
    {
        $this->url = new Url($url);
    }

    /**
     * @return Url|null
     */
    private function getUrl(): ?Url
    {
        return $this->url;
    }

    /**
     * Retourne le scheme du fichier
     *
     * @return string|null
     */
    protected function getScheme(): ?string
    {
        return $this->getUrl()->getScheme();
    }

    /**
     * Retourne le host du fichier
     *
     * @return string|null
     */
    protected function getHost(): ?string
    {
        return $this->getUrl()->getHost();
    }

    /**
     * Retourne le chemin du fichier
     *
     * @return string|null
     */
    protected function getPath(): ?string
    {
        return trim($this->getUrl()->getPath(), '/');
    }

    /**
     * Defini le mode d'ecriture ou de lecture du fichier
     *
     * @param   string  $mode
     * @return  void
     */
    protected function setMode(string $mode): void
    {
        /** On ne tient pas compte du flag B(inary) */
        $this->mode = str_replace('b', '', $mode);
    }

    /**
     * Retourne le mode d'ecriture ou de lecture du fichier
     *
     * @return string|null
     */
    protected function getMode(): ?string
    {
        return $this->mode;
    }

    /**
     * Retourne la position du pointeur du fichier
     *
     * @return  int
     */
    protected function getTell(): int
    {
        return $this->tell;
    }

    /**
     * Defini la position du pointeur du fichier
     *
     * @param   int $tell
     * @return  void
     */
    protected function setTell(int $tell): void
    {
        $this->tell = $tell;
    }

    /**
     * deplace la position du pointeur du fichier
     *
     * @param   int $tell
     * @return  void
     */
    protected function appendTell(int $tell): void
    {
        $this->tell += $tell;
    }

    /**
     * Retourne si on a atteint la fin du fichier
     *
     * @return  bool
     */
    protected function getEof(): bool
    {
        return $this->eof;
    }

    /**
     * Defini la fin du fichier
     *
     * @return  bool
     */
    protected function setEof(): bool
    {
        return $this->eof = true;
    }

    /**
     * Lit les informations sur une ressource de fichier
     *
     * @return array|null
     */
    public function stream_stat(): ?array
    {
        if ($size = $this->streamSize()) {
            return [
                'size'  => $size,
                'mode'  => $this->streamIsDir() ? 16895 : 33279,
                'ctime' => $this->streamCreatedTime(),
                'atime' => $this->streamAccessTime(),
                'mtime' => $this->streamModifiedTime()
            ];
        }

        return null;
    }

    /**
     * Place le pointeur de flux à une position
     *
     * @param int $offset
     * @param int $whence
     * @return bool
     */
    public function stream_seek(int $offset, $whence = SEEK_SET): bool
    {
        switch ($whence) {
            case SEEK_SET :
                $this->setTell($offset);
                break;
            case SEEK_CUR :
                $this->appendTell($offset);
                break;
            case SEEK_END :
                $this->setTell($this->streamSize() + $offset);
                break;
        }

        return true;
    }

    /**
     * Lit la position courante dans un flux
     *
     * @return int
     */
    public function stream_tell(): int
    {
        return $this->getTell();
    }

    /**
     * change les métadonnées du flux
     *
     * @param   string  $path
     * @param   int     $option
     * @param   mixed   $value
     * @return  bool
     */
    public function stream_metadata(string $path, int $option, $value): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public function stream_close(): void
    {
        $this->streamClose();
    }

    /**
     * Expédie le contenu
     *
     * @return bool
     */
    public function stream_flush(): bool
    {
        return true;
    }

    /**
     * Lecture du fichier
     *
     * @param int $count
     * @return string|null
     * @throws StreamException
     */
    public function stream_read(int $count): ?string
    {
        switch ($this->getMode()) {
            case 'w' :
            case 'a' :
            case 'x' :
            case 'c' :
                throw new StreamException(sprintf("[%s] has writeonly mode [%s]", $this->getUrl()->getPath(), $this->getMode()));
                break;
        }

        $read = $this->streamGetRange($this->getTell(), $count);
        $this->appendTell(min(strlen($read), $count));

        if ($this->getTell() >= $this->streamSize()) {
            $this->setEof();
        }

        $this->streamAccessTime(true);
        return $read;
    }

    /**
     * Tronque un fichier
     *
     * @param int $new_size
     * @return bool
     */
    public function stream_truncate(int $new_size): bool
    {
        $size = $this->streamSize();

        if ($new_size > $size) {
            return $this->streamSetRange($size, str_repeat(chr(0), $new_size - $size));

        } else if ($new_size < $size) {
            if ($new_size == 0) {
                $this->streamSet(null);

            } else {
                $this->streamSet($this->streamGetRange(0, $new_size));
            }

            if ($this->getTell() > $new_size) {
                $this->streamSetRange($new_size, str_repeat(chr(0), $this->getTell() - $new_size));
            }
        }

        return true;
    }

    /**
     * Lit la ressource sous-jacente de flux
     *
     * @param   int $cast_as
     * @return  bool
     */
    public function stream_cast(int $cast_as)
    {
        return false;
    }

    /**
     * Cette méthode est appelée immédiatement après l'initialisation du gestionnaire (fopen() / file_get_contents()).
     *
     * @param string $path
     * @param string $mode
     * @param int $options
     * @param string|null $opened_path
     * @return bool
     * @throws StreamException
     */
    public function stream_open(string $path, string $mode, int $options, ?string &$opened_path): bool
    {
        $this->setUrl($path);
        $this->setMode($mode);
        $exits = $this->streamExists();

        switch ($this->getMode()) {
            case 'r' :
            case 'r+' :
                if (!$exits) {
                    throw new StreamException(sprintf("File [%s] doesn't exists", $this->getUrl()->getPath()));
                }
                break;
            case 'w' :
            case 'w+' :
                $this->streamSet(null);
                break;
            case 'a' :
            case 'a+' :
                if ($tell = $this->streamSize()) {
                    $this->setTell($tell);
                } else {
                    $this->streamSet(null);
                }
                break;
            case 'x' :
            case 'x+' :
                if ($exits) {
                    throw new StreamException(sprintf("Mode X not allow [%s] file exists", $this->getUrl()->getPath()));
                }

            $this->streamSet(null);
                break;
            case 'c' :
            case 'c+' :
                if (!$exits) {
                    $this->streamSet(null);
                }
                break;

        }

        if (!$exits) {
            $this->streamCreatedTime(true);
        }

        return true;
    }

    /**
     * @return bool
     */
    public function stream_eof(): bool
    {
        return $this->getEof();
    }

    /**
     * Cette méthode est appelée en réponse à fwrite().
     *
     * @param   string  $data
     * @throws  StreamException
     * @return  int     le nombre d'octets qui ont pu être stockés correctement, et 0 si aucun n'a pu être stocké.
     */
    public function stream_write(string $data): int
    {
        $strlen = mb_strlen($data);

        switch ($this->getMode()) {
            case 'r' :
                throw new StreamException(sprintf("[%s] is in readonly mode", $this->getUrl()->getPath()));
                break;
            case 'r+' :
            case 'c' :
            case 'c+' :
                $this->streamSetRange($this->getTell(), $data);
                $this->appendTell($strlen);
                break;
            case 'w' :
            case 'w+' :
            case 'a' :
            case 'a+' :
            case 'x' :
            case 'x+' :
                $this->streamAppend($data);
                $this->appendTell($strlen);
                break;
        }

        $this->streamModifiedTime(true);
        return $strlen;
    }

    /**
     * Lit les informations sur un fichier
     *
     * @param string $path
     * @param int $flags
     * @return array|null
     */
    public function url_stat(string $path, int $flags): ?array
    {
        $this->setUrl($path);
        return $this->stream_stat();
    }

    /**
     * Efface un fichier
     *
     * @param string $path
     * @return bool
     */
    public function unlink(string $path): bool
    {
        $this->setUrl($path);
        return (bool) $this->streamDelete();
    }

    /**
     * Change les options du flux
     *
     * @param int $option
     * @param int $arg1
     * @param int $arg2
     * @return bool
     */
    public function stream_set_option(int $option, int $arg1, int $arg2): bool
    {
        return true;
    }

    /**
     * Renomme un fichier
     *
     * @param string $pathFrom
     * @param string $pathTo
     * @return bool
     */
    public function rename(string $pathFrom, string $pathTo): bool
    {
        $this->setUrl($pathFrom);
        $urlTo  = new Url($pathTo);

        /** Chagement de DB = Error */
        if ($this->getHost() != $urlTo->getHost()) {
            return false;
        }

        $rename = trim($urlTo->getPath(), '/');

        if ($this->streamRename($rename)) {
            $this->url = $urlTo;
            $this->streamCreatedTime(true);
            $this->streamModifiedTime(true);
            return true;
        }

        return false;
    }

    /**
     * Verrouillage logique de fichiers
     *
     * @param   int $operation
     * @return  bool
     */
    public function stream_lock(int $operation): bool
    {
        return true;
    }

    /**
     * Ouvre un dossier en lecture
     *
     * @param   string $path
     * @param   int    $options
     * @return  bool
     */
    public function dir_opendir(string $path, int $options): bool
    {
        $this->setUrl($path);
        $this->dir = $this->streamListDir();

        return true;
    }

    /**
     * Remet au début une ressource de dossier
     *
     * @return bool
     */
    public function dir_rewinddir(): bool
    {
        reset($this->dir);
        return true;
    }

    /**
     * Lit un fichier dans un dossier
     *
     * @return string|false
     */
    public function dir_readdir()
    {
        $read = current($this->dir);
        next($this->dir);

        return $read;
    }

    /**
     * Ferme une ressource de dossier
     *
     * @return bool
     */
    public function dir_closedir(): bool
    {
        $this->dir = [];
        return true;
    }

    /**
     * Crée un dossier
     *
     * @param string $path
     * @param int $mode
     * @param int $options
     * @return bool
     */
    public function mkdir(string $path, int $mode, int $options): bool
    {
        $this->setUrl($path);
        return $this->streamSetDir($options & 1);
    }

    /**
     * Supprime un dossier
     *
     * @param   string $path
     * @param   int    $options
     * @return  bool
     */
    public function rmdir(string $path, int $options): bool
    {
        $this->setUrl($path);
        return $this->streamDeleteDir();
    }

}