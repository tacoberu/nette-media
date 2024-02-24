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
 * Provides images. Caches images, validates allowed variants.
 */
class Generator
{

	use Nette\SmartObject;


	/** @var ThumbnailsCache */
	private $cache;

	/** @var Http\IRequest */
	private $httpRequest;

	/** @var Http\IResponse */
	private $httpResponse;

	/** @var Validator */
	private $validator;

	/** @var IProvider[] */
	private $providers = [];


	function __construct(ThumbnailsCache $cache, Http\IRequest $httpRequest, Http\IResponse $httpResponse, Validator $validator)
	{
		$this->cache = $cache;
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

		// Shrink only larger ones.
		if (isset($request->parameters['size']) && ! self::smallestThan($image, $request)) {
			$image = (new ResizeTransformation(isset($request->parameters['quality']) ? $request->parameters['quality'] : 100
				, $request->getFormat()
				, $request->getWidth()
				, $request->getHeight()
				, isset($request->parameters['algorithm']) ? $request->parameters['algorithm'] : 'fit'
				))
				->transform($image);
		}

		// Even save the unreduced preview in the cache so that I don't have to try it every time I request it
		if (isset($request->parameters['size'])) {
			$this->saveToCache($request, $image);
		}

		$image->send();
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
		throw new Application\BadRequestException("File '{$id}' is not found.", Http\IResponse::S404_NOT_FOUND);
	}



	private function smallestThan($image, $request)
	{
		$image = $image->getNetteImage();
		return ! ($image->getWidth() > $request->getWidth() && $image->getHeight() > $request->getHeight());
	}



	private function loadFromCache($request): ?Image
	{
		return $this->cache->load($request->getId(), $request->parameters['size']);
	}



	private function saveToCache($request, Image $image): void
	{
		$this->cache->save($request->getId()
			, $request->parameters['size']
			, $image
			);
	}

}
