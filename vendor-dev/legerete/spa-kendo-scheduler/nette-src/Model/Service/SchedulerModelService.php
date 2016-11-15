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
	 * @var array $eventData
	 */
	private $eventData;

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

	/**
	 * @param array $eventData
	 */
	public function createNewEvent($eventData = [])
	{
		$events = [];
		$this->eventData = $eventData;
		$event = $this->createEventEntity($this->eventData);

		$this->em->persist($event);
		$this->em->flush();

		$events[] = $event->toArray();

		return $events;
	}

	/**
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
	 * @param \DateTime $date
	 * @param string $view
	 * @param string $schedulerAction
	 * @return array
	 */
	public function readEvents(\DateTime $date, string $view, string $schedulerAction)
	{
		list($firstDayModifier, $lastDayModifier) = $this->getFirstAndLastDay($view);
		$firstDay = $date->modify($firstDayModifier);
		$lastDay = (clone $firstDay)->modify($lastDayModifier);

		$qb = $this->getSchedulerEventsRepository()->createQueryBuilder('e');
		$events = $qb
			->select('e, partial r.{id}')
			->leftJoin('e.recurrence', 'r')
			->where('e.start >= :first')
			->andWhere('e.end <= :last')
			->orWhere('e.recurrenceRule != :empty')
			->andWhere('e.recurrence is NULL')
			->andWhere('e.status = :statusOk')
//			->andWhere('e.userId = :userId')
			->setParameters(['first' => $firstDay, 'last' => $lastDay, 'empty' => '', 'statusOk' => 'ok'])
			->getQuery();

		return $events->getArrayResult();
	}

	/**
	 * @param string $view
	 * @return array
	 */
	private function getFirstAndLastDay(string $view) : array
	{
		switch ($view) {
			case 'day':
				$modifyPhrases = ['today', 'today + 1 days'];
				break;
			case 'week':
				$modifyPhrases = ['monday this week', 'sunday this week'];
				break;
			case 'month':
				$modifyPhrases = ['first day of this month', 'last day of this month'];
				break;
			case 'agenda':
				$modifyPhrases = ['monday this week', 'sunday this week'];
				break;
			default:
				$modifyPhrases = self::getFirstAndLastDay('week');
		}

		return $modifyPhrases;
	}

	/**
	 * @param array $data
	 * @return array
	 */
	public function updateEvent(array $data) : array
	{
		/**
		 * @type SchedulerEventEntity $event
		 */
		$event = $this->getSchedulerEventsRepository()->find($data['id']);

		$event
			->setTitle($data['title'])
			->setStart(new \DateTime($data['start']))
			->setEnd(new \DateTime($data['end']))
			->setStartTimezone($data['startTimezone'])
			->setEndTimezone($data['endTimezone'])
			->setDescription($data['description'])
			->setRecurrence(
				empty($data['recurrenceId']) ? NULL : $this->em->getReference(SchedulerEventEntity::class, $data['recurrenceId']))
			->setRecurrenceRule($data['recurrenceRule'])
			->setRecurrenceException($data['recurrenceException'])
			->setIsAllDay($data['isAllDay']);

		$this->em->flush();
		return $event->toArray();
	}

	/**
	 * @param integer $id
	 */
	public function destroyEvent($id)
	{
		/**
		 * @type SchedulerEventEntity $event
		 */
		$event = $this->getSchedulerEventsRepository()->find((int) $id);
			$event->destroy();

		$this->em->flush();
	}

	/**
	 * @return \Kdyby\Doctrine\EntityRepository
	 */
	private function getSchedulerEventsRepository()
	{
		return $this->em->getRepository(SchedulerEventEntity::class);
	}

}