<?php
/**
 * This file is part of the Taco Projects.
 *
 * Copyright (c) 2004, 2013 Martin Takáč (http://martin.takac.name)
 *
 * For the full copyright and license information, please view
 * the file LICENCE that was distributed with this source code.
 *
 * @author     Martin Takáč (martin@takac.name)
 */

namespace Taco\NetteWebImages;

use Nette;


class ImageRef extends Nette\Object implements Ref
{

	private $ref;


	function __construct($ref)
	{
		$this->ref = $ref;
	}



	/**
	 * @return string
	 */
	function getRef()
	{
		return $this->ref;
	}

}
