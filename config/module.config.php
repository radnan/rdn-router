<?php

return array(
	'rdn_console' => array(
		'commands' => array(
			'RdnRouter:Debug',
		),
	),

	'rdn_console_commands' => array(
		'factories' => array(
			'RdnRouter:Debug' => 'RdnRouter\Factory\Console\Command\Debug',
		),
	),
);
