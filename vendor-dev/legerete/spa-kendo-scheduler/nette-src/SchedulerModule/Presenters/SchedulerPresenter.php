<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\SpaKendoScheduler
 */

namespace Legerete\Spa\KendoScheduler\SchedulerModule\Presenters;

use Legerete\Presenters\SecuredPresenter;
use Legerete\Spa\KendoScheduler\Model\Service\SchedulerModelService;

/**
 * @author Petr Besir Horacek <sirbesir@gmail.com>
 * Sign in/out presenter.
 */
class SchedulerPresenter extends SecuredPresenter
{
	/**
	 * @var SchedulerModelService
	 * @inject
	 */
	public $modelService;

	public function handleCreate()
	{
		$events = $this->getHttpRequest()->getPost('models', []);

		foreach ($events as $event) {
			$this->modelService->createNewEvent($event);
		}
	}

	public function handleRead()
	{

	}

	public function handleUpdate()
	{

	}

	public function handleDestroy()
	{

	}

}
