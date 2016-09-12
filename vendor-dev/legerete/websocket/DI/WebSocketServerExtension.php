<?php

namespace Legerete\Websocket\DI;

use App\WebSocket\Request\WebSocketRequestFactory;
use Legerete\Websocket\Application\Application;
use Legerete\Websocket\Response\Response;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;

class WebSocketServerExtension extends CompilerExtension
{
	private $defaults = [
		'port' => 8006
	];

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();

		// Load services definitions for extension
		Compiler::loadDefinitions($builder, $this->loadFromFile(__DIR__ . '/WebSocketServerExtensionConfig.neon')['services'], $this->name);
	}

	public function beforeCompile()
	{
		$config = $this->getConfig($this->defaults);

		$builder = $this->getContainerBuilder();

		$builder->getDefinition($this->prefix('webSocketServer'))
			->setArguments([$this->prefix('@sessionProvider')]);

		$builder->getDefinition($this->prefix('httpServer'))
			->setArguments([$this->prefix('@webSocketServer')]);

		$builder->getDefinition($this->prefix('ioServer'))
			->setArguments([$this->prefix('@httpServer'), $config['port']]);

		$builder->removeDefinition('http.response');
		$builder->addDefinition('http.response')->setClass(Response::class);

		$builder->removeDefinition('http.requestFactory');
		$builder->addDefinition('http.requestFactory')->setClass(WebSocketRequestFactory::class);

		$builder->removeDefinition('application.application');
		$builder->addDefinition('application.application')->setClass(Application::class);
	}
}