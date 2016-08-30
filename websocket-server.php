<?php

	namespace WebsocketServer;

	use Ratchet\Server\IoServer;
	use Ratchet\Http\HttpServer;
	use Ratchet\WebSocket\WsServer;
	use Nette\Configurator;
	use Tracy\Debugger;

	require __DIR__.'/app/WebSocket/bootstrap.php';

	Debugger::enable(NULL, __DIR__.'/log');

//	$session = new \App\WebSocket\NetteSessionProvider(
//		new \App\WebSocket\App($container->getByType(\Nette\Application\Application::class)), $container->getByType(\Nette\Http\Session::class)
//	);
//
//	$wsServer = new HttpServer(new WsServer($session));
//
//	$server = IoServer::factory($wsServer, 8006);


	$server = IoServer::factory(
		new HttpServer(
			new WsServer(
				new \App\WebSocket\App($container->getByType(\Nette\Application\Application::class))
			)
		),
		8006
	);

	$server->run();