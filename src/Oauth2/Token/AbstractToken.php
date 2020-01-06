<?php

namespace Chukdo\Oauth2\Token;

use Chukdo\Contracts\Oauth2\Token as TokenInterface;
use Chukdo\Json\Json;

/**
 * Oauth2 token
 *
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Abstract Class AbstractToken implements TokenInterface
{
    /**
     * @var string
     */
    protected string $accessToken;

    /**
     * @var string
     */
    protected string $refreshToken;

    /**
     * @var int
     */
    protected int $expire;

    /**
     * @var string|null
     */
    protected ?string $resourceOwnerId;

    /**
     * @var Json
     */
    protected Json $values;

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->accessToken;
    }

    /**
     * @return string
     */
    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    /**
     * @return int
     */
    public function getExpires(): int
    {
        return $this->expire;
    }

    /**
     * @return bool
     */
    public function hasExpired(): bool
    {
        return $this->expire <= time();
    }

    /**
     * @return string|null
     */
    public function getResourceOwnerId(): ?string
    {
        return $this->resourceOwnerId;
    }

    /**
     * @return Json
     */
    public function values(): Json
    {
        return $this->values;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getToken();
    }
}