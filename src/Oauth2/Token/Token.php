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
Class Token implements TokenInterface
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
     * Token constructor.
     *
     * @param Json $json
     */
    public function __construct( Json $json )
    {
        $this->accessToken     = $json->offsetGet( 'access_token' );
        $this->refreshToken    = $json->offsetGet( 'refresh_token', $this->accessToken );
        $this->expire          = (int) $json->offsetGet( 'expire', $json->offsetGet( 'expire_in' ) );
        $this->resourceOwnerId = $json->offsetGet( 'resource_owner_id', null );
        $standard              = array_flip( [
                                                 'access_token',
                                                 'resource_owner_id',
                                                 'refresh_token',
                                                 'expires_in',
                                                 'expires',
                                             ] );

        if ( $this->expire === 0 ) {
            $this->expire = 1349067599;
        }

        if ( $this->expire < 1349067600 ) {
            $this->expire += time();
        }

        $this->values = new Json( array_diff_key( $json->toArray(), $standard ) );
    }

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