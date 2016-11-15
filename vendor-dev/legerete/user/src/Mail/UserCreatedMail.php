<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\User
 */

namespace Legerete\User\Mail;

use Nette\Mail\Message;
use Nette\Utils\Arrays;
use Ublaboo\Mailing\IComposableMail;
use Ublaboo\Mailing\Mail;

class UserCreatedMail extends Mail implements IComposableMail
{

	protected $mails;

	/**
	 * @param Message $message
	 * @param array $params ['userEntity' => \Legerete\User\Model\SuperClass\UserSuperClass]
	 */
	public function compose(Message $message, $params = [])
	{
		$message->setFrom(Arrays::get($params, 'defaultSender', 'sender@not.set'));
		$message->addTo($params['userEntity']->email);

		$this->setTemplateFile(__DIR__ . '/templates/UserCreatedMail.latte');
	}

}