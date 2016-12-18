<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\SpaKendoScheduler
 */

namespace Legerete\Spa\KendoIm\ImModule\Presenters;

use Legerete\Security\Presenters\SecuredPresenter;
use Legerete\Spa\KendoIm\Model\ImResponse;
use Legerete\Spa\KendoIm\Model\Service\ImModelService;
use Tracy\ILogger;
use Ublaboo\ImageStorage\ImageStorage;

/**
 * Class ImPresenter
 * @resource LeSpaIm:Im:Im
 * @privileges show
 */
class ImPresenter extends SecuredPresenter
{
	/**
	 * @var ImModelService
	 * @inject
	 */
	public $modelService;

	/**
	 * @var ILogger
	 * @inject
	 */
	public $logger;

	/**
	 * @var ImageStorage
	 * @inject
	 */
	public $imageStorage;

	public function __construct()
	{
		parent::__construct();
	}

	public function checkRequirements($element)
	{
		parent::checkRequirements($element);
	}

	public function startup()
	{
		parent::startup();
		$this->getTemplate()->imageStorage = $this->imageStorage;
	}

	/**
	 * @privileges show
	 */
	public function renderDefault()
	{
		$this->getHttpResponse()->setCode(404);
		$this->sendJson([
			'status' => 'error',
			'error' => 'Unsupported request.'
		]);
	}

	/**
	 * @privileges manage|create
	 */
	public function handleCreate()
	{
		$data = $this->getHttpRequest()->getPost();

		$data = [
			'pages' => [
				'dajlskdj',
				'dasjkdjhdkas',
				'ydfysf adf sydf 9ysdf yasdfasdfhhdfaysfd98ysfd98aysf'
			]
		];

		if (!isset($data['pages'])) {
			$this->sendJson(
				(new ImResponse)
					->setError(TRUE)
					->setMessage('Pages missing')
			);
		}

		$this->sendJson($this->modelService->createInformationMemorandum($data));
	}

	/**
	 * @privileges manage|readAll|readMy
	 * @param null $id
	 **/
	public function handleRead($id = NULL)
	{
		$id = 7;
		$jsonResponse = new ImResponse;

		if (empty($id)) {
			$this->sendJson(
				$jsonResponse->setError(FALSE)->setMessage('Missing ID of information memorandum')->toArray()
			);
		}

		$this->sendJson($this->modelService->readInformationMemorandum($id));
	}

	/**
	 * @privileges manage|update
	 */
	public function handleUpdate()
	{
		$data = $this->getHttpRequest()->getPost();
		if (!isset($data['id'])) {
			$this->sendJson(
				(new ImResponse)
					->setError(TRUE)
					->setMessage('ID missing')
			);
		}
		if (!isset($data['pages'])) {
			$this->sendJson(
				(new ImResponse)
					->setError(TRUE)
					->setMessage('Pages missing')
			);
		}

		$this->sendJson($this->modelService->updateInformationMemorandum($data));
	}

	/**
	 * @privileges manage|destroy
	 * @param NULL|int $id
	 */
	public function handleDestroy($id = NULL)
	{
		$jsonResponse = new ImResponse;

		if (empty($id)) {
			$this->sendJson(
				$jsonResponse->setError(FALSE)->setMessage('Missing ID of information memorandum')->toArray()
			);
		}

		$this->sendJson($this->modelService->destroyInformationMemorandum((int)$id));
	}

	public function handleReadAvailablePages()
	{
		$this->sendJson([
			[
				'name' => 'foo',
				'id' => 1
			],
			[
				'name' => 'bar',
				'id' => 2
			],
			[
				'name' => 'baz',
				'id' => 3
			],
			[
				'name' => 'boo',
				'id' => 4
			],
			[
				'name' => 'doo',
				'id' => 5
			],
			[
				'name' => 'doo',
				'id' => 5
			],
		]);
	}
}
