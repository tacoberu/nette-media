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


/**
 * @TODO Validace algorithm.
 * @TODO Vyhodit starý kód.
 */
class Route extends Application\Routers\Route
{

	const FORMAT_JPEG = 'jpeg';
	const FORMAT_JPG = 'jpg';
	const FORMAT_PNG = 'png';
	const FORMAT_GIF = 'gif';


	/** @var array */
	static $supportedFormats = [
		self::FORMAT_JPEG => Generator::FORMAT_JPEG,
		self::FORMAT_JPG => Generator::FORMAT_JPEG,
		self::FORMAT_PNG => Generator::FORMAT_PNG,
		self::FORMAT_GIF => Generator::FORMAT_GIF,
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
				throw new NotAllowedImageException("Image with size `{$size}' is not allowed - check your 'webimages.rules' please.");
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

		$ext = self::parseExtension($id);
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
	 * Rozdělí cestu se jménem souboru od přípony, ze které se pak určuje formát.
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
	 * Doplní další parametry.
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
	 * Používáme dvojtečku pro oddělení namespace, namísto lomítka. Protože lomítku nette router parsuje.
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
