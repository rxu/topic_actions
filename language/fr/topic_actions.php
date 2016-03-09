<?php
/** 
*
* Topic Actions extension for the phpBB Forum Software package.
*
* @copyright (c) 2013 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* French translation by Galixte (http://www.galixte.com)
*
*/

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

$lang = array_merge($lang, array(
	'SCHEDULE_TOPIC_ACTION'			=> 'Schedule topic action',
	'SELECT_ACTION'					=> 'Select action',
	'DELETE_ACTION'					=> 'Delete action',
	'TOPIC_ACTION_ERROR'			=> 'The requested action could not be performed.',
	'TOPIC_ACTION_PERFORMED'		=> 'The requested action successfully completed.',
	'TOPIC_ACTION_NO_PERMISSION'	=> 'You do not have correct permissions to perform the requested action.',
	'TOPIC_ACTION_SET'				=> 'Action time was set successfully.',
	'TOPIC_ACTION_DELETED'			=> 'Action time was deleted successfully.',
	'NO_ACTION_SELECTED'			=> 'No action was selected.',
	'NO_TIME_SET'					=> 'Action was not planned.',
	'TOPIC_ACTION'	=> array(
		'NOT_ENOUGH_PARAMS'	=> 'Pas assez de paramètres.',
		'NO_ACTION_SELECTED'=> 'Aucune action sélectionnée.',
		'NO_TIME_SET'		=> 'Action non planifiée.',
		'DELAY'				=> 'Délais de l’action.',
		'DELAY_EXPLAIN'		=> 'Action planifiée sur le sujet : %1$s le %2$s',
		'SELECT_ACTION'		=> 'Sélectionner l’action',
		'DELETE_ACTION'		=> 'Supprimer l’action',
		'TOPIC_ACTION_PERFORMED'=> 'L’action planifiée a été exécutée avec succès.',
		'TOPIC_ACTION_SET'	=> 'La planification de l’action a été paramétrée avec succès.',
		'TOPIC_ACTION_DELETED'	=> 'La planification de l’action a été supprimée avec succès.',
		'TIME'	=> array(
			'0'		=> 'Maintenant',
			'1'		=> 'dans un jour',
			'3'		=> 'dans trois jours',
			'5'		=> 'dans cinq jours',
			'7' 	=> 'dans une semaine',
			'14'	=> 'dans deux semaines',
			'30'	=> 'dans un mois'
		),
		'TYPE'	=> array(
			'trash'			=> 'Supprimer (avec restauration possible)',
			'trash_lock'	=> 'Verrouiller puis supprimer (avec restauration possible)',
			'delete'		=> 'Supprimer définitivement',
			'lock'			=> 'Lock',
			'unlock'		=> 'Unlock',
			/*			'FORK'			=> 'Copier',
						'LOCK'			=> 'Verrouiller',
						'UNLOCK'		=> 'Déverrouiller',
						'CHANGE_TYPE'	=> array(
							'MAKE_ANNOUNCE'	=> 'Créer une annonce',
							'MAKE_GLOBAL'	=> 'Créer une annonce globale',
							'MAKE_STICKY'	=> 'Créer un post-it',
							'MAKE_NORMAL'	=> 'Créer un sujet'
						)
			*/
		),
		'TYPE_NOTICE'	=> array(
			'trash'			=> 'soft deleted',
			'trash_lock'	=> 'soft deleted',
			'delete'		=> 'deleted',
			'lock'			=> 'locked',
			'unlock'		=> 'unlocked',
		),
	),
));
