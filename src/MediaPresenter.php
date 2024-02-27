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

use Nette\Application;


class MediaPresenter implements Application\IPresenter
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


	private ContentGenerator $generator;


	function __construct(ContentGenerator $generator)
	{
		$this->generator = $generator;
	}



	function run(Application\Request $request): Application\Response
	{
		switch ($request->getParameter('action')) {
			case 'show':
				$id = $request->getParameter('id');
				$source = $request->getParameter('source');
				$ext = $this->resolveExtension($id);
				//~ dump($source);
				//~ dump($id);
				//~ dump($ext);
				//~ dump($request);
				//~ dump($this->generator);


				if ( ! isset(self::$supportedFormats[$ext])) {
					$this->generator->generateFile($id);
					exit;
				}
				else {
					$this->generator->generateImage(new ImageRequest(
						self::$supportedFormats[$ext],
						$id,
						$request->getParameter('variant') ?: ContentGenerator::OriginalVariant,
						//~ $this->acquireArgument('width', $parameters),
						//~ $this->acquireArgument('height', $parameters),
						['source' => $source]
					));
					exit;
				}

			case 'download':
				$this->generator->generateDownload($request->getParameter('id'));
				exit; // @TODO To snad nemusí být, ne?
			default:
				throw new \LogicException("Unnexpected action name: '" . $request->getParameter('action') . "'.");
		}
	}



	private function resolveExtension(string $id)
	{
		$ext = strtolower(self::parseExtension($id));
		if (empty($ext)) {
			$ext = $this->generator->guessExtension($id);
		}
		return $ext;
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



	private function acquireArgument($name, array $data)
	{
		if (isset($data[$name])) {
			return $data[$name];
		}
		elseif (isset($this->defaults[$name])) {
			return $this->defaults[$name];
		}
	}

}
