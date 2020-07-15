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

$lang = array_merge($lang, [
	'SCHEDULE_TOPIC_ACTION'			=> 'Запланировать действие с темой',
	'SELECT_ACTION'					=> 'Выбрать действие',
	'DELETE_ACTION'					=> 'Удалить действие',
	'TOPIC_ACTION_ERROR'			=> 'Запрошенное действие не может быть выполнено.',
	'TOPIC_ACTION_PERFORMED'		=> 'Действие было выполнено успешно.',
	'TOPIC_ACTION_NO_PERMISSION'	=> 'У вас нет соответствующих прав доступа для выполнения запрошенного действия.',
	'TOPIC_ACTION_SET'				=> 'Время действия с темой было установлено успешно.',
	'TOPIC_ACTION_DELETED'			=> 'Время действия с темой было удалено успешно.',
	'NO_ACTION_SELECTED'			=> 'Не выбрано желаемое действие.',
	'NO_TIME_SET'					=> 'Действие не было запланировано.',
	'TOPIC_ACTION'	=> [
		'NOT_ENOUGH_PARAMS'	=> 'Недостаточно параметров.',
		'DELAY'				=> 'Время выполнения действия.',
		'DELAY_EXPLAIN'		=> 'Тема запланирована к %1$s на время: %2$s',
		'TIME'	=> [
			'0'  => 'Сейчас',
			'1'  => 'через 1 день',
			'3'  => 'через 3 дня',
			'5'  => 'через 5 дней',
			'7'  => 'через 7 дней',
			'14' => 'через 2 недели',
			'30' => 'через 1 месяц'
		],
		'TYPE'	=> [
			'trash'			=> 'Поместить в корзину',
			'trash_lock'	=> 'Закрыть сейчас и запланировать удаление в корзину',
			'delete'		=> 'Удалить',
			'lock'			=> 'Закрыть',
			'unlock'		=> 'Открыть',
			/*			'FORK'			=> 'Копировать',
						'CHANGE_TYPE'	=> array(
							'MAKE_ANNOUNCE'	=> 'Сделать объявлением',
							'MAKE_GLOBAL'	=> 'Сделать важной',
							'MAKE_STICKY'	=> 'Сделать прилепленной',
							'MAKE_NORMAL'	=> 'Сделать обычной'
						)
			*/
		],
		'TYPE_NOTICE'       => [
			'trash'      => 'удалению в корзину',
			'trash_lock' => 'удалению в корзину',
			'delete'     => 'удалению',
			'lock'       => 'закрытию',
			'unlock'     => 'открытию',
		],
	],
]);
