<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\SignInExtension
 */

namespace Legerete\UserSignInModule\Components\SignIn;

use Nette\Application\UI\Control;
use Nette\Localization\ITranslator;
use Legerete\UIForm\FormFactory;
use Nette\Security;
use Nette\Security\AuthenticationException;
use Nette\Utils\Arrays;
use Tracy\ILogger;
use Legerete\Security\IDatabaseAuthenticator;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;

/**
 * Renderable component with Sign in form.
 *
 * @method callable onLogInSuccess
 * @method callable onLogInFailed
 */
class SignInControl extends Control
{
	/**
	 * @var FormFactory
	 */
	private $formFactory;

	/**
	 * @type ILogger
	 */
	private $logger;

	/**
	 * @var Security\User $user
	 */
	private $user;

	/**
	 * @var IDatabaseAuthenticator
	 */
	private $authenticator;

	/**
	 * @var ITranslator
	 */
	private $translator;

	/**
	 * @var string|bool
	 */
	private $forgotPasswordLink;

	/**
	 * @var callback $loggedInSuccess
	 */
	public $onLogInSuccess = [];

	/**
	 * @var callback $loggedInFailed
	 */
	public $onLogInFailed = [];

	/**
	 * @var array
	 */
	public $config = [
		'allowForgotPassword' => TRUE,
		'loginAfterAuthorization' => TRUE,
	];

	/**
	 * SignInControl constructor.
	 * @param array $config
	 * @param FormFactory $formFactory
	 * @param IDatabaseAuthenticator $authenticator
	 * @param ILogger $logger
	 * @param Security\User $user
	 * @param ITranslator $translator
	 */
	public function __construct(
		array $config,
		FormFactory $formFactory,
		IDatabaseAuthenticator $authenticator,
		ILogger $logger,
		Security\User $user,
		ITranslator $translator
	)
	{
		parent::__construct();

		$this->config = array_merge($this->config, $config);
		$this->authenticator = $authenticator;
		$this->formFactory = $formFactory;
		$this->logger = $logger;
		$this->user = $user;
		$this->translator = $translator;
	}

	/**
	 * Render setup
	 */
	public function render()
	{
		$ds = DIRECTORY_SEPARATOR;
		$this->getTemplate()->setFile(realpath(__DIR__ . $ds . 'templates') . $ds . 'SignInPage.latte');
		$this->getTemplate()->allowForgotPassword = Arrays::get($this->config, 'allowForgotPassword', TRUE);
		$this->getTemplate()->render();
	}

	/**
	 * @return Form
	 */
	public function createComponentSignInForm() : Form
	{
		$form = $this->formFactory->create();

		$form->addText('login', $this->translator->translate('sign.in.form.inputEmail'))
			->addRule(Form::EMAIL,
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
	 * @param Form $form
	 * @param ArrayHash $values
	 */
	public function processSignIn(Form $form, ArrayHash $values)
	{
		if ($form->getPresenter()->isAjax()) {
			$this->redrawControl('flashes');
		}

		try {
			$this->authenticator->authenticate($values->login, $values->password, $this->config['loginAfterAuthorization']);
		    $this->onLogInSuccess();
		} catch (AuthenticationException $e) {
			$this->flashMessage($this->getFailedMessage());
		    $this->onLogInFailed();
		}
	}

	/**
	 * @return string
	 */
	private function getFailedMessage() : string
	{
		$message = $this->translator->translate('sign.in.form.error.invalidCredentials');

		if ($this->forgotPasswordLink) {
			$message .= '<br>' . sprintf($this->translator->translate('sign.in.form.error.invalidCredentials'), $this->forgotPasswordLink);
		}

		return $message;
	}

	/**
	 * @param bool|string $link
	 */
	public function setForgotPasswordLink($link = FALSE)
	{
		$this->forgotPasswordLink = $link;
		return $this;
	}

	/**
	 * @var array $config
	 */
	public function setConfig($config)
	{
		$this->config = $config;
		return $this;
	}
}
