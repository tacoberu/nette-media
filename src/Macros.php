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

use Latte;
use Latte\MacroNode;
use Latte\PhpWriter;
use InvalidArgumentException;


/**
 * {media 8JGqz.gif}
 * {media 8JGqz.gif, small}
 * {download 8JGqz.gif}
 * <img n:media="shamahFinal.jpg">
 * <img n:media="shamahFinal.jpg, small">
 * {media $img}
 */
class Macros extends Latte\Macros\MacroSet
{

	static function install(Latte\Compiler $parser)
	{
		$me = new static($parser);
		$me->addMacro('media', function (MacroNode $node, PhpWriter $writer) use ($me) {
			return $me->macroSrc($node, $writer);
		}, NULL, function(MacroNode $node, PhpWriter $writer) use ($me) {
			return ' ?> src="<?php ' . $me->macroSrc($node, $writer) . ' ?>"<?php ';
		});
		$me->addMacro('download', function (MacroNode $node, PhpWriter $writer) use ($me) {
			return $me->macroDownload($node, $writer);
		}, NULL, function(MacroNode $node, PhpWriter $writer) use ($me) {
			return ' ?> src="<?php ' . $me->macroDownload($node, $writer) . ' ?>"<?php ';
		});
	}



	/**
	 * {download name}
	 *
	 * @return string
	 * @throws Nette\Latte\CompileException
	 */
	function macroDownload(MacroNode $node, PhpWriter $writer)
	{
		$absolute = substr($node->args, 0, 2) === '//' ? '//' : '';
		$args = $absolute ? substr($node->args, 2) : $node->args;
		$args = array_map('trim', explode(',', $args));
		self::assertCountArgs($args);
		$node->modifiers = preg_replace('#\|safeurl\s*(?=\||\z)#i', '', $node->modifiers);
		$args['download'] = true;
		return $writer->using($node)
			->write('echo %escape(%modify($_presenter->link("' . $absolute . ':Nette:Micro:", ' . __class__ . '::prepareArguments(' . self::varExportNamed($args) . '))))');
	}



	/**
	 * {media name [,] [size]}
	 * n:media="name [,] [size]"
	 *
	 * @return string
	 * @throws Nette\Latte\CompileException
	 */
	function macroSrc(MacroNode $node, PhpWriter $writer)
	{
		$absolute = substr($node->args, 0, 2) === '//' ? '//' : '';
		$args = $absolute ? substr($node->args, 2) : $node->args;
		$args = array_map('trim', explode(',', $args));
		self::assertCountArgs($args);
		$node->modifiers = preg_replace('#\|safeurl\s*(?=\||\z)#i', '', $node->modifiers);
		return $writer->using($node)
			->write('echo %escape(%modify($_presenter->link("' . $absolute . ':Nette:Micro:", ' . __class__ . '::prepareArguments(' . self::varExportNamed($args) . '))))');
	}



	/**
	 * @return array
	 */
	static function prepareArguments(array $arguments)
	{
		foreach ($arguments as $key => $value) {
			if ($key === 0 && !isset($arguments['id'])) {
				$arguments['id'] = $value;
				unset($arguments[$key]);
			}
			elseif ($key === 1 && !isset($arguments['size'])) {
				$arguments['size'] = $value;
				unset($arguments[$key]);
			}
		}

		return $arguments;
	}



	private static function varExportNamed(array $args)
	{
		$args = array_map(function($x) {
			if ( ! is_string($x)) {
				return var_export($x, True);
			}
			if ($x[0] !== '$') {
				return var_export($x, True);
			}
			return $x;
		}, $args);

		foreach ($args as $k => $v) {
			if (is_string($k)) {
				$args[$k] = "'{$k}' => {$v}";
			}
		}

		return '[' . implode(', ', $args) . ']';
	}



	private static function assertCountArgs(array $args)
	{
		if (count($args) > 2) {
			throw new InvalidArgumentException('Unsupported count of arguments. First argument is name of image (with ext). Second is optional size.');
		}
	}

}
