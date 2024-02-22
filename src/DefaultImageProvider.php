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

use Nette\Utils\Validators;
use SplFileInfo;


class DefaultImageProvider implements IProvider
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
		throw new \RuntimeException("File not found.");
	}



	/**
	 * @return Content
	 */
	function getContent($id)
	{
		$path = $this->sourceDir . '/' . $id;
		if (is_file($path)) {
			return new FileContent(new SplFileInfo($path));
		}
		throw new \RuntimeException("File not found.");
	}

}
