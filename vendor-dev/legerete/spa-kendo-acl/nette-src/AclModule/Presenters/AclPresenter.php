<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\SpaKendoScheduler
 */

namespace Legerete\Spa\KendoAcl\AclModule\Presenters;

use Legerete\Presenters\SecuredPresenter;
use Legerete\Spa\KendoAcl\Model\Service\AclModelService;
use Tracy\ILogger;
use Ublaboo\ImageStorage\ImageStorage;

class AclPresenter extends SecuredPresenter
{
	/**
	 * @var AclModelService
	 * @inject
	 */
	public $modelService;

	/**
	 * @var ILogger
	 * @inject
	 */
	public $logger;


	public function __construct(ImageStorage $imageStorage)
	{
		parent::__construct();

	}

	public function startup()
	{
		parent::startup();
	}

	public function renderDefault()
	{
		$this->getHttpResponse()->setCode(404);
		$this->sendJson([
			'status' => 'error',
			'error' => 'Unsupported request.'
		]);
	}

	public function handleCreate()
	{
//		$this->modelService->createRole($data);
	}

	public function handleRead($ignore = null)
	{
		$roles = $this->modelService->readRolesWithResources((int) $ignore);
		$this->sendJson($roles);
	}

	public function handleUpdate()
	{
	}

	public function handleDestroy()
	{
	}

	private function sendInvalidDataResponse()
	{
		$this->getHttpResponse()->setCode(400);
		$this->sendJson([
			'status' => 'error',
			'error' => 'Invalid user data.'
		]);
	}
}
