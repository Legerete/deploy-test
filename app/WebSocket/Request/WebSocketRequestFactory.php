<?php

namespace App\WebSocket\Request;


use Nette\Http\RequestFactory;
use Nette\SmartObject;
use Nette\Http\UrlScript;
use Nette\Http\Url;

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
		// DETECTS URI, base path and script path of the request.
//		if($this->url === null)
//		{
//			$this->createUrl();
//		}
//		$url = $this->url;
		$uri = new UrlScript($this->url);
//		\Tracy\Debugger::log($uri);
		// legerete.user.sign/in
		$uri->scheme = 'http://';
//		$uri->port = Url::$defaultPorts['http'];
//		$uri->host = 'localhost:8005';
//		$uri->path = '/';
		$uri->canonicalize();
//		$uri->path = Strings ::fixEncoding($uri->path);
//		$uri->scriptPath = '/';
//		$_SERVER = [];

//		// GET, POST, COOKIE
//		$useFilter = (!in_array(ini_get('filter.default'), ['', 'unsafe_raw']) || ini_get('filter.default_flags'));
//
//		parse_str($url->query, $query);
//		if (!$query) {
//			$query = $useFilter ? filter_input_array(INPUT_GET, FILTER_UNSAFE_RAW) : (empty($_GET) ? [] : $_GET);
//		}
//		//$post = $useFilter ? filter_input_array(INPUT_POST, FILTER_UNSAFE_RAW) : (empty($_POST) ? array() : $_POST);
//		$cookies = $useFilter ? filter_input_array(INPUT_COOKIE, FILTER_UNSAFE_RAW) : (empty($_COOKIE) ? [] : $_COOKIE);
//
//		$files = [];
//		$headers = [
//			'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36',
//			strtolower('X-Requested-With') => 'XMLHttpRequest'
//		];
//		$post = [];


		return new \Nette\Http\Request($uri, NULL, [], [], [], [],
			'GET', NULL, NULL
		);
	}
}