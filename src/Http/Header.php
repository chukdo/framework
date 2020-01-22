<?php

namespace Chukdo\Http;

use DateTime;
use Chukdo\Helper\Arr;
use Chukdo\Helper\Http;
use Chukdo\Helper\Str;
use Chukdo\Json\Json;

/**
 * Gestion des entetes HTTP.
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Header
{
    /**
     * Status RFC 2616.
     *
     * @param array $rfc2616
     */
    public array $rfc2616 = [
        100 => 'HTTP/1.1 100 Continue',
        101 => 'HTTP/1.1 101 Switching Protocols',
        200 => 'HTTP/1.1 200 OK',
        201 => 'HTTP/1.1 201 Created',
        202 => 'HTTP/1.1 202 Accepted',
        203 => 'HTTP/1.1 203 Non-Authoritative Information',
        204 => 'HTTP/1.1 204 No Content',
        205 => 'HTTP/1.1 205 Reset Content',
        206 => 'HTTP/1.1 206 Partial Content',
        300 => 'HTTP/1.1 300 Multiple Choices',
        301 => 'HTTP/1.1 301 Moved Permanently',
        302 => 'HTTP/1.1 302 Found',
        303 => 'HTTP/1.1 303 See Other',
        304 => 'HTTP/1.1 304 Not Modified',
        305 => 'HTTP/1.1 305 Use Proxy',
        307 => 'HTTP/1.1 307 Temporary Redirect',
        400 => 'HTTP/1.1 400 Bad RequestApi',
        401 => 'HTTP/1.1 401 Unauthorized',
        402 => 'HTTP/1.1 402 Payment Required',
        403 => 'HTTP/1.1 403 Forbidden',
        404 => 'HTTP/1.1 404 Not Found',
        405 => 'HTTP/1.1 405 Method Not Allowed',
        406 => 'HTTP/1.1 406 Not Acceptable',
        407 => 'HTTP/1.1 407 Proxy Authentication Required',
        408 => 'HTTP/1.1 408 RequestApi Time-out',
        409 => 'HTTP/1.1 409 Conflict',
        410 => 'HTTP/1.1 410 Gone',
        411 => 'HTTP/1.1 411 Length Required',
        412 => 'HTTP/1.1 412 Precondition Failed',
        413 => 'HTTP/1.1 413 RequestApi Entity Too Large',
        414 => 'HTTP/1.1 414 RequestApi-URI Too Large',
        415 => 'HTTP/1.1 415 Unsupported Media Type',
        416 => 'HTTP/1.1 416 Requested range not satisfiable',
        417 => 'HTTP/1.1 417 Expectation Failed',
        500 => 'HTTP/1.1 500 Internal Server Error',
        501 => 'HTTP/1.1 501 Not Implemented',
        502 => 'HTTP/1.1 502 Bad Gateway',
        503 => 'HTTP/1.1 503 Service Unavailable',
        504 => 'HTTP/1.1 504 Gateway Time-out',
    ];

    /**
     * @var int
     */
    protected int $status = 200;

    /**
     * Entetes.
     *
     * @param Json $header
     */
    protected Json $header;

    /**
     * Cookie.
     *
     * @param Json $cookie
     */
    protected Json $cookie;

    /**
     * Header constructor.
     */
    public function __construct()
    {
        $this->header = new Json();
        $this->cookie = new Json();
    }

    /**
     * @param string $header
     *
     * @return int
     */
    public function parseHeaders( string $header ): int
    {
        if ( strpos( $header, 'HTTP' ) === 0 ) {
            $this->setStatus( (int) Str::matchOne( '/[0-9]{3}/', $header ) );
        }

        preg_match_all( '/^([^:\n]*): ?(.*)$/m', $header, $headers, PREG_SET_ORDER );

        $headers = array_merge( ...array_map( fn( $set ) => [ $set[ 1 ] => trim( $set[ 2 ] ) ], $headers ) );

        foreach ( $headers as $key => $value ) {
            if ( $key === '/^Set-Cookie:/' ) {
                $match = Str::matchAll( '/^(?<name>.*?)=(?<value>.*?);/', $value );
                $this->setCookie( $match->offsetGet( 'name' ), $match->offsetGet( 'value' ) );
            }
            else {
                $this->setHeader( (string) $key, (string) $value );
            }
        }

        return strlen( $header );
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status
     *
     * @return $this
     */
    public function setStatus( int $status ): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @param iterable $headers
     *
     * @return $this
     */
    public function setHeaders( iterable $headers ): self
    {
        foreach ( $headers as $k => $v ) {
            $this->setHeader( $k, $v );
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function unsetHeaders(): self
    {
        $this->header = new Json();

        return $this;
    }

    /**
     * @param iterable $cookies
     *
     * @return $this
     */
    public function setCookies( iterable $cookies ): self
    {
        foreach ( $cookies as $k => $v ) {
            $this->setCookie( $v[ 'name' ], $v[ 'value' ], $v[ 'expires' ], $v[ 'path' ], $v[ 'domain' ] );
        }

        return $this;
    }

    /**
     * @param string $name
     *
     * @return string|null
     */
    public function unsetCookie( string $name ): ?string
    {
        return $this->cookie->offsetUnset( $name );
    }

    /**
     * @return $this
     */
    public function unsetCookies(): self
    {
        $this->cookie = new Json();

        return $this;
    }

    /**
     * @param int         $max        age maximum autorisé du cache en seconde
     * @param bool        $revalidate oblige le client à verifier le cache sur le serveur systematiquement
     * @param string|null $control    ex. public, no_cache. laisser vide la plupart du temps
     *
     * @return $this
     */
    public function setCacheControl( int $max = 3600, bool $revalidate = false, string $control = null ): self
    {
        $cache = [];
        if ( $control !== null ) {
            $cache[] = $control;
        }
        if ( $max !== false ) {
            $cache[] = "max-age=$max";
        }
        if ( $revalidate !== false ) {
            $cache[] = 'must-revalidate';
            $cache[] = 'proxy-revalidate';
        }
        $this->setHeader( 'Cache-Control', implode( ', ', $cache ) );

        return $this;
    }

    /**
     * @return Json
     */
    public function getCacheControl(): Json
    {
        $cache   = new Json( [
                                 'max'        => 0,
                                 'revalidate' => false,
                                 'control'    => null,
                             ] );
        $control = $this->getHeader( 'Cache-Control' );

        if ( $control !== null ) {
            foreach ( explode( ', ', $control ) as $value ) {
                if ( $value === 'must-revalidate' ) {
                    $cache->offsetSet( 'revalidate', true );
                }
                elseif ( $age = Str::matchOne( '/max-age=([0-9]+)/', $value ) ) {
                    $cache->offsetSet( 'max', $age );
                }
                else {
                    $cache->offsetSet( 'control', $value );
                }
            }
        }

        return $cache;
    }

    /**
     * @param string $name
     *
     * @return string|null
     */
    public function getHeader( string $name ): ?string
    {
        return $this->header->offsetGet( $this->normalize( $name ) );
    }

    /**
     * @param string $name
     * @param string $value
     *
     * @return $this
     */
    public function setHeader( string $name, string $value ): self
    {
        $this->header->offsetSet( $this->normalize( $name ), trim( $value ) );

        return $this;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function normalize( string $name ): string
    {
        return str_replace( ' ', '-', ucwords( str_replace( [
                                                                '-',
                                                                '_',
                                                            ], ' ', strtolower( $name ) ) ) );
    }

    /**
     * @param string $auth
     *
     * @return $this
     */
    public function setAuthorization( string $auth ): self
    {
        $this->setStatus( 401 )
             ->setHeader( 'Cache-Control', 'no-store, no-cache, must-revalidate' )
             ->setHeader( 'Pragma', 'no-cache' )
             ->setHeader( 'WWW-Authenticate', 'Basic realm="' . urlencode( $auth ) . '"' );

        return $this;
    }

    /**
     * @param string $token
     *
     * @return $this
     */
    public function setBearer( string $token ): self
    {
        return $this->setHeader( 'Authorization', 'Bearer ' . $token );
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType( string $type ): self
    {
        return $this->setHeader( 'Content-type', Http::extToContentType( $type ) );
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        if ( $contentType = $this->getHeader( 'Content-type' ) ) {
            return Http::contentTypeToExt( $contentType );
        }

        return null;
    }

    /**
     * @return string|null
     */
    public function getAuthorization(): ?string
    {
        if ( $auth = $this->getHeader( 'WWW-Authenticate' ) ) {
            return Str::matchOne( '/Basic realm="([^"]+)"/', $auth );
        }

        return null;
    }

    /**
     * @param string $url
     * @param int    $status
     *
     * @return $this
     */
    public function setLocation( string $url, int $status = 302 ): self
    {
        $this->setStatus( $status )
             ->setHeader( 'Location', $url );

        return $this;
    }

    /**
     * Autorise X-Frame-Options en fonction du referer.
     *
     * @param string $origin
     *
     * @return $this
     */
    public function setXFrameOptions( string $origin ): self
    {
        if ( $origin === '*' ) {
            return $this;
        }
        if ( isset( $_SERVER[ 'HTTP_REFERER' ] ) ) {
            $allow   = false;
            $uri     = new Url( $_SERVER[ 'HTTP_REFERER' ] );
            $origins = explode( ' ', $origin );

            foreach ( $origins as $item ) {
                if ( $item && substr( $uri->getHost(), -strlen( $item ) ) === $item ) {
                    $allow = true;
                    break;
                }
            }

            if ( $allow ) {
                $this->setHeader( 'X-Frame-Options', $uri->getScheme() . '://' . $uri->getHost() );

                return $this;
            }
        }
        $this->setHeader( 'X-Frame-Options', 'SAMEORIGIN' );

        return $this;
    }

    /**
     * @param DateTime $time
     *
     * @return $this
     */
    public function setDate( DateTime $time ): self
    {
        $this->setHeader( 'Date', $time->format( DateTime::RFC850 ) );

        return $this;
    }

    /**
     * Autorise toutes les origines d'appels (utile pour les cross ajax call).
     *
     * @param string      $origin
     * @param string|null $method
     * @param string|null $allow
     *
     * @return $this
     */
    public function setAllowAllOrigin( string $origin = '*', string $method = null, string $allow = null ): self
    {
        if ( isset( $_SERVER[ 'HTTP_ORIGIN' ] ) ) {
            $isAllow = false;
            $uri     = trim( $_SERVER[ 'HTTP_ORIGIN' ], '/' );
            $origins = explode( ' ', $origin );
            $method  ??= 'GET, POST, PUT, DELETE, OPTIONS';
            $allow   ??= 'Content-Type, Content-Range, Content-Disposition, Content-Description, ' . 'Accept, Access-Control-Allow-Headers, Authorization, X-Requested-With';

            foreach ( $origins as $item ) {
                if ( $item && substr( $uri, -strlen( $item ) ) === $item ) {
                    $isAllow = true;
                    break;
                }
            }

            if ( $isAllow || $origin === '*' ) {
                $this->setHeader( 'Access-Control-Allow-Origin', $_SERVER[ 'HTTP_ORIGIN' ] )
                     ->setHeader( 'X-Origin', $_SERVER[ 'HTTP_ORIGIN' ] )
                     ->setHeader( 'Access-Control-Allow-Methods', $method )
                     ->setHeader( 'Access-Control-Allow-Credentials', 'true' )
                     ->setHeader( 'Access-Control-Allow-Headers', $allow )
                     ->setHeader( 'Access-Control-Expose-Headers', 'Content-Disposition' )
                     ->setHeader( 'Vary', 'Origin' );
            }
        }

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->send();
    }

    /**
     * @return string
     */
    public function send(): string
    {
        return $this->getStatusAsString() . "\r\n" . $this->getHeadersAsString() . $this->getCookieAsString() . "\r\n";
    }

    /**
     * @return string
     */
    public function getStatusAsString(): string
    {
        return $this->rfc2616[ $this->status ];
    }

    /**
     * @return string
     */
    public function getHeadersAsString(): string
    {
        return $this->hasHeader()
            ? implode( "\r\n", $this->getHeaders() ) . "\r\n"
            : '';
    }

    /**
     * @return bool
     */
    public function hasHeader(): bool
    {
        return $this->header()
                    ->count() > 0;
    }

    /**
     * @return Json
     */
    public function header(): Json
    {
        return $this->header;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        $headers = [];

        foreach ( $this->header() as $name => $header ) {
            $headers[] = "$name: $header";
        }

        return $headers;
    }

    /**
     * @return string
     */
    public function getCookieAsString(): string
    {
        return $this->hasHeader()
            ? implode( "\r\n", $this->getHeaders() ) . "\r\n"
            : '';
    }

    /**
     * @return bool
     */
    public function hasCookie(): bool
    {
        return $this->cookie()
                    ->count() > 0;
    }

    /**
     * @return Json
     */
    public function cookie(): Json
    {
        return $this->cookie;
    }

    /**
     * @return array
     */
    public function getCookies(): array
    {
        $cookies = [];

        foreach ( $this->cookie() as $name => $cookie ) {
            $cookieHeader = 'Set-Cookie: ' . $name . '=' . rawurlencode( $cookie->offsetGet( 'value' ) );

            if ( $expires = $cookie->offsetGet( 'expires' ) ) {
                $expires      = gmdate( DATE_RFC850, $expires );
                $cookieHeader .= "; expires=$expires";
            }

            $cookieHeader .= '; path=' . $cookie->offsetGet( 'path', '/' );

            if ( $domain = $cookie->offsetGet( 'domain' ) ) {
                $cookieHeader .= '; domain=' . $domain;
            }

            $cookies[] = $cookieHeader;
        }

        return $cookies;
    }

    /**
     * @param string $name
     *
     * @return Json|null
     */
    public function getCookie( string $name ): ?Json
    {
        return $this->cookie->offsetGet( $name );
    }

    /**
     * Ajoute un cookie.
     *
     * @param string $name    nom du cookie
     * @param string $value   valeur associée
     * @param string $expires date d'expiration en timestamp
     * @param string $path    le chemin auquel s'applique le cookie '/' all par defaut
     * @param string $domain  le domaine auquel s'applique le cookie '.google.com' pour tout google
     *
     * @return $this
     */
    public function setCookie( string $name, string $value = null, string $expires = null, string $path = null, string $domain = null ): self
    {
        $this->cookie->offsetSet( $name, [
            'value'   => $value,
            'expires' => $expires,
            'path'    => $path,
            'domain'  => $domain,
        ] );

        return $this;
    }

    /**
     * @param $key
     *
     * @return bool
     */
    public function __isset( $key ): bool
    {
        return $this->header->offsetExists( $this->normalize( $key ) );
    }

    /**
     * @param $key
     *
     * @return string|null
     */
    public function __get( $key ): ?string
    {
        return $this->getHeader( $key );
    }

    /**
     * @param $key
     * @param $value
     *
     * @return $this
     */
    public function __set( $key, $value ): self
    {
        return $this->setHeader( $key, $value );
    }

    /**
     * @param $key
     */
    public function __unset( $key )
    {
        $this->header->offsetUnset( $key );
    }

    /**
     * @param       $name
     * @param array $params
     *
     * @return mixed
     */
    public function __call( $name, $params = [] )
    {
        $value  = (string) array_shift( $params );
        $match  = new Json( Str::match( '/^(set|get|unset)([a-z]+)/i', strtolower( $name ) ) );
        $action = $match->getIndex( 0 );
        $header = $match->getIndex( 1 );

        /** Entetes HTTP autorisés */
        $method = [
            'accept'             => 'Accept',
            'acceptcharset'      => 'Accept-Charset',
            'acceptencoding'     => 'Accept-Encoding',
            'acceptlanguage'     => 'Accept-Language',
            'acceptranges'       => 'Accept-Ranges',
            'authorization'      => 'Authorization',
            'cachecontrol'       => 'Cache-Control',
            'connection'         => 'Connection',
            'contentlength'      => 'Content-Length',
            'contenttype'        => 'Content-Type',
            'contentencoding'    => 'Content-Encoding',
            'contentdisposition' => 'Content-disposition',
            'date'               => 'Date',
            'except'             => 'Expect',
            'from'               => 'From',
            'host'               => 'Host',
            'ifmatch'            => 'If-Match',
            'ifmodifiedsince'    => 'If-Modified-Since',
            'ifnonematch'        => 'If-None-Match',
            'ifrange'            => 'If-Range',
            'ifunmodifiedsince'  => 'If-Unmodified-Since',
            'maxforwards'        => 'Max-Forwards',
            'pragma'             => 'Pragma',
            'proxyauthorization' => 'Proxy-Authorization',
            'range'              => 'Range',
            'referer'            => 'Referer',
            'useragent'          => 'User-Agent',
            'age'                => 'Age',
            'allow'              => 'Allow',
            'contentmd5'         => 'Content-MD5',
            'contentrange'       => 'Content-Range',
            'etag'               => 'ETag',
            'expires'            => 'Expires',
            'lastmodified'       => 'Last-Modified',
            'location'           => 'Location',
            'retryafter'         => 'Retry-After',
            'server'             => 'Server',
            'transferencoding'   => 'Transfer-Encoding',
            'vary'               => 'Vary',
            'wwwauthenticate'    => 'WWW-Authenticate',
        ];

        /** Entetes utilisant des dates au format RFC850 */
        $date = [
            'date',
            'ifmodifiedsince',
            'ifunmodifiedsince',
            'expires',
            'lastmodified',
        ];

        /** La methode existe */
        if ( isset( $method[ $header ] ) ) {
            $key = $method[ $header ];

            /** Gestion des timestamp */
            if ( Arr::in( $header, $date ) ) {
                switch ( $action ) {
                    case 'set':
                        return $this->setHeader( $key, gmdate( DATE_RFC850, (int) $value ) );
                    case 'get':
                        if ( $h = $this->getHeader( $key ) ) {
                            $d = date_parse( $h );

                            return date( 'U', mktime( $d[ 'hour' ], $d[ 'minute' ], $d[ 'second' ], $d[ 'month' ], $d[ 'day' ], $d[ 'year' ] ) );
                        }

                        return null;
                    case 'unset':
                        return $this->unsetHeader( $key );
                }
            }

            /** Gestion des methodes Set || Get */
            else {
                switch ( $action ) {
                    case 'set':
                        return $this->setHeader( $key, $value );
                    case 'get':
                        return $this->getHeader( $key );
                    case 'unset':
                        return $this->unsetHeader( $key );
                }
            }
        }

        throw new HttpException( sprintf( "Method \Chukdo\Http\Header::%s doesn't exists", $name ) );
    }

    /**
     * @param string $name
     *
     * @return string|null
     */
    public function unsetHeader( string $name ): ?string
    {
        return $this->header->offsetUnset( $this->normalize( $name ) );
    }
}
