<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\SignInExtension
 */

namespace Legerete\UserSignInModule\Presenters;

use Legerete\Presenters\SecuredPresenter,
	Nette\Security\Identity,
	Legerete\UserSignInModule\Components as Components;

/**
 * Sign in/out presenter.
 * @author Petr Besir Horacek <sirbesir@gmail.com>
 */
class SignPresenter extends SecuredPresenter
{
	private
		/** @var Components\SignIn\SignInFactory */
		$signInFormFactory,

		/** @var Components\ForgotPasswordFactory */
		$forgotPasswordFactory,

		/** @var Components\ChooseNewPasswordFactory */
		$chooseNewPasswordFactory,

		/** @var bool $mailConfirmed */
		$mailConfirmed,

		/** @var array */
		$config;

	public function __construct(array $config = [],
								Components\SignIn\SignInFactory $signInFactory,
								Components\ForgotPasswordFactory $forgotPasswordFactory,
								Components\ChooseNewPasswordFactory $chooseNewPasswordFactory)
	{
		parent::__construct();

		$this->mailConfirmed = FALSE;
		$this->config = $config;
		$this->signInFormFactory = $signInFactory;
		$this->forgotPasswordFactory = $forgotPasswordFactory;
		$this->chooseNewPasswordFactory = $chooseNewPasswordFactory;
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
	 * @TODO implement this...
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
	 * @TODO implement translation
	 */
	public function actionOut()
	{
		$this->getUser()->logout();
		$this->flashMessage('Byl jste odhlášen.');
		$this->redirect('in');
	}

	/**
	 * @return Components\SignIn\SignInControl
	 */
	public function createComponentSignInForm() : Components\SignIn\SignInControl
	{
		return $this->signInFormFactory->create($this->config);
	}

	/**
	 * @return Components\ForgotPasswordControl
	 */
	protected function createComponentForgotPasswordForm() : Components\ForgotPasswordControl
	{
		return $this->forgotPasswordFactory->create();
	}

	/**
	 * @return Components\ChooseNewPasswordControl
	 */
	protected function createComponentChooseNewPassword() : Components\ChooseNewPasswordControl
	{
		return $this->chooseNewPasswordFactory->create();
	}

}
