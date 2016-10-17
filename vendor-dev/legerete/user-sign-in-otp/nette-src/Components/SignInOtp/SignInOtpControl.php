<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\SignInExtension
 */

namespace Legerete\UserSignInOtp\Components\SignInOtp;

use Nette\Localization\ITranslator;
use Legerete\UIForm\FormFactory;
use Nette\Application\UI as UI;
use Nette\Security as Security;
use Tracy\ILogger;
use Nette\Utils as Utils;

/**
 * Renderable component with Sign in form.
 */
class SignInOtpControl extends UI\Control
{
	/** @var FormFactory */
	private $formFactory;

	/** @type ILogger */
	private $logger;

	/** @var Security\User $user */
	private $user;

	/** @var ITranslator */
	private $translator;

	/** @var string|bool */
	private $forgotPasswordLink;

	/** @var callback $loggedInSuccess */
	public $onLogInSuccess = [];

	/** @var callback $loggedInFailed */
	public $onLogInFailed = [];

	/** @var array */
	public $config = [
		'allowForgotPassword' => TRUE
	];

	/**
	 * SignInControl constructor.
	 * @param array $config
	 * @param FormFactory $formFactory
	 * @param ILogger $logger
	 * @param Security\User $user
	 * @param ITranslator $translator
	 */
	public function __construct(array $config,
								FormFactory $formFactory,
								ILogger $logger,
								Security\User $user,
								ITranslator $translator)
	{
		parent::__construct();

		$this->config = $config;
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
	 * @param Utils\ArrayHash $values
	 */
	public function processSignIn(UI\Form $form, Utils\ArrayHash $values)
	{
		try {
		    $this->onLogInSuccess();
		} catch (Security\AuthenticationException $e) {
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
}
