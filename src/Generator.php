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

use Nette;
use Nette\Application;
use Nette\Http;
use Nette\Utils\Image as NImage;
use Nette\Utils\Validators as NValidators;
use LogicException;


/**
 * Poskytuje obrázky. Obrázky kešuje, validuje.
 */
class Generator
{

	use Nette\SmartObject;


	const FORMAT_JPEG = NImage::JPEG;
	const FORMAT_PNG = NImage::PNG;
	const FORMAT_GIF = NImage::GIF;

	/** @var string */
	private $cacheDir;

	/** @var Http\IRequest */
	private $httpRequest;

	/** @var Http\IResponse */
	private $httpResponse;

	/** @var Validator */
	private $validator;

	/** @var IProvider[] */
	private $providers = [];


	function __construct($cacheDir, Http\IRequest $httpRequest, Http\IResponse $httpResponse, Validator $validator)
	{
		$this->cacheDir = $cacheDir;
		$this->httpRequest = $httpRequest;
		$this->httpResponse = $httpResponse;
		$this->validator = $validator;
	}



	function addProvider(IProvider $provider)
	{
		$this->providers[] = $provider;
	}



	/**
	 * @return Validator
	 */
	function getValidator()
	{
		return $this->validator;
	}



	function guessExtension($id)
	{
		$file = $this->requireFileItem($id);
		if ( ! $type = $file->getContentType()) {
			return Null;
		}
		list($category, $type) = explode('/', $type, 2);
		if ($category != 'image') {
			return Null;
		}
		return $type;
	}



	function generateDownload($id)
	{
		$file = $this->requireFileItem($id);
		$this->httpResponse->setHeader('Content-Type', $file->getContentType());
		$length = $file->getSize();
		$this->httpResponse->setHeader('Content-Length', $length);
		$this->httpResponse->setHeader('Content-Disposition', 'attachment'
			. '; filename="' . $file->getName() . '"'
			. '; filename*=utf-8\'\'' . rawurlencode($file->getName()));
		echo $file->getContent();
		exit;
	}



	function generateFile($id)
	{
		$file = $this->requireFileItem($id);
		$this->httpResponse->setHeader('Content-Type', $file->getContentType());
		$this->httpResponse->setHeader('Content-Length', $file->getSize());
		echo $file->getContent();
		exit;
	}



	function generateImage(ImageRequest $request)
	{
		// Load from cache.
		if (isset($request->parameters['size']) && $image = $this->loadFromCache($request)) {
		//~ if ($image = $this->cache->get($request)) {
			$image->send();
			exit;
		}

		foreach ($this->providers as $provider) {
			$image = $provider->getImage($request);
			if ($image) {
				break;
			}
		}

		if (empty($image)) {
			$this->httpResponse->setHeader('Content-Type', 'image/jpeg');
			$this->httpResponse->setCode(Http\IResponse::S404_NOT_FOUND);
			exit;
		}

		$params = $request->getParameters();
		$quality = isset($params['quality']) ? $params['quality'] : 100;

		// Převést normalizované procentní velikost na škálu 0-9 u png
		switch ($request->getFormat()) {
			case NImage::PNG:
				$quality = (int) ($quality / 11.11);
				break;
		}

		if (isset($params['size']) && ! self::smallestThan($image, $request)) {
			$algorithm = isset($params['algorithm']) ? $params['algorithm'] : 'fit';
			$width = $request->getWidth();
			$height = $request->getHeight();
			$image = $this->resize($image, $quality, $request->getFormat(), $width, $height, $algorithm);
			if (isset($request->parameters['size'])) {
				$image = $this->saveToCache($request, $image, $quality, $request->getFormat());
				//~ $this->cache->store($request, $image);
			}
		}

		$image->send($request->getFormat(), $quality);
		exit;
	}



	private function requireFileItem($id)
	{
		foreach ($this->providers as $provider) {
			$file = $provider->getContent($id);
			if ($file) {
				return $file;
			}
		}
		//~ $this->httpResponse->setHeader('Content-Type', 'image/jpeg');
		//~ $this->httpResponse->setCode(Http\IResponse::S404_NOT_FOUND);
		//~ exit;
		throw new Application\BadRequestException("File '{$id}' is not found.", Http\IResponse::S404_NOT_FOUND);
	}



	private function smallestThan($image, $request)
	{
		$image = $image->getNetteImage();
		return ! ($image->getWidth() > $request->getWidth() && $image->getHeight() > $request->getHeight());
	}



	/**
	 * @return Nette\Utils\Image
	 */
	private function resize(Image $image, $quality, $format, $width, $height, $algorithm)
	{
		NValidators::assert($quality, 'int');
		NValidators::assert($width, 'int:1..');
		NValidators::assert($height, 'int:1..');
		NValidators::assert($algorithm, 'string');
		// @FIXME Taková divočina. To s tím vlastním Image nechce moc dobře fungovat.
		$nimage = $image->getNetteImage();
		$nimage->resize($width, $height, self::castAlgorithm($algorithm));
		return $nimage;
	}



	private function loadFromCache($request)
	{
		$file = implode(DIRECTORY_SEPARATOR, [$this->cacheDir, $request->parameters['size'], $request->getId()]);
		if (file_exists($file)) {
			return Image::fromFile($file);
		}
	}



	private function saveToCache($request, $image, $quality, $format)
	{
		$destination = implode(DIRECTORY_SEPARATOR, [$this->cacheDir, $request->parameters['size'], $request->getId()]);
		self::mkdir(dirname($destination));
		$success = $image->save($destination, $quality, $format);
		if (!$success) {
			throw new Application\BadRequestException;
		}
		return $image;
	}



	private static function mkdir($dirname)
	{
		if (!is_dir($dirname)) {
			$success = @mkdir($dirname, 0777, TRUE);
			if (!$success) {
				throw new Application\BadRequestException;
			}
		}
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
				throw new LogicException("Unsupported algorithm: $s.");
		}
	}

}
