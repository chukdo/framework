<?php

/** Namespaces */
use \Chukdo\Facades\Response;
use \Chukdo\Facades\View;

$app = require_once __DIR__ . '/../Bootstrap/App.php';

$app->lang()
    ->loadDir(__DIR__ . '/../Lang/' . \Chukdo\Helper\HttpRequest::tld());

$app->channel(\Chukdo\Helper\HttpRequest::subDomain());

$app->conf()
    ->loadDefault(__DIR__ . '/../Conf/', $app->env(), $app->channel());

Response::header('X-test', 'test header');
View::setDefaultFolder(__DIR__ . '/../Views/')
    ->loadFunction(new \Chukdo\View\Functions\Basic())
    ->render('test', ['title' => 'chukdo test', 'list' => ['c', 'h', 'u', 'k', 'd', 'o']]);