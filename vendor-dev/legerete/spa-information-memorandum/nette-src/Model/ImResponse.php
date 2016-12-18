<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Jiří Švec <me@svecjiri.com>
 * @package     Legerete\SpaKendoIm
 */

namespace Legerete\Spa\KendoIm\Model;

use Legerete\Spa\KendoIm\Model\Entity\Page;
use Nette\SmartObject;

class ImResponse
{

	use SmartObject;

	private
		$error,
		$message,
		$data;

	public function __construct()
	{
		$this->error = FALSE;
		$this->message = '';
		$this->data = [
			'imId' => NULL,
			'pages' => []
		];
	}

	/**
	 * @return array
	 */
	public function toArray(): array
	{
		return [
			'error' => $this->error,
			'message' => $this->message,
			'data' => [
				'imId' => $this->data['imId'],
				'pages' => $this->data['pages']
			]
		];
	}

	/**
	 * @return bool
	 */
	public function isError(): bool
	{
		return $this->error;
	}

	/**
	 * @return string
	 */
	public function getMessage(): string
	{
		return $this->message;
	}

	/**
	 * @return mixed
	 */
	public function getIm()
	{
		return $this->data['imId'];
	}

	/**
	 * @return array
	 */
	public function getPages(): array
	{
		return $this->data['pages'];
	}

	/**
	 * @param bool $error
	 * @return ImResponse
	 */
	public function setError(bool $error): ImResponse
	{
		$this->error = $error;

		return $this;
	}

	/**
	 * @param string $message
	 * @return ImResponse
	 */
	public function setMessage(string $message): ImResponse
	{
		$this->message = $message;

		return $this;
	}

	/**
	 * @param int $id
	 * @return ImResponse
	 */
	public function setInformationMemorandumId(int $id): ImResponse
	{
		$this->data['imId'] = $id;

		return $this;
	}

	/**
	 * @param array $pages
	 * @return ImResponse
	 */
	public function setPages(array $pages): ImResponse
	{
		$this->data['pages'] = [];

		/** @var Page $page */
		foreach ($pages as $page) {
			$this->data['pages'][] = $page->getContent();

		}

		return $this;
	}

}
