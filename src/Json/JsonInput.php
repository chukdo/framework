<?php namespace Chukdo\Json;

use Chukdo\Storage\FileUploaded;
use Chukdo\Validation\Validator;

/**
 * Gestion des inputs
 *
 * @package     Json
 * @version 	1.0.0
 * @copyright 	licence MIT, Copyright (C) 2019 Domingo
 * @since 		08/01/2019
 * @author Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class JsonInput extends Json
{
    /**
     * JsonInput constructor.
     */
    public function __construct()
    {
        parent::__construct($_REQUEST);
    }

    /**
     * @param iterable $rules
     * @return Validator
     */
    public function validate(Iterable $rules): Validator
    {
        return new Validator($this, (array) $rules);
    }

    /**
     * @param string $name
     * @param string|null $allowedMimeTypes
     * @param int|null $maxFileSize
     * @return FileUploaded
     */
    public function file(string $name, string $allowedMimeTypes = null, int $maxFileSize = null): FileUploaded
    {
        return new FileUploaded($name, $allowedMimeTypes, $maxFileSize);
    }
}
