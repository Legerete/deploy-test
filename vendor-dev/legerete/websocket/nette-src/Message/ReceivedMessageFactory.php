<?php

/**
 * @copyright   Copyright (c) 2016 legerete.cz <hello@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\WebSocket
 */

namespace Legerete\WebSocket\Message;


use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Tracy\ILogger;

class ReceivedMessageFactory
{
	/**
	 * @var ILogger
	 */
	private $logger;

	/**
	 * ReceivedMessageFactory constructor.
	 *
	 * @param ILogger $logger
	 */
	public function __construct(ILogger $logger)
	{
		$this->logger = $logger;
	}

	/**
	 * @param string $message JSON message
	 *
	 * @return ReceivedMessage
	 */
	public function create(string $message) : ReceivedMessage
	{
		try {
			$message = Json::decode($message);
			\Tracy\Debugger::log(__METHOD__);
			\Tracy\Debugger::log($message);
		} catch (JsonException $e) {
		    $this->logger->log($e);
		}

		return new ReceivedMessage($message);
	}
}