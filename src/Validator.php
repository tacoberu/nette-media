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

namespace Taco\NetteWebImages;

use Nette;


class Validator
{

	use Nette\SmartObject;


	/** @var array[] */
	private $rules = [];


	/**
	 * Adds rule.
	 *
	 * @param  int
	 * @param  int
	 */
	function addRule($name, $width = null, $height = null, $algorithm = null, $quality = 100)
	{
		$this->rules[$name] = [
			'width' => $width,
			'height' => $height,
			'algorithm' => $algorithm,
			'quality' => (int) $quality,
		];
	}



	/**
	 * Validates by name of size.
	 *
	 * @param  string
	 * @return bool
	 */
	function validate($size)
	{
		if (isset($this->rules[$size])) {
			return $this->rules[$size];
		}
	}



	/**
	 * Returns all added rules.
	 *
	 * @return array[]
	 */
	function getRules()
	{
		return $this->rules;
	}

}
