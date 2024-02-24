<?php
/**
 * Copyright (c) Martin Takáč (http://martin.takac.name)
 *
 * For the full copyright and license information, please view
 * the file LICENCE that was distributed with this source code.
 *
 * @author Martin Takáč (martin@takac.name)
 */

namespace Taco\NetteWebImages;

use RuntimeException;


class IOException extends RuntimeException
{

	static function FileNotFound()
	{
		return new self("File not found.");
	}



	static function FailedToCreateDirectory(string $dir)
	{
		return new self("Failed to create directory: '{$dir}'.");
	}



	static function FailedToSaveFile(string $file)
	{
		return new self("Failed to save file: '{$file}'.");
	}

}
