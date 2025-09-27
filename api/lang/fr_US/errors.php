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
		'title' => 'Erreur Interne du Serveur',
		'message' => 'Une erreur est survenue. Veuillez réessayer plus tard.'
	],
	'model' => [
		'not_found' => [
			'title' => 'Ressource non trouvée',
			'message' => 'La ressource demandée est introuvable.'
		],
		'not_defined' => [
			'title' => 'Modèle non défini',
			'message' => 'Le modèle spécifié n\'est pas correctement défini.'
		]
	],
	'authorization' => [
		'title' => 'Non autorisé',
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
		'title' => 'Non authentifié',
		'message' => 'Authentification requise.'
	],
	'csrf' => [
		'title' => 'Page expirée',
		'message' => 'Votre session a expiré. Veuillez actualiser et réessayer.'
	],
	'throttle' => [
		'title' => 'Trop de requêtes',
		'message' => 'Vous avez effectué trop de requêtes. Veuillez réessayer plus tard.'
	],
	'http' => [
		'title' => 'Erreur HTTP',
		'message' => 'Une erreur HTTP est survenue.'
	],
	'database' => [
		'title' => 'Erreur de base de données',
		'message' => 'Une erreur serveur est survenue. Veuillez réessayer plus tard.'
	]

];
