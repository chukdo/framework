<?php

namespace Chukdo\Oauth2\Token;

use Chukdo\Json\Json;

/**
 * Oauth2 token
 *
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class GenericToken extends AbstractToken
{
    /**
     * GenericToken constructor.
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
}