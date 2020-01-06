<?php

namespace Chukdo\Oauth2\Token;

use Chukdo\Http\Curl;
use Chukdo\Json\Json;

/**
 * Oauth2 token
 *
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class DropboxToken extends AbstractToken
{
    /**
     * @var Curl
     */
    protected Curl $curl;

    /**
     * GenericToken constructor.
     *
     * @param Json $json
     */
    public function __construct( Json $json )
    {
        $this->accessToken  = $json->offsetGet( 'access_token' );
        $this->refreshToken = $this->accessToken;
        $this->expire       = time() + time();
        $this->values       = new Json();
    }

    /**
     * @param string $path
     * @param array  $params
     * @param string $method
     *
     * @return mixed
     */
    public function Api( string $path, array $params = [], string $method = 'POST' )
    {
        $url  = 'https://api.dropboxapi.com/2/';
        $curl = new Curl( 'POST' );

        return $curl->setBearer( $this->accessToken )
                    ->setUrl( $url . trim( $path, '/' ) )
                    ->setHeader( 'Content-Type', 'Application/json' )
                    ->setInputs( $params )
                    ->content();
    }
}