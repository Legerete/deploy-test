<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Jiří Švec <me@svecjiri.com>
 * @package     Legerete\SpaKendoImModule
 */

namespace Legerete\Spa\KendoIm\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Nette\SmartObject;
use Nette\Utils\DateTime;

/**
 * @ORM\Entity
 * @package Legerete\Spa\KendoIm\Model\Entity
 */
class Page
{

	use SmartObject;

	private

		/**
		 * @ORM\Id
		 * @ORM\Column(type="integer", options={"unsigned":true})
		 * @ORM\GeneratedValue(strategy="AUTO")
		 * @var int
		 */
		$id,

		/**
		 * @ORM\ManyToOne(targetEntity="\Legerete\Spa\KendoIm\Model\Entity\InformationMemorandum", inversedBy="pages", cascade={"persist", "remove"})
		 * @var InformationMemorandum
		 */
		$informationMemorandum,

		/**
		 * @ORM\Column(type="text")
		 * @var string
		 */
		$content,

		/**
		 * @ORM\Column(type="datetime", options={"default":"CURRENT_TIMESTAMP"})
		 * @var DateTime
		 */
		$created,

		/**
		 * @ORM\Column(type="datetime", nullable=true)
		 * @var DateTime
		 */
		$modified;

	public function __construct()
	{
		$this->created = new DateTime;
	}

	/**
	 * @ORM\PrePersist
	 */
	public function beforePersist()
	{
		if (NULL !== $this->id) {
			$this->modified = new DateTime;
		}
	}

	//<editor-fold defaultstate="collapsed" desc="Getters">
	/**
	 * @return int
	 */
	public function getId(): int
	{
		return $this->id;
	}

	/**
	 * @return InformationMemorandum
	 */
	public function getInformationMemorandum()
	{
		return $this->informationMemorandum;
	}

	/**
	 * @return string
	 */
	public function getContent(): string
	{
		return $this->content;
	}

	/**
	 * @return DateTime
	 */
	public function getCreated()
	{
		return $this->created;
	}

	/**
	 * @return DateTime
	 */
	public function getModified()
	{
		return $this->modified;
	}
	//</editor-fold>

	//<editor-fold defaultstate="collapsed" desc="Setters">
	/**
	 * @param int $id
	 * @return Page
	 */
	public function setId(int $id): Page
	{
		$this->id = (int)$id;

		return $this;
	}

	/**
	 * @param mixed $content
	 * @return Page
	 */
	public function setContent(string $content): Page
	{
		$this->content = $content;

		return $this;
	}
	//</editor-fold>

}
