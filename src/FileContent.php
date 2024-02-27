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


class FileContent implements Content
{

	/**
	 * @var SplFileInfo
	 */
	private $file;


	/**
	 * @param SplFileInfo $file
	 */
	function __construct(SplFileInfo $file)
	{
		$this->file = $file;
	}



	/**
	 * @return ?string Like "images/jpeg"
	 */
	function getContentType()
	{
		$res = mime_content_type((string)$this->file);
		// normalize
		return $res;
	}



	/**
	 * @return int
	 */
	function getSize()
	{
		return (int) filesize($this->file);
	}



	/**
	 * @return string
	 */
	function getName()
	{
		return $this->file->getFileName();
	}



	/**
	 * @return ?string
	 */
	function getContent()
	{
		return file_get_contents($this->file);
	}



	/**
	 * @return SplFileInfo
	 */
	function getFile()
	{
		return $this->file;
	}

}
