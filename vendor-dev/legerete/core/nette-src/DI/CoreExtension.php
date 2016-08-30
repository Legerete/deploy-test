<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\Core
 */

namespace Legerete\DI;

use Nette\DI\CompilerExtension;


class CoreExtension extends CompilerExtension
{
	private $defaults = [
		'pageTitle' => NULL,
	];

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$this->setConfig($this->getConfig($this->defaults));

		// Load config for extension
		$this->compiler->parseServices($builder, $this->loadFromFile(__DIR__ . '/CoreExtensionConfig.neon'), $this->name);
	}

	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig();

		/**
		 * Setting up module mapping for presenter factory
		 */
		$builder->getDefinition('application.presenterFactory')->addSetup('setMapping', [['Legerete' => 'Legerete\*Module\Presenters\*Presenter']]);

		$builder->getDefinition($this->prefix('BasePresenter'))->addSetup('$service->pageTitle = ?', [$config['pageTitle']]);
	}

}
