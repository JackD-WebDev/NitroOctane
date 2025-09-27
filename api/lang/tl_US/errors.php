<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Error Language Lines (Tagalog placeholder)
	|--------------------------------------------------------------------------
	|
	| Placeholder Tagalog translations for error messages. Replace with proper
	| translations when available.
	|
	*/

	'generic' => [
		'title' => 'Panloob na Error ng Server',
		'message' => 'May nangyaring pagkakamali. Pakisubukang muli mamaya.'
	],
	'model' => [
		'not_found' => [
			'title' => 'Hindi Natagpuan ang Recurso',
			'message' => 'Ang hiniling na recurso ay hindi natagpuan.'
		],
		'not_defined' => [
			'title' => 'Hindi Naka-define ang Modelo',
			'message' => 'Ang tinukoy na modelo ay hindi tama ang pagkakadefine.'
		]
	],
	'authorization' => [
		'title' => 'Hindi Awtorisado',
		'message' => 'Wala kang pahintulot na gawin ang aksyong ito.'
	],
	'malformed_resource_response' => 'Ang data array ay dapat maglaman ng mga susi na "data", "links", at "meta".',
	'forbidden' => 'Ipinagbabawal',
	'unauthorized' => 'Wala kang pahintulot na i-access ang resource na ito.',
	'not_found' => 'Hindi natagpuan ang resource',
	'model_not_found' => 'Ang hiniling na resource ay hindi natagpuan.',
	'not_defined' => 'Hindi naka-define ang resource',
	'model_not_defined' => 'Ang hiniling na resource ay hindi naka-define.',
	'malformed' => 'Sira ang data',
	'malformed_data' => 'Sira ang mga ibinigay na data.',
	'validation' => 'Error sa pag-validate',
	'validation_error' => 'Ang mga ibinigay na data ay hindi pumasa sa pag-validate.',
	'authentication' => [
		'title' => 'Hindi Na-authenticate',
		'message' => 'Kinakailangan ang authentication.'
	],
	'csrf' => [
		'title' => 'Nag-expiry ang Pahina',
		'message' => 'Nag-expire ang iyong session. Pakirefresh at subukan muli.'
	],
	'throttle' => [
		'title' => 'Masyadong Maraming Request',
		'message' => 'Masyado kang maraming gumawa ng request. Pakisubukan muli mamaya.'
	],
	'http' => [
		'title' => 'HTTP Error',
		'message' => 'Nagkaroon ng HTTP error.'
	],
	'database' => [
		'title' => 'Database Error',
		'message' => 'Nagkaroon ng server error. Pakisubukan muli mamaya.'
	]

];
