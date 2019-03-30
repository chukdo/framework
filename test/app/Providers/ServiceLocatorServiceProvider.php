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
        $this->app->instance('\Chukdo\Storage\ServiceLocator', $serviceLocator);
        Stream::register('azure', AzureStream::class);
        $serviceLocator->setService('azure', function () {
            return BlobRestProxy::createBlobService($this->app->getConf('storage.azure.endpoint'));
        });
    }
}
