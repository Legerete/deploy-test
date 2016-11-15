<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\SignInExtension
 */

namespace Legerete\UserSignInModule\Presenters;

use Legerete\Security\IDatabaseAuthenticator;
use Legerete\Security\Presenters\SecuredPresenter;
use Legerete\UserSignInModule\Components\ChooseNewPasswordControl;
use Legerete\UserSignInModule\Components\ChooseNewPasswordFactory;
use Legerete\UserSignInModule\Components\ForgotPasswordControl;
use Legerete\UserSignInModule\Components\ForgotPasswordFactory;
use Legerete\UserSignInModule\Components\SignIn\SignInControl;
use Legerete\UserSignInModule\Components\SignIn\SignInFactory;
use Legerete\UserSignInModule\Components\SignInOtp\SignInOtpControl;
use Legerete\UserSignInModule\Components\SignInOtp\SignInOtpFactory;
use Nette\Localization\ITranslator;


/**
 * Sign in/out presenter.
 * @author Petr Besir Horacek <sirbesir@gmail.com>
 * @resource LeSignIn:UserSignIn:Sign
 * @privileges show
 */
class SignPresenter extends SecuredPresenter
{
	/**
	 * @var SignInFactory $signInFormFactory
	 */
	private $signInFormFactory;

	/**
	 * @var SignInOtpFactory $signInOtpFormFactory
	 */
	private $signInOtpFormFactory;

	/**
	 * @var ForgotPasswordFactory $forgotPasswordFactory
	 */
	private $forgotPasswordFactory;

	/**
	 * @var ChooseNewPasswordFactory $chooseNewPasswordFactory
	 */
	private $chooseNewPasswordFactory;

	/**
	 * @var bool $mailConfirmed
	 */
	private $mailConfirmed;

	/**
	 * @var ITranslator $translator
	 */
	private $translator;

	/**
	 * @var IDatabaseAuthenticator $authenticator
	 */
	private $authenticator;

	/**
	 * @var string $loginRedirectPage
	 */
	private $loginRedirectPage;

	/**
	 * @var string $loginRedirectPage
	 */
	private $logoutRedirectPage;

	/**
	 * @var string $signInComponent;
	 */
	private $signInComponent = 'signInForm';

	/**
	 * @var array
	 */
	private $config = [
		'enableOtp' => FALSE,
		'loginRedirectPage' => 'this',
	];

	public function __construct(
		array $config,
		SignInFactory $signInFactory,
		ForgotPasswordFactory $forgotPasswordFactory,
		ChooseNewPasswordFactory $chooseNewPasswordFactory,
		SignInOtpFactory $signInOtpFactory,
		ITranslator $translator,
		IDatabaseAuthenticator $authenticator
	)
	{
		parent::__construct();

		$this->config = array_merge($this->config, $config);
		$this->signInFormFactory = $signInFactory;
		$this->forgotPasswordFactory = $forgotPasswordFactory;
		$this->chooseNewPasswordFactory = $chooseNewPasswordFactory;
		$this->signInOtpFormFactory = $signInOtpFactory;
		$this->translator = $translator;
		$this->authenticator = $authenticator;
		$this->mailConfirmed = FALSE;
	}

	public function renderIn()
	{
		$this->getTemplate()->pageClass = 'sign';
		$this->getTemplate()->signInComponent = $this->signInComponent;
	}

	public function renderForgotPassword()
	{
		$this->getTemplate()->pageClass = 'forgot-password';
	}

	/**
	 * @var string $email
	 * @var string $hash
	 * @TODO implement this...
	 */
	public function actionConfirmMail($email, $hash)
	{
		if ($user = $this->clientService->verifyRegisterEmail($email, $hash)) {
			$this->mailConfirmed = TRUE;

			$this->authenticator->authenticate($user->login, $user->password, TRUE);

		}
	}

	/**
	 * @see SignPresenter::actionConfirmMail()
	 */
	public function renderConfirmMail()
	{
		if ($this->mailConfirmed) {
			$this->setView('mailConfirmed');
		} else {
			$this->setView('mailDeclined');
		}
	}

	/**
	 * Sign out user
	 */
	public function actionOut()
	{
		$this->getUser()->logout();
		$this->flashMessage($this->translator->translate('security.user.signed-out'));
		$this->redirect($this->getLogoutRedirectPage());
	}

	/* ********************************** Components ********************************** */

	/**
	 * @return SignInControl
	 */
	public function createComponentSignInForm() : SignInControl
	{
		$component = $this->signInFormFactory->create($this->config);

		if ($this->config['enableOtp']) {
			$component->onLogInSuccess[] = function () {
				$this->signInComponent = 'signInOtpForm';
				$this->redrawControl('signInForm');
			};
		} else {
			$component->onLogInSuccess[] = function() {
				$this->redirect($this->config['loginRedirectPage']);
			};
		}

		return $component;
	}

	/**
	 * @return ForgotPasswordControl
	 */
	protected function createComponentForgotPasswordForm() : ForgotPasswordControl
	{
		return $this->forgotPasswordFactory->create();
	}

	/**
	 * @return ChooseNewPasswordControl
	 */
	protected function createComponentChooseNewPassword() : ChooseNewPasswordControl
	{
		return $this->chooseNewPasswordFactory->create();
	}

	/**
	 * @return SignInOtpControl
	 */
	public function createComponentSignInOtpForm() : SignInOtpControl
	{
		$component = $this->signInOtpFormFactory->create($this->config);

		$component->onVerifySuccess[] = function() {
			\Tracy\Debugger::barDump('verify success');
			$this->redirect($this->config['loginRedirectPage']);
		};

		return $component;
	}

	/* ********************************** Getters ********************************** */

	/**
	 * @return string
	 */
	public function getLoginRedirectPage() : string
	{
		return $this->loginRedirectPage;
	}

	/**
	 * @return string
	 */
	public function getLogoutRedirectPage() : string
	{
		return $this->logoutRedirectPage ?: 'in';
	}

	/* ********************************** Setters ********************************** */

	/**
	 * @var string $loginRedirectPage
	 */
	public function setLoginRedirectPage($loginRedirectPage)
	{
		$this->loginRedirectPage = $loginRedirectPage;
		return $this;
	}

	/**
	 * @var string $logoutRedirectPage
	 */
	public function setLogoutRedirectPage($logoutRedirectPage)
	{
		$this->logoutRedirectPage = $logoutRedirectPage;
		return $this;
	}
}
