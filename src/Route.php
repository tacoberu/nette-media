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


	/** @var string */
	private $basePath;

	/** @var ContentGenerator */
	private $provider;


	/**
	 * @param  string $basePath "media"
	 * @param  ContentGenerator $provider
	 */
	function __construct(string $basePath, ContentGenerator $provider)
	{
		$this->basePath = '/' . trim($basePath, '/');
		$this->provider = $provider;
	}



	/**
	 * @return null | array<string, string>
	 */
	function match(HttpRequest $httpRequest): ?array
	{
		$path = self::getPath($httpRequest);
		if (strncmp($this->basePath . '/', $path, strlen($this->basePath) + 1) !== 0) {
			return Null;
		}
		else {
			$path = trim(substr($path, strlen($this->basePath)), '/');
		}
		$variant = self::getVariant($httpRequest);
		if ($variant && ! $this->provider->validVariant($variant)) {
			return Null;
		}

		return [
			'presenter' => self::Presenter,
			'action' => self::getActionName($httpRequest),
			'source' => trim($this->basePath, '/'),
			'id' => $path,
			'variant' => $variant,
		];
	}



	/**
	 * @param array<string, string> $params
	 */
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



	/**
	 * @param string $val
	 * @return void
	 */
	private function assertVariant($val)
	{
		if (!$this->provider->validVariant($val)) {
			throw new NotAllowedImageException("Invalid variant of image: '$val'.");
		}
	}



	/**
	 * @return string
	 */
	private static function getPath(HttpRequest $request)
	{
		return $request->getUrl()->getPath();
	}



	/**
	 * @return null|string
	 */
	private static function getVariant(HttpRequest $request)
	{
		$queries = $request->getUrl()->getQueryParameters();
		if (array_key_exists('download', $queries)) {
			return Null;
		}
		$queries = array_keys($queries);
		$variant = reset($queries);
		if (is_string($variant)) {
			return $variant;
		}
		return Null;
	}



	/**
	 * @return string
	 */
	private static function getActionName(HttpRequest $request)
	{
		$queries = $request->getUrl()->getQueryParameters();
		return array_key_exists('download', $queries)
			? 'download'
			: 'take';
	}

}
