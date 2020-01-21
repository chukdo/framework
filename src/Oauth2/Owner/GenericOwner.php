<?php

namespace Chukdo\Oauth2\Owner;

use Chukdo\Json\Json;

/**
 * Oauth2 token
 *
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class GenericOwner extends AbstractOwner
{
    /**
     * GenericToken constructor.
     *
     * @param Json $json
     */
    public function __construct( Json $json )
    {
        $this->id    = $json->offsetGet( 'id', $json->offsetGet( 'id', 'account_id' ) );
        $this->email = $json->offsetGet( 'email', '' );
        $standard    = array_flip( [
                                       'id',
                                       'account_id',
                                   ] );

        $this->values = new Json( array_diff_key( $json->toArray(), $standard ) );
    }
}