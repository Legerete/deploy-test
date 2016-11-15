<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\SignInExtension
 */

namespace Legerete\Spa\KendoAcl\DI;

use Legerete\DI\Helpers\DoctrineAnnotationDriverExtensionHelperTrait;
use Legerete\Security\AuthorizatorFactory;
use Legerete\Security\Permission;
use Legerete\Spa\Collection\SpaTemplatesControlsCollection;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;
use Nette\NotImplementedException;
use Nette\Security\IAuthorizator;

class SpaAclExtension extends CompilerExtension
{
	use DoctrineAnnotationDriverExtensionHelperTrait;

	private $defaults = [
	];

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();

		// Load services definitions for extension
		Compiler::loadDefinitions($builder, $this->loadFromFile(__DIR__ . '/config.neon')['services'], $this->name);

		$presenterFactory = $builder->getDefinition('application.presenterFactory');
		$presenterFactory->addSetup('setMapping', [['LeSpaAcl' => 'Legerete\Spa\KendoAcl\*Module\Presenters\*Presenter']]);

		$router = $builder->getDefinition('routing.router');
		$router->addSetup('$service->prepend(new Nette\Application\Routers\Route(?, ?));', ['acl[/<action>]', [
			'module' => 'LeSpaAcl:Acl',
			'presenter' => 'Acl',
			'action' => 'default',
			'alias' => 'LeSpaAcl'
		]]);

		// Add resources to authorizator
		$authorizator = $builder->getDefinition($builder->getByType(IAuthorizator::class));

		if (!$authorizator) {
			throw new NotImplementedException('Class of type '.IAuthorizator::class.' not implemented. For use '.self::class.' is required.');
		}
		$authorizator->addSetup('addResource', ['LeSpaAcl:Acl:Acl']);
		$authorizator->addSetup('addResourcePrivileges', ['LeSpaAcl:Acl:Acl', [
			Permission::PRIVILEGE_SHOW,
			Permission::PRIVILEGE_MANAGE
		]]);

		$templatesCollection = $builder->getDefinition($builder->getByType(SpaTemplatesControlsCollection::class));
		$templatesCollection->addSetup('set', ['aclTemplate', $this->prefix('@aclTemplate')]);
	}

	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();

		// Add default english dictionary
		$builder->getDefinition('translation.default')->addSetup('addResource', ['neon', __DIR__ . '/../lang/acl.en.neon', 'en', 'acl']);
	}
}