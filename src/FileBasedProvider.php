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


class FileBasedProvider implements IProvider
{

	/**
	 * Umístění zdrojů obrázků.
	 * @var string
	 */
	private $sourceDir;


	/**
	 * @param string
	 */
	function __construct($sourceDir)
	{
		Validators::assert($sourceDir, 'string:1..');
		$this->sourceDir = $sourceDir;
	}



	/**
	 * @return Image
	 */
	function getImage(ImageRequest $request)
	{
		$path = $this->sourceDir . '/' . $request->getId();
		if (is_file($path)) {
			$format = $request->getFormat();
			return Image::fromFile($path /*, $format*/);
		}
		return Null;
	}



	/**
	 * @param string $id Name of file without sourceDir.
	 * @return Content
	 */
	function getContent($id)
	{
		Validators::assert($id, 'string:1..');
		$path = $this->sourceDir . '/' . $id;
		if (is_file($path)) {
			return new FileContent(new SplFileInfo($path));
		}
		return Null;
	}

}
