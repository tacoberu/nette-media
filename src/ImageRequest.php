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


class ImageRequest extends Nette\Object
{

	/** @var string */
	private $id;

	/** @var int|NULL */
	private $width;

	/** @var int|NULL */
	private $height;

	/** @var int */
	private $format;

	/** @var array */
	private $parameters;



	/**
	 * @param  int
	 * @param  string
	 * @param  int|NULL
	 * @param  int|NULL
	 * @param  array
	 */
	function __construct($format, $id, $width, $height, array $parameters)
	{
		$this->id = $id;
		$this->width = $width;
		$this->height = $height;
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
	 * @return int|NULL
	 */
	function getWidth()
	{
		return $this->width;
	}



	/**
	 * @return int|NULL
	 */
	function getHeight()
	{
		return $this->height;
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
