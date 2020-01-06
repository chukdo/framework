<?php

namespace Chukdo\Oauth2\Provider;

use Chukdo\Contracts\Oauth2\Token as TokenInterface;
use Chukdo\Oauth2\Token\Token;

Class GenericProvider extends AbstractProvider
{
    /**
     * @param string $grantType code|password|client|implicit (utile uniquement en JS)
     * @param array  $options
     * @param string $method
     *
     * @return TokenInterface
     */
    public function getToken( string $grantType, array $options = [], string $method = 'POST' ): TokenInterface
    {
        return new Token( $this->getTokenUrl( $grantType, $options )
                               ->httpMethod( $method )
                               ->content() );
    }
}