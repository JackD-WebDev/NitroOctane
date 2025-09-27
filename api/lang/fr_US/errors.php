<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Error Language Lines (French placeholder)
	|--------------------------------------------------------------------------
	|
	| Placeholder French translations for error messages. Replace with proper
	| translations when available.
	|
	*/

	'generic' => [
		'title' => 'ERREUR INTERNE DU SERVEUR',
		'message' => 'Une erreur est survenue. Veuillez réessayer plus tard.'
	],
	'model' => [
		'not_found' => [
			'title' => 'Ressource non trouvée',
			'message' => 'La ressource demandée est introuvable.'
		],
		'not_defined' => [
			'title' => 'MODÈLE NON DÉFINI',
			'message' => 'Le modèle spécifié n\'est pas correctement défini.'
		]
	],
	'authorization' => [
		'title' => 'NON AUTORISÉ',
		'message' => 'Vous n\'êtes pas autorisé à effectuer cette action.'
	],
	'malformed_resource_response' => 'Le tableau de données doit contenir les clés "data", "links" et "meta".',
	'forbidden' => 'Interdit',
	'unauthorized' => 'Vous n\'êtes pas autorisé à accéder à cette ressource.',
	'not_found' => 'Ressource non trouvée',
	'model_not_found' => 'La ressource demandée est introuvable.',
	'not_defined' => 'Ressource non définie',
	'model_not_defined' => 'La ressource demandée n\'est pas définie.',
	'malformed' => 'Données malformées',
	'malformed_data' => 'Les données fournies sont malformées.',
	'validation' => 'Erreur de validation',
	'validation_error' => 'Les données fournies n\'ont pas passé la validation.',
	'authentication' => [
		'title' => 'NON AUTHENTIFIÉ',
		'message' => 'Authentification requise.'
	],
	'csrf' => [
		'title' => 'PAGE EXPIRÉE',
		'message' => 'Votre session a expiré. Veuillez actualiser et réessayer.'
	],
	'throttle' => [
		'title' => 'TROP DE REQUÊTES',
		'message' => 'Vous avez effectué trop de requêtes. Veuillez réessayer plus tard.'
	],
	'http' => [
		'title' => 'ERREUR HTTP',
		'message' => 'Une erreur HTTP est survenue.'
	],
	'database' => [
		'title' => 'ERREUR DE BASE DE DONNÉES',
		'message' => 'Une erreur serveur est survenue. Veuillez réessayer plus tard.'
	]

];
