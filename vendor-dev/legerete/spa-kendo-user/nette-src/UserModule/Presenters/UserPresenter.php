<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\SpaKendoScheduler
 */

namespace Legerete\Spa\KendoUser\UserModule\Presenters;

use Legerete\Security\Presenters\SecuredPresenter;
use Legerete\Spa\KendoUser\Model\Service\UserModelService;
use Legerete\User\Model\Entity\UserEntity;
use Tracy\ILogger;
use Ublaboo\ImageStorage\ImageStorage;

/**
 * Class UserPresenter
 * @package Legerete\Spa\KendoUser\UserModule\Presenters
 * @resource LeSpaUser:User:User
 * @privileges show
 */
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

		$userEntity = $this->modelService->createNewUser($data);

		$this->sendJson($this->modelService->readUser($userEntity->getId()));
	}

	/**
	 * @privileges manage|readAll|readMy
	 * @param null $id
	 **/
	public function handleRead($id = null)
	{
		if (!$id) {
			$users = $this->modelService->readUsers();
			array_walk($users, function(&$user) {
				$this->getUserAvatars($user);
			});
		} else {
			if ($this->getUser()->isAllowed('LeSpaUser:User:User', 'manage') || $this->getUser()->isAllowed('LeSpaUser:User:User', 'readAll') || $this->getUser()->getId() == $id) {
				$users = $this->modelService->readUser($id);
				$this->getUserAvatars($users);
			} else {
				$this->sendForbiddenResponse();
			}
		}

		$this->sendJson($users);
	}

	/**
	 * @privileges manage|update
	 */
	public function handleUpdate()
	{
		$data = $this->getHttpRequest()->getPost();

		$admin = FALSE;
		if (! $admin = $this->getUser()->isAllowed('LeSpaUser:User:User', 'manage')) {
			if ($data['id'] != $this->getUser()->getId()) {
				$this->sendForbiddenResponse();
			}
		}

		try {
			$user = $this->modelService->updateUser($data, $admin);
			$this->getUserAvatars($user);
			$this->sendJson($user);
		} catch (\InvalidArgumentException $e) {
		    $this->logger->log($e);
			$this->sendInvalidDataResponse();
		}
	}

	/**
	 * @privileges manage|destroy
	 */
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

	/**
	 * @privileges update|manage
	 */
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

	public function handleIsEmailAvailable($email)
	{
		$response = [
			'available' => $this->modelService->isEmailAvailable($email),
		];
		$this->sendJson($response);
	}

	public function handleIsUsernameAvailable($username)
	{
		$response = [
			'available' => $this->modelService->isUsernameAvailable($username),
		];
		$this->sendJson($response);
	}

	/**
	 * @privileges manage|create|update
	 */
	public function handleReadAvailableRoles()
	{
		$this->sendJson($this->modelService->getAvailableRoles());
	}

	/**
	 * @param $id
	 * @privileges manage
	 */
	public function handleBlockUser($id)
	{
		$this->sendJson($this->modelService->blockUser($id));
	}

	/**
	 * @param $id
	 * @privileges manage
	 */
	public function handleUnblockUser($id)
	{
		$this->sendJson($this->modelService->unblockUser($id));
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
