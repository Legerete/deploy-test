<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\SignInExtension
 */

namespace Legerete\UserSignInOtp\Presenters;

use Legerete\Presenters\SecuredPresenter;
use Legerete\UserSignInOtp\Components\SignInOtp\SignInOtpControl;
use Legerete\UserSignInOtp\Components\SignInOtp\SignInOtpFactory;

/**
 * Sign in/out presenter.
 */
class SignOtpPresenter extends SecuredPresenter
{
	/**
	 * @var SignInOtpFactory
	 */
	private $signInFormFactory;

	/**
	 * @var array
	 */
	private $config;

	public function __construct(array $config = [], SignInOtpFactory $signInFactory)
	{
		parent::__construct();

		$this->mailConfirmed = FALSE;
		$this->config = $config;
		$this->signInFormFactory = $signInFactory;
	}

	public function renderIn()
	{
		$this->getTemplate()->pageClass = 'sign';
	}

	/**
	 * @return SignInOtpControl
	 */
	public function createComponentSignInForm() : SignInOtpControl
	{
		return $this->signInFormFactory->create($this->config);
	}
}
