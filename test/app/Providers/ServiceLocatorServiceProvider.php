<?php namespace App\Providers;

Use \Chukdo\Bootstrap\ServiceProvider;
Use \Chukdo\Storage\ServiceLocator;
Use \Chukdo\Storage\Wrappers\AzureStream;
Use \Chukdo\Helper\Stream;
Use MicrosoftAzure\Storage\Blob\BlobRestProxy;

class ServiceLocatorServiceProvider extends ServiceProvider {

    /**
     * @return void
     */
    public function register(): void
    {
        $serviceLocator = ServiceLocator::getInstance();
        $this->app->instance('\Chukdo\Storage\ServiceLocator', $serviceLocator);
        Stream::register('azure', AzureStream::class);
        $serviceLocator->setService('azure', function () {
                return BlobRestProxy::createBlobService($this->app->getConf('storage.azure.endpoint'));
            }
        );
    }
}
