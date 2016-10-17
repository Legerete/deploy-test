<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\SignInExtension
 */

namespace Legerete\Security\DI;

use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;

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

		// Load services definitions for extension
		Compiler::loadDefinitions($builder, $this->loadFromFile(__DIR__ . '/SecurityExtensionConfig.neon')['services'], $this->name);
	}
}
