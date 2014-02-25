<?php
/** 
*
* topic_actions [Ukrainian]
*
* @package language
* @copyright (c) 2014 Alex Fryschyn (Sherlock)
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
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

	'TOPIC_ACTION'	=> array(
		'NOT_ENOUGH_PARAMS'	=> 'Недостатньо параметрів.',
		'NO_ACTION_SELECTED'=> 'Не обрано бажану дію.',
		'NO_TIME_SET'		=> 'Дію не було заплановано.',
		'DELAY'				=> 'Час виконання дії.',
		'DELAY_EXPLAIN'		=> 'Запланована дія з темою: %1$s на час: %2$s',
		'SELECT_ACTION'		=> 'Вибрати дію',
		'DELETE_ACTION'		=> 'Видалити дію',
		'TOPIC_ACTION_PERFORMED'=> 'Дію було виконано успішно.',
		'TOPIC_ACTION_SET'	=> 'Час дії з темою було встановлено успішно.',
		'TOPIC_ACTION_DELETED'	=> 'Час дії з темою було видалено успішно.',
		'TIME'				=> array(
			'0'		=> 'Зараз',
			'1'		=> 'через 1 день',
			'3'		=> 'через 3 дня',
			'5'		=> 'через 5 днів',
			'7' 	=> 'через 7 днів',
			'14'	=> 'через 2 тижні',
			'30'	=> 'через 1 місяць'
		),
		'TYPE'	=> array(
			'RECYCLE'		=> 'Перемістити в корзину',
			'RECYCLE_LOCK'	=> 'Перемістити в корзину (закрити)',
			'DELETE'		=> 'Видалити',
/*			'FORK'			=> 'Копіювати',
			'LOCK'			=> 'Закрити',
			'UNLOCK'		=> 'Відкрити',
			'CHANGE_TYPE'	=> array(
				'MAKE_ANNOUNCE'	=> 'Зробити оголошенням',
				'MAKE_GLOBAL'	=> 'Зробити важливою',
				'MAKE_STICKY'	=> 'Зробити прикріпленою',
				'MAKE_NORMAL'	=> 'Зробити звичайною',
			)
*/		)
	)

));

?>