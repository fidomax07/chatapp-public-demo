<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Format of the timestamp
	|--------------------------------------------------------------------------
	|
	| This defines the format of the timestamp that is returned on most API
	| calls.
	|
	*/
	'date_format' => env('API_DATE_FORMAT', 'Y-m-d'),
	'timestamp_format' => env('API_TIMESTAMP_FORMAT', 'Y-m-d H:i:s'),

];
