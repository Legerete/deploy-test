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

$container = $configurator->createContainer();
