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
class ContentRequest
{

	use Nette\SmartObject;


	const OriginalVariant = '__orig__';


	/** @var string */
	private $id;

	/** @var string|null */
	private $variant;

	/** @var array<string, string> */
	private $parameters;



	/**
	 * @param  string $id "dee/Boston city Flow.jpeg"
	 * @param  string|null $variant Code of rule.
	 * @param  array<string, string> $parameters
	 */
	function __construct($id, $variant, array $parameters)
	{
		$this->id = $id;
		$this->variant = $variant;
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
	 * @return array<string, string>
	 */
	function getParameters()
	{
		return $this->parameters;
	}



	/**
	 * @param string $name
	 * @param mixed $default
	 * @return mixed
	 */
	function getParameter($name, $default = Null)
	{
		if (array_key_exists($name, $this->parameters)) {
			return $this->parameters[$name];
		}
		return $default;
	}



	/**
	 * @return bool
	 */
	function isTakeOriginal()
	{
		return $this->variant === self::OriginalVariant;
	}

}
