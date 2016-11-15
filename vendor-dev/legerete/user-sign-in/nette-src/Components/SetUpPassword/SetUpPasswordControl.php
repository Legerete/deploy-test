<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\SignInExtension
 */

namespace Legerete\UserSignInModule\Components\SetUpPassword;

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
 * @method onSuccess(ArrayHash $values)
 * @method onFailure(ArrayHash $values)
 */
class SetUpPasswordControl extends UI\Control
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
	 * @var callback $onSuccess
	 */
	public $onSuccess = [];

	/**
	 * @var callback $onFailure
	 */
	public $onFailure = [];

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
		$this->getTemplate()->setFile(__DIR__ . $ds . 'templates' . $ds . 'SetUpPassword.latte');
		$this->getTemplate()->render();
	}

	/**
	 * @return Form
	 */
	public function createComponentSetUpPasswordForm() : Form
	{
		$form = $this->formFactory->create();

		$form->addPassword('password', $this->translator->translate('sign.setupPassword.form.password'))
			->setRequired($this->translator->translate('sign.setupPassword.form.error.passwordNotFilled'));

		$form->addPassword('passwordRe', $this->translator->translate('sign.setupPassword.form.passwordRe'))
			->addCondition(Form::FILLED)
				->addRule(Form::EQUAL, $this->translator->translate('sign.setupPassword.form.error.passwordNotEquals'), $form['password'])
			->setRequired($this->translator->translate('sign.setupPassword.form.error.passwordReNotFilled'));

		$form->addSubmit('submit', $this->translator->translate('sign.setupPassword.form.buttonSetPassword'));
		$form->onSuccess[] = [$this, 'processPassword'];
		$form->onError[] = [$this, 'onError'];

		return $form;
	}

	public function onError(Form $form, ArrayHash $values)
	{
		$this->redrawControl('errors');
		$this->onFailure($values);
	}

	/**
	 * @param Form $form
	 * @param ArrayHash $values
	 */
	public function processPassword(Form $form, ArrayHash $values)
	{
		$this->onSuccess($values);
	}
}
