<?php

namespace App\Controlers;

use Chukdo\Http\Controler;
use Chukdo\Http\RequestApi;
use Chukdo\Http\Response;
use Chukdo\Http\Input;
use Chukdo\Contracts\Oauth2\Provider as ProviderInterface;
use Chukdo\Oauth2\Provider\DropboxProvider;
use Chukdo\Oauth2\Provider\GenericProvider;

class Oauth2 extends Controler
{
    /**
     * @var ProviderInterface
     */
    protected ProviderInterface $client;

    /**
     * Oauth2 constructor.
     */
    public function __construct()
    {
        $this->client = new GenericProvider();
        $this->client->setClientId( '1gnu9jmet15ofyp' )
                     ->setClientSecret( 'rngrh6odd07b3t3' )
                     ->setUrlAuthorize( 'https://www.dropbox.com/oauth2/authorize' )
                     ->setUrlAccessToken( 'https://api.dropboxapi.com/oauth2/token' )
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
        $api   = new RequestApi( 'POST', 'https://api.dropboxapi.com/2/file_requests/list_v2' );
        $res   = $api->setBearer( $token->getToken() )
                     ->setType( 'json' )
                     ->setInput( 'limit', 3 )
                     ->send();

        return $response->content( $res->raw() );
    }
}