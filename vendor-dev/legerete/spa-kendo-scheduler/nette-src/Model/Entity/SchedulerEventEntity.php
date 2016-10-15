<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\SpaKendoScheduler
 */

namespace Legerete\Spa\KendoScheduler\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Table;

/**
 * @ORM\Entity
 * @Table(name="scheduler_event")
 */
class SchedulerEventEntity
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 */
	protected $id;

	/**
	 * @ORM\Column(type="string")
	 */
	protected $title;

	/**
	 * @ORM\Column(type="datetime")
	 */
	protected $start;

	/**
	 * @ORM\Column(type="datetime", name="`end`")
	 */
	protected $end;

	/**
	 * @ORM\Column(type="string")
	 */
	protected $startTimezone;

	/**
	 * @ORM\Column(type="string")
	 */
	protected $endTimezone;

	/**
	 * @ORM\Column(type="string")
	 */
	protected $description;

	/**
	 * @ORM\ManyToOne(targetEntity="SchedulerEventEntity")
	 */
	protected $recurrence;

	/**
	 * @ORM\Column(type="string")
	 */
	protected $recurrenceRule;

	/**
	 * @ORM\Column(type="string")
	 */
	protected $recurrenceException;

	/**
	 * @ORM\Column(type="integer")
	 */
	protected $ownerId;

	/**
	 * @ORM\Column(type="integer")
	 */
	protected $isAllDay;

	/**
	 * @ORM\Column(type="string")
	 */
	protected $status = 'ok';

	/**
	 * SchedulerEventEntity constructor.
	 *
	 * @param $title
	 * @param $start
	 * @param $end
	 * @param $startTimezone
	 * @param $endTimezone
	 * @param $description
	 * @param $ownerId
	 * @param $isAllDay
	 */
	public function __construct($title, $start, $end, $startTimezone, $endTimezone, $description, $recurrenceRule, $ownerId, $isAllDay)
	{
		$this->title = $title;
		$this->start = (is_object($start) && get_class($start) === \DateTime::class) ? $start : new \DateTime($start);
		$this->end = (is_object($end) && get_class($end) === \DateTime::class) ? $end : new \DateTime($end);
		$this->startTimezone = (new \DateTimeZone($startTimezone))->getName();
		$this->endTimezone = (new \DateTimeZone($endTimezone))->getName();
		$this->description = $description;
		$this->recurrenceRule = $recurrenceRule;
		$this->ownerId = $ownerId;
		$this->isAllDay = filter_var($isAllDay, FILTER_VALIDATE_BOOLEAN);
	}



	/* ********************************** Getters & setters ********************************** */

	/**
	 * @return integer
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return mixed
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * @var mixed $title
	 */
	public function setTitle($title)
	{
		$this->title = $title;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getStart()
	{
		return $this->start;
	}

	/**
	 * @var mixed $start
	 */
	public function setStart(\DateTime $start)
	{
		$this->start = $start;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getEnd()
	{
		return $this->end;
	}

	/**
	 * @var mixed $end
	 */
	public function setEnd(\DateTime $end)
	{
		$this->end = $end;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getStartTimezone()
	{
		return new \DateTimeZone($this->startTimezone);
	}

	/**
	 * @var mixed $startTimezone
	 */
	public function setStartTimezone($startTimezone)
	{
		$this->startTimezone = $startTimezone;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getEndTimezone()
	{
		return new \DateTimeZone($this->endTimezone);
	}

	/**
	 * @var mixed $endTimezone
	 */
	public function setEndTimezone($endTimezone)
	{
		$this->endTimezone = $endTimezone;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * @var mixed $description
	 */
	public function setDescription($description)
	{
		$this->description = $description;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getRecurrence()
	{
		return $this->recurrence;
	}

	/**
	 * @var mixed $recurrence
	 */
	public function setRecurrence($recurrence)
	{
		$this->recurrence = $recurrence;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getRecurrenceRule()
	{
		return $this->recurrenceRule;
	}

	/**
	 * @var mixed $recurrenceRule
	 */
	public function setRecurrenceRule($recurrenceRule)
	{
		$this->recurrenceRule = $recurrenceRule;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getRecurrenceException()
	{
		return $this->recurrenceException;
	}

	/**
	 * @var mixed $recurrenceException
	 */
	public function setRecurrenceException($recurrenceException)
	{
		$this->recurrenceException = $recurrenceException;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getOwnerId()
	{
		return $this->ownerId;
	}

	/**
	 * @var mixed $ownerId
	 */
	public function setOwnerId($ownerId)
	{
		$this->ownerId = $ownerId;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getIsAllDay()
	{
		return $this->isAllDay;
	}

	/**
	 * @var mixed $isAllDay
	 */
	public function setIsAllDay($isAllDay)
	{
		$this->isAllDay = filter_var($isAllDay, FILTER_VALIDATE_BOOLEAN);
		return $this;
	}

	public function destroy()
	{
		$this->status = 'del';
		return $this;
	}

	/**
	 * @return array
	 */
	public function toArray() : array
	{
		return [
			'id' => $this->id,
			'title'=> $this->title,
			'start'=> $this->start,
			'end'=> $this->end,
			'startTimezone'=> $this->startTimezone,
			'endTimezone'=> $this->endTimezone,
			'description'=> $this->description,
			'recurrenceId'=> $this->recurrence,
			'recurrenceRule'=> $this->recurrenceRule,
			'recurrenceException'=> $this->recurrenceException,
			'ownerId'=> 1,
			'isAllDay'=> $this->isAllDay,
		];
	}

}