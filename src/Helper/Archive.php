<?php namespace Chukdo\Helper;

/**
 * Gestion des exceptions
 *
 * @package 	Helper
 * @version 	1.0.0
 * @copyright 	licence MIT, Copyright (C) 2019 Domingo
 * @since 		08/01/2019
 * @author 		Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class ArchiveException extends \Exception {}

/**
 * Classe Archive
 * gestion des fichiers compressés
 *
 * @package helper
 * @version 1.0.0
 * @copyright licence GPL, Copyright (C) 2012 Domingo
 * @since 10/06/2012
 * @author Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
final class Archive
{
    /**
     * Constructeur privé, empeche l'intanciation de la classe statique
     * @return void
     */
    private function __construct() {}

    /**
     * @param string $data
     * @return string
     */
    public static function ungzipString(string $data): string
    {
        $flags          = ord(substr($data, 3, 1));
        $headerlen      = 10;
        $extralen       = 0;
        $filenamelen    = 0;

        if ($flags & 4) {
            $extralen = unpack('v' ,substr($data, 10, 2));
            $extralen = $extralen[1];
            $headerlen += 2 + $extralen;
        }

        /** Filename */
        if ($flags & 8) {
            $headerlen = strpos($data, chr(0), $headerlen) + 1;
        }

        /** Comment */
        if ($flags & 16) {
            $headerlen = strpos($data, chr(0), $headerlen) + 1;
        }

        /** CRC at end of file */
        if ($flags & 2) {
            $headerlen += 2;
        }

        return gzinflate(substr($data, $headerlen));
    }

    /**
     * Decompresse une donnée encodé zip (compression des données via ajax)
     *
     * @param string $data
     * @return string
     */
    public static function unzipString(string $data): string
    {
        $head = unpack("Vsig/vver/vflag/vmeth/vmodt/vmodd/Vcrc/Vcsize/Vsize/vnamelen/vexlen", substr($data,0,30));
        $raw  = gzinflate(substr($data,30 + $head['namelen'] + $head['exlen'], $head['csize']));

        return $raw;
    }

    /**
     * @param $file
     * @param $path
     * @param bool $list
     * @param bool $root
     * @return array|int
     * @throws exception
     */
    public static function unzipFile($file, $path, $list = false, $root = false)
    {
        $path     = rtrim($path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
        $ret      = $list ? [] : 0;
        $open     = zip_open($file);       
        $ziperror = array(
            ZIPARCHIVE::ER_MULTIDISK => 'Multi-disk zip archives not supported.',
            ZIPARCHIVE::ER_RENAME => 'Renaming temporary file failed.',
            ZIPARCHIVE::ER_CLOSE => 'Closing zip archive failed', 
            ZIPARCHIVE::ER_SEEK => 'Seek error',
            ZIPARCHIVE::ER_READ => 'Read error',
            ZIPARCHIVE::ER_WRITE => 'Write error',
            ZIPARCHIVE::ER_CRC => 'CRC error',
            ZIPARCHIVE::ER_ZIPCLOSED => 'Containing zip archive was closed',
            ZIPARCHIVE::ER_NOENT => 'No such file.',
            ZIPARCHIVE::ER_EXISTS => 'File already exists',
            ZIPARCHIVE::ER_OPEN => 'Can\'t open file', 
            ZIPARCHIVE::ER_TMPOPEN => 'Failure to create temporary file.', 
            ZIPARCHIVE::ER_ZLIB => 'Zlib error',
            ZIPARCHIVE::ER_MEMORY => 'Memory allocation failure', 
            ZIPARCHIVE::ER_CHANGED => 'Entry has been changed',
            ZIPARCHIVE::ER_COMPNOTSUPP => 'Compression method not supported.', 
            ZIPARCHIVE::ER_EOF => 'Premature EOF',
            ZIPARCHIVE::ER_INVAL => 'Invalid argument',
            ZIPARCHIVE::ER_NOZIP => 'Not a zip archive',
            ZIPARCHIVE::ER_INTERNAL => 'Internal error',
            ZIPARCHIVE::ER_INCONS => 'Zip archive inconsistent', 
            ZIPARCHIVE::ER_REMOVE => 'Can\'t remove file',
            ZIPARCHIVE::ER_DELETED => 'Entry has been deleted'
        );
        
        /** Creation repertoire */
        mkdir($path, 0777, true);

        /** Erreur d'ouverture du fichier */
        if (!is_resource($open)) {
            throw new ArchiveException('Zip File Function error: '.$ziperror[$open]);
        }

        while(($read = zip_read($open)) !== false) {
;
            /** Erreur d'ouverture du fichier */
            if (!is_resource($read)) {
                throw new ArchiveException('Zip File Function error: '.$ziperror[$read]);
            }

            $name = zip_entry_name($read);
            $dir  = trim(dirname($name), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
            $file = ($root ? $path : $path.$dir).basename($name);
            $size = zip_entry_filesize($read);

            /** Dossier */            
            if (substr($name, -1) == '/') {  
                if (!is_dir($path.$dir)) {
                    mkdir($path.$dir, 0777, true);
                }
                
            /** Fichier */
            } else {
                if (!$root) {
                    if (!is_dir($path.$dir)) {
                        mkdir($path.$dir, 0777, true);
                    }
                }
                if (($fp = fopen($file, 'wb')) !== false) {
                    while ($size > 0) {
                        $block   = min($size, 10240);
                        $size   -= $block;
                        $content = zip_entry_read($read, $block);
        
                        if ($content !== false) {
                            fwrite($fp, $content);
                        }
                    }
                    
                    fclose($fp);
                    @chmod($file, 0777);
                    
                    if ($list) {
                        $ret[] = $file;
                    } else {
                        $ret++;
                    }
                
                /** Error */
                } else {
                    throw new ArchiveException('Zip File Function error: can\'t write file '.$file);
                }
            }
        }
        
        return $ret;
    }
}