<?php

namespace Chukdo\Http;

/**
 * Gestion des URLs.
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
    protected $url = [
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
     * @param string|null $url
     */
    public function __construct( string $url = null ) {
        if( $url ) {
            $this->parseUrl($url);
        }
    }

    /**
     * @param string $url
     * @return Url
     */
    public function parseUrl( string $url ): Url {
        $mergeUrl = [
            'scheme'   => 'file',
            'host'     => '',
            'port'     => '',
            'user'     => '',
            'pass'     => '',
            'path'     => '',
            'query'    => '',
            'fragment' => '',
        ];

        $url = array_merge($mergeUrl,
            (array) parse_url($url));

        $this->setScheme($url[ 'scheme' ])
            ->setHost($url[ 'host' ])
            ->setPort($url[ 'port' ])
            ->setUser($url[ 'user' ])
            ->setPass($url[ 'pass' ])
            ->setPath($url[ 'path' ])
            ->setQuery($url[ 'query' ])
            ->SetFragment($url[ 'fragment' ]);

        return $this;
    }

    /**
     * @param string $fragment
     * @return Url
     */
    public function setFragment( string $fragment ): Url {
        $this->url[ 'fragment' ] = $fragment;

        return $this;
    }

    /**
     * @param string $query
     * @return Url
     */
    public function setQuery( string $query ): Url {
        parse_str($query,
            $this->url[ 'input' ]);

        return $this;
    }

    /**
     * @param string $path
     * @return Url
     */
    public function setPath( string $path ): Url {
        $this->setDir(dirname($path));
        $this->setFile(basename($path));

        return $this;
    }

    /**
     * @param string $dir
     * @return Url
     */
    public function setDir( string $dir ): Url {
        $this->url[ 'dir' ] = $dir;

        return $this;
    }

    /**
     * @param string $file
     * @return Url
     */
    public function setFile( string $file ): Url {
        $this->url[ 'file' ] = $file;

        return $this;
    }

    /**
     * @param string $pass
     * @return Url
     */
    public function setPass( string $pass ): Url {
        $this->url[ 'pass' ] = $pass;

        return $this;
    }

    /**
     * @param string $user
     * @return Url
     */
    public function setUser( string $user ): Url {
        $this->url[ 'user' ] = $user;

        return $this;
    }

    /**
     * @param string $port
     * @return Url
     */
    public function setPort( string $port ): Url {
        $this->url[ 'port' ] = $port;

        return $this;
    }

    /**
     * @param string $host
     * @return Url
     */
    public function setHost( string $host ): Url {
        $this->url[ 'host' ] = $host;

        return $this;
    }

    /**
     * @param string $scheme
     * @return Url
     */
    public function setScheme( string $scheme ): Url {
        $this->url[ 'scheme' ] = $scheme;

        return $this;
    }

    /**
     * @param string $key
     * @param string $value
     * @return Url
     */
    public function setInput( string $key, string $value ): Url {
        $this->url[ 'input' ][ $key ] = $value;

        return $this;
    }

    /**
     * @param string $key
     * @return string
     */
    public function getInput( string $key ): string {
        return isset($this->url[ 'input' ][ $key ])
            ? $this->url[ 'input' ][ $key ]
            : '';
    }

    /**
     * @return string
     */
    public function buildUrl(): string {
        return $this->buildDsn() . $this->buildPath() . $this->buildQuery() . $this->buildFragment();
    }

    /**
     * Construit la partie de l'url "protocole://authentification@hote:port".
     * @return string
     */
    public function buildDsn(): string {
        return $this->buildScheme() . $this->buildAuth() . $this->buildHost() . $this->buildPort();
    }

    /**
     * @return string
     */
    public function buildScheme(): string {
        if( $scheme = $this->getScheme() ) {
            return $scheme . '://';
        }

        return '';
    }

    /**
     * @return string
     */
    public function getScheme(): string {
        return $this->url[ 'scheme' ];
    }

    /**
     * @return string
     */
    public function buildAuth(): string {
        if( $user = $this->getUser() ) {
            if( $pass = $this->getPass() ) {
                $pass = ':' . $pass;
            }

            return $user . $pass . '@';
        }

        return '';
    }

    /**
     * @return string
     */
    public function getUser(): string {
        return $this->url[ 'user' ];
    }

    /**
     * @return string
     */
    public function getPass(): string {
        return $this->url[ 'pass' ];
    }

    /**
     * @return string
     */
    public function buildHost(): string {
        return $this->getHost();
    }

    /**
     * @return string
     */
    public function getHost(): string {
        return $this->url[ 'host' ];
    }

    /**
     * @return string
     */
    public function buildPort(): string {
        if( $port = $this->getPort() ) {
            return ':' . $port;
        }

        return '';
    }

    /**
     * @return string
     */
    public function getPort(): String {
        return $this->url[ 'port' ];
    }

    /**
     * @return string
     */
    public function buildPath(): string {
        return $this->getPath();
    }

    /**
     * @return string
     */
    public function getPath(): string {
        return rtrim($this->getDir(),
                '/') . '/' . $this->getFile();
    }

    /**
     * @return string
     */
    public function getDir(): string {
        return $this->url[ 'dir' ];
    }

    /**
     * @return string
     */
    public function getFile(): string {
        return $this->url[ 'file' ];
    }

    /**
     * @return string
     */
    public function buildQuery(): string {
        if( $query = $this->getQuery() ) {
            return '?' . $query;
        }

        return '';
    }

    /**
     * @return string
     */
    public function getQuery(): string {
        return http_build_query($this->url[ 'input' ]);
    }

    /**
     * @return string
     */
    public function buildFragment(): string {
        if( $fragment = $this->getFragment() ) {
            return '#' . $fragment;
        }

        return '';
    }

    /**
     * @return string
     */
    public function getFragment(): string {
        return $this->url[ 'fragment' ];
    }
}
