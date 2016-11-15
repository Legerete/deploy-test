<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\SignInExtension
 */

namespace Legerete\UserSignInOtpModule\Presenters;

use Legerete\Security\Presenters\SecuredPresenter;
use Legerete\UserSignInOtp\Components\SignInOtp\SignInOtpControl;
use Legerete\UserSignInOtp\Components\SignInOtp\SignInOtpFactory;

/**
 * Sign in/out presenter.
 */
class SignOtpPresenter extends SecuredPresenter
{
	/**
	 * @var SignInOtpFactory $signInOtpFormFactory
	 */
	private $signInOtpFormFactory;

	/**
	 * @var array
	 */
	private $config;

	/**
	 * @var bool $mailConfirmed
	 */
	private $mailConfirmed = FALSE;

	public function __construct(array $config = [], SignInOtpFactory $signInFactory)
	{
		parent::__construct();

//		$this->mailConfirmed = FALSE;
		$this->config = $config;
		$this->signInOtpFormFactory = $signInFactory;
	}

	public function renderIn()
	{
		$this->getTemplate()->pageClass = 'sign';
	}

	/**
	 * @return SignInOtpControl
	 */
	public function createComponentSignInOtpForm() : SignInOtpControl
	{
		return $this->signInOtpFormFactory->create($this->config);
	}
}
