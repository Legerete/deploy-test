<?php

namespace App\Console\Command;

use Kdyby\Doctrine\EntityRepository;
use Legerete\Security\Model\Entity\ResourceEntity;
use Legerete\Security\Permission;
use Nette\Security\IAuthorizator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Kdyby\Doctrine\EntityManager;
use App;

class UpdateAclResources extends Command
{

	/**
	 * @var EntityManager
	 * @inject
	 */
	public $em;

	/**
	 * @var IAuthorizator $permissions
	 * @inject
	 */
	public $permissions;

	protected function configure()
	{
		$this->setName('db:update:acl-resources')
			->setDescription('Automaticlay update ACL resources by registered resources from extensions.');
	}


	protected function execute(InputInterface $input, OutputInterface $output)
	{
		/**
		 * @var Permission $permissions
		 */
		$permissions = $this->permissions;

		try {
			$this->em->beginTransaction();

			$removedResources = 0;
			$createdResources = 0;

			foreach ($permissions->getResources() as $resourceName) {
				$dbResource = $this->resourceRepository()->findOneBy([
					'name' => $resourceName
				]);

				if (!$dbResource) {
					$resource = new ResourceEntity($resourceName);
					$this->em->persist($resource)->flush();
					$createdResources++;
				}
			}

			$output->writeLn("<info>db:update:acl-resources - [$createdResources] resources created. [$removedResources] resources removed.</info>");
			$output->writeLn("<comment>db:update:acl-resources - @todo - removing unused resources not functioning</comment>");

			$this->em->commit();
			return 0;
		} catch (\Exception $exc) {
			$output->writeLn('<error>db:update:acl-resources - ' . $exc->getMessage() . '</error>');

			return 1;
		}
	}

	/**
	 * @return EntityRepository
	 */
	private function resourceRepository() :EntityRepository
	{
		return $this->em->getRepository(ResourceEntity::class);
	}

}
