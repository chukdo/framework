<?php

namespace App\Controlers;

use Chukdo\Helper\To;
use Chukdo\Http\Controler;
use Chukdo\Http\Response;
use Chukdo\Http\Input;
use Chukdo\Oauth2\Provider\GenericProvider;

class Oauth2 extends Controler
{
    /**
     * @var GenericProvider
     */
    protected GenericProvider $client;

    /**
     * Oauth2 constructor.
     */
    public function __construct()
    {
        $this->client = new GenericProvider();
        $this->client->setUrlAuthorize( 'https://www.dropbox.com/oauth2/authorize' )
                     ->setUrlAccessToken( 'https://api.dropboxapi.com/oauth2/token' )
                     ->setClientId( '1gnu9jmet15ofyp' )
                     ->setClientSecret( 'rngrh6odd07b3t3' )
                     ->setRedirectUri( 'https://0452c7ee.ngrok.io/oauth2/callback/' );
    }

    /**
     * @param Input    $inputs
     * @param Response $response
     *
     * @return Response
     */
    public function authorize( Input $inputs, Response $response ): Response
    {
        $response->redirect( $this->client->getAuthorizationUrl() );
    }

    /**
     * @param Input    $inputs
     * @param Response $response
     *
     * @return Response
     */
    public function callback( Input $inputs, Response $response ): Response
    {
        if ( !$this->client->checkState( $inputs->state ) ) {
            return $response->status( 500 )
                ->content( 'state error' );
        }

        $token = $this->client->getToken( 'authorization_code', [ 'authorization_code' => $inputs->code ] );

        return $response->content( (string) $token->getToken() . To::html( $token->values() ) );
    }
}