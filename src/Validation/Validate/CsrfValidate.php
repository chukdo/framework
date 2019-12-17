<?php

namespace Chukdo\Validation\Validate;

use Chukdo\Contracts\Validation\Validate as ValidateInterface;
use Chukdo\Helper\Crypto;

/**
 * Validate handler.
 *
 * @version   1.0.0
 * @copyright licence MIT, Copyright (C) 2019 Domingo
 * @since     08/01/2019
 * @author    Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class CsrfValidate implements ValidateInterface
{
    /**
     * @var string
     */
    protected string $salt;

    /**
     * @return string
     */
    public function name(): string
    {
        return 'csrf';
    }

    /**
     * @param array $attributes
     *
     * @return self
     */
    public function attributes( array $attributes ): ValidateInterface
    {
        foreach ( $attributes as $attr ) {
            $this->salt = $attr;
        }

        return $this;
    }

    /**
     * @param $input
     *
     * @return bool
     */
    public function validate( $input ): bool
    {
        return Crypto::decodeCsrf( $input, $this->salt );
    }
}
