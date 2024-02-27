<?php
/**
 * Copyright (c) Martin Takáč (http://martin.takac.name)
 *
 * For the full copyright and license information, please view
 * the file LICENCE that was distributed with this source code.
 *
 * @author Martin Takáč (martin@takac.name)
 */

namespace Taco\NetteMedia;

use SplFileInfo;


/**
 * @FIXME
 */
class FileContent implements Content
{


	/**
	 * @param string
	 */
	function __construct(SplFileInfo $file)
	{
		$this->file = $file;
	}



	function getContentType()
	{
		$res = mime_content_type((string)$this->file);
		// normalize
		return $res;
	}



	function getSize()
	{
		return filesize($this->file);
	}



	function getName()
	{
		return $this->file->getFileName();
	}



	function getContent()
	{
		return file_get_contents($this->file);
	}

}
