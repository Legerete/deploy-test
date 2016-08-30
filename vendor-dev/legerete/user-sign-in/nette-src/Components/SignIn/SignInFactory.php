<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\SignInExtension
 */

namespace Legerete\UserModule\Components\SignIn;

use Kdyby\Doctrine\EntityManager;
use Legerete\UIForm\FormFactory;
use Nette\Localization\ITranslator;
use Nette\Security\User;
use Tracy\ILogger;

/**
 * Factory for creating Sign in form from \Legerete\CRM\UserModule\Components\SignIn\SignInControl
 * @author Petr Besir Horáček <sirbesir@gmail.com>
 */
class SignInFactory extends \Nette\Application\UI\Control
{
	/**
	 * @var EntityManager
	 */
	private $em;

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
	 * SignInFactory constructor.
	 *
	 * @param FormFactory $formFactory
	 * @param EntityManager $em
	 * @param ILogger $logger
	 * @param User $user
	 * @param ITranslator $translator
	 */
	public function __construct(FormFactory $formFactory, EntityManager $em, ILogger $logger, User $user, ITranslator $translator)
	{
		parent::__construct();

		$this->formFactory = $formFactory;
		$this->em = $em;
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
		return new SignInControl($config, $this->formFactory, $this->em, $this->logger, $this->user, $this->translator);
	}
}
