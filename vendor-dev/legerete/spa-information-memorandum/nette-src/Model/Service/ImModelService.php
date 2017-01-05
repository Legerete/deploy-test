<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\SpaKendoScheduler
 */

namespace Legerete\Spa\KendoIm\Model\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\AbstractQuery;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Doctrine\EntityRepository;
use Legerete\Security\Model\Entity\RoleEntity;
use Legerete\Security\Model\Entity\UserEntity;
use Legerete\Spa\KendoUser\Model\Exception\UserNotFoundException;
use Legerete\User\Model\SuperClass\UserSuperClass;
use Nette\Application\BadRequestException;
use Nette\Security\User;
use Nette\Utils\Arrays;
use Nette\Utils\Finder;
use OTPHP\TOTP;

class ImModelService
{

	/**
	 * @var EntityManager $em
	 */
	private $em;

	/**
	 * @var string $templatesDirectory
	 */
	private $templatesDirectory;

	/**
	 * @var Finder $templatesFiles
	 */
	private $templatesFiles;

	/**
	 * ImModelService constructor.
	 *
	 * @param EntityManager $em
	 */
	public function __construct(EntityManager $em)
	{
		$this->em = $em;
	}

	public function readTemplates()
	{
		return $this->makeTemplatesAssocArray($this->getTemplatesFiles());
	}

	private function makeTemplatesAssocArray(Finder $templatesFiles)
	{
		$files = [];
		/**
		 * @type \SplFileInfo $file
		 */
		foreach ($templatesFiles as $file) {
			preg_match('/^([0-9-]+)?(.*)(\.latte|\.[x]{0,1}html)/s', $file->getFilename(), $matches);

			if (! array_key_exists($matches[2], $files)) {
				$files[$matches[2]] = [
					'name' => $matches[2],
					'file' => $matches[0]
				];
			}
		}

		sort($files);

		return $files;
	}

	private function getTemplatesFiles()
	{
		if (!count($this->templatesFiles)) {
			$this->readTemplatesFiles();
		}
		return $this->templatesFiles;
	}

	/**
	 * Read templates files from $this->templatesDirectory
	 */
	private function readTemplatesFiles()
	{
		$files = $this->templatesFiles = Finder::findFiles('*.latte')
			->from($this->getTemplatesDirectory());
		return $files;
	}

	/**
	 * @return string
	 */
	private function getTemplatesDirectory(): string
	{
		return $this->templatesDirectory;
	}

	/**
	 * @var string $templatesDirectory
	 */
	public function setTemplatesDirectory($templatesDirectory)
	{
		$this->templatesDirectory = $templatesDirectory;
		return $this;
	}

}