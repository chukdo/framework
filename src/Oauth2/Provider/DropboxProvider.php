<?php

namespace Chukdo\Oauth2\Provider;

use Chukdo\Contracts\Oauth2\Token as TokenInterface;
use Chukdo\Oauth2\Token\DropboxToken;

Class DropboxProvider extends AbstractProvider
{
    /**
     * @var string
     */
    protected string $urlAuthorize = 'https://www.dropbox.com/oauth2/authorize';

    /**
     * @var string
     */
    protected string $urlAccessToken = 'https://api.dropboxapi.com/oauth2/token';

    /**
     * @param string $grantType authorisation_code|password|client_credentials|token_refresh|implicit (utile uniquement en JS)
     * @param array  $options
     * @param string $method
     *
     * @return TokenInterface
     */
    public function getToken( string $grantType, array $options = [], string $method = 'POST' ): TokenInterface
    {
        return new DropboxToken( $this->getTokenUrl( $grantType, $options )
                                      ->httpMethod( $method )
                                      ->content() );
    }
}