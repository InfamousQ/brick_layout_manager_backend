<?php

return [
	"paths" => [
		"migrations" => '/var/www/html/db/migrations',
		"seeds" =>  '/var/www/html/db/seeds',
	],
	"environments" => [
		"default_migration_table" => "phinxlog",
		"default_database" => "development",

		"development" => [
			"name" => getenv('DB_NAME'),
			"adapter" => "pgsql",
			"host" => getenv('DB_HOST'),
			"port" => getenv('DB_PORT'),
			"user" => getenv('DB_USER'),
			"pass" => getenv('DB_PASS'),
			"charset" => "utf8",
		],

		"test" => [
			"name" => getenv('DB_NAME'),
			"adapter" => "pgsql",
			"host" => getenv('DB_HOST'),
			"port" => getenv('DB_PORT'),
			"user" => getenv('DB_USER'),
			"pass" => getenv('DB_PASS'),
			"charset" => "utf8",
		],
	],
	"version_order" => "creation",
];