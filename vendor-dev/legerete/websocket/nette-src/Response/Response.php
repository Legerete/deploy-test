<?php

/**
 * @copyright   Copyright (c) 2016 legerete.cz <hello@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\WebSocket
 */

namespace Legerete\WebSocket\Response;

use Nette\Http\Response as NetteResponse;
use Nette\Utils\Json;


/**
 * HttpResponse class.
 */
class Response extends NetteResponse
{

	/**
	 * Redirects to a new URL. Note: call exit() after it.
	 * @param  string $url URL
	 * @param  int $code HTTP code
	 * @return void
	 */
	public function redirect($url, $code = self::S302_FOUND)
	{
		echo Json::encode(['redirect' => $url, 'code' => $code]);
	}
}
