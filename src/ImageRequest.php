<?php
/**
 * Copyright (c) Martin Takáč (http://martin.takac.name)
 *
 * For the full copyright and license information, please view
 * the file LICENCE that was distributed with this source code.
 *
 * @author Martin Takáč (martin@takac.name)
 * @credits dotBlue (http://dotblue.net)
 */

namespace Taco\NetteMedia;

use Nette;


/**
 * @property array $parameters
 */
class ImageRequest
{

	use Nette\SmartObject;


	/** @var string */
	private $id;

	/** @var string|null */
	private $variant;

	/** @var int */
	private $format;

	/** @var array */
	private $parameters;



	/**
	 * @param  int $format Number of jpeg, gif, png, etc.
	 * @param  string $id "dee/Boston city Flow.jpeg"
	 * @param  string|null $variant Code of rule.
	 * @param  array
	 */
	function __construct($format, $id, $variant, array $parameters)
	{
		$this->id = $id;
		$this->variant = $variant;
		$this->format = $format;
		$this->parameters = $parameters;
	}



	/**
	 * @return string
	 */
	function getId()
	{
		return $this->id;
	}



	/**
	 * @return string|null
	 */
	function getVariant()
	{
		return $this->variant;
	}



	/**
	 * @return int
	 */
	function getFormat()
	{
		return $this->format;
	}



	/**
	 * @return array
	 */
	function getParameters()
	{
		return $this->parameters;
	}

}
