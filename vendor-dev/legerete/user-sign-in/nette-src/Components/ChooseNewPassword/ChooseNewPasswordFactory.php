<?php

	namespace Legerete\UserModule\Components;

	use Kdyby\Doctrine\EntityManager;
	use Tracy\ILogger;

	/**
	 * Menu
	 * @author Petr Besir Horáček <sirbesir@gmail.com>
	 */
	class ChooseNewPasswordFactory extends \Nette\Application\UI\Control
	{
		/**
		 * @var EntityManager
		 */
		private $em;

		/**
		 * @type ILogger
		 */
		private $logger;

		public function __construct(EntityManager $em, ILogger $logger)
		{
			$this->em = $em;
			$this->logger = $logger;
		}

		/**
		 * Render setup
		 */
		public function create()
		{
			return new ChooseNewPasswordControl($this->em, $this->logger);
		}
	}
