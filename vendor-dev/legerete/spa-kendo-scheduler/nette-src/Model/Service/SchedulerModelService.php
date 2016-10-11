<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\SpaKendoScheduler
 */

namespace Legerete\Spa\KendoScheduler\Model\Service;

use Kdyby\Doctrine\EntityManager;
use Legerete\Spa\KendoScheduler\Model\Entity\SchedulerEventEntity;
use Nette\Security\User;
use Recurr\RecurrenceCollection;

class SchedulerModelService
{
	/**
	 * @var string $timeZone
	 * @see http://php.net/manual/en/timezones.php
	 */
	private $timeZone;

	/**
	 * @var User $user
	 */
	private $user;

	/**
	 * @var EntityManager $em
	 */
	private $em;

	/**
	 * SchedulerModelService constructor.
	 *
	 * @param EntityManager $em
	 * @param User $user
	 */
	public function __construct(EntityManager $em, User $user, string $timeZone = null)
	{
		$this->em = $em;
		$this->user = $user;
		$this->timeZone = $timeZone ?: date_default_timezone_get();
	}

	public function createNewEvent($eventData = [])
	{
		$event = $this->createEventEntity($eventData);

//		$this->em->persist($event);

		\Tracy\Debugger::barDump($this->getRecurrences($event));
	}

	/**
	 * @param array $eventData
	 * @return SchedulerEventEntity
	 */
	private function createEventEntity(array $eventData) : SchedulerEventEntity
	{
		return new SchedulerEventEntity(
			$eventData['title'],
			$eventData['start'],
			$eventData['end'],
			!empty($eventData['startTimezone']) ? $eventData['startTimezone'] : $this->timeZone,
			!empty($eventData['endTimezone']) ? $eventData['endTimezone'] : $this->timeZone,
			$eventData['description'],
			$eventData['recurrenceRule'],
			$this->user->getId(),
			$eventData['isAllDay']
		);
	}

	/**
	 * @param SchedulerEventEntity $parentEvent
	 * @return RecurrenceCollection
	 */
	private function getRecurrences(SchedulerEventEntity $parentEvent) : RecurrenceCollection
	{
		$startDate   = $parentEvent->getStart()->setTimezone($parentEvent->getStartTimezone());
		$endDate     = $parentEvent->getEnd()->setTimezone($parentEvent->getEndTimezone());
		$rule = new \Recurr\Rule($parentEvent->getRecurenceRule(), $startDate, $endDate, $this->timeZone);
		$transformer = new \Recurr\Transformer\ArrayTransformer();

		return $transformer->transform($rule);
	}

}