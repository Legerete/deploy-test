<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\SignInExtension
 */

namespace Legerete\UserModule\DI;

use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;
use Nette\NotImplementedException;
use Nette\Security\IAuthorizator;


class SignInExtension extends CompilerExtension
{

	/**
	 * Default settings for components etc.
	 * @var array $defaults
	 */
	private $defaults = [
		'allowForgotPassword' => TRUE,
	];

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);

		// Load services definitions for extension
		Compiler::loadDefinitions($builder, $this->loadFromFile(__DIR__ . '/SignInExtensionConfig.neon')['services'], $this->name);

		// Inject config to SignPresenter
		$builder->getDefinition($this->prefix('SignPresenter'))->setArguments([$config]);

		// Add resources to authorizator
//		$authorizator = $builder->getDefinition($builder->getByType(IAuthorizator::class));
//
//		if (!$authorizator) {
//			throw new NotImplementedException('Class of type '.IAuthorizator::class.' not implemented. For use '.self::class.' is required.');
//		}
//		$authorizator->addSetup('addResource', ['Legerete:User:Sign']);
	}

	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();

		// Add default english dictionary
		$builder->getDefinition('translation.default')->addSetup('addResource', ['neon', __DIR__ . '/../lang/sign.en.neon', 'en', 'sign']);
	}

}
