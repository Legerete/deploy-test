<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\SpaKendoScheduler
 */

namespace Legerete\Spa\KendoIm\ImModule\Presenters;

use Legerete\Security\Presenters\SecuredPresenter;
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
	}

	/**
	 * @privileges manage|readAll|readMy
	 * @param null $id
	 **/
	public function handleRead($id = null)
	{
		$ims = [];

		$this->sendJson($ims);
	}

	/**
	 * @privileges manage|update
	 */
	public function handleUpdate()
	{
		$data = $this->getHttpRequest()->getPost();
	}

	/**
	 * @privileges manage|destroy
	 */
	public function handleDestroy()
	{
		$data = $this->getHttpRequest()->getPost();
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
