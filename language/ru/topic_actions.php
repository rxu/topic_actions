<?php
/** 
*
* topic_actions [Russian]
*
* @package language
* @copyright (c) 2014 Ruslan Uzdenov (rxu)
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
		'NOT_ENOUGH_PARAMS'	=> 'Недостаточно параметров.',
		'NO_ACTION_SELECTED'=> 'Не выбрано желаемое действие.',
		'NO_TIME_SET'		=> 'Действие не было запланировано.',
		'DELAY'				=> 'Время выполнения действия.',
		'DELAY_EXPLAIN'		=> 'Запланировано действие с темой: %1$s на время: %2$s',
		'SELECT_ACTION'		=> 'Выбрать действие',
		'DELETE_ACTION'		=> 'Удалить действие',
		'TOPIC_ACTION_PERFORMED'=> 'Действие было выполнено успешно.',
		'TOPIC_ACTION_SET'	=> 'Время действия с темой было установлено успешно.',
		'TOPIC_ACTION_DELETED'	=> 'Время действия с темой было удалено успешно.',
		'TIME'				=> array(
			'0'		=> 'Сейчас',
			'1'		=> 'через 1 день',
			'3'		=> 'через 3 дня',
			'5'		=> 'через 5 дней',
			'7' 	=> 'через 7 дней',
			'14'	=> 'через 2 недели',
			'30'	=> 'через 1 месяц'
		),
		'TYPE'	=> array(
			'RECYCLE'		=> 'Поместить в корзину',
			'RECYCLE_LOCK'	=> 'Поместить в корзину (закрыть)',
			'DELETE'		=> 'Удалить',
/*			'FORK'			=> 'Копировать',
			'LOCK'			=> 'Закрыть',
			'UNLOCK'		=> 'Открыть',
			'CHANGE_TYPE'	=> array(
				'MAKE_ANNOUNCE'	=> 'Сделать объявлением',
				'MAKE_GLOBAL'	=> 'Сделать важной',
				'MAKE_STICKY'	=> 'Сделать прилепленной',
				'MAKE_NORMAL'	=> 'Сделать обычной'
			)
*/		)
	)

));

?>