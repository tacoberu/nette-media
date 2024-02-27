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

namespace Taco\NetteMedia;

use Nette;
use Nette\Application;
use Nette\Http;
use Nette\Utils\Image as NImage;
use Nette\Utils\Validators as NValidators;
use LogicException;


/**
 * Provides images. Caches images, validates allowed variants.
 */
class ContentGenerator
{

	use Nette\SmartObject;

	const OriginalVariant = '__orig__';

	/** @var ThumbnailsCache */
	private $cache;

	/** @var Http\IResponse */
	private $httpResponse;

	/** @var IProvider[] */
	private $providers = [];

	/** @var Transformation[] */
	private $transformations = [];


	function __construct(ThumbnailsCache $cache, Http\IResponse $httpResponse)
	{
		$this->cache = $cache;
		$this->httpResponse = $httpResponse;
	}



	function addProvider(IProvider $provider)
	{
		$this->providers[] = $provider;
	}



	/**
	 * @param string $name
	 * @param array<Transformation> $chains
	 */
	function addTransformation($name, $chains)
	{
		$this->transformations[$name] = $chains;
	}



	/**
	 * Ověřit, zda tato varianta transformace/obrázku je dostupná.
	 */
	function validVariant($val)
	{
		return isset($this->transformations[$val]);
	}



	/**
	 * @param string $id
	 */
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



	/**
	 * @param string $id
	 */
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
		// Try load from cache.
		if ($image = $this->loadFromCache($request)) {
			$image->send();
			exit;
		}

		$image = $this->requireImageByRequest($request);
		foreach ($this->resolveTransformation($request->getVariant(), $request->getFormat()) as $transformation) {
			$image = $transformation->transform($image);
		}

		// Even save the unreduced preview in the cache so that I don't have to try it every time I request it
		$this->saveToCache($request, $image);

		$image->send();
		exit;
	}



	private function requireImageByRequest($request)
	{
		foreach ($this->providers as $provider) {
			$item = $provider->getImage($request);
			if ($item) {
				return $item;
			}
		}

		if (empty($item)) {
			throw new Application\BadRequestException("File '{$request->getId()}' is not found.", Http\IResponse::S404_NOT_FOUND);
			//~ $this->httpResponse->setHeader('Content-Type', 'image/jpeg');
			//~ $this->httpResponse->setCode(Http\IResponse::S404_NOT_FOUND);
			//~ exit;
		}
	}



	private function requireFileItem($id)
	{
		foreach ($this->providers as $provider) {
			$item = $provider->getContent($id);
			if ($item) {
				return $item;
			}
		}
		throw new Application\BadRequestException("File '{$id}' is not found.", Http\IResponse::S404_NOT_FOUND);
	}



	private function loadFromCache($request): ?Image
	{
		return $this->cache->load($request->getId(), $request->getVariant());
	}



	private function saveToCache($request, Image $image): void
	{
		$this->cache->save($request->getId()
			, $request->getVariant()
			, $image
			);
	}



	/**
	 * @param string $variant "big", "small", "preview", etc
	 * @return array<Transformation>
	 */
	private function resolveTransformation($variant, $format) : array
	{
		if (isset($this->transformations[$variant])) {
			return $this->transformations[$variant];
		}
		elseif ($variant === self::OriginalVariant) {
			return [];
		}
		throw new LogicException("Illegal variant name: '{$variant}'.");
	}

}
