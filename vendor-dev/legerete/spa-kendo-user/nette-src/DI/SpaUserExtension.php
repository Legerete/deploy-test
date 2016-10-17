<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\SignInExtension
 */

namespace Legerete\Spa\KendoUser\DI;

use Legerete\DI\Helpers\DoctrineAnnotationDriverExtensionHelperTrait;
use Legerete\Security\AuthorizatorFactory;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;
use Nette\NotImplementedException;
use Nette\Security\IAuthorizator;

class SpaUserExtension extends CompilerExtension
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
		$authorizator->addSetup('addResource', ['LeSpaScheduler:Scheduler:Scheduler']);

		// @todo DEVELOP TEMPORARY! Delete Me!
		$authorizator->addSetup('allow', [AuthorizatorFactory::ROLE_GUEST, 'LeSpaScheduler:Scheduler:Scheduler']);

//		// Add mapping to doctrine
		$this->registerDoctrineEntityAnnotationDriver(__DIR__.'/../Model/Entity', 'Legerete\Spa\KendoScheduler\Model\Entity');
	}

	public function beforeCompile()
	{
	}
}