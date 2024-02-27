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

use Nette\Application;


class Helpers
{

	/**
	 * @return void
	 */
	static function prependRoute(Application\Routers\RouteList $router, Application\IRouter $route)
	{
		$router[] = $route;

		$lastKey = count($router) - 1;
		foreach ($router as $i => $r) {
			if ($i === $lastKey) {
				break;
			}
			$router[$i + 1] = $r;
		}

		$router[0] = $route;
	}

}
