<?php

error_reporting(E_ALL);
set_error_handler('triggerError');
set_exception_handler('triggerException');
register_shutdown_function('triggerErrorShutdown');

function triggerError($e, $message, $file = __FILE__, $line = __LINE__) { echo("<pre>ERROR: $message on file $file at line $line</pre>");echo \Chukdo\Helper\Convert::toHtml(debug_backtrace());exit;}
function triggerErrorShutdown() { if ($error = error_get_last()) { triggerError($error['type'],$error['message'], $error['file'], $error['line']);}}
function triggerException($e) { triggerError($e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine());}

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
Facade::setClassAlias('\Chukdo\Facades\ServiceLocator', 'ServiceLocator');
Facade::setClassAlias('\Chukdo\Helper\Stream', 'Stream');

/** Configuration */
Conf::loadConf(CONF_PATH.'conf.json');
Conf::loadConf(CONF_PATH.'conf_prod.json');

App::env(App::getConf('env'));
App::channel('orpi');
App::register('\App\Providers\ExceptionLoggerServiceProvider');

ExceptionLogger::emergency('trop cool enfin ca marche... OK');

echo \Chukdo\Helper\Convert::toHtml(App::getConf('env'));

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