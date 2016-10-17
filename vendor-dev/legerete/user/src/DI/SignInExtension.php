<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\SignInExtension
 */

namespace Legerete\User\DI;

use Legerete\DI\Helpers\DoctrineAnnotationDriverExtensionHelperTrait;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;


class UserExtension extends CompilerExtension
{
	use DoctrineAnnotationDriverExtensionHelperTrait;

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);

		// Load services definitions for extension
		Compiler::loadDefinitions($builder, $this->loadFromFile(__DIR__ . '/UserExtensionConfig.neon')['services'], $this->name);

		$this->registerDoctrineEntityAnnotationDriver(__DIR__.'/../Model/Entity', 'Legerete\User\Model\Entity');
	}

	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();
	}

}
