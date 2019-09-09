<?php

namespace Chukdo\Contracts\Http;

use Chukdo\Storage\FileUploaded;

/**
 * Interface de gestion des INPUTS.
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
interface Input
{
    /**
     * @param string      $name
     * @param string|null $allowedMimeTypes
     * @param int|null    $maxFileSize
     * @return FileUploaded|null
     */
    public function file( string $name, string $allowedMimeTypes = null, int $maxFileSize = null ): ?FileUploaded;
}