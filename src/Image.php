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

use Nette\Utils\Image as NImage;
use Nette;


/**
 * Když načteme obrázek ze souboru, nebo ze stringu, a neděláme nad tím žádné operace, tak je blbé, když nám ho gd modifikuje.
 */
class Image
{
	/** @var string */
	private $file;

	/** @var string */
	private $content;

	/**
	 * Opens image from file.
	 * @param  string
	 * @throws Nette\NotSupportedException if gd extension is not loaded
	 * @throws UnknownImageFileException if file not found or file type is not known
	 * @return self
	 */
	static function fromFile($file)
	{
		$inst = new static();
		$inst->file = $file;
		return $inst;
	}



	/**
	 * Create a new image from the image stream in the string.
	 * @param  string
	 * @return self
	 * @throws ImageException
	 */
	static function fromString($content)
	{
		$inst = new static();
		$inst->content = $content;
		return $inst;
	}



	/**
	 * @param  resource
	 */
	private function __construct()
	{
	}



	function getNetteImage(): ?NImage
	{
		if ($this->file) {
			return NImage::fromFile($this->file);
		}
		// Nahráli jsme obsah z contentu.
		if ($this->content) {
			return NImage::fromString($this->content);
		}
	}



	/**
	 * Outputs image to browser.
	 * @param  int  image type
	 * @param  int  quality 0..100 (for JPEG and PNG)
	 * @return bool TRUE on success or FALSE on failure.
	 */
	function send($type = NImage::JPEG, $quality = NULL)
	{
		if (!in_array($type, array(NImage::JPEG, NImage::PNG, NImage::GIF), TRUE)) {
			throw new Nette\InvalidArgumentException("Unsupported image type '$type'.");
		}
		header('Content-Type: ' . image_type_to_mime_type($type));
		return $this->save(NULL, $quality, $type);
	}



	/**
	 * @deprecated
	 * Saves image to the file.
	 * @param  string  filename
	 * @param  int  quality 0..100 (for JPEG and PNG)
	 * @param  int  optional image type
	 * @return bool TRUE on success or FALSE on failure.
	 */
	function save($file = NULL, $quality = NULL, $type = NULL)
	{
		if ($type === NULL) {
			$type = self::typeFromFile($file);
		}

		// Nahráli jsme obsah pomocí souboru.
		if ($this->file) {
			$origtype = self::typeFromFile($this->file);
			// Pokud nejsou žádné změny, tak rovnou vypisujeme originál.
			if ($type == $origtype && (
					empty($quality)
					|| ($type == NImage::GIF)
					|| ($type == NImage::PNG && $quality == 9)
					|| ($type == NImage::JPEG && $quality == 100))) {
				if ($file) {
					return copy($this->file, $file);
				}
				else {
					readfile($this->file);
					return True;
				}
			}
		}

		// Nahráli jsme obsah jako proud znaků.
		if ($this->content) {
			die('=====[' . __line__ . '] ' . __file__);
		}

		// Změny jsou
		if ($image = $this->getNetteImage()) {
			$image->save($file, $quality, $type);
			return True;
		}
		return False;
	}



	private static function typeFromFile($file)
	{
		if ( ! $ext = pathinfo($file, PATHINFO_EXTENSION)) {
			$ext = self::typeFromFileContent($file);
		}
		switch (strtolower($ext)) {
			case 'jpg':
			case 'jpeg':
				return NImage::JPEG;
			case 'png':
				return NImage::PNG;
			case 'gif':
				return NImage::GIF;
			default:
				throw new Nette\InvalidArgumentException("Unsupported file extension '$file'.");
		}
	}



	private static function typeFromFileContent($file)
	{
		$type = mime_content_type((string)$file);
		if (empty($type)) {
			return Null;
		}
		list($category, $type) = explode('/', $type, 2);
		if ($category != 'image') {
			return Null;
		}
		return $type;
	}


}
