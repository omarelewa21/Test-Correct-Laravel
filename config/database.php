<?php

$readHosts = [];
$writeHosts = [];

if(env('DB_CONNECTION','mysql') === 'mysql_master_slave'){
    $rHosts = explode(',',env('DB_READ_HOSTS'));
    $wHosts = explode(',',env('DB_WRITE_HOSTS'));

    foreach($rHosts as $host){
        $hostData = explode(':',$host);
        if(count($hostData) === 1){
            $readHosts[] = ['host' => $host];
        } else if (count($hostData) === 2){
            $readHosts[] = ['host' => $hostData[0],'port' => $hostData[1]];
        } else { // probably an ip v6 address
            $readHosts[] = ['host' => $host];
        }
    }

    foreach($wHosts as $host){
        $hostData = explode(':',$host);
        if(count($hostData) === 1){
            $writeHosts[] = ['host' => $host];
        } else if (count($hostData) === 2){
            $writeHosts[] = ['host' => $hostData[0],'port' => $hostData[1]];
        } else { // probably an ip v6 address
            $writeHosts[] = ['host' => $host];
        }
    }
}

return [

	/*
	|--------------------------------------------------------------------------
	| PDO Fetch Style
	|--------------------------------------------------------------------------
	|
	| By default, database results will be returned as instances of the PHP
	| stdClass object; however, you may desire to retrieve records in an
	| array format for simplicity. Here you can tweak the fetch style.
	|
	*/

	'fetch' => PDO::FETCH_CLASS,

	/*
	|--------------------------------------------------------------------------
	| Default Database Connection Name
	|--------------------------------------------------------------------------
	|
	| Here you may specify which of the database connections below you wish
	| to use as your default connection for all database work. Of course
	| you may use many connections at once using the Database library.
	|
	*/

    'default' => env('DB_CONNECTION', 'mysql'),

	/*
	|--------------------------------------------------------------------------
	| Database Connections
	|--------------------------------------------------------------------------
	|
	| Here are each of the database connections setup for your application.
	| Of course, examples of configuring each database platform that is
	| supported by Laravel is shown below to make development simple.
	|
	|
	| All database work in Laravel is done through the PHP PDO facilities
	| so make sure you have the driver for your particular database of
	| choice installed on your machine before you begin development.
	|
	*/

	'connections' => [

		'sqlite' => [
			'driver'   => 'sqlite',
			'database' => env('DB_DATABASE', database_path('database.sqlite')),//':memory:',database_path().'/seeds/test.db',
			'prefix'   => '',
            'foreign_key_constraints' => true,
		],

        'mysql_master_slave' => [
            'read'      => $readHosts,
            'write'     => $writeHosts,
            'sticky'    => env('DB_STICKY', true),
            'driver'    => 'mysql',
            'database'  => env('DB_DATABASE', 'forge'),
            'username'  => env('DB_USERNAME', 'forge'),
            'password'  => env('DB_PASSWORD', ''),
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
            'strict'    => false,
        ],

		'mysql' => [
			'driver'    => 'mysql',
			'host'      => env('DB_HOST', 'localhost'),
			'database'  => env('DB_DATABASE', 'forge'),
			'username'  => env('DB_USERNAME', 'forge'),
            'port'      => env('DB_PORT', '3306'),
			'password'  => env('DB_PASSWORD', ''),
			'charset'   => 'utf8',
			'collation' => 'utf8_unicode_ci',
			'prefix'    => '',
			'strict'    => false,
		],

		'pgsql' => [
			'driver'   => 'pgsql',
			'host'     => env('DB_HOST', 'localhost'),
			'database' => env('DB_DATABASE', 'forge'),
			'username' => env('DB_USERNAME', 'forge'),
			'password' => env('DB_PASSWORD', ''),
			'charset'  => 'utf8',
			'prefix'   => '',
			'schema'   => 'public',
		],

		'sqlsrv' => [
			'driver'   => 'sqlsrv',
			'host'     => env('DB_HOST', 'localhost'),
			'database' => env('DB_DATABASE', 'forge'),
			'username' => env('DB_USERNAME', 'forge'),
			'password' => env('DB_PASSWORD', ''),
			'prefix'   => '',
		],

	],

	/*
	|--------------------------------------------------------------------------
	| Migration Repository Table
	|--------------------------------------------------------------------------
	|
	| This table keeps track of all the migrations that have already run for
	| your application. Using this information, we can determine which of
	| the migrations on disk haven't actually been run in the database.
	|
	*/

	'migrations' => 'migrations',

	/*
	|--------------------------------------------------------------------------
	| Redis Databases
	|--------------------------------------------------------------------------
	|
	| Redis is an open source, fast, and advanced key-value store that also
	| provides a richer set of commands than a typical key-value systems
	| such as APC or Memcached. Laravel makes it easy to dig right in.
	|
	*/

	'redis' => [

		'cluster' => false,

		'default' => [
			'host'     => '127.0.0.1',
			'port'     => 6379,
			'database' => 0,
		],

	],

];
