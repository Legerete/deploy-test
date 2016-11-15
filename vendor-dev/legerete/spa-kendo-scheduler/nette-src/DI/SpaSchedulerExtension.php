<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\SignInExtension
 */

namespace Legerete\Spa\KendoScheduler\DI;

use Legerete\DI\Helpers\DoctrineAnnotationDriverExtensionHelperTrait;
use Legerete\Security\AuthorizatorFactory;
use Legerete\Security\Permission;
use Legerete\Spa\Collection\KendoTemplatesCollection;
use Legerete\Spa\Collection\SpaTemplatesControlsCollection;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;
use Nette\NotImplementedException;
use Nette\Security\IAuthorizator;

class SpaSchedulerExtension extends CompilerExtension
{
	use DoctrineAnnotationDriverExtensionHelperTrait;

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

		// Add resources to authorizator
		$authorizator = $builder->getDefinition($builder->getByType(IAuthorizator::class));

		if (!$authorizator) {
			throw new NotImplementedException('Class of type '.IAuthorizator::class.' not implemented. For use '.self::class.' is required.');
		}

		$resource = 'LeSpaScheduler:Scheduler:Scheduler';
		$authorizator->addSetup('addResource', [$resource]);
		$authorizator->addSetup('addResourcePrivileges', [$resource, [
			Permission::PRIVILEGE_SHOW,
			Permission::PRIVILEGE_CREATE,
			Permission::PRIVILEGE_READ_MY,
			Permission::PRIVILEGE_UPDATE,
			Permission::PRIVILEGE_DESTROY,
			Permission::PRIVILEGE_MANAGE,
		]]);

		// @todo DEVELOP TEMPORARY! Delete Me!
		$authorizator->addSetup('allow', [AuthorizatorFactory::ROLE_GUEST, 'LeSpaScheduler:Scheduler:Scheduler']);

		// Add mapping to doctrine
		$this->registerDoctrineEntityAnnotationDriver(__DIR__.'/../Model/Entity', 'Legerete\Spa\KendoScheduler\Model\Entity');

		$templatesCollection = $builder->getDefinition($builder->getByType(SpaTemplatesControlsCollection::class));
		$templatesCollection->addSetup('set', ['schedulerTemplate', $this->prefix('@schedulerTemplate')]);
	}

	public function beforeCompile()
	{
	}
}