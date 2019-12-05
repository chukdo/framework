<?php

namespace Chukdo\Oauth2\Client;

use Chukdo\Helper\Arr;

abstract Class AbstractClient
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
     * @return string
     */
    public function getGrantType(): string
    {
        return $this->grantType;
    }

    /**
     * @param string $grantType
     *
     * @return $this
     */
    public function setGrantType( string $grantType ): self
    {
        $this->grantType = $grantType;

        return $this;
    }

    /**
     * @return string
     */
    public function getUser(): string
    {
        return $this->user;
    }

    /**
     * @param string $user
     *
     * @return $this
     */
    public function setUser( string $user ): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     *
     * @return $this
     */
    public function setPassword( string $password ): self
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
     * @return $this
     */
    public function setScope( ...$scopes ): self
    {
        $this->scope = Arr::merge( $this->scope, Arr::spreadArgs( $scopes ) );

        return $this;
    }

    /**
     * @return string
     */
    public function getScopeDefault(): string
    {
        return $this->scopeDefault;
    }

    /**
     * @param string $scopeDefault
     *
     * @return $this
     */
    public function setScopeDefault( string $scopeDefault ): self
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
     * @return $this
     */
    public function setScopeSeparator( string $scopeSeparator ): self
    {
        $this->scopeSeparator = $scopeSeparator;

        return $this;
    }

    /**
     * @return string
     */
    public function getClientId(): string
    {
        return $this->clientId;
    }

    /**
     * @param string $clientId
     *
     * @return $this
     */
    public function setClientId( string $clientId ): self
    {
        $this->clientId = $clientId;

        return $this;
    }

    /**
     * @return string
     */
    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

    /**
     * @param string $clientSecret
     *
     * @return $this
     */
    public function setClientSecret( string $clientSecret ): self
    {
        $this->clientSecret = $clientSecret;

        return $this;
    }

    /**
     * @return string
     */
    public function getRedirectUri(): string
    {
        return $this->redirectUri;
    }

    /**
     * @param string $redirectUri
     *
     * @return $this
     */
    public function setRedirectUri( string $redirectUri ): self
    {
        $this->redirectUri = $redirectUri;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrlAuthorize(): string
    {
        return $this->urlAuthorize;
    }

    /**
     * @param string $urlAuthorize
     *
     * @return $this
     */
    public function setUrlAuthorize( string $urlAuthorize ): self
    {
        $this->urlAuthorize = $urlAuthorize;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrlAccessToken(): string
    {
        return $this->urlAccessToken;
    }

    /**
     * @param string $urlAccessToken
     *
     * @return $this
     */
    public function setUrlAccessToken( string $urlAccessToken ): self
    {
        $this->urlAccessToken = $urlAccessToken;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrlResourceOwner(): string
    {
        return $this->urlResourceOwner;
    }

    /**
     * @param string $urlResourceOwner
     *
     * @return $this
     */
    public function setUrlResourceOwner( string $urlResourceOwner ): self
    {
        $this->urlResourceOwner = $urlResourceOwner;

        return $this;
    }
}