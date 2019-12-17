<?php

namespace App\Providers;

use Chukdo\Bootstrap\ServiceProvider;
use Chukdo\Helper\Stream;
use Chukdo\Storage\ServiceLocator;
use Chukdo\Storage\Wrappers\AzureStream;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;

class ServiceLocatorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $serviceLocator = ServiceLocator::getInstance();
        $this->app->instance( ServiceLocator::class, $serviceLocator );
        Stream::register( 'azure', AzureStream::class );
        $serviceLocator->setService( 'azure', fn() => BlobRestProxy::createBlobService( $this->app->conf( 'storage.azure.endpoint' ) ) );
    }
}
