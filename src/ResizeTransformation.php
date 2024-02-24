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
use Nette\Utils\Validators as NValidators;
use LogicException;


class ResizeTransformation implements Transformation
{

	private $quality, $format, $width, $height, $algorithm;

	function __construct($quality, $format, $width, $height, $algorithm)
	{
		NValidators::assert($quality, 'int');
		NValidators::assert($width, 'int:1..');
		NValidators::assert($height, 'int:1..');
		NValidators::assert($algorithm, 'string');
		//~ NValidators::assert($format, 'int');
		$this->quality = $quality;
		$this->format = $format;
		$this->width = $width;
		$this->height = $height;
		$this->algorithm = $algorithm;
	}



	function transform(Image $image): Image
	{
		$nimage = $image->getNetteImage();
		$nimage->resize($this->width, $this->height, self::castAlgorithm($this->algorithm));
		return Image::fromNetteImage($nimage, $this->format, $this->quality);
	}



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

}
