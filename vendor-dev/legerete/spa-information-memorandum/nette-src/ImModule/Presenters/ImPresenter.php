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
use Nette\Http\Response;
use Nette\Utils\FileSystem;
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

	/**
	 * @var string $pageLayoutPath
	 */
	private $pageLayoutPath;

	public function __construct(ImageStorage $imageStorage)
	{
		parent::__construct();

		$this->imageStorage = $imageStorage;
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
		$this->sendJson($this->modelService->readTemplates());
	}

	public function handleReadPageLayout()
	{
		$layoutName = $this->getHttpRequest()->getQuery('layout', FALSE);

		if (file_exists($this->pageLayoutPath . DIRECTORY_SEPARATOR . $layoutName)) {
			$layout = $this->createTemplate();
			$layout->setFile($this->pageLayoutPath . DIRECTORY_SEPARATOR . $layoutName);

			$this->sendJson([
				'status' => 'ok',
				'layout' => (string) $layout,
			]);
		} else {
			$this->getHttpResponse()->setCode(Response::S404_NOT_FOUND);
			$this->sendJson([
				'status' => 'error',
				'errorText' => 'Layout not found'
			]);
		}
	}

	/**
	 * @privileges update|manage
	 */
	public function handleUploadImage()
	{
		$file = $this->getHttpRequest()->getFile('im-image');
		$savedFile = $this->imageStorage->saveUpload($file, 'im');
		$bigImage = $this->imageStorage->fromIdentifier([$savedFile->identifier, '1112x2000', 'fit']);
		$this->sendJson([
			'original' => $bigImage->createLink(),
		]);
	}

	/**
	 * @var string $pageLayoutPath
	 */
	public function setPageLayoutPath($pageLayoutPath)
	{
		$this->pageLayoutPath = $pageLayoutPath;
		return $this;
	}
}
