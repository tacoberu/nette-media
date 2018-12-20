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

use Nette\Image;


class FakeImageProvider implements IProvider
{

	function getImage(ImageRequest $request)
	{
		$width = $request->getWidth();
		$height = $request->getHeight();

		$source = "http://fakeimg.pl/{$width}x{$height}";
		return Image::fromString(file_get_contents($source));
	}

}
