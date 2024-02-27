<?php
/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Taco\NetteMedia;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use LogicException;
use Nette\Http\Request;
use Nette\Http\UrlScript;


class RouteTest extends TestCase
{

	function _testSample1()
	{
		$validator = $this->createMock(ContentGenerator::class);
		$request = $this->createMock(Request::class);
		//~ $request->getUrl()->expects($this->once())
			//~ ->method('getPath')
			//~ ->willReturn('abc');
		$router = new Route('x', $validator);

dump($request->expects($this->once())->method('getUrl'));
dump($request->getUrl()->expects($this->once()));

die("\n------\n" . __file__ . ':' . __line__ . "\n");
dump($request->getUrl());
dump($request->getUrl()->getPath());
		$this->assertNull($router->match($request));
	}



	function testConstructUrl_Null()
	{
		$validator = $this->createMock(ContentGenerator::class);
		$urlScript = $this->createMock(UrlScript::class);
		$router = new Route('x', $validator);
		$this->assertNull($router->constructUrl([], $urlScript));
		$this->assertNull($router->constructUrl(['a' => 2, 'b' => 1], $urlScript));
		$this->assertNull($router->constructUrl([
			'action' => 'fileManager',
			'id' => null,
			'filter' => 'ector',
			'presenter' => 'Special',
		], $urlScript));
		$this->assertNull($router->constructUrl([
			'action' => 'download',
			'id' => null,
			'presenter' => Route::Presenter,
		], $urlScript));
	}



	#[DataProvider('dataConstructUrl_Found')]
	function testConstructUrl_Found(string $expected, array $params)
	{
		$validator = $this->createMock(ContentGenerator::class);
		$validator->expects($this->any())
			->method('validVariant')
			->with('big')
			->willReturn(true);
		$urlScript = $this->createMock(UrlScript::class);
		$router = new Route('x', $validator);

		$this->assertSame($expected, $router->constructUrl($params, $urlScript));
	}



	static function dataConstructUrl_Found() : array
	{
		return [
			'orig' => ['/x/abc/dddd.jpg', [
				'id' => 'abc/dddd.jpg',
				'presenter' => Route::Presenter,
				]],
			'ignore action' => ['/x/abc/dddd.jpg', [
				'action' => 'download',
				'id' => 'abc/dddd.jpg',
				'presenter' => Route::Presenter,
				]],
			'download' => ['/x/abc/dddd.jpg?download', [
				'download' => 1,
				'id' => 'abc/dddd.jpg',
				'presenter' => Route::Presenter,
				]],
			'variant' => ['/x/abc/dddd.jpg?big', [
				'size' => 'big',
				'id' => 'abc/dddd.jpg',
				'presenter' => Route::Presenter,
				]],
		];
	}

}
