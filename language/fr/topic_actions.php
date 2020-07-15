<?php
/**
*
* Topic actions schedule extension for the phpBB Forum Software package.
* French translation by Galixte (http://www.galixte.com)
*
* @copyright (c) 2015 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
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
	$lang = [];
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
//
// Some characters you may want to copy&paste:
// ’ « » “ ” …
//

$lang = array_merge($lang, [
	'SCHEDULE_TOPIC_ACTION'			=> 'Planifier l’action sur le sujet',
	'SELECT_ACTION'					=> 'Sélectionner',
	'DELETE_ACTION'					=> 'Supprimer',
	'TOPIC_ACTION_ERROR'			=> 'L’action demandée n’a pu être réalisée.',
	'TOPIC_ACTION_PERFORMED'		=> 'L’action demandée a pu être réalisée avec succès.',
	'TOPIC_ACTION_NO_PERMISSION'	=> 'Afin de réaliser l’action demandée il est nécessaire d’avoir les permissions adéquates.',
	'TOPIC_ACTION_SET'				=> 'Temps d’action paramétré avec succès.',
	'TOPIC_ACTION_DELETED'			=> 'Temps d’action supprimé avec succès.',
	'NO_ACTION_SELECTED'			=> 'Aucune action sélectionnée.',
	'NO_TIME_SET'					=> 'L’action n’a pas été planifiée.',
	'TOPIC_ACTION'	=> [
		'NOT_ENOUGH_PARAMS'	=> 'Pas assez de paramètres.',
		'DELAY'				=> 'Délais de l’action.',
		'DELAY_EXPLAIN'		=> 'Action planifiée sur le sujet : %1$s le %2$s',
		'TIME'	=> [
			'0'  => 'Maintenant',
			'1'  =>  'dans un jour',
			'3'  => 'dans trois jours',
			'5'  => 'dans cinq jours',
			'7'  => 'dans une semaine',
			'14'  => 'dans deux semaines',
			'30'  => 'dans un mois'
		],
		'TYPE'	=> [
			'trash'			=> 'Supprimer (restauration possible)',
			'trash_lock'	=> 'Verrouiller puis supprimer (restauration possible)',
			'delete'		=> 'Supprimer définitivement',
			'lock'			=> 'Verrouiller',
			'unlock'		=> 'Déverrouiller',
			/*			'FORK'			=> 'Copier',
						'CHANGE_TYPE'	=> array(
							'MAKE_ANNOUNCE'	=> 'Créer une annonce',
							'MAKE_GLOBAL'	=> 'Créer une annonce globale',
							'MAKE_STICKY'	=> 'Créer un post-it',
							'MAKE_NORMAL'	=> 'Créer un sujet'
						)
			*/
		],
		'TYPE_NOTICE'	=> [
			'trash'			=> 'supprimé (restauration possible)',
			'trash_lock'	=> 'verrouillé puis supprimer (restauration possible)',
			'delete'		=> 'supprimé',
			'lock'			=> 'verrouillé',
			'unlock'		=> 'déverrouillé',
		],
	],
]);
