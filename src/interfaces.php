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


interface IProvider
{

	const FIT = 0;
	const EXACT = 1;
	const EXACT_HEIGHT_FIT_WIDTH = 2;



	function getImage(ImageRequest $request);

}



interface Ref
{

	function getRef();

}



interface Transformation
{

	function transform(Image $image): Image;

}



interface Content
{

	function getContentType();

	function getSize();

	function getName();

	function getContent();

}



interface ThumbnailsCache
{

	/**
	 * @param string $id For example: 'dee/uee.jpg'
	 * @param string $variant For example: 'small'
	 */
	function load(string $id, string $variant): ?Image;



	/**
	 * @param string $id For example: 'dee/uee.jpg'
	 * @param string $variant For example: 'small'
	 */
	function save(string $id, string $variant, Image $image): void;

}
