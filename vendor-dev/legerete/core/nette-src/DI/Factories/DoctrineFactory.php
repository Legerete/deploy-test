<?php

namespace Legerete\DI\Factories;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Tools\Setup;

class DoctrineFactory
{
	/** @var Configuration */
	protected $configuration;

	/** @var AnnotationDriver */
	protected $annotationDriver;

	/** @var array */
	protected $parameters;

	public function __construct($isDevMode, $proxyDir, array $parameters)
	{
		$configuration = Setup::createConfiguration($isDevMode, $proxyDir);
		$this->configuration = $configuration;
		$this->parameters = $parameters;
		$this->annotationDriver = $this->configuration->newDefaultAnnotationDriver([], FALSE);
		$this->configuration->setMetadataDriverImpl($this->annotationDriver);
	}

	public function addPaths($paths)
	{
		$this->annotationDriver->addPaths((array)$paths);
	}

	/**
	 * @return EntityManager
	 * @throws \Doctrine\ORM\ORMException
	 */
	public function create()
	{
		return EntityManager::create($this->parameters, $this->configuration);
	}
}