<?php

namespace Legerete\Spa\KendoScheduler\DI;

use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;

class SpaSchedulerExtension extends CompilerExtension
{

	private $defaults = [
		'timeZone' => null // date_default_timezone_get()
	];

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();

		// Load services definitions for extension
		Compiler::loadDefinitions($builder, $this->loadFromFile(__DIR__ . '/config.neon')['services'], $this->name);

		$presenterFactory = $builder->getDefinition('application.presenterFactory');
		$presenterFactory->addSetup('setMapping', [['LeSpaScheduler' => 'Legerete\Spa\KendoScheduler\*Module\Presenters\*Presenter']]);

		$router = $builder->getDefinition('routing.router');
		$router->addSetup('$service->prepend(new Nette\Application\Routers\Route(?, ?));', ['scheduler[/<action>]', [
			'module' => 'LeSpaScheduler:Scheduler',
			'presenter' => 'Scheduler',
			'action' => 'default',
			'alias' => 'LeSpaScheduler'
		]]);


	}

	public function beforeCompile()
	{
	}
}