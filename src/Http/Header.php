<?php

namespace Chukdo\Http;

use Chukdo\Helper\Str;
use Chukdo\Json\Json;

/**
 * Gestion des entetes HTTP.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Header
{
    /**
     * Status RFC 2616.
     * @param array $status
     */
    public $rfc2616 = [
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
        400 => 'HTTP/1.1 400 Bad Request',
        401 => 'HTTP/1.1 401 Unauthorized',
        402 => 'HTTP/1.1 402 Payment Required',
        403 => 'HTTP/1.1 403 Forbidden',
        404 => 'HTTP/1.1 404 Not Found',
        405 => 'HTTP/1.1 405 Method Not Allowed',
        406 => 'HTTP/1.1 406 Not Acceptable',
        407 => 'HTTP/1.1 407 Proxy Authentication Required',
        408 => 'HTTP/1.1 408 Request Time-out',
        409 => 'HTTP/1.1 409 Conflict',
        410 => 'HTTP/1.1 410 Gone',
        411 => 'HTTP/1.1 411 Length Required',
        412 => 'HTTP/1.1 412 Precondition Failed',
        413 => 'HTTP/1.1 413 Request Entity Too Large',
        414 => 'HTTP/1.1 414 Request-URI Too Large',
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
     * Entete HTTP.
     * @param string $http
     */
    protected $http = '';
    /**
     * Entetes.
     * @param Json $header
     */
    protected $header;
    /**
     * Cookie.
     * @param Json $cookie
     */
    protected $cookie;

    /**
     * Header constructor.
     */
    public function __construct()
    {
        $this->header = new Json();
        $this->cookie = new Json();
    }

    /**
     * @return int|null
     */
    public function getStatus(): ?int
    {
        return Str::match('/([0-9]{3})/',
            $this->getHttp());
    }

    /**
     * @return string
     */
    public function getHttp(): string
    {
        return $this->http;
    }

    /**
     * Defini l'entete HTTP
     * Ex. POST /test_rest_modele.php HTTP/1.1 pour un requete HTTP
     * Ex. HTTP/1.1 200 OK pour une reponse HTTP.
     * @param string $value
     * @return Header
     */
    public function setHttp( string $value ): self
    {
        $this->http = $value;

        return $this;
    }

    /**
     * @param iterable $headers
     * @return Header
     */
    public function setHeaders( iterable $headers ): self
    {
        foreach ( $headers as $k => $v ) {
            $this->setHeader($k,
                $v);
        }

        return $this;
    }

    /**
     * @return Header
     */
    public function unsetHeaders(): self
    {
        $this->header = new Json();

        return $this;
    }

    /**
     * @param iterable $cookies
     * @return Header
     */
    public function setCookies( iterable $cookies ): self
    {
        foreach ( $cookies as $k => $v ) {
            $this->setCookie($v[ 'name' ],
                $v[ 'value' ],
                $v[ 'expires' ],
                $v[ 'path' ],
                $v[ 'domain' ]);
        }

        return $this;
    }

    /**
     * @param string $name
     * @return string|null
     */
    public function unsetCookie( string $name ): ?string
    {
        return $this->cookie->offsetUnset($name);
    }

    /**
     * @return Header
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
     * @return Header
     */
    public function setCacheControl( int $max = 3600, bool $revalidate = false, string $control = null ): self
    {
        $cache = [];

        if ( $control !== false ) {
            $cache[] = $control;
        }

        if ( $max !== false ) {
            $cache[] = "max-age=$max";
        }

        if ( $revalidate !== false ) {
            $cache[] = 'must-revalidate';
            $cache[] = 'proxy-revalidate';
        }

        $this->setHeader('Cache-Control',
            implode(', ',
                $cache));

        return $this;
    }

    /**
     * @return Json
     */
    public function getCacheControl(): Json
    {
        $cache = new Json([
            'max'        => 0,
            'revalidate' => false,
            'control'    => null,
        ]);

        foreach ( explode(', ',
            $this->getHeader('Cache-Control')) as $value ) {
            if ( $value == 'must-revalidate' ) {
                $cache->offsetSet('revalidate',
                    true);
            }
            elseif ( ( $age = Str::match('/max-age=([0-9]+)/',
                    $value) ) !== false ) {
                $cache->offsetSet('max',
                    $age);
            }
            else {
                $cache->offsetSet('control',
                    $value);
            }
        }

        return $cache;
    }

    /**
     * @param string $name
     * @return string|null
     */
    public function getHeader( string $name ): ?string
    {
        return $this->header->offsetGet($this->normalize($name));
    }

    /**
     * @param string $name
     * @param string $value
     * @return Header
     */
    public function setHeader( string $name, string $value ): self
    {
        $this->header->offsetSet($this->normalize($name),
            trim($value));

        return $this;
    }

    /**
     * @param string $name
     * @return string
     */
    public function normalize( string $name ): string
    {
        return str_replace(' ',
            '-',
            ucwords(str_replace([
                '-',
                '_',
            ],
                ' ',
                strtolower($name))));
    }

    /**
     * @param string $auth
     * @return Header
     */
    public function setAuthorization( string $auth ): self
    {
        $this->setStatus(401)
            ->setHeader('Cache-Control',
                'no-store, no-cache, must-revalidate')
            ->setHeader('Pragma',
                'no-cache')
            ->setHeader('WWW-Authenticate',
                'Basic realm="' . urlencode($auth) . '"');

        return $this;
    }

    /**
     * @param int $status
     * @return Header
     */
    public function setStatus( int $status ): self
    {
        if ( isset($this->rfc2616[ $status ]) ) {
            $this->setHttp($this->rfc2616[ $status ]);
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAuthorization(): ?string
    {
        if ( ( $auth = $this->getHeader('WWW-Authenticate') ) !== false ) {
            return Str::match('/Basic realm="([^"]+)"/',
                $auth);
        }

        return null;
    }

    /**
     * @param string $url
     * @param int    $status
     * @return Header
     */
    public function setLocation( string $url, int $status = 302 ): self
    {
        $this->setStatus($status)
            ->setHeader('Location',
                $url);

        return $this;
    }

    /**
     * Autorise X-Frame-Options en fonction du referer.
     * @param string|null $origin
     * @return Header
     */
    public function setXFrameOptions( string $origin = null ): self
    {
        if ( $origin == '*' ) {
            return $this;
        }

        if ( isset($_SERVER[ 'HTTP_REFERER' ]) ) {
            $allow   = false;
            $uri     = new Url($_SERVER[ 'HTTP_REFERER' ]);
            $origins = explode(' ',
                $origin);

            foreach ( $origins as $origin ) {
                if ( $origin
                     && substr($uri->getHost(),
                        -strlen($origin)) == $origin ) {
                    $allow = true;
                    break;
                }
            }

            if ( $allow ) {
                $this->setHeader('X-Frame-Options',
                    $uri->getScheme() . '://' . $uri->getHost());

                return $this;
            }
        }

        $this->setHeader('X-Frame-Options',
            'SAMEORIGIN');

        return $this;
    }

    /**
     * Autorise toutes les origines d'appels (utile pour les cross ajax call).
     * @param string|null $origin
     * @param string|null $method
     * @param string|null $allow
     * @return Header
     */
    public function setAllowAllOrigin( string $origin = null, string $method = null, string $allow = null ): self
    {
        if ( isset($_SERVER[ 'HTTP_ORIGIN' ]) ) {
            $uri     = trim($_SERVER[ 'HTTP_ORIGIN' ],
                '/');
            $origins = explode(' ',
                $origin);
            $method  = $method
                ?: 'GET, POST, PUT, DELETE, OPTIONS';
            $allow   = $allow
                ?: 'Content-Type, Content-Range, Content-Disposition, Content-Description, '
                   . 'Accept, Access-Control-Allow-Headers, Authorization, X-Requested-With';

            foreach ( $origins as $origin ) {
                if ( $origin
                     && substr($uri,
                        -strlen($origin)) == $origin ) {
                    $allow = true;
                    break;
                }
            }

            if ( $allow || $origin == '*' ) {
                $this->setHeader('Access-Control-Allow-Origin',
                    $_SERVER[ 'HTTP_ORIGIN' ])
                    ->setHeader('X-Origin',
                        $_SERVER[ 'HTTP_ORIGIN' ])
                    ->setHeader('Access-Control-Allow-Methods',
                        $method)
                    ->setHeader('Access-Control-Allow-Credentials',
                        'true')
                    ->setHeader('Access-Control-Allow-Headers',
                        $allow)
                    ->setHeader('Access-Control-Expose-Headers',
                        'Content-Disposition')
                    ->setHeader('Vary',
                        'Origin');
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
        $http    = $this->getHttp() . "\r\n";
        $headers = '';
        $cookies = '';

        foreach ( $this->getHeaders() as $name => $header ) {
            $headers .= "$name: $header\r\n";
        }

        foreach ( $this->getCookies() as $name => $cookie ) {
            $cookies .= $cookie . "\r\n";
        }

        return $http . $headers . $cookies . "\r\n";
    }

    /**
     * @return Json
     */
    public function getHeaders(): Json
    {
        return $this->header;
    }

    /**
     * @return Json
     */
    public function getCookies(): Json
    {
        $cookies = new Json();

        foreach ( $this->cookie as $name => $cookie ) {
            $cookies->append($this->getCookie($name));
        }

        return $cookies;
    }

    /**
     * @param string $name
     * @return Json|null
     */
    public function getCookie( string $name ): ?Json
    {
        if ( $cookie = $this->cookie->offsetGet($name) ) {
            return Str::match('/([^=]+)=([^;]+)(?:; expires=([^;]+))?(?:; path=([^;]+))?(?:; domain=([^;]+))?/i',
                $cookie);
        }

        return null;
    }

    /**
     * Ajoute un cookie.
     * @param string $name    nom du cookie
     * @param string $value   valeur associée
     * @param string $expires date d'expiration en timestamp
     * @param string $path    le chemin auquel s'applique le cookie '/' all par defaut
     * @param string $domain  le domaine auquel s'applique le cookie '.google.com' pour tout google
     * @return Header
     */
    public function setCookie( string $name, string $value = null, string $expires = null, string $path = null, string $domain = null ): self
    {
        $value  = rawurlencode($value);
        $cookie = 'Set-Cookie: ' . $name . '=' . $value;

        if ( $expires ) {
            $expires = gmdate(DATE_RFC850,
                $expires);
            $cookie  .= "; expires=$expires";
        }

        $cookie .= '; path=' . ( $path
                ?: '/' );

        if ( $domain ) {
            $cookie .= '; domain=' . $domain;
        }

        $this->cookie->offsetSet($name,
            $cookie);

        return $this;
    }

    /**
     * @param $key
     * @return bool
     */
    public function __isset( $key )
    {
        return $this->header->offsetExists($this->normalize($key));
    }

    /**
     * @param $key
     * @return string
     */
    public function __get( $key )
    {
        return $this->getHeader($key);
    }

    /**
     * @param $key
     * @param $value
     * @return Header
     */
    public function __set( $key, $value )
    {
        return $this->setHeader($key,
            $value);
    }

    /**
     * @param $key
     * @return bool
     */
    public function __unset( $key )
    {
        return (bool) $this->header->offsetUnset($key);
    }

    /**
     * @param       $name
     * @param array $params
     * @return Header|false|string|null
     */
    public function __call( $name, $params = [] )
    {
        $value  = array_shift($params);
        $match  = new Json(Str::match('/^(set|get|unset)([a-z]+)/i',
            strtolower($name)));
        $action = $match->get(0);
        $header = $match->get(1);

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
            // Date RFC850
            'except'             => 'Expect',
            'from'               => 'From',
            'host'               => 'Host',
            'ifmatch'            => 'If-Match',
            'ifmodifiedsince'    => 'If-Modified-Since',
            // Date RFC850
            'ifnonematch'        => 'If-None-Match',
            'ifrange'            => 'If-Range',
            'ifunmodifiedsince'  => 'If-Unmodified-Since',
            // Date RFC850
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
            // Date RFC850
            'lastmodified'       => 'Last-Modified',
            // Date RFC850
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

        /* La methode existe */
        if ( isset($method[ $header ]) ) {
            $key = $method[ $header ];

            /* Gestion des timestamp */
            if ( in_array($header,
                $date) ) {
                switch ( $action ) {
                    case 'set':
                        return $this->setHeader($key,
                            gmdate(DATE_RFC850,
                                $value));
                    case 'get':
                        $d = date_parse($this->getHeader($key));

                        return date('U',
                            mktime($d[ 'hour' ],
                                $d[ 'minute' ],
                                $d[ 'second' ],
                                $d[ 'month' ],
                                $d[ 'day' ],
                                $d[ 'year' ]));
                    case 'unset':
                        return $this->unsetHeader($key);
                }

                /* Gestion des methodes Set || Get */
            }
            else {
                switch ( $action ) {
                    case 'set':
                        return $this->setHeader($key,
                            $value);
                    case 'get':
                        return $this->getHeader($key);
                    case 'unset':
                        return $this->unsetHeader($key);
                }
            }
        }

        throw new HttpException(sprintf("Method \Chukdo\Http\Header::%s doesn't exists", $name));
    }

    /**
     * @param string $name
     * @return string|null
     */
    public function unsetHeader( string $name ): ?string
    {
        return $this->header->offsetUnset($this->normalize($name));
    }
}
