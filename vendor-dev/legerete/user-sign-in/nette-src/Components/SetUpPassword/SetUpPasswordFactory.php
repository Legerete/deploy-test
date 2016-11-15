<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\SignInExtension
 */

namespace Legerete\UserSignInModule\Components\SetUpPassword;

use Kdyby\Doctrine\EntityManager;
use Legerete\UIForm\FormFactory;
use Nette\Localization\ITranslator;
use Nette\Security\User;
use Tracy\ILogger;

/**
 * Factory for creating SetUpPasswordControl
 */
class SetUpPasswordFactory
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
	 * SetUpPasswordFactory constructor.
	 *
	 * @param FormFactory $formFactory
	 * @param EntityManager $em
	 * @param ILogger $logger
	 * @param User $user
	 * @param ITranslator $translator
	 */
	public function __construct(
		FormFactory $formFactory,
		EntityManager $em,
		ILogger $logger,
		User $user,
		ITranslator $translator
	)
	{
		$this->formFactory = $formFactory;
		$this->logger = $logger;
		$this->user = $user;
		$this->translator = $translator;
	}

	/**
	 * @param array $config [
	 * ]
	 *
	 * @return SetUpPasswordControl
	 */
	public function create(array $config = []) : SetUpPasswordControl
	{
		return new SetUpPasswordControl($config, $this->formFactory, $this->logger, $this->user, $this->translator);
	}
}
