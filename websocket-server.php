<?php

namespace WebsocketServer;

use Ratchet\Server\IoServer;

require __DIR__.'/app/WebSocket/bootstrap.php';

$container->getByType(IoServer::class)->run();