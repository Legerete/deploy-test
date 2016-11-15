<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\SpaKendoScheduler
 */

namespace Legerete\Spa\KendoScheduler\SchedulerModule\Presenters;

use Legerete\Security\Presenters\SecuredPresenter;
use Legerete\Spa\KendoScheduler\Model\Service\SchedulerModelService;

class SchedulerPresenter extends \Legerete\Security\Presenters\SecuredPresenter
{
	/**
	 * @var SchedulerModelService
	 * @inject
	 */
	public $modelService;

	public function handleCreate()
	{
		$events = $this->getHttpRequest()->getPost('models', '[]');

		$createdEvents = [];
		foreach ($events as $event) {
			$createdEvents += $this->modelService->createNewEvent($event);
		}

		$this->sendJson($createdEvents);
	}

	/**
	 * @param string $date Date for filtering results
	 * @param string $view
	 * @param string $schedulerAction
	 */
	public function handleRead($date = '', $view = '', $schedulerAction = '')
	{
		if (empty($date)) {
			$this->sendJson([]);
		}

		$events = $this->modelService->readEvents(new \DateTime($date), $view, $schedulerAction);

		$this->sendJson($events);
	}

	public function handleUpdate()
	{
		$events = $this->getHttpRequest()->getPost('models', '[]');

		$updatedEvents = [];
		foreach ($events as $event) {
			if (isset($event['id']))
			{
				$updatedEvents[] = $this->modelService->updateEvent($event);
			}
		}

		$this->sendJson($updatedEvents);
	}

	public function handleDestroy()
	{
		$events = $this->getHttpRequest()->getPost('models', '[]');

		foreach ($events as $event) {
			if (isset($event['id']))
			{
				$this->modelService->destroyEvent($event['id']);
			}
		}
	}

}
