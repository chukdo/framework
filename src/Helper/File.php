<?php

/**
 * Gestion des Fichiers
 *
 * @package 	Helper
 * @version 	1.0.0
 * @copyright 	licence MIT, Copyright (C) 2019 Domingo
 * @since 		08/01/2019
 * @author 		Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
final class File
{
    /**
     * File constructor.
     */
    private function __construct() {}

    /**
     * Hash un fichier et retourne son chemin de stockage
     * 
     * @param string $name nom du fichier
     * @param int $hashlevel nombre de sous repertoire pour le stockage du fichier
     * @return string chemin complet du fichier à stocker
     */
    public static function hash(string $name, int $hashlevel = 2): string
    {
        $file = crc32($name);
        $path = '';
        $hash = str_split(hash('crc32', $file), 2);
        
        /** Hashlevel */
        for ($i = 0; $i < $hashlevel; $i++) {
            $path .= $hash[$i].'/';   
        }

        return $path.$file;
    }
    
    /**
     * Determine le content-type d'un fichier
     * 
     * @param string $file
     * @return string content-type
     */
    public static function getContentType(string $file): string
    {
        $finfo  = finfo_open(FILEINFO_MIME_TYPE);
        $type   = finfo_file($finfo, $file);

        finfo_close($finfo);
        
        return $type;
    }

    /**
     * @param $file
     * @return bool
     */
    public static function isImage(string $file): bool
    {
        $contentType = self::getContentType($file);

        return substr($contentType, 0, 5) == 'image';
    }

    /**
     * @param $file
     * @return bool
     */
    public static function isPdf(string $file): bool
    {
        $contentType = self::getContentType($file);

        return $contentType == 'application/pdf' || $contentType == 'application/octet-stream';
    }

    /**
     * @param string $url
     * @param string $filePath
     * @param int $maxSize
     * @return string
     * @throws FileException
     */
    public static function uploadFromUrl(string $url, string $filePath, int $maxSize = 1024 * 1024 * 16): string
    {
        $file   = $filePath . Data::uid('upload_url_');
        $size   = 0;

        /** Récupération et sauvegarde du fichier */
        if ($fpw = fopen($file, 'wb')) {
            if ($fpr = fopen($url, 'rb')) {
                while(($buffer = fgets($fpr, 4096)) !== false) {
                    fwrite($fpw, $buffer);
                    $size += 4096;

                    if ($size > $maxSize) {
                        fclose($fpw);
                        fclose($fpr);
                        throw new \Chukdo\Bootstrap\AppException('Le fichier est trop lourd, il dépasse les '.helper_data::memory($maxSize, true));
                    }
                }
            } else {
                fclose($fpw);
                fclose($fpr);
                throw new \Chukdo\Bootstrap\AppException('Impossible de télécharger le fichier, l\'adresse '.$url.' n\'est pas valide');
            }

            fclose($fpw);
            fclose($fpr);
        } else {
            throw new \Chukdo\Bootstrap\AppException('Impossible de sauvegarder le fichier téléchargé à l\'adresse '.$url);
        }

        return $file;
    }

    /**
     * Sauvegarde les fichiers téléchargés dans l'espace de stockage de l'application
     *
     * @param string $type mime type des type de fichiers acceptés séparés par des virgules
     * @param string $filePath chemin ou sauvegarger les données
     * @param int $maxsize taille maximale des fichiers
     * @return array
     * @throws FileException
     */
	public static function upload(string $type, string $filePath, int $maxsize = 1024 * 1024 * 16): array
	{
		$upload = [];
		$types = explode(',', $type);

		foreach (self::normalizeFile() as $key => $file) {
			$name	= $file['name'];
			$tmp	= $file['tmp_name'];
			$mime	= $file['type'];
			$size	= $file['size'];
			$error	= $file['error'];
			$err	= '';
			$accept = false;

			foreach ($types as $type) {
			    if (preg_match("#$type#i", $mime)) {
			        $accept = true;
			    }
			}

            /** Pas d'erreur lors du téléchargement */
            if ($error == 0) {

                /** Fichier téléchargé */
                $file = $filePath . \Chukdo\Helper\data::uid('upload_');

                if (move_uploaded_file($tmp, $file)) {
                    chmod($file, 0777);

                    /** Type de fichier valide */
                    if ($accept) {

                        /** Valide la taille du fichier */
                        if ($size <= $maxsize || $maxsize == 0) {
                            $upload[$key] = ['file' => $file, 'mime' => $mime, 'size' => $size, 'name' => $name];
                        } else {
                            $err = "La taille du fichier $name ($size Octets) est supérieur à la valeur autorisée ($maxsize Octets) ";
                        }
                    } else {
                        $err = "Le type du fichier $name n'est pas valide";
                    }
                } else {
                    $err = "Le fichier $name n'a pu être téléchargé";
                }
            } else {

                /** Messages d'erreurs */
                switch ($error) {
                    case 1 : $err = "Le fichier $name excède la taille maximale autorisé par le serveur"; break;
                    case 2 : $err = "Le fichier $name excède la taille maximale autorisé par le formulaire"; break;
                    case 3 : $err = "Le fichier $name n'a été que partiellement téléchargé"; break;
                    case 4 : break; /** empty file */
                    case 5 :
                    case 6 : $err = "Le dossier temporaire de téléchargement est manquant"; break;
                    case 7 : $err = "Échec de l'écriture du fichier $name sur le disque."; break;
                    case 8 : $err = "Une extension PHP a arrété l'envoi du fichier $name"; break;
                }
            }

            /** Gestion des erreurs */
            if ($err != '') {
                throw new \Chukdo\Bootstrap\AppException($err);
            }
        }

        return $upload;
    }

    /**
     * Normalise la structure $_FILES pour les fichiers sous forme de tableaux
     *
     * @return array
     */
	public static function normalizeFile(): array
	{
		$nFile = [];

		foreach ($_FILES as $name => $file) {
			foreach ($file as $type => $value) {
				if (is_array($value)) {
					foreach (self::__normalizeFile($value) as $nName => $nValue) {
						$nFile[$name.'/'.$nName][$type] = $nValue;
					}
				} else {
					$nFile[$name][$type] = $value;
				}
			}
		}

		return $nFile;
	}
	
    /**
     * Methode recursive de normalizeFile
     * 
     * @param array $array 
     * @return array
     */
	private static function __normalizeFile(array $array): array
	{
		$nFile = [];
		
		foreach ($array as $k => $v) {
			if (is_array($v)) {
				foreach(self::__normalizeFile($v) as $_k => $_v) {
					$nFile[$k.'/'.$_k] = $_v;
				}
			} else {
				$nFile[$k] = $v;
			}
		}
		return $nFile;
	}
}