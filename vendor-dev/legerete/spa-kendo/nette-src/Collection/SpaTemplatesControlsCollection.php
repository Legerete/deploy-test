<?php


namespace Legerete\Spa\Collection;

use Doctrine\Common\Collections\ArrayCollection;

class SpaTemplatesControlsCollection extends ArrayCollection
{

	/**
	 * An array containing the entries of this collection.
	 *
	 * @var array
	 */
	private $elements;

	/**
	 * Initializes a new ArrayCollection.
	 *
	 * @param array $elements
	 */
	public function __construct(array $elements = [])
	{
		$this->elements = $elements;
		return parent::__construct($elements);
	}
}
