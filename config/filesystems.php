<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Default Filesystem Disk
	|--------------------------------------------------------------------------
	|
	| Here you may specify the default filesystem disk that should be used
	| by the framework. A "local" driver, as well as a variety of cloud
	| based drivers are available for your choosing. Just store away!
	|
	| Supported: "local", "s3", "rackspace"
	|
	*/

	'default' => 'local',

	/*
	|--------------------------------------------------------------------------
	| Default Cloud Filesystem Disk
	|--------------------------------------------------------------------------
	|
	| Many applications store files both locally and in the cloud. For this
	| reason, you may specify a default "cloud" driver here. This driver
	| will be bound as the Cloud disk implementation in the container.
	|
	*/

	'cloud' => 's3',

	/*
	|--------------------------------------------------------------------------
	| Filesystem Disks
	|--------------------------------------------------------------------------
	|
	| Here you may configure as many filesystem "disks" as you wish, and you
	| may even configure multiple disks of the same driver. Defaults have
	| been setup for each driver as an example of the required options.
	|
	*/

	'disks' => [

		'local' => [
			'driver' => 'local',
			'root'   => storage_path().'/app',
            'throw' => true,
		],
        'inline_images' => [
            'driver' => 'local',
            'root'   => storage_path().'/inlineimages',
            'throw' => true,
        ],

        'attachments' => [
            'driver' => 'local',
            'root'   => storage_path().'/attachments',
            'throw' => true,
        ],
        'pdf_images' => [
            'driver' => 'local',
            'root'   => storage_path().'/pdf_images',
            'throw' => true,
        ],
        'temp_pdf' => [
            'driver' => 'local',
            'root'   => storage_path().'/temp_pdf',
            'throw' => true,
        ],
        'cake' => [
            'driver' => 'local',
            'root' => env('CAKE_STORAGE_PATH', base_path().'/../testportal.test-correct/app/tmp/'),
            'throw' => true,
        ],

        \tcCore\Http\Helpers\SvgHelper::DISK => [
            'driver' => 'local',
            'root'   => storage_path('drawing-question-svg'),
            'throw' => true,
        ],

		's3' => [
			'driver' => 's3',
			'key'    => 'your-key',
			'secret' => 'your-secret',
			'bucket' => 'your-bucket',
		],

		'rackspace' => [
			'driver'    => 'rackspace',
			'username'  => 'your-username',
			'key'       => 'your-key',
			'container' => 'your-container',
			'endpoint'  => 'https://identity.api.rackspacecloud.com/v2.0/',
			'region'    => 'IAD',
		],

        'test_uploads' => [
            'driver' => 'local',
            'root' => storage_path('app/files'),
            'throw' => true,
        ],

        'content_source' => [
            'driver' => 'local',
            'root' => app_path('Services/ContentSource'),
            'throw' => true,
        ],

	],

];
