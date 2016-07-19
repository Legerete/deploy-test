<?php

/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Monolog\Processor;

/**
 * Injects Git branch and Git commit SHA in all records
 *
 * @author Nick Otter
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class StackTraceProcessor
{
	public function __construct()
	{
	}

	/**
	 * @param  array $record
	 * @return array
	 */
	public function __invoke(array $record)
	{

		@$exc = $record['context']['tracy']['exception'];
		if ($exc instanceof \Exception) {
			$record['context'] += $this->generateCallTrace($exc);
			unset($record['context']['tracy']['exception']);
		}

		return $record;
	}

	private function generateCallTrace($e)
	{
		$previousText = '';
		if ($previous = $e->getPrevious()) {
			do {
				$previousText .= ', ' . get_class($previous) . '(code: ' . $previous->getCode() . '): ' . $previous->getMessage() . ' at ' . $previous->getFile() . ':' . $previous->getLine();
			} while ($previous = $previous->getPrevious());
		}

		$str = get_class($e) . '(code: ' . $e->getCode() . '): ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine() . $previousText;

		$stacktrace = explode("\n", $e->getTraceAsString());
		array_walk($stacktrace, function (&$step) {
			$step = preg_replace("/^#[0-9]+ /", '', $step);
		});

		return [
			'exception' => $str,
			'trace' => $stacktrace
		];
	}

}
