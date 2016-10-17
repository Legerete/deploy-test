<?php

	namespace Legerete\UserSignInModule\Components;

	use Kdyby\Doctrine\EntityManager;
	use Nette\Localization\ITranslator;
	use Tracy\ILogger;
	use Ublaboo\Mailing\MailFactory;

	/**
	 * ForgotPasswordFactory
	 * @author Petr Besir Horáček <sirbesir@gmail.com>
	 */
	class ForgotPasswordFactory extends \Nette\Application\UI\Control
	{
		/**
		 * @var EntityManager
		 */
		private $em;

		/**
		 * @var IMailService
		 */
		private $mailService;

		/**
		 * @type ILogger
		 */
		private $logger;

		/**
		 * @var ITranslator
		 */
		private $translator;

		/**
		 * ForgotPasswordFactory constructor.
		 *
		 * @param EntityManager $em
		 * @param MailFactory $mailService
		 * @param ILogger $logger
		 * @param ITranslator $translator
		 */
		public function __construct(EntityManager $em, MailFactory $mailService, ILogger $logger, ITranslator $translator)
		{
			parent::__construct();

			$this->em = $em;
			$this->mailService = $mailService;
			$this->logger = $logger;
			$this->translator = $translator;
		}

		/**
		 * @return ForgotPasswordControl
		 */
		public function create() : ForgotPasswordControl
		{
			return new ForgotPasswordControl($this->em, $this->mailService, $this->logger, $this->translator);
		}
	}
