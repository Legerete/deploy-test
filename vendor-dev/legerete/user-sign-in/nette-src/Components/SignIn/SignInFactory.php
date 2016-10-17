<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\SignInExtension
 */

namespace Legerete\UserSignInModule\Components\SignIn;

use Kdyby\Doctrine\EntityManager;
use Legerete\Security\IDatabaseAuthenticator;
use Legerete\UIForm\FormFactory;
use Nette\Localization\ITranslator;
use Nette\Security\User;
use Tracy\ILogger;

/**
 * Factory for creating Sign in form
 */
class SignInFactory extends \Nette\Application\UI\Control
{
	/**
	 * @var ILogger
	 */
	private $logger;

	/**
	 * @var User
	 */
	private $user;

	/**
	 * @var FormFactory
	 */
	private $formFactory;

	/**
	 * @var ITranslator
	 */
	private $translator;

	/**
	 * @var IDatabaseAuthenticator
	 */
	private $authenticator;

	/**
	 * SignInFactory constructor.
	 *
	 * @param FormFactory $formFactory
	 * @param ILogger $logger
	 * @param User $user
	 * @param ITranslator $translator
	 */
	public function __construct(FormFactory $formFactory, IDatabaseAuthenticator $authenticator, EntityManager $em, ILogger $logger, User $user, ITranslator $translator)
	{
		parent::__construct();

		$this->formFactory = $formFactory;
		$this->authenticator = $authenticator;
		$this->logger = $logger;
		$this->user = $user;
		$this->translator = $translator;
	}

	/**
	 * @param array $config [
	 *        allowForgotPassword
	 * ]
	 *
	 * @return SignInControl
	 */
	public function create(array $config = []) : SignInControl
	{
		return new SignInControl($config, $this->formFactory, $this->authenticator, $this->logger, $this->user, $this->translator);
	}
}
