<?php

namespace App\Utils\Latte;

use Nette\Utils\Json,
	Latte;

class Macros extends Latte\Macros\MacroSet
{
	/**
	 * File name with
	 */
	const FILE_PACKAGE_JSON = 'package.json';

	private static $resourceVersion;

	/**
	 * Initialize serial version for resources.
	 */
	private static function initResourceVersion()
	{
		if (NULL === self::$resourceVersion) {
			$ds = DIRECTORY_SEPARATOR;
			$file = realpath(__DIR__ . $ds . '..' . $ds . '..' . $ds . '..' . $ds) . $ds . self::FILE_PACKAGE_JSON;

			if (is_readable($file)) {
				$json = Json::decode(file_get_contents($file));

				if (isset($json->version)) {
					self::$resourceVersion = '?v=' . (string)$json->version;
					return ;
				}

				self::$resourceVersion = '';
			}
		}
	}

	public static function install(Latte\Compiler $compiler)
	{
		$set = new static($compiler);
		$set->addMacro('resourceVersion', array($set, 'macroResourceVersion'));

		return $set;
	}

	/**
	 * Append resource version
	 *
	 * @param Latte\PhpWriter $writer
	 * @return string
	 */
	public static function macroResourceVersion(Latte\MacroNode $node, Latte\PhpWriter $writer)
	{
		return $writer->write(
			'echo \App\Utils\Latte\Macros::renderMacroResourceVersion(%node.word, %node.array)'
		);
	}

	public static function renderMacroResourceVersion($path, $args)
	{
		if (NULL === self::$resourceVersion) {
			self::initResourceVersion();
		}

		return self::$resourceVersion;
	}
}
