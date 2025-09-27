<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Error Language Lines
	|--------------------------------------------------------------------------
	|
	| The following language lines are used by the error controller to build
	| the error related messages. You are free to change them to anything
	| you want to customize your views to better match your application.
	|
	*/

	'generic' => [
		'title' => 'Internal Server Error',
		'message' => 'Something went wrong. Please try again later.'
	],
	'model' => [
		'not_found' => [
			'title' => 'Resource Not Found',
			'message' => 'The requested resource was not found.'
		],
		'not_defined' => [
			'title' => 'Model Not Defined',
			'message' => 'The specified model is not properly defined.'
		]
	],
	'authorization' => [
		'title' => 'Unauthorized',
		'message' => 'You are not authorized to perform this action.'
	],
	'malformed_resource_response' => 'The data array must contain "data", "links", and "meta" keys.',
	'forbidden' => 'Forbidden',
	'unauthorized' => 'You are not authorized to access this resource.',
	'not_found' => 'Resource not found',
	'model_not_found' => 'The requested resource was not found.',
	'not_defined' => 'Resource not defined',
	'model_not_defined' => 'The requested resource is not defined.',
	'malformed' => 'Malformed data',
	'malformed_data' => 'The data provided is malformed.',
	'validation' => 'Validation error',
	'validation_error' => 'The data provided did not pass validation.',
	'authentication' => [
		'title' => 'Unauthenticated',
		'message' => 'Authentication required.'
	],
	'csrf' => [
		'title' => 'Page Expired',
		'message' => 'Your session has expired. Please refresh and try again.'
	],
	'throttle' => [
		'title' => 'Too Many Requests',
		'message' => 'You have made too many requests. Please try again later.'
	],
	'http' => [
		'title' => 'HTTP Error',
		'message' => 'An HTTP error occurred.'
	],
	'database' => [
		'title' => 'Database Error',
		'message' => 'A server error occurred. Please try again later.'
	]

];