<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\SignInExtension
 */

namespace Legerete\UserSignInModule\Components\SignInOtp;

use Nette\Application\UI\Form;
use Nette\Localization\ITranslator;
use Legerete\UIForm\FormFactory;
use Nette\Application\UI;
use Nette\Security;
use Nette\Utils\ArrayHash;
use OTPHP\TOTP;
use Tracy\ILogger;

/**
 * Renderable component with Sign in otp form.
 *
 * @method onVerifySuccess()
 * @method onVerifyFailed()
 */
class SignInOtpControl extends UI\Control
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
	 * @var ITranslator
	 */
	private $translator;

	/**
	 * @var callback $loggedInSuccess
	 */
	public $onVerifySuccess = [];

	/**
	 * @var callback $loggedInFailed
	 */
	public $onVerifyFailed = [];

	/**
	 * @var array
	 */
	public $config = [
	];

	/**
	 * SignInControl constructor.
	 * @param array $config
	 * @param FormFactory $formFactory
	 * @param ILogger $logger
	 * @param Security\User $user
	 * @param ITranslator $translator
	 */
	public function __construct(
		array $config,
		FormFactory $formFactory,
		ILogger $logger,
		Security\User $user,
		ITranslator $translator)
	{
		parent::__construct();

		$this->config = array_merge($this->config, $config);
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
		$this->getTemplate()->setFile(realpath(__DIR__ . $ds . 'templates') . $ds . 'SignInOtpPage.latte');
		$this->getTemplate()->render();
	}

	/**
	 * @return Form
	 */
	public function createComponentSignInOtpForm() : Form
	{
		$form = $this->formFactory->create();

		$form->addText('token', $this->translator->translate('sign.inOtp.form.inputToken'))
			->addRule(Form::NUMERIC,
				$this->translator->translate('sign.inOtp.form.error.badTokenFormat')
			)
			->setRequired($this->translator->translate('sign.inOtp.form.error.tokenNotFilled'));

		$form->addSubmit('submit', $this->translator->translate('sign.inOtp.form.buttonSignIn'));
		$form->onSuccess[] = [$this, 'processSignIn'];

		return $form;
	}

	/**
	 * @param Form $form
	 * @param ArrayHash $values
	 */
	public function processSignIn(Form $form, ArrayHash $values)
	{
		if ($this->getToken()->verify((string) $values->token)) {
			$this->user->getStorage()->setAuthenticated(TRUE);
			$this->onVerifySuccess();
		} else {
			$this->redrawControl('flashes');
			$this->flashMessage($this->translator->translate('sign.inOtp.form.error.invalidToken'));
		    $this->onVerifyFailed();
		}
	}

	/**
	 * @return TOTP
	 */
	private function getToken()
	{
		return new TOTP($this->user->getIdentity()->email, $this->user->getIdentity()->otp);
	}
}
