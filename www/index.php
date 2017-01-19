<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
$container = require __DIR__ . '/../app/bootstrap.php';
$container->getByType(Nette\Application\Application::class)->run();
