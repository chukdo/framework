<?php

error_reporting(E_ALL);
set_error_handler('triggerError');
set_exception_handler('triggerException');
register_shutdown_function('triggerErrorShutdown');

function triggerError($e, $message, $file = __FILE__, $line = __LINE__) { echo("<pre>ERROR: $message on file $file at line $line</pre>");echo \Chukdo\Helper\Convert::toHtml((new \Chukdo\Json\Json(debug_backtrace()))->toFlatJson());exit;}
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
Facade::setClassAlias('\Chukdo\Facades\Console', 'Console');
Facade::setClassAlias('\Chukdo\Facades\ServiceLocator', 'ServiceLocator');
Facade::setClassAlias('\Chukdo\Helper\Stream', 'Stream');

/** Configuration */
Conf::loadConf(CONF_PATH.'conf.json');
Conf::loadConf(CONF_PATH.'conf_prod.json');

App::env(App::getConf('env'));
App::channel('orpi');
App::register('\App\Providers\ExceptionLoggerServiceProvider');

ExceptionLogger::debug('trop cool enfin ca marche... OK');
echo '<pre>';
Console::setHeaders(array('Language', 70 => 'Year'))
    ->setIndent(4)
    ->display();

Console::addRow()
    ->addColumn('PHP')
    ->addColumn(Console::background('1994QZSDFGHHJGFDSQSDFGHHJGFDS', 'red'))
    ->addRow()
    ->addColumn('C++')
    ->addColumn(Console::color(1983, 'blue'))
    ->addRow()
    ->addColumn('C')
    ->addColumn(1970)
    ->flush();

Console::addRow()
    ->addColumn('PHP')
    ->addColumn('1994')
    ->addRow()
    ->addColumn('C++')
    ->addColumn(1983)
    ->addRow()
    ->addColumn('C')
    ->addColumn(1970)
    ->setIndent(4)
    ->flushAll();

Console::setHeaders(array('Language', 10 => 'Year'))
    ->addRow(array('PHP', 1994))
    ->addRow(array('C++', 1983))
    ->addRow(array('C', 1970))
    ->setPadding(8)
    ->showAllBorders()
    ->flushAll();

Console::addRow('PHP')
    ->addRow('C++')
    ->addRow('C - CF')
    ->hideBorder()
    ->flush();
echo '</pre>';
exit;
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