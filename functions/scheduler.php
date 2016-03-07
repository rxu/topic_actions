<?php
/**
 *
 * @package       Topic Actions
 * @copyright (c) 2013 - 2016 rxu and LavIgor
 * @license       http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace rxu\TopicActions\functions;

/**
 * Topic Actions scheduler.
 */
class scheduler
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \rxu\TopicActions\functions\manager */
	protected $manager;

	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\template\template $template, \phpbb\user $user, \rxu\TopicActions\functions\manager $manager)
	{
		$this->db = $db;
		$this->template = $template;
		$this->user = $user;
		$this->manager = $manager;
	}

	/**
	 * Set Topic action time (also checks for permissions).
	 *
	 * @param string $action   The requested action
	 * @param int    $time     The requested schedule time
	 * @param int    $topic_id Topic ID
	 * @param int    $forum_id Forum ID
	 * @return string|false
	 */
	public function set_topic_action_time($action = '', $time = 0, $topic_id = 0, $forum_id = 0)
	{
		if (!$topic_id || ($action && !$this->manager->check_auth($action, $topic_id, $forum_id)))
		{
			return false;
		}

		$sql = 'SELECT icons_id FROM ' . ICONS_TABLE . '
			WHERE icons_url ' . $this->db->sql_like_expression($this->db->get_any_char() . 'trash.png');
		$result = $this->db->sql_query($sql);
		$icon_id = (int) $this->db->sql_fetchfield('icons_id');
		$this->db->sql_freeresult($result);

		$sql_ary = array(
			'topic_action_time' => ($time > 0) ? (time() + $time * 86400) : 0,
			'topic_action_type' => $action,
			'icon_id'           => ($time > 0 && $icon_id) ? $icon_id : 0,
		);

		$sql = 'UPDATE ' . TOPICS_TABLE . ' SET  ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
			WHERE topic_id = ' . (int) $topic_id;

		if ($this->db->sql_query($sql))
		{
			return $sql_ary;
		}

		return false;
	}

	/**
	 * Unsets scheduled topic action for the specified topic.
	 *
	 * @param int $topic_id Topic ID
	 * @return string|false
	 */
	public function unset_topic_action($topic_id)
	{
		return $this->set_topic_action_time('', 0, $topic_id);
	}

	/**
	 * Build HTML string for action selection field.
	 *
	 * @param int    $forum_id Forum ID
	 * @param int    $topic_id Topic ID
	 * @param string $default  The default selected option
	 * @return bool|string
	 */
	public function topic_action_select($forum_id, $topic_id, $default = 'trash')
	{
		$actions = $this->manager->get_actions($topic_id, $forum_id);
		if (!sizeof($actions))
		{
			return false;
		}

		$topic_action_select = '';
		foreach ($actions as $key => $action)
		{
			$selected = ($action == $default) ? ' selected="selected"' : '';
			$topic_action_select .= '<option value="' . $action . '"' . $selected . '>' . $this->user->lang(array('TOPIC_ACTION', 'TYPE', $action)) . '</option>';
		}

		$topic_action_select = '<select id="topic_action" name="topic_action">' . $topic_action_select . '</select>';

		return $topic_action_select;
	}

	/**
	 * Build HTML string for time selection field.
	 *
	 * @param int $default The default selected option
	 * @return string|false
	 */
	public function topic_action_time_select($default = 0)
	{
		$topic_action_time_select = '';
		$actions = array();
		if (sizeof($this->user->lang['TOPIC_ACTION']['TIME']))
		{
			$actions = array_keys($this->user->lang['TOPIC_ACTION']['TIME']);
		}
		else
		{
			return false;
		}

		foreach ($actions as $key => $days)
		{
			$selected = ($key == $default) ? ' selected="selected"' : '';
			$topic_action_time_select .= '<option value="' . $days . '"' . $selected . '>' . $this->user->lang['TOPIC_ACTION']['TIME'][$days] . '</option>';
		}

		$topic_action_time_select = (!empty($topic_action_time_select)) ? '<select id="topic_action_time" name="topic_action_time">' . $topic_action_time_select . '</select>' : '';

		return $topic_action_time_select;
	}
}
