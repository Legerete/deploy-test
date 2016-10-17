<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\Core
 */

namespace Legerete\DI;

use Doctrine\ORM\EntityManagerInterface;
use Legerete\DI\Factories\DoctrineFactory;
use Nette\DI\CompilerExtension;
use Nette\DI\Helpers;


class CoreExtension extends CompilerExtension
{
	private $defaults = [
		'pageTitle' => NULL,
	];

	protected $doctrineDefaults = [
		'doctrine' => [
			'driver'   => 'pdo_mysql',
			'user'     => 'root',
			'password' => NULL,
			'dbname'   => '',
			'charset'  => 'utf8',
		]
	];

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$this->setConfig($this->getConfig($this->defaults));

		// Load config for extension
		$this->compiler->parseServices($builder, $this->loadFromFile(__DIR__ . '/CoreExtensionConfig.neon'), $this->name);

		// Add our entity manager for easy set-up doctrine mapping
		$builder->addDefinition('legerete.doctrine.entity.manager.factory')
			->setClass(
				DoctrineFactory::class,
				[
					Helpers::expand('%debugMode%', $builder->parameters),
					Helpers::expand('%tempDir%/doctrine', $builder->parameters),
					$this->getConfig()['doctrine']
				]
			);

		$builder->addDefinition($this->prefix('doctrine.entity.manager'))
			->setClass(EntityManagerInterface::class)
			->setFactory('@legerete.doctrine.entity.manager.factory::create');

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
