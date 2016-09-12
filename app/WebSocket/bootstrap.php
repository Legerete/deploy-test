<?php

use Nette\Http\UrlScript;
use Nette\Http\Url;
use Nette\Utils\Strings;
use Nette\Http\Request;

require __DIR__ . '/../../vendor/autoload.php';

$configurator = new Nette\Configurator;

\Tracy\Debugger::enable(TRUE);
$configurator->setDebugMode(TRUE); // enable for your remote IP
$configurator->enableDebugger(__DIR__ . '/../../log');

$configurator->setTempDirectory(__DIR__ . '/../../temp');

$configurator->addConfig(__DIR__ . '/../config/config.neon');
$configurator->addConfig(__DIR__ . '/../config/config.local.neon');


$configurator->addConfig(__DIR__ . '/config/config.neon');

// Registrace vlastni tovarny na vytvareni 'Nette\Http\IRequest'.

$container = $configurator->createContainer();
//
//
//
//$container->removeService('http.requestFactory');
//$container->removeService('http.request');
//
//$service = new App\WebSocket\Request\WebSocketRequestFactory;
//$service->setProxy([]);
//
//$container->addService('http.requestFactory', $service);
//	$uri = new UrlScript;
//	$uri->scheme = 'http';
////	$uri->port = Url::$defaultPorts['http'];
//	$uri->port = 8005;
//	$uri->host = 'localhost';
//	$uri->path = '/legerete.user.sign/in';
//	$uri->canonicalize();
//	$uri->path = Strings::fixEncoding($uri->path);
//	$uri->scriptPath = '/';


//$container->addService('http.request', new Request($uri, NULL, [], [], [], [
//	'X-Requested-With' => 'XMLHttpRequest'
//], 'GET', null, null));



