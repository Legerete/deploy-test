<?php

return [
	'parameters' => [
		'database' => [
				'host' => getenv('MYSQL_HOST'),
				'port' => getenv('MYSQL_PORT'),
				'user' => getenv('MYSQL_USER'),
				'password' => getenv('MYSQL_PASSWORD'),
				'database' => getenv('MYSQL_NAME'),
				'debug' => false,
				'metadata' => [
					'App' => '%appDir%'
				]
			]
	]

];
