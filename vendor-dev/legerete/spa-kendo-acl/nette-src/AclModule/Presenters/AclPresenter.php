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
		$roles = $this->getHttpRequest()->getPost('models');
		$createdRoles = $this->modelService->createRoles($roles, true);
		$this->sendJson($createdRoles);
	}

	public function handleRead($ignore = null)
	{
		$roles = $this->modelService->readRolesWithResources((int) $ignore);
		$this->sendJson($roles);
	}

	public function handleUpdate()
	{
		$roles = $this->getHttpRequest()->getPost('models');
		$updatedRoles = $this->modelService->updateRoles($roles, true);
		$this->sendJson($updatedRoles);
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
