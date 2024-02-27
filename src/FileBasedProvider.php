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

use Nette\Utils\Validators;
use SplFileInfo;


class FileBasedProvider implements MediaProvider
{

	/**
	 * Umístění zdrojů souborů.
	 * @var string
	 */
	private $sourceDir;


	/**
	 * @param string $sourceDir
	 */
	function __construct($sourceDir)
	{
		Validators::assert($sourceDir, 'string:1..');
		$this->sourceDir = $sourceDir;
	}



	/**
	 * V tomto případě nám doplnující informace o zdroji nezajímají.
	 * @return ?Content
	 */
	function getContent(ContentRequest $request)
	{
		$path = $this->sourceDir . '/' . $request->getId();
		if (is_file($path)) {
			return new FileContent(new SplFileInfo($path));
		}
		return Null;
	}

}
