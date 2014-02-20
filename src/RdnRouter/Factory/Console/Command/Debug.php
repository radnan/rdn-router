<?php

namespace RdnRouter\Factory\Console\Command;

use RdnConsole\Factory\Command\AbstractCommandFactory;
use RdnRouter\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class Debug extends AbstractCommandFactory
{
	public function configure()
	{
		$this->adapter
			->setName('router:debug')
			->setDescription('Displays route information for the http router')
			->addArgument(
				'name',
				InputArgument::OPTIONAL,
				'Show information for a specific route.'
			)
		;
	}

	protected function create()
	{
		$routes = $this->config('router', 'routes');
		return new Command\Debug($routes);
	}
}
