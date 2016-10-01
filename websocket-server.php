<?php

/**
 * @copyright   Copyright (c) 2016 legerete.cz <hello@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\WebSocket
 */

namespace WebSocketServer;

use Nette\DI\Container;
use Ratchet\Server\IoServer;

require __DIR__.'/app/WebSocket/bootstrap.php';

/** @var Container $container */
$container->getByType(IoServer::class)->run();