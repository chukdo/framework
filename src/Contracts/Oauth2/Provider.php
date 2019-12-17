<?php

namespace Chukdo\Contracts\Oauth2;

/**
 * Interface Oauth2 provider
 *
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
interface Provider
{
    /**
     * @return string
     */
    public function getAuthorizationUrl(): string;

    /**
     * @param string $grantType code|password|client_credentials|implicit (utile uniquement en JS)
     * @param array  $options
     * @param string $method
     *
     */
    public function getToken( string $grantType, array $options = [], string $method = 'POST' );

    /**
     * @param string $keyword
     *
     * @return Provider
     */
    public function setKeywordState( string $keyword ): Provider;

    /**
     * @return string
     */
    public function getKeywordState(): string;

    /**
     * @param string $state
     *
     * @return bool
     */
    public function checkState( string $state ): bool;

    /**
     * @return string
     */
    public function getState(): string;

    /**
     * @return string
     */
    public function getGrantType(): string;

    /**
     * @param string $grantType
     *
     * @return Provider
     */
    public function setGrantType( string $grantType ): Provider;

    /**
     * @return string
     */
    public function getUser(): string;

    /**
     * @param string $user
     *
     * @return Provider
     */
    public function setUser( string $user ): Provider;

    /**
     * @return string
     */
    public function getPassword(): string;

    /**
     * @param string $password
     *
     * @return Provider
     */
    public function setPassword( string $password ): Provider;

    /**
     * @return string
     */
    public function getScope(): string;

    /**
     * @param mixed ...$scopes
     *
     * @return Provider
     */
    public function setScope( ...$scopes ): Provider;

    /**
     * @return string
     *
     *
     */
    public function getScopeDefault(): string;

    /**
     * @param string $scopeDefault
     *
     * @return Provider
     */
    public function setScopeDefault( string $scopeDefault ): Provider;

    /**
     * @return string
     */
    public function getScopeSeparator(): string;

    /**
     * @param string $scopeSeparator
     *
     * @return Provider
     */
    public function setScopeSeparator( string $scopeSeparator ): Provider;

    /**
     * @return string
     */
    public function getClientId(): string;

    /**
     * @param string $clientId
     *
     * @return Provider
     */
    public function setClientId( string $clientId ): Provider;

    /**
     * @return string
     */
    public function getClientSecret(): string;

    /**
     * @param string $clientSecret
     *
     * @return Provider
     */
    public function setClientSecret( string $clientSecret ): Provider;

    /**
     * @return string|null
     */
    public function getRedirectUri(): string;

    /**
     * @param string $redirectUri
     *
     * @return Provider
     */
    public function setRedirectUri( string $redirectUri ): Provider;

    /**
     * @return string
     */
    public function getUrlAuthorize(): string;

    /**
     * @param string $urlAuthorize
     *
     * @return Provider
     */
    public function setUrlAuthorize( string $urlAuthorize ): Provider;

    /**
     * @return string
     */
    public function getUrlAccessToken(): string;

    /**
     * @param string $urlAccessToken
     *
     * @return Provider
     */
    public function setUrlAccessToken( string $urlAccessToken ): Provider;

    /**
     * @return string|null
     */
    public function getUrlResourceOwner(): string;

    /**
     * @param string $urlResourceOwner
     *
     * @return Provider
     */
    public function setUrlResourceOwner( string $urlResourceOwner ): Provider;
}
