<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\SpaKendoIm
 */

namespace Legerete\Spa\KendoIm\DI;

use Legerete\DI\Helpers\DoctrineAnnotationDriverExtensionHelperTrait;
use Legerete\Security\Permission;
use Legerete\Spa\Collection\SpaTemplatesControlsCollection;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;
use Nette\DI\ContainerBuilder;
use Nette\NotImplementedException;
use Nette\Security\IAuthorizator;

class SpaImExtension extends CompilerExtension
{
	use DoctrineAnnotationDriverExtensionHelperTrait;

	private $defaults = [
		'templatesDir' => '%appDir%/templates/spa-information-memorandum',
		'thumbnailsDir' => '/assets/spa-information-memorandum',
	];

	public function loadConfiguration()
	{
		/**
		 * @var $builder ContainerBuilder
		 */
		$builder = $this->getContainerBuilder();
		$config = $builder->expand($this->getConfig($this->defaults));

		// Load services definitions for extension
		Compiler::loadDefinitions($builder, $this->loadFromFile(__DIR__ . '/config.neon')['services'], $this->name);

		$modelService = $builder->getDefinition($this->prefix('imModelService'));
		$modelService->addSetup('setTemplatesDirectory', [$config['templatesDir']]);

		$presenterFactory = $builder->getDefinition('application.presenterFactory');
		$presenterFactory->addSetup('setMapping', [['LeSpaIm' => 'Legerete\Spa\KendoIm\*Module\Presenters\*Presenter']]);

		$router = $builder->getDefinition('routing.router');
		$router->addSetup('$service->prepend(new Nette\Application\Routers\Route(?, ?));', ['im[/<action>]', [
			'module' => 'LeSpaIm:Im',
			'presenter' => 'Im',
			'action' => 'default',
			'alias' => 'LeSpaIm'
		]]);

		// Add resources to authorizator
		$authorizator = $builder->getDefinition($builder->getByType(IAuthorizator::class));

		if (!$authorizator) {
			throw new NotImplementedException('Class of type '.IAuthorizator::class.' not implemented. For use '.self::class.' is required.');
		}
		$authorizator->addSetup('addResource', ['LeSpaIm:Im:Im']);
		$authorizator->addSetup('addResourcePrivileges', ['LeSpaIm:Im:Im', [
			Permission::PRIVILEGE_SHOW,
			Permission::PRIVILEGE_READ_ALL,
			Permission::PRIVILEGE_READ_MY,
			Permission::PRIVILEGE_CREATE,
			Permission::PRIVILEGE_UPDATE,
			Permission::PRIVILEGE_DESTROY,
			Permission::PRIVILEGE_MANAGE,
		]]);

//		// Add mapping to doctrine
		$this->registerDoctrineEntityAnnotationDriver(__DIR__.'/../Model/Entity', 'Legerete\Spa\KendoIm\Model\Entity');

		$templatesCollection = $builder->getDefinition($builder->getByType(SpaTemplatesControlsCollection::class));
		$templatesCollection->addSetup('set', ['imTemplate', $this->prefix('@imTemplate')]);

		$builder->getDefinition($this->prefix('imPresenter'))->addSetup('setPageLayoutPath', [$config['templatesDir']]);
	}

	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();

		// Add default english dictionary
		$builder->getDefinition('translation.default')->addSetup('addResource', ['neon', __DIR__ . '/../lang/im.en.neon', 'en', 'im']);
	}
}