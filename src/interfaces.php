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


interface MediaProvider
{

	/**
	 * @return Content
	 */
	function getContent(ContentRequest $request);

}



interface Transformation
{

	function transform(Image $image): Image;

}



interface Content
{

	/**
	 * @return string Like "images/jpeg"
	 */
	function getContentType();

	/**
	 * @return int
	 */
	function getSize();

	/**
	 * @return string
	 */
	function getName();

	/**
	 * @return string
	 */
	function getContent();

}



interface ThumbnailsCache
{

	/**
	 * @param string $id For example: 'dee/uee.jpg'
	 * @param string $variant For example: 'small'
	 * @return ?Image
	 */
	function load(string $id, string $variant);



	/**
	 * @param string $id For example: 'dee/uee.jpg'
	 * @param string $variant For example: 'small'
	 * @return void
	 */
	function save(string $id, string $variant, Image $image);

}
