<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\Spa
 */

namespace Legerete\Spa\DI;

use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;

class SpaExtension extends CompilerExtension
{
	private $defaults = [
	];

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();

		// Load services definitions for extension
		Compiler::loadDefinitions($builder, $this->loadFromFile(__DIR__ . '/config.neon')['services'], $this->name);
	}

	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();
	}
}