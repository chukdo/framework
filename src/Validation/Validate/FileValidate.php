<?php

namespace Chukdo\Validation\Validate;

use Chukdo\Contracts\Validation\Validate as ValidateInterface;
use Chukdo\Storage\FileUploaded;

/**
 * Validate handler.
 * @version   1.0.0
 * @copyright licence MIT, Copyright (C) 2019 Domingo
 * @since     08/01/2019
 * @author    Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class FileValidate implements ValidateInterface
{
    /**
     * @var int|null
     */
    protected $maxFileSize = null;

    /**
     * @var string|null
     */
    protected $allowedMimeTypes = null;

    /**
     * @return string
     */
    public function name(): string
    {
        return 'file';
    }

    /**
     * @param array $attributes
     *
     * @return self
     */
    public function attributes( array $attributes ): ValidateInterface
    {
        foreach ( $attributes as $attr ) {
            if ( (string) (int) $attr === $attr ) {
                $this->maxFileSize = 1024 * 1024 * (int) $attr;
            } else {
                $this->allowedMimeTypes .= $attr . ',';
            }
        }

        $this->allowedMimeTypes = trim( $this->allowedMimeTypes, ',' );

        return $this;
    }

    /**
     * @param $input
     *
     * @return bool
     */
    public function validate( $input ): bool
    {
        if ( $input instanceof FileUploaded ) {
            $input->setAllowedMimeTypes( $this->allowedMimeTypes );
            $input->setMaxFileSize( $this->maxFileSize );

            return $input->isValid();
        }

        return false;
    }
}
