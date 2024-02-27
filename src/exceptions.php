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

use RuntimeException;
use LogicException;
use Nette\Application;


class IOException extends RuntimeException
{

	static function FileNotFound(string $file)
	{
		return new self("File '{$file}' is not found.");
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



class InvalidConfigException extends LogicException
{}



class NotAllowedImageException extends Application\BadRequestException
{}
