<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\SignInExtension
 */

namespace Legerete\Security\DI;

use Legerete\Security\AuthorizatorFactory;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;
use Nette\Security\IAuthorizator;

class SecurityExtension extends CompilerExtension
{
	private $defaults = [
		'roles' => [
			'guest',
		],
		'resources' => [],
		'privileges' => [],
	];

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();

		\Tracy\Debugger::barDump($this->getConfig());

		// Load services definitions for extension
		Compiler::loadDefinitions($builder, $this->loadFromFile(__DIR__ . '/SecurityExtensionConfig.neon')['services'], $this->name);
	}

//	public function beforeCompile()
//	{
//		$builder = $this->getContainerBuilder();
//		$acl = $builder->getDefinition($builder->getByType(IAuthorizator::class));
//		$aclFactory = $builder->getDefinition($builder->getByType(AuthorizatorFactory::class));
//		\Tracy\Debugger::barDump($acl);
//		$aclFactory->addSetup('addRoles', [$acl]);
//		$aclFactory->addSetup('addResources', [$acl]);
//		$aclFactory->addSetup('addPrivileges', [$acl]);
//	}
}
