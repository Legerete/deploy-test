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
		$this->registerDoctrineEntityAnnotationDriver(__DIR__.'/../Model/Entity', 'Legerete\User\Model\Entity');
	}

}
