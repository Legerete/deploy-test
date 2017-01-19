<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @author      Jiří Švec <me@svecjiri.com>
 * @package     Legerete\SpaKendoIm
 */

namespace Legerete\Spa\KendoIm\Model\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\AbstractQuery;
use Kdyby\Doctrine\EntityManager;
use Legerete\Spa\KendoIm\Model\Entity;
use Legerete\Spa\KendoIm\Model\ImResponse;
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

	/**
	 * Create the information memorandum
	 * Input parameter structure:
	 * <code>
	 * [
	 *     'id' => null, // optional
	 *     'pages' => [
	 *         'content 1',
	 *         ...,
	 *         'content X',
	 *     ]
	 * ]
	 * </code>
	 * @param array $memorandum
	 * @return array
	 */
	public function createInformationMemorandum(array $memorandum): array
	{
		$response = new ImResponse;
		$this->em->beginTransaction();
		try {
			$entityMemorandum = new Entity\InformationMemorandum;
			foreach ($memorandum['pages'] as $page) {
				$entityPage = (new Entity\Page)->setContent($page);
				$entityMemorandum->addPage($entityPage);
			}

			$this->em->persist($entityMemorandum);
			$this->em->flush()->commit();
		} catch (\Exception $e) {
			$this->em->rollback();
			$response->setError(TRUE)->setMessage('Occur error while creating a record.');
		}

		return $response->toArray();
	}

	/**
	 * Update the information memorandum
	 * Input parameter structure:
	 * <code>
	 * [
	 *     'id' => 1,
	 *     'pages' => [
	 *         'content 1',
	 *         ...,
	 *         'content X',
	 *     ]
	 * ]
	 * </code>
	 * @param array $memorandum
	 * @return array
	 */
	public function updateInformationMemorandum(array $memorandum): array
	{
		$response = new ImResponse;

		/** @var Entity\InformationMemorandum $entity */
		$entity = $this->em
			->getRepository(Entity\InformationMemorandum::class)
			->findOneById($memorandum['id']);

		if (NULL === $entity) {
			$response->setError(TRUE)->setMessage('No record found.');
			return $response->toArray();
		}

		$this->em->beginTransaction();
		$entity->setPages($memorandum['pages']);

		try {
			$this->em->persist($entity)->flush()->commit();
		} catch (\Exception $e) {
			$response->setError(TRUE)->setMessage('Occur error while updating a record.');
		}

		return $response->toArray();
	}

	/**
	 * @param int $memorandumId
	 * @return array
	 */
	public function readInformationMemorandum(int $memorandumId): array
	{
		$response = new ImResponse;

		/** @var Entity\InformationMemorandum $entity */
		$entity = $this->em
			->getRepository(Entity\InformationMemorandum::class)
			->findOneById($memorandumId);

		if (NULL === $entity) {
			$response->setError(TRUE)->setMessage('The information memorandum by ID not found.');

			return $response->toArray();
		}

		$response ->setMessage('OK')
			->setInformationMemorandumId($entity->getId())
			->setPages($entity->getPages());

		return $response->toArray();
	}

	/**
	 * Destroy the information memorandum
	 * @param int $memorandumId
	 * @return array
	 */
	public function destroyInformationMemorandum(int $memorandumId): array
	{
		$response = new ImResponse;

		/** @var Entity\InformationMemorandum $entity */
		$entity = $this->em
			->getRepository(Entity\InformationMemorandum::class)
			->findOneById($memorandumId);

		if (NULL === $entity) {
			$response->setError(TRUE)
				->setMessage('The information memorandum by ID not found.');

			return $response->toArray();
		}

		try {
			$this->em->beginTransaction();
			$this->em->remove($entity);
			$this->em->flush()->commit();
			$response->setMessage('OK');
		} catch (\Exception $e) {
			$this->em->flush()->rollback();
			$response->setError(TRUE)->setMessage('Occur error while deleting a record.');
		}

		return $response->toArray();
	}
}
