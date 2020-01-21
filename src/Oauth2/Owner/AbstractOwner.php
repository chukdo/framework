<?php

namespace Chukdo\Oauth2\Owner;

use Chukdo\Contracts\Oauth2\Owner as OwnerInterface;
use Chukdo\Json\Json;

/**
 * Oauth2 token
 *
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Abstract Class AbstractOwner implements OwnerInterface
{
    /**
     * @var string
     */
    protected string $id;

    /**
     * @var string
     */
    protected string $email;

    /**
     * @var Json
     */
    protected Json $values;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return Json
     */
    public function values(): Json
    {
        return $this->values;
    }


}