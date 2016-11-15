<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\User
 */

namespace Legerete\User\EventListener;


use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Events\Subscriber;
use Legerete\User\Mail\UserCreatedMail;
use Nette\Object;
use Ublaboo\Mailing\MailFactory;

class UserCreatedListener extends Object implements Subscriber
{

	/**
	 * @var EntityManager
	 */
	private $em;

	/**
	 * @var MailFactory
	 */
	private $mailFactory;

	/**
	 * UserCreatedListener constructor.
	 *
	 * @param EntityManager $em
	 */
	public function __construct(EntityManager $em, MailFactory $mailFactory)
	{
		$this->em = $em;
		$this->mailFactory = $mailFactory;
	}

	public function getSubscribedEvents()
	{
		return [
			Events::postPersist => 'sendRegisterEmail'
		];

	}

	public function sendRegisterEmail(LifecycleEventArgs $lifecycleEventArgs)
	{
		$entity = $lifecycleEventArgs->getEntity();

		$mail = $this->mailFactory->createByType(UserCreatedMail::class, ['userEntity' => $entity]);
		$mail->send();
	}
}