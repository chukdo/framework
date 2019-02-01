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

/** Resolveur */
App::resolvingAny(function($o, $n) {
    echo '<h1>+ Resolving '.$n.'</h1>';
    App::printr($o);
});

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

App::printr(Conf::get('/storage/azure/endpoint'));

if ($handle = opendir('azure://files-dev/5a1c21cd6f527501062e77f2/')) {
    echo "Gestionnaire du dossier azure : $handle<br/>";
    echo "Entrées :<br/>";

    while (false !== ($entry = readdir($handle))) {
        echo $entry . "<br/>";
        App::printr(stat('azure://files-dev/5a1c21cd6f527501062e77f2/' . $entry));
    }

    closedir($handle);
}


Stream::register('redis', '\Chukdo\Storage\Wrappers\RedisStream');
ServiceLocator::setService('redis',
    function () {
        return new \Chukdo\Db\Redis();
    }
);

app::printr(parse_url('redis://1/test/ici'));

//mkdir("redis://1/test/ici/", true);

$fp = fopen("redis://1/test/ici/test.json", 'w');

fwrite($fp, 'toto;');
fwrite($fp, 'tata;');
fwrite($fp, 'tutu;');
fwrite($fp, 'titi;');
fclose($fp);
app::printr(stat("redis://1/test/ici/test.json"));
app::printr(file_get_contents("redis://1/test/ici/test.json"));
app::printr(rename("redis://1/test/ici/test.json", "redis://1/test/ici/testnew.json"));
app::printr(file_get_contents("redis://1/test/ici/testnew.json"));
app::printr(stat("redis://1/test/ici/testnew.json"));
app::printr(file_exists("redis://1/test/ici/testnew.json"));
app::printr(stat("redis://1/test/ici/"));
app::printr(is_file("redis://1/test/ici/testnew.jsonsdfds"));// renvoi toujours false



echo app::printr(is_dir("redis://test/ici"));

//mkdir("redis://0/test/ici/maison/", true);

file_put_contents("redis://0/test/ici/a.txt", 'a');
file_put_contents("redis://0/test/la/b.txt", 'b');
file_put_contents("redis://0/test/ici/c.txt", 'c');
file_put_contents("redis://0/test/ici/la/d.txt", 'd');
file_put_contents("redis://0/test/la/e.txt", 'e');
file_put_contents("redis://0/test/la/fifi.txt", 'f');
file_put_contents("redis://0/test/ici/fifi.txt", 'TITITITIITIT');

app::printr(file_get_contents("redis://0/test/ici/fifi.txt"));
unlink("redis://0/test/ici/fifi.txt");

if ($handle = opendir('redis://0/test/ici')) {
    echo "Gestionnaire du dossier : $handle<br/>";
    echo "Entrées :<br/>";

    /* Ceci est la façon correcte de traverser un dossier. */
    while (false !== ($entry = readdir($handle))) {
        echo $entry . "<br/>";
        //echo "$entry".(is_dir('redis://'.$entry) ? ":1" : ":0")."<br/>";
    }

    closedir($handle);
}


app::printr(Conf::toArray());
/**
$confJsonPath = '/storage/www/modelo/conf_json';

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($confXmlPath), RecursiveIteratorIterator::SELF_FIRST);

foreach ($iterator as $file) {
if (substr($file, -3, 3) == 'xml') {
$newFile    = $confJsonPath . substr(str_replace($confXmlPath, '', $file), 0, -4) . '.json';
$newDir     = dirname($newFile);

if (!is_dir($newDir)) {
mkdir($newDir, 0777, true);
}

$xml = \Chukdo\Xml\Xml::loadFromFile($file);
file_put_contents($newFile ,$xml->toJson());
}
}
 */
app::setAlias('Ao', '\Chukdo\Json\Json');
$x = app::make('Ao')->offsetSet('toto', 'titi');
app::printr($x);

$y = app::make('\Chukdo\Json\Json')->offsetSet('tata', 'tutu');
app::printr($y);

$z = app::make('\Chukdo\Json\Json')->offsetSet('tata', 'tutu');
app::printr($z);

Event::listen('test', function($c) {
    echo 'test: '.$c;
});

Event::flush('test');

Event::listen('test', function($c) {
    echo 'test2: '.$c;
});

Event::fire('test', 'coucou2');

echo Chukdo\Helper\Data::password(89);

app::printr([1,2,3]);


