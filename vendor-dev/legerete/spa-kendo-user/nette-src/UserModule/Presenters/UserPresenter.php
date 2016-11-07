<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\SpaKendoScheduler
 */

namespace Legerete\Spa\KendoUser\UserModule\Presenters;

use Legerete\Presenters\SecuredPresenter;
use Legerete\Spa\KendoUser\Model\Service\UserModelService;
use Legerete\User\Model\Entity\UserEntity;
use Tracy\ILogger;
use Ublaboo\ImageStorage\ImageStorage;
use Ublaboo\ImageStorage\ImageStoragePresenterTrait;

class UserPresenter extends SecuredPresenter
{
	/**
	 * @var UserModelService
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

	public function startup()
	{
		parent::startup();
		$this->getTemplate()->imageStorage = $this->imageStorage;
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
		$data = $this->getHttpRequest()->getPost();

		$userEntity = $this->modelService->createNewUser($data);

		$this->sendJson($this->modelService->readUser($userEntity->getId()));
	}

	public function handleRead($id = null)
	{
		if (!$id) {
			$users = $this->modelService->readUsers();
			array_walk($users, function(&$user) {
				$this->getUserAvatars($user);
			});
		} else {
			$users = $this->modelService->readUser($id);
			$this->getUserAvatars($users);
		}

		$this->sendJson($users);
	}

	public function handleUpdate()
	{
		$data = $this->getHttpRequest()->getPost();

		try {
			$user = $this->modelService->updateUser($data);
			$this->getUserAvatars($user);
			$this->sendJson($user);
		} catch (\InvalidArgumentException $e) {
		    $this->logger->log($e);
			$this->sendInvalidDataResponse();
		}
	}

	public function handleDestroy()
	{
		$data = $this->getHttpRequest()->getPost();

		try {
			$this->modelService->destroyUser($data['id']);
			$this->sendJson(['status' => 'success']);
		} catch (\InvalidArgumentException $e) {
		    $this->logger->log($e);
			$this->sendInvalidDataResponse();
		}
	}

	public function handleAvatar()
	{
//		try {
			$file = $this->getHttpRequest()->getFile('user-avatar');
			$savedFile = $this->imageStorage->saveUpload($file, UserEntity::AVATAR_NAMESPACE);
			$bigImage = $this->imageStorage->fromIdentifier([$savedFile->identifier, UserEntity::AVATAR_DIMENSIONS_LARGE]);
			$this->sendJson([
				'big-image' => $bigImage->createLink(),
				'original' => $savedFile->identifier
			]);
//		} catch (\Exception $e) {
//		    $this->logger->log($e);
//		}
	}

	public function handleReadAvailableRoles()
	{
		$this->sendJson($this->modelService->getAvailableRoles());
	}

	private function getUserAvatars(&$user)
	{
		$user['avatarBig'] = $this->imageStorage->fromIdentifier([$user['avatar'], UserEntity::AVATAR_DIMENSIONS_LARGE])->createLink();
		$user['avatarSmall'] = $this->imageStorage->fromIdentifier([$user['avatar'], UserEntity::AVATAR_DIMENSIONS_SMALL])->createLink();
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
