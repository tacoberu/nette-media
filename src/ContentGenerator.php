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


	/** @var ThumbnailsCache */
	private $cache;

	/** @var MediaProvider[] */
	private $providers = [];

	/** @var array<array<Transformation>> */
	private $transformations = [];

	/** @var string[] */
	private $transformable = [
		'jpeg',
		'png',
		'gif',
		'bmp',
		'avif',
		'webp',
	];


	function __construct(ThumbnailsCache $cache)
	{
		$this->cache = $cache;
	}



	/**
	 * @return self
	 */
	function addProvider(MediaProvider $provider)
	{
		$this->providers[] = $provider;
		return $this;
	}



	/**
	 * @param string $name
	 * @param array<Transformation> $chains
	 * @return self
	 */
	function addTransformation($name, $chains)
	{
		$this->transformations[$name] = $chains;
		return $this;
	}



	/**
	 * Ověřit, zda tato varianta transformace/obrázku je dostupná.
	 * @param string $val
	 * @return bool
	 */
	function validVariant($val)
	{
		return isset($this->transformations[$val]);
	}



	/**
	 * Nepokoušej se tento soubor/obrázek zobrazit. Vynutit nabídku k uložení.
	 * @param ContentRequest $request
	 * @return ContentResponse
	 */
	function generateDownload(ContentRequest $request)
	{
		$content = $this->requireFileItem($request);
		return new ContentResponse($content, $request->getParameter('name', $content->getName()), true);
	}



	/**
	 * @param ContentRequest $request
	 * @return ContentResponse
	 */
	function generateTake(ContentRequest $request)
	{
		if ($request->isTakeOriginal()) {
			return $this->generateFile($request);
		}
		if ( ! $this->isTransformable($request)) {
			return $this->generateFile($request);
		}
		return $this->generateWithTransformation($request);
	}



	/**
	 * @param ContentRequest $request
	 * @return ContentResponse
	 */
	private function generateFile(ContentRequest $request)
	{
		$content = $this->requireFileItem($request);
		return new ContentResponse($content, $request->getParameter('name', $content->getName()), false);
	}



	/**
	 * @return ContentResponse
	 */
	private function generateWithTransformation(ContentRequest $request)
	{
		// Try load from cache.
		if ($image = $this->loadFromCache($request)) {
			return new ContentResponse($image, $request->getParameter('name', basename($request->getId())), false);
		}

		$image = $this->requireImageByRequest($request);
		foreach ($this->resolveTransformation($request->getVariant()) as $transformation) {
			$image = $transformation->transform($image);
		}

		// Even save the unreduced preview in the cache so that I don't have to try it every time I request it
		$this->saveToCache($request, $image);

		return new ContentResponse($image, $request->getParameter('name', basename($request->getId())), false);
	}



	/**
	 * @return Image
	 */
	private function requireImageByRequest(ContentRequest $request)
	{
		return Image::fromContent($this->requireFileItem($request));
	}



	/**
	 * @return ?Content
	 */
	private function getFileItem(ContentRequest $request)
	{
		foreach ($this->providers as $provider) {
			if ($item = $provider->getContent($request)) {
				return $item;
			}
		}
		return Null;
	}



	/**
	 * @return Content
	 */
	private function requireFileItem(ContentRequest $request)
	{
		if ($item = $this->getFileItem($request)) {
			return $item;
		}
		throw new Application\BadRequestException("File '{$request->getId()}' is not found.", Http\IResponse::S404_NOT_FOUND);
	}



	private function loadFromCache(ContentRequest $request): ?Image
	{
		return $this->cache->load($request->getId(), $request->getVariant());
	}



	/**
	 * @return void
	 */
	private function saveToCache(ContentRequest $request, Image $image)
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
	private function resolveTransformation($variant) : array
	{
		if (isset($this->transformations[$variant])) {
			return $this->transformations[$variant];
		}
		elseif ($variant === ContentRequest::OriginalVariant) {
			return [];
		}
		throw new LogicException("Illegal variant name: '{$variant}'.");
	}



	/**
	 * @return bool
	 */
	private function isTransformable(ContentRequest $request)
	{
		return in_array(self::parseExtension($request->getId()), $this->transformable, True);
	}



	/**
	 * It splits the filename path from the extension, from which the format is then determined.
	 * @param string $filename
	 * @return string|null
	 */
	private function parseExtension($filename)
	{
		$filename = strtolower($filename);
		if ($index = strrpos($filename, '.')) {
			$ext = substr($filename, $index + 1);
			$ext = MimeTypeUtils::normalizeExtension($ext);
			return $ext;
		}
		return Null;
	}



	/**
	 * @return ?string
	 */
	private function guessMimeTypeFor(ContentRequest $request)
	{
		if ($type = self::guessMimeTypeByExtension($request->getId())) {
			return $type;
		}
		if ( ! $file = $this->getFileItem($request)) {
			return Null;
		}
		if ( ! $type = $file->getContentType()) {
			return Null;
		}
		return $type;
	}



	/**
	 * @param string $filename
	 * @return ?string
	 */
	private static function guessMimeTypeByExtension($filename)
	{
		$ext = pathinfo($filename, PATHINFO_EXTENSION);
		$ext = MimeTypeUtils::normalizeExtension($ext);
		return MimeTypeUtils::getMimeType($ext);
	}

}
