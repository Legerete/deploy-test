<?php

	namespace Legerete\UserSignInModule\Components;

	use App\Entity\Client;
	use Kdyby\Doctrine\EntityManager;
	use Nette\Application\UI\Form;
	use Nette\Localization\ITranslator;
	use Tracy\ILogger;
	use Ublaboo\Mailing\MailFactory;

	/**
	 * Menu
	 * @author Petr Besir Horáček <sirbesir@gmail.com>
	 */
	class ForgotPasswordControl extends \Nette\Application\UI\Control
	{
		/**
		 * @var EntityManager
		 */
		private $em;

		/**
		 * @var Form
		 */
		private $form;

		/**
		 * @var MailFactory
		 */
		private $mailService;

		/**
		 * @type ILogger
		 */
		private $logger;

		/**
		 * @var bool
		 */
		private $confirm = FALSE;

		/**
		 * @var string
		 */
		private $forgotPasswordLink;

		/**
		 * @var ITranslator
		 */
		private $t;

		/**
		 * SignUp constructor.
		 *
		 * @param EntityManager $em
		 * @param MailFactory $mailService
		 * @param ILogger $logger
		 */
		public function __construct(EntityManager $em, MailFactory $mailService, ILogger $logger, ITranslator $translator)
		{
			$this->form = new Form();

			$this->em = $em;
			$this->mailService = $mailService;
			$this->logger = $logger;
			$this->t = $translator;
		}

		/**
		 * Render setup
		 * @see Nette\Application\Control#render()
		 */
		public function render()
		{
			if ($this->confirm === TRUE) {
				$this->getTemplate()->setFile(__DIR__ . '/templates/Confirm.latte');
			} elseif ($this->confirm === 'FAIL') {
				$this->getTemplate()->setFile(__DIR__ . '/templates/EmailFail.latte');
			} else {
				$this->getTemplate()->setFile(__DIR__ . '/templates/ResetRequest.latte');
				$this->getTemplate()->forgotPasswordLink = $this->forgotPasswordLink ? $this->getPresenter()->link($this->forgotPasswordLink) : FALSE;
			}
			$this->getTemplate()->render();
		}

		/**
		 * @return Form
		 */
		public function createComponentForgotPasswordForm()
		{
			$this->form->addText('login', 'E-mail')->addRule(Form::EMAIL,
				$this->t->translate('sign.forgotPassword.form.error.fillEmailInCorrectFormat'))->setRequired($this->t->translate('sign.forgotPassword.form.error.emailNotFilled'));

			$this->form->addSubmit('submit', $this->t->translate('sign.forgotPassword.form.buttonResetPassword'));
			$this->form->onSuccess[] = [$this, 'processLostPasswordForm'];

			return $this->form;
		}

		/**
		 * @param Form $form
		 */
		public function processLostPasswordForm(Form $form)
		{
			$values = $form->getValues();
			$client = $this->getClient($values->address);

			if (!$client) {
				$this->flashMessage('Účet s tímto e-mailem nebyl nalezen.', 'danger');
				$this->redrawControl('flashes');
				sleep(5);
				return;
			}

			$client->createResetPasswordHash();
			$this->em->flush();

			try {
				$mail = $this->mailService->createByType('App\Mailing\ResetPasswordConfirmMail',
					[
						'email' => $values->address,
						'hash' => $client->getPasswordResetHash(),
						'validTo' => $client->getPasswordResetHashValidTo(),

					]
				);
				$mail->send();
				$this->confirm = TRUE;
				$this->redrawControl('form');
			} catch (\Exception $e) {
				$this->confirm = 'FAIL';
				$this->logger->log($e);
			}

		}

		/**
		 * @param $email
		 *
		 * @return null|Client
		 * @throws \Doctrine\ORM\NonUniqueResultException
		 */
		private function getClient($email)
		{
			return $this->clientRepository()->findOneBy(['login' => $email]);
		}

		/**
		 * @return \Kdyby\Doctrine\EntityRepository
		 */
		private function clientRepository()
		{
			return $this->em->getRepository('\App\Entity\Client');
		}

	}
