<?php

namespace Chukdo\Contracts\Oauth2;

use Chukdo\Contracts\Json\Json;

/**
 * Interface Oauth2 token
 *
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
interface Token
{
    /**
     * @return string
     */
    public function getToken(): string;

    /**
     * @return string
     */
    public function getRefreshToken(): string;

    /**
     * @return int
     */
    public function getExpires(): int;

    /**
     * @return bool
     */
    public function hasExpired(): bool;

    /**
     * @return string|null
     */
    public function getResourceOwnerId(): ?string;

    /**
     * @return Json
     */
    public function values(): Json;
}
