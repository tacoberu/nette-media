<?php declare(strict_types=1);
/**
 * Copyright (c) Martin Takáč (http://martin.takac.name)
 *
 * For the full copyright and license information, please view
 * the file LICENCE that was distributed with this source code.
 *
 * @author Martin Takáč (martin@takac.name)
 */

namespace Taco\NetteMedia;

use Nette;
use Nette\Application\Response;
use Nette\Application\BadRequestException;
use Nette\Http\IRequest;
use Nette\Http\IResponse;


class ContentResponse implements Response
{
	use Nette\SmartObject;

	/** @var Content */
	private $content;

	/** @var string */
	private $name;

	/** @var bool */
	private $forceDownload;


	function __construct(
		Content $content,
		string $name,
		bool $forceDownload
	) {
		$this->content = $content;
		$this->name = $name;
		$this->forceDownload = $forceDownload;
	}



	/**
	 * Sends response to output.
	 * @return void
	 */
	function send(IRequest $httpRequest, IResponse $httpResponse): void
	{
		try {
			$httpResponse->setContentType($this->content->getContentType());
			$httpResponse->setHeader(
				'Content-Disposition',
				($this->forceDownload ? 'attachment' : 'inline')
					. '; filename="' . $this->name . '"'
					. '; filename*=utf-8\'\'' . rawurlencode($this->name)
			);
			if ($filesize = $this->content->getSize()) {
				$httpResponse->setHeader('Content-Length', (string) $filesize);
			}
			echo $this->content->getContent();
		}
		catch (\Exception $e) {
			throw new BadRequestException("Cannot open content: '{$this->content->getName()}'.");
		}
	}

}
