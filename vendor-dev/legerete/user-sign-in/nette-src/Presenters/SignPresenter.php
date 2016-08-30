<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\SignInExtension
 */

namespace Legerete\UserModule\Presenters;

use Legerete\Presenters\SecuredPresenter;
use Legerete\UserModule\Components\SignIn\SignInControl;
use Legerete\UserModule\Components\SignIn\SignInFactory;
use Legerete\UserModule\Components\ForgotPasswordFactory;
use Legerete\UserModule\Components\ForgotPasswordControl;
use Legerete\UserModule\Components\ChooseNewPasswordFactory;
use Legerete\UserModule\Components\ChooseNewPasswordControl;
use Nette\Security\Identity;

/**
 * @author Petr Besir Horacek <sirbesir@gmail.com>
 * Sign in/out presenter.
 */
class SignPresenter extends SecuredPresenter
{
	/**
	 * @inject
	 * @var SignInFactory
	 */
	public $signInForm;

	/**
	 * @inject
	 * @var ForgotPasswordFactory
	 */
	public $lostPasswordFactory;

	/**
	 * @inject
	 * @var ChooseNewPasswordFactory
	 */
	public $chooseNewPasswordFactory;

	/**
	 * @var bool $mailConfirmed
	 */
	private $mailConfirmed = FALSE;

	/**
	 * @var array
	 */
	private $config;

	public function __construct(array $config = [])
	{
		parent::__construct();

		$this->config = $config;
	}

	public function renderIn()
	{
		$this->getTemplate()->pageClass = 'sign';
	}

	public function renderForgotPassword()
	{
		$this->getTemplate()->pageClass = 'forgot-password';
	}

	/**
	 * @var string $email
	 * @var string $hash
	 */
	public function actionConfirmMail($email, $hash)
	{
		if ($user = $this->clientService->verifyRegisterEmail($email, $hash)) {
			$this->mailConfirmed = TRUE;

			$identity = new Identity($user->login, 'client', ['client' => $user]);
			$this->user->login($identity);
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
		$this->flashMessage('Byl jste odhlášen.');
		$this->redirect('in');
	}

	/**
	 * @return SignInControl
	 */
	public function createComponentSignInForm() : SignInControl
	{
		return $this->signInForm->create($this->config);
	}

	/**
	 * @return ForgotPasswordControl
	 */
	protected function createComponentForgotPasswordForm() : ForgotPasswordControl
	{
		return $this->lostPasswordFactory->create();
	}

	/**
	 * @return ChooseNewPasswordControl
	 */
	protected function createComponentChooseNewPassword() : ChooseNewPasswordControl
	{
		return $this->chooseNewPasswordFactory->create();
	}

}
