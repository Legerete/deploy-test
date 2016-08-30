<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\CRM\UserModule
 */

namespace Legerete\CRM\UserModule\DI;

use Nette\DI\CompilerExtension;


class ForgotPasswordExtension extends CompilerExtension
{

	/**
	 * Default settings for components etc.
	 * @var array $defaults
	 */
	private $defaults = [
		'signInLink' => false,
	];

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();

		// Load config for extension
		$this->compiler->parseServices($builder, $this->loadFromFile(__DIR__ . '/ForgotPasswordExtensionConfig.neon'), $this->name);
	}


	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();
	}

}
