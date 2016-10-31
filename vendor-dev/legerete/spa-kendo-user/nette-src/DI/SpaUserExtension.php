<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\SignInExtension
 */

namespace Legerete\Spa\KendoUser\DI;

use Legerete\DI\Helpers\DoctrineAnnotationDriverExtensionHelperTrait;
use Legerete\Security\AuthorizatorFactory;
use Legerete\Security\Permission;
use Legerete\Spa\Collection\SpaTemplatesControlsCollection;
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
		$presenterFactory->addSetup('setMapping', [['LeSpaUser' => 'Legerete\Spa\KendoUser\*Module\Presenters\*Presenter']]);

		$router = $builder->getDefinition('routing.router');
		$router->addSetup('$service->prepend(new Nette\Application\Routers\Route(?, ?));', ['user[/<action>]', [
			'module' => 'LeSpaUser:User',
			'presenter' => 'User',
			'action' => 'default',
			'alias' => 'LeSpaUser'
		]]);

		// Add resources to authorizator
		$authorizator = $builder->getDefinition($builder->getByType(IAuthorizator::class));

		if (!$authorizator) {
			throw new NotImplementedException('Class of type '.IAuthorizator::class.' not implemented. For use '.self::class.' is required.');
		}
		$authorizator->addSetup('addResource', ['LeSpaUser:User:User']);
		$authorizator->addSetup('addResourcePrivileges', ['LeSpaUser:User:User', [
			Permission::PRIVILEGE_SHOW,
			Permission::PRIVILEGE_READ_ALL,
			Permission::PRIVILEGE_CREATE,
			Permission::PRIVILEGE_UPDATE,
			Permission::PRIVILEGE_DESTROY,
		]]);

		// @todo DEVELOP TEMPORARY! Delete Me!
		$authorizator->addSetup('allow', [AuthorizatorFactory::ROLE_GUEST, 'LeSpaUser:User:User']);

//		// Add mapping to doctrine
		$this->registerDoctrineEntityAnnotationDriver(__DIR__.'/../Model/Entity', 'Legerete\Spa\KendoUser\Model\Entity');

		$templatesCollection = $builder->getDefinition($builder->getByType(SpaTemplatesControlsCollection::class));
		$templatesCollection->addSetup('set', ['userTemplate', $this->prefix('@userTemplate')]);
	}

	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();

		// Add default english dictionary
		$builder->getDefinition('translation.default')->addSetup('addResource', ['neon', __DIR__ . '/../lang/users.en.neon', 'en', 'users']);
	}
}