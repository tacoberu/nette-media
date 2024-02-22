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

use Nette;
use Nette\Application\Routers\Route as NetteRoute;
use Nette\Bridges\ApplicationLatte\ILatteFactory;
use Nette\DI;
use Nette\DI\Definitions\Statement;
use Nette\Schema\Expect;
use Latte;


class Extension extends DI\CompilerExtension
{

	/**
	 * úložiště pro nakešovaná data
	 * @var string
	 */
	private string $cacheDir;


	function __construct($cacheDir)
	{
		$this->cacheDir = $cacheDir;
	}



	function getConfigSchema(): Nette\Schema\Schema
	{
		return Expect::structure([
			'prependRoutesToRouter' => Expect::bool()->default(True),
			'injectToLatte' => Expect::bool()->default(True),
			'cacheDir' => Expect::string()->default($this->cacheDir),
			'routes' => Expect::listOf('string'),
			'rules' => Expect::arrayOf(Expect::structure([
				'width' => Expect::int(),
				'height' => Expect::int(),
				'algorithm' => Expect::string(),
				'quality' => Expect::int(),
			]), 'string'),
			'providers' => Expect::listOf(Statement::class),
		]);
	}



	function loadConfiguration()
	{
		$container = $this->getContainerBuilder();

		$validator = $container->addDefinition($this->prefix('validator'))
			->setClass(Validator::class);

		$generator = $container->addDefinition($this->prefix('generator'))
			->setFactory(Generator::class, [
				'cacheDir' => $this->getConfig()->cacheDir,
			]);

		foreach ($this->getConfig()->rules as $name => $rule) {
			$validator->addSetup('$service->addRule(?, ?, ?, ?, ?)', [
				$name,
				$rule->width,
				$rule->height,
				isset($rule->algorithm) ? $rule->algorithm : Null,
				isset($rule->quality) ? $rule->quality : Null,
			]);
		}

		if (count($this->getConfig()->routes)) {
			$router = $container->addDefinition($this->prefix('router'))
				->setClass(Nette\Application\Routers\RouteList::class)
				->addTag($this->prefix('routeList'))
				->setAutowired(False);

			$i = 0;
			foreach ($this->getConfig()->routes as $route => $definition) {
				if (!is_array($definition)) {
					$definition = [
						'mask' => $definition,
						'defaults' => [],
					];
				}
				else {
					if (!isset($definition['defaults'])) {
						$definition['defaults'] = [];
					}
				}

				$route = $container->addDefinition($this->prefix('route' . $i))
					->setFactory(Route::class, [
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
					}
					else {
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

		if (empty($this->getConfig()->providers)) {
			throw new InvalidConfigException("You have to register at least one IProvider in '" . $this->prefix('providers') . "' directive.");
		}

		foreach ($this->getConfig()->providers as $name => $provider) {
			$generator->addSetup('addProvider', [$provider]);
		}

		if ($this->config->injectToLatte) {
			$latteFactory = $container->getDefinitionByType(ILatteFactory::class);
			if (version_compare(Latte\Engine::VERSION, '3', '<')) {
				$latteFactory->getResultDefinition()
					->addSetup('?->onCompile[] = function ($engine) { ' . Macros::class . '::install($engine->getCompiler()); }'
						, ['@self']);
			}
			else {
				throw new \LogicException('comming soon...');
			}
		}
	}



	function beforeCompile()
	{
		$container = $this->getContainerBuilder();

		if ($this->getConfig()->prependRoutesToRouter) {
			$router = $container->getByType(Nette\Application\IRouter::class);
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
