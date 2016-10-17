<?php

	namespace Legerete\UserSignInModule\Components;

	use App\Entity\Client;
	use Kdyby\Doctrine\EntityManager;
	use Nette\Application\UI\Form;
	use Tracy\ILogger;
	use Ublaboo\Mailing\MailFactory;

	/**
	 * Menu
	 * @author Petr Besir Horáček <sirbesir@gmail.com>
	 */
	class ChooseNewPasswordControl extends \Nette\Application\UI\Control
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
		 * @type ILogger
		 */
		private $logger;

		/**
		 * @var string $email
		 */
		private $email;

		/**
		 * @var string $hash
		 */
		private $hash;

		/**
		 * @var bool
		 */
		private $confirm;

		/**
		 * ChooseNewPassword constructor.
		 *
		 * @param EntityManager $em
		 * @param ILogger $logger
		 */
		public function __construct(EntityManager $em, ILogger $logger)
		{
			$this->form = new Form();

			$this->em = $em;
			$this->logger = $logger;
		}

		/**
		 * Render setup
		 * @see Nette\Application\Control#render()
		 */
		public function render($email = NULL, $hash = NULL)
		{
			if (!$this->confirm) {
				$this->email = $email;
				$this->hash = $hash;

				$this->getTemplate()->setFile(__DIR__ . '/templates/ChooseNewPassword.latte');
			} else {
				$this->getTemplate()->setFile(__DIR__ . '/templates/Confirm.latte');
			}


			$this->getTemplate()->render();
		}

		/**
		 * @return Form
		 */
		public function createComponentChooseNewPasswordForm()
		{
			$this->form->addPassword('password')->addRule(Form::MIN_LENGTH,
					'Heslo musí mít alespoň %d znaků. Nezapomeňte, chráníte své údaje.', 6);
			$this->form->addPassword('passwordRe')->addCondition(Form::FILLED,
					$this->form['password'])->addRule(Form::EQUAL, 'Hesla se musí shodovat.', $this->form['password']);

			$this->form->addHidden('address', $this->email);
			$this->form->addHidden('hash', $this->hash);

			$this->form->addSubmit('submit', 'Odeslat');
			$this->form->onSuccess[] = [$this, 'processChooseNewPassword'];

			return $this->form;
		}

		/**
		 * @param Form $form
		 */
		public function processChooseNewPassword(Form $form)
		{
			$values = $form->getValues();
			$client = $this->getClient($values->address, $values->hash);
			$this->redrawControl();

			if (!$client) {
				$this->flashMessage('Odkaz pro obnovení hesla je neplatný.', 'danger');
				return;
			} elseif ($client->getPasswordResetHashValidTo() < new \DateTime()) {
				$this->flashMessage('Odkaz pro obnovení hesla vypršel. Zažádat o nový si můžete na adrese pro <a href="'.$this->getPresenter()->link(':Public:Users:LostPassword:default').'">reset hesla</a>.', 'danger');
				return;
			}

			$client->setPassword($values->password)->setPasswordResetHash(NULL)->setPasswordResetHashValidTo(NULL);
			$this->em->flush();

			$this->confirm = TRUE;
		}

		/**
		 * @var string $email
		 * @var string $hash
		 *
		 * @return Client
		 * @throws \Doctrine\ORM\NonUniqueResultException
		 */
		private function getClient($email, $hash)
		{
			return $this->clientRepository()->findOneBy(['login' => $email, 'passwordResetHash' => $hash]);
		}

		/**
		 * @return \Kdyby\Doctrine\EntityRepository
		 */
		private function clientRepository()
		{
			return $this->em->getRepository('\App\Entity\Client');
		}

	}
