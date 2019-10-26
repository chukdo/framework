<?php

namespace Chukdo\Contracts\Storage;
/**
 * Interface de gestion de flux.
 *
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
interface Stream
{
	/**
	 * Retourne le contenu du fichier.
	 *
	 * @return mixed
	 */
	public function streamGet();
	
	/**
	 * Ajoute du contenu au debut du fichier (et le ramene à 0 s'il existe avant).
	 *
	 * @param string $content
	 *
	 * @return bool
	 */
	public function streamSet( string $content ): bool;
	
	/**
	 * Ajoute du contenu à la fin du fichier.
	 *
	 * @param string $content
	 *
	 * @return bool
	 */
	public function streamAppend( string $content ): bool;
	
	/**
	 * Retourne une portion du fichier du fichier.
	 *
	 * @param int $offset
	 * @param int $length
	 *
	 * @return string|null
	 */
	public function streamGetRange( int $offset, int $length ): ?string;
	
	/**
	 * Ecris une portion du fichier en commencant à l'offet défini.
	 *
	 * @param int    $offset
	 * @param string $content
	 *
	 * @return bool
	 */
	public function streamSetRange( int $offset, string $content ): bool;
	
	/**
	 * Retourne si le fichier existe.
	 *
	 * @return bool
	 */
	public function streamExists(): bool;
	
	/**
	 * Retourne la taille du fichier.
	 *
	 * @return int
	 */
	public function streamSize(): int;
	
	/**
	 * Supprime le fichier.
	 *
	 * @return bool
	 */
	public function streamDelete(): bool;
	
	/**
	 * Renomme le fichier.
	 *
	 * @param string $newkey
	 *
	 * @return bool
	 */
	public function streamRename( string $newkey ): bool;
	
	/**
	 * Defini ou retourne la derniere date d'acces au fichier.
	 *
	 * @param bool $time timestamp
	 *
	 * @return int
	 */
	public function streamAccessTime( $time = null ): int;
	
	/**
	 * Defini ou retourne la date de creation du fichier.
	 *
	 * @param bool $time timestamp
	 *
	 * @return int
	 */
	public function streamCreatedTime( $time = false ): int;
	
	/**
	 * Defini ou retourne la derniere date de modification au fichier.
	 *
	 * @param bool $time timestamp
	 *
	 * @return int
	 */
	public function streamModifiedTime( $time = false ): int;
	
	/**
	 * Libere le flux.
	 */
	public function streamClose(): bool;
	
	/**
	 * Crée un dossier.
	 *
	 * @param bool $recursive
	 *
	 * @return bool
	 */
	public function streamSetDir( bool $recursive ): bool;
	
	/**
	 * Supprime un dossier.
	 *
	 * @return bool
	 */
	public function streamDeleteDir(): bool;
	
	/**
	 * Retourne si le fichier est un dossier.
	 *
	 * @return bool
	 */
	public function streamIsDir(): bool;
	
	/**
	 * Retourne la liste des fichiers present dans le dossier.
	 *
	 * @return array
	 */
	public function streamListDir(): array;
}
