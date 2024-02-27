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
use Nette\Utils\Validators as NValidators;
use LogicException;


class ResizeTransformation implements Transformation
{

	/**
	 * @var int
	 */
	private $quality;

	/**
	 * @var ?int
	 */
	private $format;

	/**
	 * @var int
	 */
	private $width;

	/**
	 * @var int
	 */
	private $height;

	/**
	 * @var string
	 */
	private $algorithm;

	/**
	 * @param int $quality
	 * @param int $width
	 * @param int $height
	 * @param string $algorithm
	 * @param ?int $format
	 */
	function __construct($quality, $width, $height, $algorithm, $format = Null)
	{
		NValidators::assert($quality, 'int');
		NValidators::assert($width, 'int:1..');
		NValidators::assert($height, 'int:1..');
		NValidators::assert($algorithm, 'string');
		NValidators::assert($format, 'int|null');
		$this->quality = $quality;
		$this->width = $width;
		$this->height = $height;
		$this->algorithm = $algorithm;
		$this->format = $format;
	}



	function transform(Image $image): Image
	{
		// Shrink only larger ones.
		if (self::smallestThan($image, $this->width, $this->height)) {
			return $image;
		}
		$nimage = $image->getNetteImage();
		$nimage->resize($this->width, $this->height, self::castAlgorithm($this->algorithm));
		if ($this->format) {
			$format = $this->format;
		}
		else {
			$format = $image->getType();
			if ($format === Null) {
				$format = Image::DefaultFormat;
			}
		}

		return Image::fromNetteImage($nimage, $format, $this->quality);
	}



	/**
	 * @param string $s
	 * @return int<0, 15>
	 */
	private static function castAlgorithm($s)
	{
		switch ($s) {
			case 'shrink':
				return NImage::SHRINK_ONLY;
			case 'stretch':
				return NImage::STRETCH;
			case 'fit':
				return NImage::FIT;
			case 'fill':
				return NImage::FILL;
			case 'exact':
				return NImage::EXACT;
			default:
				throw new LogicException("Unsupported algorithm: '{$s}'.");
		}
	}



	/**
	 * @param Image $image
	 * @param int $width
	 * @param int $height
	 * @return bool
	 */
	private function smallestThan($image, $width, $height)
	{
		$image = $image->getNetteImage();
		return ! ($image->getWidth() > $width && $image->getHeight() > $height);
	}

}
