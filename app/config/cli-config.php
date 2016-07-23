<?php

/**
 * Configuration for Doctrine2 CLI
 */

use Doctrine\ORM\Tools\Console\ConsoleRunner;

$ds = DIRECTORY_SEPARATOR;
/** @var $container Nette\DI\Container */
$container = require_once realpath(__DIR__ . $ds . '..') . $ds . 'bootstrap.php';

$entityManager = $container->getByType('Kdyby\Doctrine\EntityManager');

return ConsoleRunner::createHelperSet($entityManager);