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

	/**
	 * If is render method called without parameter [do] (handle method),
	 * then will send info about unsuported request.
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
	 * Process create roles, expected models[] in post
	 * $models = [
	 * 		0 => [
	 * 			'title' => 'Foo Role Name',
	 * 			'resources' => [
	 * 				'LeSpaAclAclAcl' => [
	 * 					'show' => 'false',
	 * 					'create' => 'true',
	 * 					'readAll' => 'false',
	 * 					'update' => 'false',
	 * 					'destroy' => 'false',
	 * 				],
	 * 			],
	 * 		],
	 * ]
	 */
	public function handleCreate()
	{
		$roles = $this->getHttpRequest()->getPost('models');
		$createdRoles = $this->modelService->createRoles($roles, true);
		$this->sendJson($createdRoles);
	}

	/**
	 * Read roles info
	 * @param null|integer $ignore If is set, query will ignore role with id [$ignore]
	 */
	public function handleRead($ignore = null)
	{
		$roles = $this->modelService->readRolesWithResources((int) $ignore);
		$this->sendJson($roles);
	}

	/**
	 * Process update roles, expected models[] in post
	 * $models = [
	 * 		0 => [
	 * 			'id' => '1', // unMutable
	 * 			'title' => 'Foo Role Name',
	 * 			'resources' => [
	 * 				'LeSpaAclAclAcl' => [
	 * 					'show' => 'false',
	 * 					'create' => 'true',
	 * 					'readAll' => 'false',
	 * 					'update' => 'false',
	 * 					'destroy' => 'false',
	 * 				],
	 * 			],
	 * 		],
	 * ]
	 */
	public function handleUpdate()
	{
		$roles = $this->getHttpRequest()->getPost('models');
		$updatedRoles = $this->modelService->updateRoles($roles, true);
		$this->sendJson($updatedRoles);
	}

	/**
	 * Process destroy of role
	 */
	public function handleDestroy()
	{
	}
}
