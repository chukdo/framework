<?php

namespace Chukdo\Oauth2\Provider;

use Chukdo\Contracts\Oauth2\Token as TokenInterface;
use Chukdo\Oauth2\Token\GenericToken;

Class GenericProvider extends AbstractProvider
{
    /**
     * @param string $grantType authorisation_code|password|client_credentials|token_refresh|implicit (utile uniquement en JS)
     * @param array  $options
     * @param string $method
     *
     * @return TokenInterface
     */
    public function getToken( string $grantType, array $options = [], string $method = 'POST' ): TokenInterface
    {
        return new GenericToken( $this->getTokenUrl( $grantType, $options )
                                      ->httpMethod( $method )
                                      ->content() );
    }
}