<?php

/** Definition des chemins */
DEFINE('CONF_PATH', '/storage/www/chukdo/test/app/conf/');
DEFINE('APP_PATH', '/storage/www/chukdo/test/app/');
DEFINE('CHUKDO_PATH', '/storage/www/chukdo/src/');
DEFINE('VENDOR_PATH', '/storage/www/chukdo/vendor/');

/** Inclusion des loaders */
require_once(CHUKDO_PATH.'Bootstrap/Loader.php');
require_once(VENDOR_PATH.'autoload.php');

/** boostrap framework */
$loader = new Chukdo\Bootstrap\loader();
$loader->registerNameSpace('\Chukdo', CHUKDO_PATH);
$loader->registerNameSpace('\App', APP_PATH);
$loader->register();

$app = new \Chukdo\Bootstrap\App();

/** Declaration des facades */
Use \Chukdo\Facades\Facade;

Facade::setFacadeApplication($app);
Facade::setClassAlias(\Chukdo\Facades\Facade::class, 'Facade');
Facade::setClassAlias(\Chukdo\Facades\App::class, 'App');
Facade::setClassAlias(\Chukdo\Facades\Event::class, 'Event');
Facade::setClassAlias(\Chukdo\Facades\Response::class, 'Response');
Facade::setClassAlias(\Chukdo\Facades\Conf::class, 'Conf');
Facade::setClassAlias(\Chukdo\Facades\ServiceLocator::class, 'ServiceLocator');
Facade::setClassAlias(\Chukdo\Helper\Stream::class, 'Stream');

/** Configuration */
Conf::loadConf(CONF_PATH.'conf.json');
//Conf::loadConf(CONF_PATH.'conf_prod.json');

/** App */

App::env(App::getConf('env'));
App::channel('orpi');
App::register(\App\Providers\LoggerHandlerServiceProvider::class);
App::register(\App\Providers\ExceptionLoggerServiceProvider::class);
App::registerHandleExceptions();

/** Service locator */
App::setAlias(\Chukdo\Storage\ServiceLocator::class, 'ServiceLocator');
App::instance('ServiceLocator', \Chukdo\Storage\ServiceLocator::getInstance());

/** Declaration de flux */
Stream::register('azure', \Chukdo\Storage\Wrappers\AzureStream::class);
ServiceLocator::setService('azure',
    function () {
        return MicrosoftAzure\Storage\Blob\BlobRestProxy::createBlobService(Conf::get('storage.azure.endpoint'));
    }
);

$json = new \Chukdo\Json\Json([
    'a' => '08/01/75',
    'q' => 'qsdfghjklm'
]);
r($json->is('date', 'a', 'd/m/Y'));

//ExceptionLogger::emergency('coucou les loulous');
//Response::file('azure://files-dev/566170fe8bc5d2cf3d000000/5948da9a28b8b.pdf')->send()->end();


Response::json($json)->send()->end();

Class test2 {
    public function render()
    {
        throw new \Chukdo\Bootstrap\AppException('au lit les én$#@fants');
    }
}

Class test {
    public function test()
    {
        $t2 = new test2();
        $t2->render();
    }
}

$test = new test();
$test->test();



exit;
