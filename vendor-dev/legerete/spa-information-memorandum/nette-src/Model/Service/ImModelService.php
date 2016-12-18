<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Jiří Švec <me@svecjiri.com>
 * @package     Legerete\SpaKendoIm
 */

namespace Legerete\Spa\KendoIm\Model\Service;

use Kdyby\Doctrine\EntityManager;
use Legerete\Spa\KendoIm\Model\Entity;
use Legerete\Spa\KendoIm\Model\ImResponse;

class ImModelService
{

	/** @var EntityManager $entityManager */
	private $entityManager;

	/**
	 * @param EntityManager $em
	 */
	public function __construct(EntityManager $em)
	{
		$this->entityManager = $em;
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
		$this->entityManager->beginTransaction();
		try {
			$entityMemorandum = new Entity\InformationMemorandum;
			$this->entityManager->persist($entityMemorandum);

			foreach ($memorandum['pages'] as $page) {
				$entityPage = (new Entity\Page)->setContent($page);
				$entityMemorandum->addPage($entityPage);
				$this->entityManager->persist($entityPage);
			}

			$this->entityManager->flush()->commit();
		} catch (\Exception $e) {
			$this->entityManager->rollback();
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
		$entity = $this->entityManager
			->getRepository(Entity\InformationMemorandum::class)
			->findOneById($memorandum['id']);

		if (NULL === $entity) {
			$response->setError(TRUE)->setMessage('No record found.');
			return $response->toArray();
		}

		$this->entityManager->beginTransaction();
		$entity->setPages($memorandum['pages']);

		try {
			$this->entityManager->persist($entity)->flush()->commit();
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
		$entity = $this->entityManager
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
		$entity = $this->entityManager
			->getRepository(Entity\InformationMemorandum::class)
			->findOneById($memorandumId);

		if (NULL === $entity) {
			$response->setError(TRUE)
				->setMessage('The information memorandum by ID not found.');

			return $response->toArray();
		}

		try {
			$this->entityManager->beginTransaction();
			$this->entityManager->remove($entity);
			$this->entityManager->flush()->commit();
			$response->setMessage('OK');
		} catch (\Exception $e) {
			$this->entityManager->flush()->rollback();
			$response->setError(TRUE)->setMessage('Occur error while deleting a record.');
		}

		return $response->toArray();
	}
}
