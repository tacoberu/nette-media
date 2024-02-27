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

use Nette\Utils\Validators;
use SplFileInfo;


/**
 * Storage of cached files to local storage.
 */
class FileBasedThumbnailCache implements ThumbnailsCache
{

	/**
	 * @var string
	 */
	private $cacheDir;


	/**
	 * @param string $cacheDir
	 */
	function __construct($cacheDir)
	{
		Validators::assert($cacheDir, 'string:1..');
		$this->cacheDir = $cacheDir;
	}



	/**
	 * @param string $id For example: 'dee/uee.jpg'
	 * @param string $variant For example: 'small'
	 * @return ?Image
	 */
	function load(string $id, string $variant): ?Image
	{
		$file = $this->buildDestination($id, $variant);
		if (file_exists($file)) {
			return Image::fromFile($file);
		}
		return Null;
	}



	/**
	 * @param string $id For example: 'dee/uee.jpg'
	 * @param string $variant For example: 'small'
	 * @return void
	 */
	function save(string $id, string $variant, Image $image)
	{
		$destination = $this->buildDestination($id, $variant);
		self::mkdir(dirname($destination));
		$image->save($destination);
	}



	/**
	 * @return string
	 */
	private function buildDestination(string $id, string $variant)
	{
		return implode(DIRECTORY_SEPARATOR, [
			$this->cacheDir,
			$variant,
			$id,
		]);
	}



	/**
	 * @param string $dirname
	 * @return void
	 */
	private static function mkdir($dirname)
	{
		if (!is_dir($dirname)) {
			$success = @mkdir($dirname, 0777, TRUE);
			if (!$success) {
				throw IOException::FailedToCreateDirectory($dirname);
			}
		}
	}

}
