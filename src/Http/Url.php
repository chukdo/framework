<?php

namespace Chukdo\Http;

use Chukdo\Helper\Str;
use Chukdo\Helper\Arr;

/**
 * Gestion des URLs.
 *
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Url
{
    /**
     * @var array
     */
    protected array $url = [
        'scheme'   => 'file',
        'host'     => '',
        'port'     => '',
        'user'     => '',
        'pass'     => '',
        'dir'      => '',
        'file'     => '',
        'input'    => [],
        'fragment' => '',
    ];

    /**
     * Url constructor.
     *
     * @param string|null $url
     * @param iterable    $params
     * @param string|null $defaultScheme
     */
    public function __construct( string $url = null, iterable $params = [], string $defaultScheme = null )
    {
        if ( $url ) {
            if ( $defaultScheme && !Str::match( '/^[a-z0-9]+:\/\//', $url ) ) {
                $url = $defaultScheme . '://' . $url;
            }

            $this->parseUrl( $url );

            foreach ( $params as $key => $value ) {
                $this->setInput( $key, $value );
            }
        }
    }

    /**
     * @param string $url
     *
     * @return $this
     */
    public function parseUrl( string $url ): self
    {
        $mergeUrl  = [
            'scheme'   => 'file',
            'host'     => '',
            'port'     => '',
            'user'     => '',
            'pass'     => '',
            'path'     => '',
            'query'    => '',
            'fragment' => '',
        ];
        $urlMerged = Arr::merge( $mergeUrl, (array) parse_url( $url ) );
        $this->setScheme( $urlMerged[ 'scheme' ] )
             ->setHost( $urlMerged[ 'host' ] )
             ->setPort( $urlMerged[ 'port' ] )
             ->setUser( $urlMerged[ 'user' ] )
             ->setPass( $urlMerged[ 'pass' ] )
             ->setPath( $urlMerged[ 'path' ] )
             ->setQuery( $urlMerged[ 'query' ] )
             ->SetFragment( $urlMerged[ 'fragment' ] );

        return $this;
    }

    /**
     * @param string $fragment
     *
     * @return $this
     */
    public function setFragment( string $fragment ): self
    {
        $this->url[ 'fragment' ] = $fragment;

        return $this;
    }

    /**
     * @param string $query
     *
     * @return $this
     */
    public function setQuery( string $query ): self
    {
        parse_str( $query, $this->url[ 'input' ] );

        return $this;
    }

    /**
     * @param string $path
     *
     * @return $this
     */
    public function setPath( string $path ): self
    {
        $this->setDir( dirname( $path ) );
        $this->setFile( basename( $path ) );

        return $this;
    }

    /**
     * @param string $dir
     *
     * @return $this
     */
    public function setDir( string $dir ): self
    {
        $this->url[ 'dir' ] = $dir;

        return $this;
    }

    /**
     * @param string $file
     *
     * @return $this
     */
    public function setFile( string $file ): self
    {
        $this->url[ 'file' ] = $file;

        return $this;
    }

    /**
     * @param string $pass
     *
     * @return $this
     */
    public function setPass( string $pass ): self
    {
        $this->url[ 'pass' ] = $pass;

        return $this;
    }

    /**
     * @param string $user
     *
     * @return $this
     */
    public function setUser( string $user ): self
    {
        $this->url[ 'user' ] = $user;

        return $this;
    }

    /**
     * @param string $port
     *
     * @return $this
     */
    public function setPort( string $port ): self
    {
        $this->url[ 'port' ] = $port;

        return $this;
    }

    /**
     * @param string $host
     *
     * @return $this
     */
    public function setHost( string $host ): self
    {
        $this->url[ 'host' ] = $host;

        return $this;
    }

    /**
     * @param string $scheme
     *
     * @return $this
     */
    public function setScheme( string $scheme ): self
    {
        $this->url[ 'scheme' ] = $scheme;

        return $this;
    }

    /**
     * @param string $key
     * @param        $value
     *
     * @return $this
     */
    public function setInput( string $key, $value ): self
    {
        $this->url[ 'input' ][ $key ] = $value;

        return $this;
    }

    /**
     * @param iterable $inputs
     *
     * @return $this
     */
    public function setInputs( iterable $inputs ): self
    {
        foreach ( $inputs as $key => $value ) {
            $this->setInput( $key, $value );
        }

        return $this;
    }

    /**
     * @param string $key
     *
     * @return string
     */
    public function getInput( string $key ): string
    {
        return $this->url[ 'input' ][ $key ] ?? '';
    }

    /**
     * @return array
     */
    public function getInputs(): array
    {
        return $this->url[ 'input' ];
    }

    /**
     * @return string
     */
    public function getSubDomain(): string
    {
        $subDomains = $this->getSubDomains();

        return empty( $subDomains )
            ? ''
            : $subDomains[ 0 ];
    }

    /**
     * @return array
     */
    public function getSubDomains(): array
    {
        $domainAndTld = '.' . $this->getDomain() . '.' . $this->getTld();
        $host         = str_replace( 'www.', '', $this->getHost() );
        if ( Str::contain( $host, $domainAndTld ) ) {
            return explode( '.', str_replace( $domainAndTld, '', $host ) );
        }

        return [];
    }

    /**
     * @return string
     */
    public function getDomain(): string
    {
        $tld  = '.' . $this->getTld();
        $host = $this->getHost();

        if ( Str::contain( $host, '.' ) ) {
            $uri    = explode( '.', str_replace( $tld, '', $host ) );
            $domain = end( $uri );

            return $domain !== false
                ? $domain
                : '';
        }

        return '';
    }

    /**
     * @return string
     */
    public function getTld(): string
    {
        $tld = explode( '.', substr( $this->getHost(), strlen( $this->getHost() ) - 8 ) );
        array_shift( $tld );

        return empty( $tld )
            ? ''
            : implode( '.', $tld );
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->url[ 'host' ];
    }

    public function __toString()
    {
        return $this->buildCompleteUrl();
    }

    /**
     * @return string
     */
    public function buildCompleteUrl(): string
    {
        return $this->buildDsn() . $this->buildPath() . $this->buildQuery() . $this->buildFragment();
    }

    /**
     * Construit la partie de l'url "protocole://authentification@hote:port".
     *
     * @return string
     */
    public function buildDsn(): string
    {
        return $this->buildScheme() . $this->buildAuth() . $this->buildHost() . $this->buildPort();
    }

    /**
     * @return string
     */
    public function buildScheme(): string
    {
        if ( $scheme = $this->getScheme() ) {
            return $scheme . '://';
        }

        return '';
    }

    /**
     * @return string
     */
    public function getScheme(): string
    {
        return $this->url[ 'scheme' ];
    }

    /**
     * @return string
     */
    public function buildAuth(): string
    {
        if ( $user = $this->getUser() ) {
            if ( $pass = $this->getPass() ) {
                $pass = ':' . $pass;
            }

            return $user . $pass . '@';
        }

        return '';
    }

    /**
     * @return string
     */
    public function getUser(): string
    {
        return $this->url[ 'user' ];
    }

    /**
     * @return string
     */
    public function getPass(): string
    {
        return $this->url[ 'pass' ];
    }

    /**
     * @return string
     */
    public function buildHost(): string
    {
        return $this->getHost();
    }

    /**
     * @return string
     */
    public function buildPort(): string
    {
        if ( $port = $this->getPort() ) {
            return ':' . $port;
        }

        return '';
    }

    /**
     * @return string
     */
    public function getPort(): String
    {
        return $this->url[ 'port' ];
    }

    /**
     * @return string
     */
    public function buildPath(): string
    {
        return $this->getPath();
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return rtrim( $this->getDir(), '/' ) . '/' . $this->getFile();
    }

    /**
     * @return string
     */
    public function getDir(): string
    {
        return $this->url[ 'dir' ];
    }

    /**
     * @return string
     */
    public function getFile(): string
    {
        return $this->url[ 'file' ];
    }

    /**
     * @return string
     */
    public function buildQuery(): string
    {
        if ( $query = $this->getQuery() ) {
            return '?' . $query;
        }

        return '';
    }

    /**
     * @return string
     */
    public function getQuery(): string
    {
        return http_build_query( $this->url[ 'input' ] );
    }

    /**
     * @return string
     */
    public function buildFragment(): string
    {
        if ( $fragment = $this->getFragment() ) {
            return '#' . $fragment;
        }

        return '';
    }

    /**
     * @return string
     */
    public function getFragment(): string
    {
        return $this->url[ 'fragment' ];
    }

    /**
     * @return string
     */
    public function buildUrl(): string
    {
        return $this->buildDsn() . $this->buildPath() . $this->buildFragment();
    }
}
