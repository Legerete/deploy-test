<?php

/**
 * @copyright   Copyright (c) 2016 legerete.cz <hello@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\WebSocket
 */

namespace Legerete\WebSocket\Message;


class ReceivedMessage
{
	/**
	 * @var string $request
	 */
	private $request;

	/**
	 * @var array $post
	 */
	private $post = [];

	/**
	 * @var array $files
	 */
	private $files = [];

	/**
	 * @var array
	 */
	private $extraData = [];

	/**
	 * ReceivedMessage constructor.
	 *
	 * @param \stdClass $message
	 */
	public function __construct(\stdClass $message)
	{
		$originalMessage = clone $message;
		$this->fillProperties($originalMessage);

		return $this;
	}

	/**
	 * @param \stdClass $message
	 */
	private function fillProperties(\stdClass $message)
	{
		$properties = get_object_vars($message);

		foreach ($properties as $key => $property) {
			if (property_exists(self::class, $key)) {
				$this->$key = $property;
				unset($properties[$key]);
			}
		}

		if (count($properties)) {
			$this->extraData = $properties;
		}
	}

	public function getRequestUrl() : string
	{
		return $this->request;
	}

	/**
	 * @return array
	 */
	public function getPost(): array
	{
		return $this->post;
	}

	/**
	 * @return array
	 */
	public function getFiles(): array
	{
		return $this->files;
	}

	/**
	 * @return array
	 */
	public function getExtraData(): array
	{
		return $this->extraData;
	}


}