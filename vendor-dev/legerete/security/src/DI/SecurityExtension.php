<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\SignInExtension
 */

namespace Legerete\Security\DI;

use Legerete\DI\Helpers\DoctrineAnnotationDriverExtensionHelperTrait;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;

class SecurityExtension extends CompilerExtension
{
	use DoctrineAnnotationDriverExtensionHelperTrait;

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
		Compiler::loadDefinitions($builder, $this->loadFromFile(__DIR__ . '/config.neon')['services'], $this->name);

		$this->registerDoctrineEntityAnnotationDriver(__DIR__.'/../Model/Entity', 'Legerete\Security\Model\Entity');

	}
}
