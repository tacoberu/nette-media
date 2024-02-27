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
use Nette\Http\IRequest as HttpRequest;
use Nette\Http\UrlScript;


class Route implements Nette\Routing\Router
{

	const Presenter = 'WebMedia:Media';


	const FORMAT_JPEG = 'jpeg';
	const FORMAT_JPG = 'jpg';
	const FORMAT_PNG = 'png';
	const FORMAT_GIF = 'gif';
	const FORMAT_BMP = 'bmp';
	const FORMAT_AVIF = 'avif';
	const FORMAT_WEBP = 'webp';


	/** @var array */
	private static $supportedFormats = [
		self::FORMAT_JPEG => Image::FORMAT_JPEG,
		self::FORMAT_JPG => Image::FORMAT_JPEG,
		self::FORMAT_PNG => Image::FORMAT_PNG,
		self::FORMAT_GIF => Image::FORMAT_GIF,
		self::FORMAT_BMP => Image::FORMAT_BMP,
		self::FORMAT_AVIF => Image::FORMAT_AVIF,
		self::FORMAT_WEBP => Image::FORMAT_WEBP,
	];

	/** @var string */
	private $basePath;

	/** @var string */
	private $defaults;

	/** @var Generator */
	private $generator;


	/**
	 * @param  string $basePath "media"
	 * @param  Generator
	 * @param  int|NULL ?
	 */
	function __construct(string $basePath, ContentGenerator $generator)
	{
		$this->basePath = '/' . trim($basePath, '/');
		$this->generator = $generator;
	}



	function match(HttpRequest $httpRequest): ?array
	{
		$path = $httpRequest->getUrl()->getPath();
		$ext = strtolower(self::parseExtension($path));
		if ( ! isset(self::$supportedFormats[$ext])) {
			return Null;
		}
		if (strncmp($this->basePath . '/', $path, strlen($this->basePath) + 1) !== 0) {
			return Null;
		}
		else {
			$path = trim(substr($path, strlen($this->basePath)), '/');
		}
		$queries = $httpRequest->getUrl()->getQueryParameters();
		$variant = self::getVariant($queries);
		if ($variant && ! $this->generator->validVariant($variant)) {
			return Null;
		}

		return [
			'presenter' => self::Presenter,
			'action' => array_key_exists('download', $queries) ? 'download' : 'show',
			'source' => trim($this->basePath, '/'),
			'id' => $path,
			'variant' => $variant,
		];
	}



	function constructUrl(array $params, UrlScript $refUrl): ?string
	{
		unset($params['action']);
		if (!isset($params['presenter'])
				|| $params['presenter'] !== self::Presenter
				|| !isset($params['id'])) {
			return Null;
		}
		$path = "{$this->basePath}/{$params['id']}";
		if (array_key_exists('download', $params)) {
			$path .= '?download';
			return $path;
		}
		if (count($params) === 2) {
			return $path;
		}
		if (array_key_exists('size', $params)) {
			$this->assertVariant($params['size']);
			$path .= '?' . $params['size'];
			return $path;
		}
		return Null;
	}



	private function assertVariant($val)
	{
		if (!$this->generator->validVariant($val)) {
			throw new NotAllowedImageException("Invalid variant of image: '$val'.");
		}
	}



	private static function getVariant(array $queries)
	{
		if (array_key_exists('download', $queries)) {
			return Null;
		}
		$queries = array_keys($queries);
		$variant = reset($queries);
		return $variant;
	}



	private function acquireArgument($name, array $data)
	{
		if (isset($data[$name])) {
			return $data[$name];
		}
		elseif (isset($this->defaults[$name])) {
			return $this->defaults[$name];
		}
	}



	/**
	 * It splits the filename path from the extension, from which the format is then determined.
	 * @return string|null
	 */
	private function parseExtension($id)
	{
		if ($id instanceof Ref) {
			$id = $id->getRef();
		}
		if ($index = strrpos($id, '.')) {
			return substr($id, $index + 1);
		}
		return Null;
	}

}
