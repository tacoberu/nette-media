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

	private ContentGenerator $provider;


	function __construct(ContentGenerator $generator)
	{
		$this->provider = $generator;
	}



	function run(Application\Request $request): Application\Response
	{
		switch ($request->getParameter('action')) {
			case 'take':
				return $this->provider->generateTake(new ContentRequest(
					$request->getParameter('id'),
					$request->getParameter('variant') ?: ContentRequest::OriginalVariant,
					['source' => $request->getParameter('source')]
				));

			case 'download':
				return $this->provider->generateDownload(new ContentRequest(
					$request->getParameter('id'),
					$request->getParameter('variant') ?: ContentRequest::OriginalVariant,
					['source' => $request->getParameter('source')]
				));

			default:
				throw new Application\BadRequestException("Unnexpected action name: '" . $request->getParameter('action') . "'.");

		}
	}

}
