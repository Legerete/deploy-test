<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\SignInExtension
 */

namespace Legerete\UserSignInModule\DI;

use Legerete\Security\AuthorizatorFactory;
use Legerete\Security\Permission;
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
		'enableOtp' => FALSE,
		'allowForgotPassword' => TRUE,
		'afterLoginRedirectPage' => '',
		'afterLogoutRedirectPage' => '',
		'loginAfterAuthorization' => 'on',
	];

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);

		// Load services definitions for extension
		Compiler::loadDefinitions($builder, $this->loadFromFile(__DIR__ . '/SignInExtensionConfig.neon')['services'], $this->name);

		// Inject config to Presenters
		$builder->getDefinition($this->prefix('SignPresenter'))->setArguments([$config]);
		$builder->getDefinition($this->prefix('ActivatePresenter'))->setArguments([$config]);

		$presenterFactory = $builder->getDefinition('application.presenterFactory');
		$presenterFactory->addSetup('setMapping', [['LeSignIn' => 'Legerete\*Module\Presenters\*Presenter']]);

		$router = $builder->getDefinition('routing.router');
		$router->addSetup('$service->prepend(new Nette\Application\Routers\Route(?, ?));', ['sign[/<action>]', [
			'module' => 'LeSignIn:UserSignIn',
			'presenter' => 'Sign',
			'action' => 'default',
			'alias' => 'LeSignIn'
		]]);
		$router->addSetup('$service->prepend(new Nette\Application\Routers\Route(?, ?));', ['activate/<userId>/<token>', [
			'module' => 'LeSignIn:UserSignIn',
			'presenter' => 'Activate',
			'action' => 'default',
			'alias' => 'LeAccountActivate'
		]]);

		// Add resources to authorizator
		$authorizator = $builder->getDefinition($builder->getByType(IAuthorizator::class));

		if (!$authorizator) {
			throw new NotImplementedException('Class of type '.IAuthorizator::class.' not implemented. For use '.self::class.' is required.');
		}
		$authorizator->addSetup('addResource', ['LeSignIn:UserSignIn:Sign']);
		$authorizator->addSetup('addResource', ['LeSignIn:UserSignIn:Activate']);
		$authorizator->addSetup('allow', [AuthorizatorFactory::ROLE_GUEST, 'LeSignIn:UserSignIn:Sign']);
		$authorizator->addSetup('allow', [AuthorizatorFactory::ROLE_GUEST, 'LeSignIn:UserSignIn:Activate']);
	}

	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();

		// Add default english dictionary
		$builder->getDefinition('translation.default')->addSetup('addResource', ['neon', __DIR__ . '/../lang/sign.en.neon', 'en', 'sign']);
	}

}
