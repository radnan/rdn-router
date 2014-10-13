<?php

namespace RdnRouter\Console\Command;

use RdnConsole\Command\AbstractCommand;
use RuntimeException;
use Symfony\Component\Console\Helper\TableHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Debug extends AbstractCommand
{
	/**
	 * @var array
	 */
	protected $routes;

	public function __construct($routes)
	{
		$this->routes = $routes;
	}

	public function execute(InputInterface $input, OutputInterface $output)
	{
		$name = $input->getArgument('name');

		if ($name)
		{
			$route = $this->getSingleRoute($name);
			$output->writeln(array(
				'<comment>Parent</comment>       '. (strpos($name, '/') !== false ? dirname($name) : '<root>'),
				'<comment>Name</comment>         '. $name,
				'<comment>Type</comment>         '. $route['type'],
				'<comment>Terminal?</comment>    '. ($route['terminal'] ? 'Yes' : 'No'),
				'<comment>Pattern</comment>      '. $route['pattern'],
				'<comment>Defaults</comment>     '. ($this->formatArray($route['defaults']) ?: '-'),
				'<comment>Constraints</comment>  '. ($this->formatArray($route['constraints']) ?: '-'),
				'<comment>Child routes</comment> '. ($this->formatArray($route['child_routes']) ?: '-'),
			));
		}
		else
		{
			$routes = $this->getAllRoutes();

			/** @var TableHelper $table */
			$table = $this->getAdapter()->getHelper('table');
			$table->setHeaders(array(
				'Name',
				'Type',
				'Terminal?',
				'Pattern',
				'Controller',
				'Action',
			));

			foreach ($routes as $name => $route)
			{
				$table->addRow(array(
					$name,
					$route['type'],
					$route['terminal'] ? 'Yes' : 'No',
					$route['pattern'],
					isset($route['defaults']['controller']) ? $route['defaults']['controller'] : '',
					isset($route['defaults']['action']) ? $route['defaults']['action'] : '',
				));
			}

			$table->render($output);
		}
	}

	protected function getSingleRoute($name)
	{
		$routes = $this->parseRoutes($this->routes);
		if (!isset($routes[$name]))
		{
			throw new RuntimeException("Could not find route ($name)");
		}

		return $routes[$name];
	}

	protected function getAllRoutes()
	{
		$routes = $this->parseRoutes($this->routes);
		uksort($routes, function($a, $b)
		{
			return strnatcasecmp($a, $b);
		});
		return $routes;
	}

	protected function formatArray($values)
	{
		$output = array();
		foreach ($values as $name => $value)
		{
			$output[] = (!is_numeric($name) ? $name .': ' : '') . $value;
		}
		return implode("\n" . str_repeat(' ', 13), $output);
	}

	protected function parseRoutes(array $routes, $parent = '', $pattern = '', $options = array())
	{
		$parsed = array();
		foreach ($routes as $name => $route)
		{
			$parsed = array_merge($parsed, $this->parseRoute($route, $parent . $name, $pattern, $options));
		}
		return $parsed;
	}

	protected function parseRoute(array $route, $name = '', $pattern = '', $options = array())
	{
		$parsed = array();

		$route = array_replace_recursive(array(
			'type' => 'Literal',
			'options' => array(
				'route' => '',
				'defaults' => array(),
				'constraints' => array(),
			),
			'may_terminate' => false,
			'child_routes' => array(),
		), $route);
		$options = array_replace_recursive(array(
			'defaults' => array(),
			'constraints' => array(),
		), $options);

		$pattern .= $route['options']['route'];
		$defaults = array_merge($options['defaults'], $route['options']['defaults']);
		$constraints = array_merge($options['constraints'], $route['options']['constraints']);

		$parsed[$name] = array(
			'name' => $name,
			'type' => $route['type'],
			'pattern' => $pattern,
			'terminal' => ($route['may_terminate'] || empty($route['child_routes'])),
			'defaults' => $defaults,
			'constraints' => $constraints,
			'child_routes' => array_map(function($child) use ($name)
			{
				return $name .'/'. $child;
			}, array_keys($route['child_routes'])),
		);

		sort($parsed[$name]['child_routes']);

		$parsed = array_merge($parsed, $this->parseRoutes($route['child_routes'], $name .'/', $pattern, array(
			'defaults' => $defaults,
			'constraints' => $constraints,
		)));

		return $parsed;
	}
}
