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

use Nette\Application;


class Route extends Application\Routers\Route
{

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
	private $defaults;

	/** @var Generator */
	private $generator;


	/**
	 * @param  string "assets-<id>[-<size>]
	 * @param  array ?
	 * @param  Generator
	 * @param  int|NULL ?
	 */
	function __construct($mask, array $defaults, Generator $generator, $flags = 0)
	{
		$this->defaults = $defaults;
		$this->generator = $generator;

		$defaults[NULL][self::FILTER_OUT] = function ($parameters) {
			$size = $this->acquireArgument('size', $parameters);
			if ($size && ! $opts = $this->generator->getValidator()->validate($size)) {
				return False;
			}

			$id = $this->acquireArgument('id', $parameters);
			$parameters['id'] = self::escape($id);

			if (isset($this->defaults[NULL][self::FILTER_OUT])) {
				$parameters = call_user_func($this->defaults[NULL][self::FILTER_OUT], $parameters);
			}

			return $parameters;
		};

		$defaults['presenter'] = 'Nette:Micro';
		$defaults['callback'] = $this;

		parent::__construct($mask, $defaults, $flags);
	}



	function __invoke($presenter)
	{
		$parameters = $this->unpackParameters($presenter->getRequest()->getParameters());
		unset($parameters['callback']);

		$id = $parameters['id'];
		unset($parameters['id']);

		if (array_key_exists('download', $parameters)) {
			$this->generator->generateDownload(self::unescape($id));
			return;
		}

		$ext = strtolower(self::parseExtension($id));
		if (empty($ext)) {
			$ext = $this->generator->guessExtension($id);
		}
		if ( ! isset(self::$supportedFormats[$ext])) {
			$this->generator->generateFile(self::unescape($id));
		}
		else {
			$format = self::$supportedFormats[$ext];
			$this->generator->generateImage(new ImageRequest(
				$format,
				self::unescape($id),
				$this->acquireArgument('width', $parameters),
				$this->acquireArgument('height', $parameters),
				$parameters
			));
		}
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



	/**
	 * Adds other parameters.
	 * @return array
	 */
	private function unpackParameters($args)
	{
		if ($size = $this->acquireArgument('size', $args)) {
			$opts = $this->generator->getValidator()->validate($size);
			$args = array_merge($args, $opts);
		}
		return $args;
	}



	/**
	 * We use a colon to separate namespaces, instead of a slash. Because the slash is parsed by the nette router.
	 */
	private static function unescape($id)
	{
		if ($id instanceof Ref) {
			$id = $id->getRef();
		}
		return strtr($id, ':', '/');
	}



	private static function escape($id)
	{
		if ($id instanceof Ref) {
			$id = $id->getRef();
		}
		return strtr($id, '/', ':');
	}

}

class NotAllowedImageException extends Application\BadRequestException {}
