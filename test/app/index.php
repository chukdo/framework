<?php

/** Gestion basique des erreurs */
error_reporting(E_ALL);
set_error_handler('triggerError');
set_exception_handler('triggerException');
register_shutdown_function('triggerErrorShutdown');

function triggerError($e, $message, $file = __FILE__, $line = __LINE__) { echo("<pre>ERROR: $message on file $file at line $line</pre>");app::printr(debug_backtrace());exit;}
function triggerErrorShutdown() { if ($error = error_get_last()) { triggerError($error['type'],$error['message'], $error['file'], $error['line']);}}
function triggerException($e) { triggerError($e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine());}

/** Definition des chemins */
DEFINE('CONF_PATH', '/storage/www/chukdo/test/conf/');
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

/** Declaration des facades */
Use \Chukdo\Facades\Facade;

Facade::setFacadeApplication(new \Chukdo\Bootstrap\App());
Facade::setClassAlias('\Chukdo\Facades\Facade', 'Facade');
Facade::setClassAlias('\Chukdo\Facades\App', 'App');
Facade::setClassAlias('\Chukdo\Facades\Event', 'Event');
Facade::setClassAlias('\Chukdo\Facades\Conf', 'Conf');
Facade::setClassAlias('\Chukdo\Facades\ServiceLocator', 'ServiceLocator');
Facade::setClassAlias('\Chukdo\Helper\Stream', 'Stream');

/** Configuration */
Conf::loadConf(CONF_PATH.'conf.json');

/** Service locator */
App::setAlias('\Chukdo\Storage\ServiceLocator', 'ServiceLocator');
App::instance('ServiceLocator', \Chukdo\Storage\ServiceLocator::getInstance());

/** Declaration de flux */
Stream::register('azure', '\Chukdo\Storage\Wrappers\AzureStream');
ServiceLocator::setService('azure',
    function () {
        return MicrosoftAzure\Storage\Blob\BlobRestProxy::createBlobService(Conf::get('/storage/azure/endpoint'));
    }
);

Stream::register('redis', '\Chukdo\Storage\Wrappers\RedisStream');
ServiceLocator::setService('redis',
    function () {
        return new \Chukdo\Db\Redis();
    }
);

App::printr(Conf::get('/storage/azure/endpoint'));