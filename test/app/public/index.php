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
Facade::setClassAlias('\Chukdo\Facades\Facade', 'Facade');
Facade::setClassAlias('\Chukdo\Facades\App', 'App');
Facade::setClassAlias('\Chukdo\Facades\Event', 'Event');
Facade::setClassAlias('\Chukdo\Facades\Conf', 'Conf');
Facade::setClassAlias('\Chukdo\Facades\Console', 'Console');
Facade::setClassAlias('\Chukdo\Facades\ServiceLocator', 'ServiceLocator');
Facade::setClassAlias('\Chukdo\Helper\Stream', 'Stream');

/** Configuration */
Conf::loadConf(CONF_PATH.'conf.json');
Conf::loadConf(CONF_PATH.'conf_prod.json');

/** App */
App::env(App::getConf('env'));
App::channel('orpi');
App::register('\App\Providers\LoggerHandlerServiceProvider');
App::register('\App\Providers\ExceptionLoggerServiceProvider');
App::registerHandleExceptions();

//r($app);

echo '<pre>';
Console::setHeaders(array('Language', 70 => 'Year'))
    ->addRow(["toto titi est gros minet", ''])
    ->setIndent(4)
    ->flush();

//ExceptionLogger::emergency('allo ?');
throw new Exception('au lit les enfants');
echo \Chukdo\Helper\Convert::toHtml(App::getConf('env'));

// console
// http_reponse

// exception handler

exit;

/** Service locator */
App::setAlias('\Chukdo\Storage\ServiceLocator', 'ServiceLocator');
App::instance('ServiceLocator', \Chukdo\Storage\ServiceLocator::getInstance());

/** Declaration de flux */
Stream::register('azure', '\Chukdo\Storage\Wrappers\AzureStream');
ServiceLocator::setService('azure',
    function () {
        return MicrosoftAzure\Storage\Blob\BlobRestProxy::createBlobService(Conf::get('storage/azure/endpoint'));
    }
);

echo \Chukdo\Helper\Convert::toHtml(Conf::get('storage/azure/endpoint'));