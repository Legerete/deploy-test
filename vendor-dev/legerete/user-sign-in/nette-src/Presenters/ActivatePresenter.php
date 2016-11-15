<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\SignInExtension
 */

namespace Legerete\UserSignInModule\Presenters;

use Legerete\Security\IDatabaseAuthenticator;
use Legerete\Security\Presenters\SecuredPresenter;
use Legerete\User\Model\SuperClass\UserSuperClass;
use Legerete\UserSignInModule\Components\SetUpPassword\SetUpPasswordControl;
use Legerete\UserSignInModule\Components\SetUpPassword\SetUpPasswordFactory;
use Legerete\UserSignInModule\Components\SignInOtp\SignInOtpControl;
use Legerete\UserSignInModule\Components\SignInOtp\SignInOtpFactory;
use Legerete\UserSignInModule\Model\Service\UserModelService;
use Nette\Localization\ITranslator;
use Nette\Utils\ArrayHash;
use OTPHP\TOTP;


/**
 * Sign in/out presenter.
 * @author Petr Besir Horacek <sirbesir@gmail.com>
 * @resource LeSignIn:UserSignIn:Activate
 * @privileges show
 */
class ActivatePresenter extends SecuredPresenter
{
	/**
	 * @var bool $mailConfirmed
	 */
	private $mailConfirmed;

	/**
	 * @var SignInOtpFactory $signInOtpFormFactory
	 */
	private $signInOtpFormFactory;

	/**
	 * @var SetUpPasswordFactory $setUpPasswordFormFactory
	 */
	private $setUpPasswordFormFactory;

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
	 * @var UserSuperClass
	 */
	private $userEntity;

	/**
	 * @var UserModelService
	 */
	private $userModelService;

	private $step = 1;

	/**
	 * @var array
	 */
	private $config = [
		'enableOtp' => FALSE,
		'loginRedirectPage' => 'this',
	];

	/**
	 * ActivatePresenter constructor.
	 *
	 * @param array $config
	 * @param SignInOtpFactory $signInOtpFactory
	 * @param ITranslator $translator
	 * @param IDatabaseAuthenticator $authenticator
	 */
	public function __construct(
		array $config,
		SignInOtpFactory $signInOtpFactory,
		ITranslator $translator,
		IDatabaseAuthenticator $authenticator,
		UserModelService $userModelService,
		SetUpPasswordFactory $setUpPasswordFactory
	)
	{
		parent::__construct();

		$this->config = array_merge($this->config, $config);
		$this->signInOtpFormFactory = $signInOtpFactory;
		$this->translator = $translator;
		$this->authenticator = $authenticator;
		$this->userModelService = $userModelService;
		$this->setUpPasswordFormFactory = $setUpPasswordFactory;
		$this->mailConfirmed = FALSE;
	}

	public function actionDefault($userId, $token)
	{
		$this->userEntity = $this->userModelService->getUserEntityByIdAndActivationToken($userId, $token);
		if ($this->getUser()->isLoggedIn()) {
			$this->getUser()->logout(TRUE);
		}

		if (!$this->userEntity) {
			$this->setView('invalidActivationToken');
		}

		if (! $this->config['enableOtp']) {
			$this->authenticator->authenticate();
			$this->redirect($this->config['loginRedirectPage']);
		}
	}

	public function renderDefault($userId, $token)
	{
		if ($this->config['enableOtp']) {
			$this->getTemplate()->enableOtp = $this->config['enableOtp'];

			$otp = new TOTP($this->userEntity->getEmail(), $this->userEntity->getOtp());

			$this->getTemplate()->otpLink = $otp->getProvisioningUri();
			$this->getTemplate()->step = $this->step;
		}
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

	private function activateAccount()
	{
		$this->userModelService->activateAccount($this->userEntity->getId());
	}

	/* ********************************** Components ********************************** */

	/**
	 * @return SignInOtpControl
	 */
	public function createComponentSignInOtpForm() : SignInOtpControl
	{
		$component = $this->signInOtpFormFactory->create($this->config);

		$component->onVerifySuccess[] = function() {
			$this->activateAccount();
			$this->redirect($this->config['loginRedirectPage']);
		};

		return $component;
	}

	/**
	 * @return SetUpPasswordControl
	 */
	public function createComponentSetUpPassword() : SetUpPasswordControl
	{
		$component = $this->setUpPasswordFormFactory->create();

		$component->onSuccess[] = function (ArrayHash $values) {
			$this->userModelService->setUserPassword($this->userEntity->getId(), $values->password);
			$this->authenticator->authenticate($this->userEntity->getEmail(), $values->password, FALSE);
			$this->step = 2;
			$this->redrawControl('contentBox');
		};

		return $component;
	}
}
