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
	 * @ORM\Column(type="datetime")
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
	protected $recurence;

	/**
	 * @ORM\Column(type="string")
	 */
	protected $recurenceRule;

	/**
	 * @ORM\Column(type="string")
	 */
	protected $recurenceException;

	/**
	 * @ORM\Column(type="integer")
	 */
	protected $ownerId;

	/**
	 * @ORM\Column(type="bool")
	 */
	protected $isAllDay;

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
	public function __construct($title, $start, $end, $startTimezone, $endTimezone, $description, $recurenceRule, $ownerId, $isAllDay)
	{
		$this->title = $title;
		$this->start = new \DateTime($start);
		$this->end = new \DateTime($end);
		$this->startTimezone = new \DateTimeZone($startTimezone);
		$this->endTimezone = new \DateTimeZone($endTimezone);
		$this->description = $description;
		$this->recurenceRule = $recurenceRule;
		$this->ownerId = $ownerId;
		$this->isAllDay = $isAllDay;
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
	public function setStart($start)
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
	public function setEnd($end)
	{
		$this->end = $end;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getStartTimezone()
	{
		return $this->startTimezone;
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
		return $this->endTimezone;
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
	public function getRecurence()
	{
		return $this->recurence;
	}

	/**
	 * @var mixed $recurence
	 */
	public function setRecurence($recurence)
	{
		$this->recurence = $recurence;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getRecurenceRule()
	{
		return $this->recurenceRule;
	}

	/**
	 * @var mixed $recurenceRule
	 */
	public function setRecurenceRule($recurenceRule)
	{
		$this->recurenceRule = $recurenceRule;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getRecurenceException()
	{
		return $this->recurenceException;
	}

	/**
	 * @var mixed $recurenceException
	 */
	public function setRecurenceException($recurenceException)
	{
		$this->recurenceException = $recurenceException;
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
		$this->isAllDay = $isAllDay;
		return $this;
	}



}