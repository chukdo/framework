<?php

/** Bootsrap ERROR */
error_reporting(E_ALL);
set_error_handler('triggerError');
set_exception_handler('triggerException');
register_shutdown_function('triggerErrorShutdown');

function dd($data) {echo('<pre>');var_dump($data);exit;}
function triggerException($e) {dd($e);}
function triggerError($code, $message, $file = __FILE__, $line = __LINE__, $context) {throw new ErrorException($message,$code, 1, $file, $line, $context);}
function triggerErrorShutdown() { if ($error = error_get_last()) { triggerError($error['type'],$error['message'], $error['file'], $error['line'], null);}}

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
Facade::setClassAlias(\Chukdo\Facades\Request::class, 'Request');
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
    'a' => [
        'b' => [
            'c' => 'okc',
            'd' => 'okd'
        ],
        'c' => [
            'd' => 'oke',
            'f' => 'okf'
        ]
    ],
    'b' => 'qsdfghjklm'
]);

dd($json->get('a.*.d'));
exit;
//ExceptionLogger::emergency('coucou les loulous');
//Response::file('azure://files-dev/566170fe8bc5d2cf3d000000/5948da9a28b8b.pdf')->send()->end();
Response::json($json)->send()->end();
