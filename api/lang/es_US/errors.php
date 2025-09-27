<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Error Language Lines (Spanish placeholder)
	|--------------------------------------------------------------------------
	|
	| Placeholder Spanish translations for error messages. Replace these with
	| proper translations when available.
	|
	*/

	'generic' => [
		'title' => 'ERROR INTERNO DEL SERVIDOR',
		'message' => 'Algo salió mal. Por favor, inténtelo de nuevo más tarde.'
	],
	'model' => [
		'not_found' => [
			'title' => 'RECURSO NO ENCONTRADO',
			'message' => 'El recurso solicitado no fue encontrado.'
		],
		'not_defined' => [
			'title' => 'MODELO NO DEFINIDO',
			'message' => 'El modelo especificado no está correctamente definido.'
		]
	],
	'authorization' => [
		'title' => 'NO AUTORIZADO',
		'message' => 'No está autorizado para realizar esta acción.'
	],
	'malformed_resource_response' => 'El array de datos debe contener las claves "data", "links" y "meta".',
	'forbidden' => 'Prohibido',
	'unauthorized' => 'No está autorizado para acceder a este recurso.',
	'not_found' => 'Recurso no encontrado',
	'model_not_found' => 'El recurso solicitado no fue encontrado.',
	'not_defined' => 'Recurso no definido',
	'model_not_defined' => 'El recurso solicitado no está definido.',
	'malformed' => 'Datos malformados',
	'malformed_data' => 'Los datos proporcionados están mal formados.',
	'validation' => 'Error de validación',
	'validation_error' => 'Los datos proporcionados no pasaron la validación.',
	'authentication' => [
		'title' => 'NO AUTENTICADO',
		'message' => 'Autenticación requerida.'
	],
	'csrf' => [
		'title' => 'PÁGINA EXPIRADA',
		'message' => 'Su sesión ha expirado. Por favor, actualice e inténtelo de nuevo.'
	],
	'throttle' => [
		'title' => 'DEMASIADAS PETICIONES',
		'message' => 'Ha realizado demasiadas peticiones. Por favor, inténtelo de nuevo más tarde.'
	],
	'http' => [
		'title' => 'ERROR HTTP',
		'message' => 'Ocurrió un error HTTP.'
	],
	'database' => [
		'title' => 'ERROR DE BASE DE DATOS',
		'message' => 'Ocurrió un error en el servidor. Por favor, inténtelo de nuevo más tarde.'
	]

];