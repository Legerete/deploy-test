<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Jiří Švec <me@svecjiri.com>
 * @package     Legerete\SpaKendoImModule
 */

namespace Legerete\Spa\KendoIm\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Nette\SmartObject;
use Nette\Utils\DateTime;

/**
 * @ORM\Entity
 * @package Legerete\Spa\KendoIm\Model\Entity
 */
class InformationMemorandum
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
		 * @ORM\OneToMany(targetEntity="\Legerete\Spa\KendoIm\Model\Entity\Page", mappedBy="informationMemorandum", cascade={"persist", "remove"}, onDelete="cascade"))
		 * @var Page[]
		 */
		$pages,

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
		$this->pages = new ArrayCollection;
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
	 * @return array
	 */
	public function getPages(): array
	{
		return $this->pages->toArray();
	}

	/**
	 * @return DateTime
	 */
	public function getCreated(): DateTime
	{
		return $this->created;
	}

	/**
	 * @return DateTime
	 */
	public function getModified(): DateTime
	{
		return $this->modified;
	}
	//</editor-fold>

	//<editor-fold defaultstate="collapsed" desc="Setters">
	/**
	 * @param int $id
	 * @return InformationMemorandum
	 */
	public function setId(int $id): InformationMemorandum
	{
		$this->id = (int)$id;

		return $this;
	}

	public function setPages($pages)
	{
		foreach ($this->getPages() as $page) {
			$this->removePage($page);
		}

		foreach ($pages as $page) {
			$entity = (new Page)->setContent($page);
			$this->addPage($entity);
		}

		return $this;
	}

	/**
	 * @param Page $page
	 * @return InformationMemorandum
	 */
	public function addPage(Page $page): InformationMemorandum
	{
		if ($this->pages->contains($page)) {
			return $this;
		}

		$this->pages->add($page);

		return $this;
	}

	/**
	 * @param Page $page
	 * @return InformationMemorandum
	 */
	public function removePage(Page $page): InformationMemorandum
	{
		if (!$this->pages->contains($page)) {
			return $this;
		}

		$this->pages->removeElement($page);

		return $this;
	}
	//</editor-fold>

}
