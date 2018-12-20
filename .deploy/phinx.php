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
			"name" => "lmanager",
			"adapter" => "pgsql",
			"host" => "bl_db",
			"user" => getenv('PHINX_DB_USER'),
			"pass" => getenv('PHINX_DB_PASS'),
			"port" => 5432,
			"charset" => "utf8",
		],

		"test" => [
			"name" => "lmanager_test",
			"adapter" => "pgsql",
			"host" => "bl_db",
			"user" => getenv('PHINX_TEST_DB_USER'),
			"pass" => getenv('PHINX_TEST_DB_PASS'),
			"port" => 5432,
			"charset" => "utf8",
		],
	],
	"version_order" => "creation",
];