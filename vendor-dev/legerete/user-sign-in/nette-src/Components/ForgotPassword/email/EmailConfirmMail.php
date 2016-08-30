<?php

	namespace Legerete\Mailing;

	use Nette, Ublaboo\Mailing\Mail, Ublaboo\Mailing\IComposableMail;

	class ResetPasswordConfirmMail extends Mail implements IComposableMail
	{

		/**
		 * There you will always have your mail addresses from configuration file
		 * @var array
		 */
		protected $mails;


		public function compose(Nette\Mail\Message $message, $params = NULL)
		{
			$detailLink = $this->linkGenerator->link('Public:Users:LostPassword:createNewPassword',
				['email' => $params['email'], 'hash' => $params['hash']]);
			$this->args += ['confirmLink' => $detailLink];

			$this->setTemplateFile(realpath(__DIR__ . '/default.latte'));

			$message->setFrom($this->mails['defaultSender']);
			$message->addTo($params['email']);
		}

	}
