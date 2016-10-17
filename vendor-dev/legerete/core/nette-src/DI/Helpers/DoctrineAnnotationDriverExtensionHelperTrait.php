<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\Core
 */

namespace Legerete\DI\Helpers;

use Kdyby\Doctrine\Mapping\AnnotationDriver;
use Nette\DI\ContainerBuilder;

trait DoctrineAnnotationDriverExtensionHelperTrait
{
	/**
	 * @param string $directory
	 * @param string $namespace
	 */
	private function registerDoctrineEntityAnnotationDriver(string $directory, string $namespace)
	{
		/**
		 * @var ContainerBuilder $builder
		 */
		$builder = $this->getContainerBuilder();

		$name = $this->DoctrineMetadataExtensionGenerateAnnotationDriverName($namespace);

		$builder->addDefinition($name)
			->setClass(AnnotationDriver::class)
			->setArguments([[$directory], $builder->getDefinition('annotations.reader'), $builder->getDefinition('doctrine.cache.default.metadata')]);

		$builder->getDefinition('doctrine.default.metadataDriver')
			->addSetup('addDriver', ['@'.$name, $namespace]);
	}

	/**
	 * @param string $namespace
	 * @return string
	 */
	private function DoctrineMetadataExtensionGenerateAnnotationDriverName(string $namespace)
	{
		$name = str_replace('\\', '.', strtolower($namespace));
		return $name.'.annotation.driverImpl';
	}
}