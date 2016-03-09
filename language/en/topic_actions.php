<?php
/**
 *
 * @package       Topic Actions
 * @copyright (c) 2013 - 2016 rxu and LavIgor
 * @license       http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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
	'SCHEDULE_TOPIC_ACTION'      => 'Schedule topic action',
	'SELECT_ACTION'              => 'Select action',
	'DELETE_ACTION'              => 'Delete action',
	'TOPIC_ACTION_ERROR'         => 'The requested action could not be performed.',
	'TOPIC_ACTION_PERFORMED'     => 'The requested action successfully completed.',
	'TOPIC_ACTION_NO_PERMISSION' => 'You do not have correct permissions to perform the requested action.',
	'TOPIC_ACTION_SET'           => 'Action time was set successfully.',
	'TOPIC_ACTION_DELETED'       => 'Action time was deleted successfully.',
	'NO_ACTION_SELECTED'         => 'No action was selected.',
	'NO_TIME_SET'                => 'Action was not planned.',
	'TOPIC_ACTION'               => array(
		'NOT_ENOUGH_PARAMS' => 'Not enough parameters.',
		'DELAY'             => 'Action delay.',
		'DELAY_EXPLAIN'     => 'This topic is planned to be %1$s on: %2$s',
		'TIME'              => array(
			'0'  => 'Now',
			'1'  => 'in 1 day',
			'3'  => 'in 3 days',
			'5'  => 'in 5 days',
			'7'  => 'in 7 days',
			'14' => 'in 2 weeks',
			'30' => 'in 1 month'
		),
		'TYPE'              => array(
			'trash'      => 'Soft delete',
			'trash_lock' => 'Lock now and shedule soft delete',
			'delete'     => 'Delete',
			'lock'       => 'Lock',
			'unlock'     => 'Unlock',
			/*			'FORK'			=> 'Копировать',
						'CHANGE_TYPE'	=> array(
							'MAKE_ANNOUNCE'	=> 'Сделать объявлением',
							'MAKE_GLOBAL'	=> 'Сделать важной',
							'MAKE_STICKY'	=> 'Сделать прилепленной',
							'MAKE_NORMAL'	=> 'Сделать обычной'
						)
			*/
		),
		'TYPE_NOTICE'       => array(
			'trash'      => 'soft deleted',
			'trash_lock' => 'soft deleted',
			'delete'     => 'deleted',
			'lock'       => 'locked',
			'unlock'     => 'unlocked',
		),
	),
));
