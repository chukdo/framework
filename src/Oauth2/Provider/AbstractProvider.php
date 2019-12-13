<?php

namespace Chukdo\Oauth2\Provider;

use Chukdo\Contracts\Oauth2\Provider as ProviderInterface;
use Chukdo\Helper\Arr;
use Chukdo\Helper\Url;

abstract Class AbstractProvider implements ProviderInterface
{
    /**
     * @var string
     */
    protected $clientId;

    /**
     * @var string
     */
    protected $clientSecret;

    /**
     * @var string
     */
    protected $redirectUri;

    /**
     * @var string
     */
    protected $urlAuthorize;

    /**
     * @var string
     */
    protected $urlAccessToken;

    /**
     * @var string
     */
    protected $urlResourceOwner;

    /**
     * @var string
     */
    protected $grantType;

    /**
     * @var string
     */
    protected $user;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var string
     */
    protected $scopeSeparator = ',';

    /**
     * @var string
     */
    protected $scopeDefault = '';

    /**
     * @var array
     */
    protected $scope = [];

    /**
     * @var string
     */
    protected $keywordState;

    /**
     * @return string
     */
    public function getAuthorizationUrl(): string
    {
        $client_id     = $this->getClientId();
        $redirect_url  = $this->getRedirectUri();
        $response_type = 'code';
        $scope         = $this->getScope();
        $state         = $this->getState();

        // pas de client id => err
        // pas ...
        // ...

        Url::build();

        $url = $this->getUrlAuthorize() . '?';

        //scope
        //state

        //client_id
        //redirect_uri

        //reponse_type = code || token


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
     * @return string|null
     */
    public function getKeywordState(): ?string
    {
        return $this->keywordState;
    }

    /**
     * @return string
     */
    public function getState(): string
    {
        return hash_hmac( 'sha256', $this->getClientId(), $this->keywordState, true );
    }

    /**
     * @return string|null
     */
    public function getGrantType(): ?string
    {
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
     * @return string|null
     */
    public function getUser(): ?string
    {
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
     * @return string|null
     */
    public function getPassword(): ?string
    {
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
     * @return string|null
     */
    public function getClientId(): ?string
    {
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
     * @return string|null
     */
    public function getClientSecret(): ?string
    {
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
    public function getRedirectUri(): ?string
    {
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
     * @return string|null
     */
    public function getUrlAuthorize(): ?string
    {
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
     * @return string|null
     */
    public function getUrlAccessToken(): ?string
    {
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
    public function getUrlResourceOwner(): ?string
    {
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