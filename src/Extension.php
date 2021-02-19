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

use Nette\Application\Routers\Route as NetteRoute;
use Nette\DI;


class Extension extends DI\CompilerExtension
{

	/** @var array */
	private $defaults = [
		'routes' => [],
		'prependRoutesToRouter' => TRUE,
		'rules' => [],
		'providers' => [],
		// úložiště pro nakešovaná data
		'cacheDir' => '%wwwDir%'
	];



	function loadConfiguration()
	{
		$container = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);

		$validator = $container->addDefinition($this->prefix('validator'))
			->setClass(Validator::class);

		$generator = $container->addDefinition($this->prefix('generator'))
			->setClass(Generator::class, [
				$config['cacheDir'],
			]);

		foreach ($config['rules'] as $name => $rule) {
			$validator->addSetup('$service->addRule(?, ?, ?, ?, ?)', [
				$name,
				$rule['width'],
				$rule['height'],
				isset($rule['algorithm']) ? $rule['algorithm'] : Null,
				isset($rule['quality']) ? $rule['quality'] : Null,
			]);
		}

		if ($config['routes']) {
			$router = $container->addDefinition($this->prefix('router'))
				->setClass('Nette\Application\Routers\RouteList')
				->addTag($this->prefix('routeList'))
				->setAutowired(False);

			$i = 0;
			foreach ($config['routes'] as $route => $definition) {
				if (!is_array($definition)) {
					$definition = [
						'mask' => $definition,
						'defaults' => [],
					];
				} else {
					if (!isset($definition['defaults'])) {
						$definition['defaults'] = [];
					}
				}

				$route = $container->addDefinition($this->prefix('route' . $i))
					->setClass(Route::class, [
						$definition['mask'],
						$definition['defaults'],
						$this->prefix('@generator'),
					])
					->addTag($this->prefix('route'))
					->setAutowired(FALSE);

				if (isset($definition['id'])) {
					if (($parameter = $this->recognizeMaskParameter($definition['id'])) || $parameter === FALSE || $parameter === NULL) {
						$route->addSetup('setIdParameter', [
							$parameter,
						]);
					} else {
						$route->addSetup('setId', [
							$definition['id'],
						]);
					}
				}

				$router->addSetup('$service[] = ?', [
					$this->prefix('@route' . $i),
				]);

				$i++;
			}
		}

		if (count($config['providers']) === 0) {
			throw new InvalidConfigException("You have to register at least one IProvider in '" . $this->prefix('providers') . "' directive.");
		}

		foreach ($config['providers'] as $name => $provider) {
			$this->compiler->parseServices($container, [
				'services' => [$this->prefix('provider' . $name) => $provider],
			]);
			$generator->addSetup('addProvider', [$this->prefix('@provider' . $name)]);
		}

		if ($latte = $container->getDefinition('nette.latteFactory')) {
			$latte->addSetup(Macros::class . '::install(?->getCompiler())', ['@self']);
		}
	}



	function beforeCompile()
	{
		$container = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);

		if ($config['prependRoutesToRouter']) {
			$router = $container->getByType('Nette\Application\IRouter');
			if ($router) {
				if (!$router instanceof DI\ServiceDefinition) {
					$router = $container->getDefinition($router);
				}
			} else {
				$router = $container->getDefinition('router');
			}
			$router->addSetup(Helpers::class . '::prependRoute', [
				'@self',
				$this->prefix('@router'),
			]);
		}
	}



	/**
	 * @param  string
	 * @return string|NULL
	 */
	private function recognizeMaskParameter($value)
	{
		if ((substr($value, 0, 1) === '<') && (substr($value, -1) === '>')) {
			return substr($value, 1, -1);
		}
	}

}

class InvalidConfigException extends \Exception {}
