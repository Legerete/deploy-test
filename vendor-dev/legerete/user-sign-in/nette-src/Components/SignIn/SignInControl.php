<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\SignInExtension
 */

namespace Legerete\UserModule\Components\SignIn;

use Kdyby\Doctrine\EntityManager;
use Nette\Localization\ITranslator;
use Legerete\UIForm\FormFactory;
use Nette\Application\UI\Form;
use Nette\Security\Identity;
use Nette\Security\Passwords;
use Nette\Security\User;
use Nette\Utils\Arrays;
use Tracy\ILogger;
use Ublaboo\Mailing\MailFactory;

/**
 * Renderable component with Sign in form.
 */
class SignInControl extends \Nette\Application\UI\Control
{

	/**
	 * @var FormFactory
	 */
	private $formFactory;

	/**
	 * @var EntityManager
	 */
	private $em;

	/**
	 * @type ILogger
	 */
	private $logger;

	/**
	 * @var User $user
	 */
	private $user;

	/**
	 * @var ITranslator
	 */
	private $t;

	/**
	 * @var Form
	 */
	private $form;

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
	];

	/**
	 * SignInControl constructor.
	 *
	 * @param array $config
	 * @param FormFactory $formFactory
	 * @param EntityManager $em
	 * @param ILogger $logger
	 * @param User $user
	 * @param ITranslator $translator
	 */
	public function __construct(array $config, FormFactory $formFactory, EntityManager $em, ILogger $logger, User $user, ITranslator $translator)
	{
		parent::__construct();

		$this->config = $config;
		$this->formFactory = $formFactory;
		$this->em = $em;
		$this->logger = $logger;
		$this->user = $user;
		$this->t = $translator;

		$this->form = $this->formFactory->create();
	}

	/**
	 * Render setup
	 */
	public function render()
	{
		$this->getTemplate()->setFile(__DIR__ . '/templates/SignInPage.latte');
		$this->getTemplate()->allowForgotPassword = Arrays::get($this->config, 'allowForgotPassword', TRUE);
		$this->getTemplate()->render();
	}

	/**
	 * @return Form
	 */
	public function createComponentSignInForm() : Form
	{
		// E-mail
		$this->form->addText('login', $this->t->translate('sign.in.form.inputEmail'))->addRule(Form::EMAIL,
			$this->t->translate('sign.in.form.error.fillEmailInCorrectFormat'))->setRequired($this->t->translate('sign.in.form.error.emailNotFilled'));

		// Password
		$this->form->addPassword('password')->setRequired($this->t->translate('sign.in.form.error.passwordNotFilled'));

		// Submit
		$this->form->addSubmit('submit', $this->t->translate('sign.in.form.buttonSignIn'));

		// Events
		$this->form->onSuccess[] = [$this, 'processSignIn'];

		return $this->form;
	}

	/**
	 * @param Form $form
	 */
	public function processSignIn(Form $form)
	{
		$values = $form->getValues();
		$user = $this->clientRepository()->findOneBy(['login' => $values->address]);

		if (! $user || ! Passwords::verify($values->password, $user->password) || $user->isDel()) {
			$this->flashMessage('Neplatné přihlašovací údaje. <a href="' . $this->getPresenter()->link(':Public:Users:LostPassword:') . '">Zapoměli jste heslo</a>?');
			$this->redrawControl('flashes');

			$this->onLogInFailed();
		} else {
			$identity = new Identity($user->login, 'client', ['client' => $user]);
			$this->user->login($identity);

			$this->onLogInSuccess();
		}
	}

	/**
	 * @param bool|string $link
	 */
	public function setForgotPasswordLink($link = FALSE)
	{
		$this->forgotPasswordLink = $link;
	}

	/**
	 * @return \Kdyby\Doctrine\EntityRepository
	 */
	private function clientRepository()
	{
		return $this->em->getRepository('\App\Entity\Client');
	}

}
