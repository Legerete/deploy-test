<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\SignInExtension
 */

namespace Legerete\UserModule\Components\SignIn;

use Kdyby\Doctrine\EntityManager,
	Nette\Localization\ITranslator,
	Legerete\UIForm\FormFactory,
	Nette\Application\UI as UI,
	Nette\Security\Identity,
	Nette\Security\Passwords,
	Nette\Security\User,
	Nette\Utils\Arrays,
	Tracy\ILogger,
	Nette\Utils\ArrayHash;

/**
 * Renderable component with Sign in form.
 */
class SignInControl extends UI\Control
{

	private
		/** @var FormFactory */
		$formFactory,

		/** @var EntityManager */
		$entityManager,

		/** @type ILogger */
		$logger,

		/** @var User $user */
		$user,

		/** @var ITranslator */
		$translator,

		/** @var string|bool */
		$forgotPasswordLink;

	public
		/** @var callback $loggedInSuccess */
		$onLogInSuccess = [],

		/** @var callback $loggedInFailed */
		$onLogInFailed = [],

		/** @var array */
		$config = [
			'allowForgotPassword' => TRUE
		];

	/**
	 * SignInControl constructor.
	 * @param array $config
	 * @param FormFactory $formFactory
	 * @param EntityManager $em
	 * @param ILogger $logger
	 * @param User $user
	 * @param ITranslator $translator
	 */
	public function __construct(array $config,
	                            FormFactory $formFactory,
	                            EntityManager $em,
	                            ILogger $logger,
	                            User $user,
	                            ITranslator $translator)
	{
		parent::__construct();

		$this->config = $config;
		$this->formFactory = $formFactory;
		$this->entityManager = $em;
		$this->logger = $logger;
		$this->user = $user;
		$this->translator = $translator;
	}

	/**
	 * Render setup
	 */
	public function render()
	{
		$this->getTemplate()->setFile(__DIR__ . '/templates/SignInPage.latte');
		$this->getTemplate()->allowForgotPassword = Arrays::get($this->config, 'allowForgotPassword', TRUE);
		$this->getTemplate()->render();
	}

	/**
	 * @return UI\Form
	 */
	public function createComponentSignInForm() : UI\Form
	{
		$form = new UI\Form;

		$form->addText('login', $this->translator->translate('sign.in.form.inputEmail'))
			->addRule(UI\Form::EMAIL,
				$this->translator->translate('sign.in.form.error.fillEmailInCorrectFormat')
			)
			->setRequired($this->translator->translate('sign.in.form.error.emailNotFilled'));

		$form->addPassword('password')
			->setRequired($this->translator->translate('sign.in.form.error.passwordNotFilled'));

		$form->addSubmit('submit', $this->translator->translate('sign.in.form.buttonSignIn'));
		$form->onSuccess[] = [$this, 'processSignIn'];

		return $form;
	}

	/**
	 * @param UI\Form $form
	 * @param ArrayHash $values
	 */
	public function processSignIn(UI\Form $form, ArrayHash $values)
	{
		/** @var \App\CoreModule\Entity\Client $user */
		$user = $this->clientRepository()->findOneBy(['login' => $values->address]);

		// TODO implement translations
		if (!$user || !Passwords::verify($values->password, $user->password) || !$user->getIsActive()) {
			$this->flashMessage('Neplatné přihlašovací údaje. '
				. '<a href="' . $this->getPresenter()->link(':Public:Users:LostPassword:') . '">Zapoměli jste heslo</a>?');
			$this->redrawControl('flashes');
			$this->onLogInFailed();
		} else {
			$identity = new Identity($user->getLogin(), 'client', ['client' => $user]);
			$this->user->login($identity);
			$this->onLogInSuccess();
		}
	}

	/**
	 * @param bool|string $link
	 */
	public function setForgotPasswordLink($link = FALSE)
	{
		$this->forgotPasswordLink = $link;
	}

	/**
	 * @return \Kdyby\Doctrine\EntityRepository
	 */
	private function clientRepository()
	{
		return $this->entityManager->getRepository(\App\CoreModule\Entity\Client::class);
	}

}
