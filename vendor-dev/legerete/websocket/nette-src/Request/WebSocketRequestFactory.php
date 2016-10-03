<?php

/**
 * @copyright   Copyright (c) 2016 legerete.cz <hello@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\WebSocket
 */

namespace Legerete\WebSocket\Request;


use Legerete\WebSocket\Message\ReceivedMessage;
use Nette\Http\Request;
use Nette\Http\RequestFactory;
use Nette\SmartObject;
use Nette\Http\UrlScript;
use Nette\Utils\Strings;

class WebSocketRequestFactory extends RequestFactory
{
	use SmartObject;

	/**
	 * @var ReceivedMessage
	 */
	private $message;

	/**
	 * @param ReceivedMessage $message
	 */
	public function setMessage(ReceivedMessage $message)
	{
		$this->message = $message;
	}

	/**
	 * @return \Nette\Http\Request
	 */
	public function createHttpRequest() : Request
	{
		if (!$this->message) {
			$uri = new UrlScript('');
		} else {
			$uri = new UrlScript($this->message->getRequestUrl());
		}

		$uri->canonicalize();
		$uri->path = Strings ::fixEncoding($uri->path);
		$uri->scriptPath = '/';

		$headers = [
			// Provide messages to application as Ajax request
			strtolower('X-Requested-With') => 'XMLHttpRequest'
		];

		return new \Nette\Http\Request($uri, NULL, $this->createPost(), $this->createFiles(), [], $headers,
			'GET', NULL, NULL
		);
	}

	/**
	 * @return array|mixed
	 */
	private function createPost() : array
	{
		if (!$this->message) {
			return [];
		} else {
			return filter_var_array($this->message->getPost(), FILTER_SANITIZE_ENCODED);
		}
	}

	/**
	 * @return array
	 */
	private function createFiles() : array
	{
//		if (!$this->message) {
			return [];
//		} else {
//			return $this->message->getFiles();
//		}
	}
}