<?php
/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Taco\NetteMedia;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use LogicException;


class MimeTypeUtilsTest extends TestCase
{

	#[DataProvider('dataConstructUrl_Found')]
	function testConstructUrl_Found(string $expected, string $src)
	{
		$this->assertSame($expected, MimeTypeUtils::normalizeExtension($src));
	}



	static function dataConstructUrl_Found() : array
	{
		return [
			'empty' => ['', ''],
			'noop' => ['png', 'png'],
			'unkonw' => ['abcde', 'abcde'],
			'jpg' => ['jpeg', 'jpg'],
			'jpeg' => ['jpeg', 'jpeg'],
			'jpe' => ['jpeg', 'jpe'],
		];
	}



	#[DataProvider('dataGetExtension')]
	function testGetExtension(?string $expected, string $src)
	{
		$this->assertSame($expected, MimeTypeUtils::getExtension($src));
	}



	static function dataGetExtension() : array
	{
		return [
			'empty' => [null, ''],
			'unkonw' => [Null, 'image/abcde'],
			'noop' => ['png', 'image/png'],
			'jpg' => ['jpeg', 'image/jpeg'],
			'php' => ['php', 'text/x-php'],
			'txt' => ['txt', 'text/plain'],
		];
	}



	#[DataProvider('dataGetMimeType')]
	function testGetMimeType(?string $expected, string $src)
	{
		$this->assertSame($expected, MimeTypeUtils::getMimeType($src));
	}



	static function dataGetMimeType() : array
	{
		return [
			'empty' => [null, ''],
			'unkonw' => [Null, 'abcde'],
			'noop' => ['image/png', 'png'],
			'jpg' => ['image/jpeg', 'jpeg'],
			'php' => ['text/x-php', 'php'],
			'plain' => ['text/plain', 'txt'],
		];
	}

}
