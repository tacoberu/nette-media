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

use Nette\Utils\Image as NImage;
use Nette;
use LogicException;
use RuntimeException;


/**
 * When we load an image from a file or from a string, and we don't do any
 * operations on it, it's stupid when gd modifies it for us.
 */
class Image
{
	const FORMAT_JPEG = NImage::JPEG;
	const FORMAT_PNG = NImage::PNG;
	const FORMAT_GIF = NImage::GIF;
	const FORMAT_WEBP = NImage::WEBP;
	const FORMAT_AVIF = NImage::AVIF;
	const FORMAT_BMP = NImage::BMP;


	const DefaultFormat = self::FORMAT_JPEG;


	/**
	 * We uploaded the content using a file.
	 * @var string
	 */
	private $file;

	/**
	 * We uploaded the content as a character stream.
	 * @var string
	 */
	private $content;

	/**
	 * We did some transformations so we have the image stored in GD resource.
	 * @var NImage
	 */
	private $nobj;

	/**
	 * When performing transformations, we can affect the quality of image compression. But it only applies to NImage.
	 */
	private ?int $quality = null;

	/**
	 * When performing transformations, we can change the image format. But it only applies to NImage.
	 */
	private ?int $type = null;


	/**
	 * Opens image from file.
	 * @param  string
	 * @return self
	 */
	static function fromFile(string $file)
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
	static function fromString(string $content)
	{
		$inst = new static();
		$inst->content = $content;
		return $inst;
	}



	static function fromNetteImage(NImage $obj, ?int $type = Null, ?int $quality = Null)
	{
		$inst = new static();
		$inst->nobj = $obj;
		$inst->type = $type;
		$inst->quality = $quality;
		return $inst;
	}



	private function __construct()
	{
	}



	function getNetteImage(): ?NImage
	{
		switch (True) {
			case isset($this->file):
				return NImage::fromFile($this->file);

			case isset($this->content):
				return NImage::fromString($this->content);

			case isset($this->nobj):
				return clone $this->nobj;

			default:
				throw new LogicException("oops.");
		}
	}



	/**
	 * Internal typ: jpeg = 2, png = 3
	 * @return ?int
	 */
	function getType()
	{
		switch (True) {
			case isset($this->file):
				return NImage::detectTypeFromFile($this->file);

			case isset($this->content):
				return NImage::detectTypeFromString($this->content);

			// If the content is stored as a gd resource, it has no type.
			case isset($this->nobj):
				return Null;

			default:
				throw new LogicException("oops.");
		}
	}



	/**
	 * @return string like "images/jpeg"
	 */
	private function getMimeType(): string
	{
		if ($val = $this->getType()) {
			return image_type_to_mime_type($val);
		}
		return Null;
	}



	/**
	 * Outputs image to browser.
	 */
	function send(): void
	{
		switch (True) {
			case isset($this->file):
				$mimeType = $this->getMimeType() ?? image_type_to_mime_type(self::DefaultFormat);
				header('Content-Type: ' . $mimeType);
				readfile($this->file);
				exit;

			case isset($this->content):
				$mimeType = $this->getMimeType() ?? image_type_to_mime_type(self::DefaultFormat);
				header('Content-Type: ' . $mimeType);
				echo($this->content);
				exit;

			case isset($this->nobj):
				$type = $this->type ?? NImage::JPEG;
				$quality = self::normalizeQuality($this->quality, $type);
				$this->nobj->send($type, $quality);
				exit;

			default:
				throw new LogicException("oops.");
		}
	}



	/**
	 * Saves image to the file.
	 * @param  string  filename
	 */
	function save(string $file): void
	{
		switch (True) {
			case isset($this->file):
				copy($this->file, $file);
				return;

			case isset($this->content):
				if ( ! @file_put_contents($file, $this->content)) {
					throw IOException::FailedToSaveFile($file);
				}
				return;

			case isset($this->nobj):
				self::assertFileExtension($file, $this->type);
				$type = $this->type ?? NImage::JPEG;
				$quality = self::normalizeQuality($this->quality, $type);
				$this->nobj->save($file, $quality, $type);
				return;

			default:
				throw new LogicException("oops.");
		}
	}



	private static function typeByFileExtension($file)
	{
		if ($ext = pathinfo($file, PATHINFO_EXTENSION)) {
			switch (strtolower($ext)) {
				case 'jpe':
				case 'jpg':
				case 'jpeg':
					return NImage::JPEG;
				case 'png':
					return NImage::PNG;
				case 'gif':
					return NImage::GIF;
				case 'bmp':
					return NImage::BMP;
				case 'webp':
					return NImage::WEBP;
				case 'avif':
					return NImage::AVIF;
				default:
					$file = basename($file);
					throw new Nette\InvalidArgumentException("Unsupported file image extension '$file'.");
			}
		}
		return Null;
	}



	private static function normalizeQuality($quality, $type)
	{
		// Convert normalized percentage size to 0-9 scale for png
		switch ($type) {
			case NImage::PNG:
				$quality = (int) ($quality / 11.11);
				break;
		}
		return $quality;
	}



	private static function assertFileExtension(string $file, $type)
	{
		if ($type === Null) {
			return;
		}
		$currtype = self::typeByFileExtension($file);

		// a directory or an image without an extension
		if ($currtype === Null) {
			return;
		}
		if ($type === $currtype) {
			return;
		}
		$ext = pathinfo($file, PATHINFO_EXTENSION);
		$mimecontent = image_type_to_mime_type($type);
		throw new RuntimeException("You are trying to save a file with the extension '{$ext}' but the image type is: '{$mimecontent}'.");
	}

}
