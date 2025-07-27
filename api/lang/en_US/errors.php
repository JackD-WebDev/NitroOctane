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
	'validation_error' => 'The data provided did not pass validation.'

];