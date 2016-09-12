<?php

namespace Legerete\Websocket\Request;


use Nette\Http\RequestFactory;
use Nette\SmartObject;
use Nette\Http\UrlScript;
use Nette\Http\Url;
use Nette\Utils\Strings;

class WebSocketRequestFactory extends RequestFactory
{
	use SmartObject;

	/**
	 * @var Url
	 */
	private $url;

	public $request;

	private function createUrl()
	{
		$this->url = new UrlScript;
	}

	public function setUrl($url)
	{
		$this->url = $url;
		return $this;
	}

	public function createHttpRequest()
	{
		$uri = new UrlScript($this->url);
		$uri->scheme = 'http';
		$uri->port = 8005;
		$uri->host = 'localhost';
		$uri->canonicalize();
		$uri->path = Strings ::fixEncoding($uri->path);
		$uri->scriptPath = '/';

		$headers = [
			// Provide messages as Ajax request
			strtolower('X-Requested-With') => 'XMLHttpRequest'
		];

		/**
		 * @todo doplnit $_POST
		 * @todo doplnit $_FILES
		 * @todo doplnit $_COOKIES
		 */
		return new \Nette\Http\Request($uri, NULL, [], [], [], $headers,
			'GET', NULL, NULL
		);
	}
}