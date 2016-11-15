<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\SignInOtpExtension
 */

namespace Legerete\UserSignInOtp\DI;

use Legerete\DI\Helpers\DoctrineAnnotationDriverExtensionHelperTrait;
use Legerete\Security\AuthorizatorFactory;
use Legerete\Security\Permission;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;
use Nette\NotImplementedException;
use Nette\Security\IAuthorizator;


class SignInOtpExtension extends CompilerExtension
{
	use DoctrineAnnotationDriverExtensionHelperTrait;

	/**
	 * Default settings for components etc.
	 * @var array $defaults
	 */
	private $defaults = [
		'afterLoginRedirectPage' => '',
		'noIdentityRedirect' => 'LeSignIn:UserSignIn:Sign:in'
	];

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);

		// Load services definitions for extension
		Compiler::loadDefinitions($builder, $this->loadFromFile(__DIR__ . '/SignInOtpExtensionConfig.neon')['services'], $this->name);

		// Inject config to SignPresenter
		$builder->getDefinition($this->prefix('SignPresenter'))->setArguments([$config]);

		// Add extension mapping
		$presenterFactory = $builder->getDefinition('application.presenterFactory');
		$presenterFactory->addSetup('setMapping', [['LeSignInOtp' => 'Legerete\*Module\Presenters\*Presenter']]);

		// Add route for extension
		$router = $builder->getDefinition('routing.router');
		$router->addSetup('$service->prepend(new Nette\Application\Routers\Route(?, ?));', ['sign/otp[/<action>]', [
			'module' => 'LeSignInOtp:UserSignInOtp',
			'presenter' => 'SignOtp',
			'action' => 'default',
			'alias' => 'LeSignInOtp'
		]]);

		// Add extension resources to authorizator
		$authorizator = $builder->getDefinition($builder->getByType(IAuthorizator::class));

		if (!$authorizator) {
			throw new NotImplementedException('Class of type '.IAuthorizator::class.' not implemented. For use '.self::class.' is required.');
		}
//		$authorizator->addSetup('addResource', ['LeSignIn:UserSignInOtp:SignOtp']);
//		$authorizator->addSetup('addResourcePrivilege', ['LeSignIn:UserSignInOtp:SignOtp', Permission::PRIVILEGE_SHOW]);
//		$authorizator->addSetup('allow', [AuthorizatorFactory::ROLE_GUEST, 'LeSignIn:UserSignInOtp:SignOtp']);

		// Add Doctrine Entity mapping
		$this->registerDoctrineEntityAnnotationDriver(__DIR__.'/../Entity', 'Legerete\UserSignInOtp\Entity');
	}

	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();

		// Add default english dictionary
		$builder->getDefinition('translation.default')->addSetup('addResource', ['neon', __DIR__ . '/../lang/sign-otp.en.neon', 'en', 'sign-otp']);
	}

}
