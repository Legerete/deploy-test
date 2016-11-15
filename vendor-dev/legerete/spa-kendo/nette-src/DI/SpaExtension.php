<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\Spa
 */

namespace Legerete\Spa\DI;

use Legerete\Security\Permission;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;
use Nette\NotImplementedException;
use Nette\Security\IAuthorizator;

class SpaExtension extends CompilerExtension
{
	private $defaults = [
	];

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();

		// Load services definitions for extension
		Compiler::loadDefinitions($builder, $this->loadFromFile(__DIR__ . '/config.neon')['services'], $this->name);

		// Add resources to authorizator
		$authorizator = $builder->getDefinition($builder->getByType(IAuthorizator::class));

		if (!$authorizator) {
			throw new NotImplementedException('Class of type '.IAuthorizator::class.' not implemented. For use '.self::class.' is required.');
		}

		$authorizator->addSetup('addResourcePrivileges', ['SPA', [
			Permission::PRIVILEGE_SHOW
		]]);
	}

	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();
	}
}