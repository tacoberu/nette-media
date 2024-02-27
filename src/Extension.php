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

use Nette;
use Nette\Application\Routers\Route as NetteRoute;
use Nette\Bridges\ApplicationLatte\ILatteFactory;
use Nette\DI;
use Nette\DI\Definitions\Statement;
use Nette\Schema\Expect;
use Latte;
use LogicException;


class Extension extends DI\CompilerExtension
{

	function getConfigSchema(): Nette\Schema\Schema
	{
		return Expect::structure([
			'prependRoutesToRouter' => Expect::bool()->default(True),
			'injectToLatte' => Expect::bool()->default(True),
			'cache' => Expect::type(Statement::class),
			'route' => Expect::type(Statement::class),
			'transformations' => Expect::arrayOf(Expect::listOf(Statement::class)),
			'providers' => Expect::listOf(Statement::class),
		]);
	}



	/**
	 * @return void
	 */
	function loadConfiguration()
	{
		$container = $this->getContainerBuilder();

		$container->addDefinition($this->prefix('cache'))
			->setCreator($this->getConfig()->cache);

		$container->getDefinition($container->getByType(Nette\Application\IPresenterFactory::class))->addSetup(
			'setMapping',
			[['WebMedia' => __namespace__ . '\*Presenter']]
		);

		$generator = $container->addDefinition($this->prefix('generator'))
			->setClass(ContentGenerator::class);

		foreach ($this->getConfig()->transformations as $name => $rule) {
			$generator->addSetup('$service->addTransformation(?, ?)', [
				$name,
				$rule,
			]);
		}

		$router = $container->addDefinition($this->prefix('router'))
			->setClass(Nette\Application\Routers\RouteList::class)
			->addTag($this->prefix('routeList'))
			->setAutowired(False);
		$router->addSetup('$service[] = ?', [
			$this->getConfig()->route,
		]);
		if (empty($this->getConfig()->providers)) {
			throw new InvalidConfigException("You have to register at least one MediaProvider in '" . $this->prefix('providers') . "' directive.");
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



	/**
	 * @return void
	 */
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

}
