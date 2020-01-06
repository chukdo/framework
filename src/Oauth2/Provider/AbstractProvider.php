<?php

namespace Chukdo\Oauth2\Provider;

use Chukdo\Contracts\Oauth2\Token as TokenInterface;
use Chukdo\Contracts\Oauth2\Provider as ProviderInterface;
use Chukdo\Helper\Arr;
use Chukdo\Http\Url;
use Chukdo\Oauth2\Oauth2Exception;

abstract Class AbstractProvider implements ProviderInterface
{
    /**
     * @var string
     */
    protected string $clientId;

    /**
     * @var string
     */
    protected string $clientSecret;

    /**
     * @var string
     */
    protected string $redirectUri;

    /**
     * @var string
     */
    protected string $urlAuthorize;

    /**
     * @var string
     */
    protected string $urlAccessToken;

    /**
     * @var string
     */
    protected string $urlResourceOwner;

    /**
     * @var string
     */
    protected string $grantType;

    /**
     * @var string
     */
    protected string $user;

    /**
     * @var string
     */
    protected string $password;

    /**
     * @var string
     */
    protected string $scopeSeparator = ',';

    /**
     * @var string
     */
    protected string $scopeDefault = '';

    /**
     * @var array
     */
    protected array $scope = [];

    /**
     * @var string
     */
    protected string $keywordState;

    /**
     * @return string
     */
    public function getAuthorizationUrl(): string
    {
        $url = new Url( $this->getUrlAuthorize(), [
            'client_id'     => $this->getClientId(),
            'redirect_uri'  => $this->getRedirectUri(),
            'response_type' => 'code',
            'scope'         => $this->getScope(),
            'state'         => $this->getState(),
        ] );

        return $url->buildUrl();
    }

    /**
     * @param string $grantType authorisation_code|password|client_credentials|token_refresh|implicit (utile uniquement en JS)
     * @param array  $options
     * @param string $method
     *
     * @return TokenInterface
     */
    abstract public function getToken( string $grantType, array $options = [], string $method = 'POST' ): TokenInterface;

    /**
     * @param string $grantType
     * @param array  $options
     *
     * @return Url
     */
    protected function getTokenUrl( string $grantType, array $options = [] ): Url
    {
        $url = new Url( $this->getUrlAccessToken(), [
            'client_id'     => $this->getClientId(),
            'client_secret' => $this->getClientSecret(),
        ] );

        if ( $grantType === 'code' && !isset( $options[ 'code' ] ) ) {
            throw new Oauth2Exception( 'Access GenericToken Url need a option [code]' );
        }

        if ( $grantType === 'password' && !isset( $options[ 'username' ], $options[ 'password' ] ) ) {
            throw new Oauth2Exception( 'Access GenericToken Url need a option [username] and [password]' );
        }

        if ( $grantType === 'refresh' && !isset( $options[ 'refresh_token' ] ) ) {
            throw new Oauth2Exception( 'Access GenericToken Url need a option [code]' );
        }

        switch ( $grantType ) {
            case 'authorization_code' :
                $url->setInput( 'redirect_uri', $this->getRedirectUri() )
                    ->setInput( 'grant_type', 'authorization_code' )
                    ->setInput( 'code', $options[ 'authorization_code' ] );
                break;
            case 'password' :
                $url->setInput( 'redirect_uri', $this->getRedirectUri() )
                    ->setInput( 'grant_type', 'password' )
                    ->setInput( 'username', $options[ 'username' ] )
                    ->setInput( 'password', $options[ 'password' ] );
                break;
            case 'client_credentials' :
                $url->setInput( 'grant_type', 'client_credentials' )
                    ->setInput( 'scope', $this->getScope() );
                break;
            case 'refresh_token':
                $url->setInput( 'grant_type', 'refresh_token' )
                    ->setInput( 'refresh_token', $options[ 'refresh_token' ] )
                    ->setInput( 'scope', $this->getScope() );
                break;
            case 'implicit' :
                $url->setInput( 'redirect_uri', $this->getRedirectUri() )
                    ->setInput( 'response_type', 'token' )
                    ->setInput( 'state', $this->getState() )
                    ->setInput( 'scope', $this->getScope() );
                break;
            default :
                throw new Oauth2Exception( sprintf( 'Grant Type [%s] is unknow', $grantType ) );
        }

        return $url;
    }

    /**
     * @param string $keyword
     *
     * @return ProviderInterface
     */
    public function setKeywordState( string $keyword ): ProviderInterface
    {
        $this->keywordState = $keyword;

        return $this;
    }

    /**
     * @return string
     */
    public function getKeywordState(): string
    {
        return $this->keywordState ?? md5( $this->getClientId() . $this->getRedirectUri() );
    }

    /**
     * @param string $state
     *
     * @return bool
     */
    public function checkState( string $state ): bool
    {
        return $state === $this->getState();
    }

    /**
     * @return string
     */
    public function getState(): string
    {
        return hash_hmac( 'sha256', $this->getClientId(), $this->getKeywordState() );
    }

    /**
     * @return string
     */
    public function getGrantType(): string
    {
        if ( $this->grantType === null ) {
            throw new Oauth2Exception( 'GrantType not defined' );
        }

        return $this->grantType;
    }

    /**
     * @param string $grantType
     *
     * @return ProviderInterface
     */
    public function setGrantType( string $grantType ): ProviderInterface
    {
        $this->grantType = $grantType;

        return $this;
    }

    /**
     * @return string
     */
    public function getUser(): string
    {
        if ( $this->user === null ) {
            throw new Oauth2Exception( 'User not defined' );
        }

        return $this->user;
    }

    /**
     * @param string $user
     *
     * @return ProviderInterface
     */
    public function setUser( string $user ): ProviderInterface
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        if ( $this->password === null ) {
            throw new Oauth2Exception( 'User not defined' );
        }

        return $this->password;
    }

    /**
     * @param string $password
     *
     * @return ProviderInterface
     */
    public function setPassword( string $password ): ProviderInterface
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return string
     */
    public function getScope(): string
    {
        return Arr::empty( $this->scope )
            ? $this->getScopeDefault()
            : implode( $this->getScopeSeparator(), $this->scope );
    }

    /**
     * @param mixed ...$scopes
     *
     * @return ProviderInterface
     */
    public function setScope( ...$scopes ): ProviderInterface
    {
        $this->scope = Arr::merge( $this->scope, Arr::spreadArgs( $scopes ) );

        return $this;
    }

    /**
     * @return string
     *
     *
     */
    public function getScopeDefault(): string
    {
        return $this->scopeDefault;
    }

    /**
     * @param string $scopeDefault
     *
     * @return ProviderInterface
     */
    public function setScopeDefault( string $scopeDefault ): ProviderInterface
    {
        $this->scopeDefault = $scopeDefault;

        return $this;
    }

    /**
     * @return string
     */
    public function getScopeSeparator(): string
    {
        return $this->scopeSeparator;
    }

    /**
     * @param string $scopeSeparator
     *
     * @return ProviderInterface
     */
    public function setScopeSeparator( string $scopeSeparator ): ProviderInterface
    {
        $this->scopeSeparator = $scopeSeparator;

        return $this;
    }

    /**
     * @return string
     */
    public function getClientId(): string
    {
        if ( $this->clientId === null ) {
            throw new Oauth2Exception( 'ClientId not defined' );
        }

        return $this->clientId;
    }

    /**
     * @param string $clientId
     *
     * @return ProviderInterface
     */
    public function setClientId( string $clientId ): ProviderInterface
    {
        $this->clientId = $clientId;

        return $this;
    }

    /**
     * @return string
     */
    public function getClientSecret(): string
    {
        if ( $this->clientSecret === null ) {
            throw new Oauth2Exception( 'ClientSecret not defined' );
        }

        return $this->clientSecret;
    }

    /**
     * @param string $clientSecret
     *
     * @return ProviderInterface
     */
    public function setClientSecret( string $clientSecret ): ProviderInterface
    {
        $this->clientSecret = $clientSecret;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getRedirectUri(): string
    {
        if ( $this->redirectUri === null ) {
            throw new Oauth2Exception( 'RedirectUri not defined' );
        }

        return $this->redirectUri;
    }

    /**
     * @param string $redirectUri
     *
     * @return ProviderInterface
     */
    public function setRedirectUri( string $redirectUri ): ProviderInterface
    {
        $this->redirectUri = $redirectUri;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrlAuthorize(): string
    {
        if ( $this->urlAuthorize === null ) {
            throw new Oauth2Exception( 'UrlAuthorize not defined' );
        }

        return $this->urlAuthorize;
    }

    /**
     * @param string $urlAuthorize
     *
     * @return ProviderInterface
     */
    public function setUrlAuthorize( string $urlAuthorize ): ProviderInterface
    {
        $this->urlAuthorize = $urlAuthorize;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrlAccessToken(): string
    {
        if ( $this->urlAccessToken === null ) {
            throw new Oauth2Exception( 'urlAccessToken not defined' );
        }

        return $this->urlAccessToken;
    }

    /**
     * @param string $urlAccessToken
     *
     * @return ProviderInterface
     */
    public function setUrlAccessToken( string $urlAccessToken ): ProviderInterface
    {
        $this->urlAccessToken = $urlAccessToken;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getUrlResourceOwner(): string
    {
        if ( $this->urlResourceOwner === null ) {
            throw new Oauth2Exception( 'urlResourceOwner not defined' );
        }

        return $this->urlResourceOwner;
    }

    /**
     * @param string $urlResourceOwner
     *
     * @return ProviderInterface
     */
    public function setUrlResourceOwner( string $urlResourceOwner ): ProviderInterface
    {
        $this->urlResourceOwner = $urlResourceOwner;

        return $this;
    }
}