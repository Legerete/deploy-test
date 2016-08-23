<?php

namespace App\Utils\Monolog\DI;

use Nette\PhpGenerator as Code,
	Tracy\Debugger;

/**
 * Integrates the Monolog seamlessly into your Nette Framework application.
 *
 * @author Petr Horáček <petr.horacek@wunderman.cz>
 */
class MonologExtension extends \Kdyby\Monolog\DI\MonologExtension
{

	const TAG_HANDLER = 'monolog.handler';
	const TAG_PROCESSOR = 'monolog.processor';

	private $defaults = [
		'handlers' => [],
		'processors' => [],
		'name' => 'app',
		'hookToTracy' => TRUE,
		// 'registerFallback' => TRUE,
	];

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);
		$builder->parameters[$this->name] = ['name' => $config['name']];

		$builder->addDefinition($this->prefix('logger'))
			->setClass('Kdyby\Monolog\Logger', [$config['name']]);

		// change channel name to priority if available
		$builder->addDefinition($this->prefix('processor.priorityProcessor'))
			->setClass('Kdyby\Monolog\Processor\PriorityProcessor')
			->addTag(self::TAG_PROCESSOR);

		if (!isset($builder->parameters['logDir'])) {
			if (Debugger::$logDirectory) {
				$builder->parameters['logDir'] = Debugger::$logDirectory;

			} else {
				$builder->parameters['logDir'] = $builder->expand('%appDir%/../log');
			}
		}

		if (!is_dir($builder->parameters['logDir'])) {
			@mkdir($builder->parameters['logDir']);
		}

		// handlers
		foreach ($config['handlers'] as $handlerName => $implementation) {
			$this->compiler->parseServices($builder, [
				'services' => [$serviceName = $this->prefix('handler.' . $handlerName) => $implementation],
			]);

			$builder->getDefinition($serviceName)->addTag(self::TAG_HANDLER);
		}

		// processors
		foreach ($config['processors'] as $processorName => $implementation) {
			$this->compiler->parseServices($builder, [
				'services' => [$serviceName = $this->prefix('processor.' . $processorName) => $implementation],
			]);

			$builder->getDefinition($serviceName)->addTag(self::TAG_PROCESSOR);
		}

		// Tracy adapter
		$builder->addDefinition($this->prefix('adapter'))
			->setClass('App\Utils\Monolog\Diagnostics\MonologAdapter', [$this->prefix('@logger')])
			->addTag('logger');

		if ($builder->hasDefinition('tracy.logger')) { // since Nette 2.3
			$builder->removeDefinition($existing = 'tracy.logger');

			if (method_exists($builder, 'addAlias')) { // since Nette 2.3
				$builder->addAlias($existing, $this->prefix('adapter'));

			} else { // old way of providing BC
				$builder->addDefinition($existing)
					->setFactory($this->prefix('@adapter'));
			}
		}
	}

}
