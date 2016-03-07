<?php
/**
 *
 * @package       Topic Actions
 * @copyright (c) 2013 - 2016 rxu and LavIgor
 * @license       http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace rxu\TopicActions\event;

/**
 * Event listener
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface
{
	/** @var bool Whether the topic has scheduled action */
	public $flag;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \rxu\TopicActions\functions\manager */
	protected $manager;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $php_ext;

	public function __construct(\phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\template\template $template, \phpbb\user $user, \phpbb\request\request_interface $request, \rxu\TopicActions\functions\manager $manager, $phpbb_root_path, $php_ext)
	{
		$this->config = $config;
		$this->db = $db;
		$this->template = $template;
		$this->user = $user;
		$this->request = $request;
		$this->manager = $manager;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
		$this->flag = false;
	}

	static public function getSubscribedEvents()
	{
		return array(
			'core.modify_quickmod_options'     => 'action_type',
			'core.modify_quickmod_actions'     => 'topic_action',
			'core.viewtopic_modify_page_title' => 'template_assign_vars',
			'core.viewforum_modify_topicrow'   => 'modify_icon_path_viewtopic',
			'core.search_modify_tpl_ary'       => 'modify_icon_path_search',
			'core.page_footer'                 => 'modify_header',
		);
	}

	public function action_type($event)
	{
		$module = $event['module'];
		$module->load('mcp', 'main', 'quickmod');
	}

	public function topic_action($event)
	{
		$action = $event['action'];
		$quickmod = $event['quickmod'];

		if ($action != 'topic_action' || !$quickmod)
		{
			return;
		}

		$this->user->add_lang_ext('rxu/TopicActions', 'topic_actions');
		$this->user->add_lang(array('viewtopic'));

		$forum_id = $this->request->variable('f', 0);
		$topic_id = $this->request->variable('t', 0);
		$topic_action = $this->request->variable('topic_action', '');
		$topic_action_time = $this->request->variable('topic_action_time', -1);
		$delete_action = $this->request->variable('delete_action', false);

		if (!$topic_id)
		{
			trigger_error('NO_TOPIC_SELECTED');
		}

		$message = '';

		if ($delete_action)
		{
			if ($this->set_topic_action_time('', 0, $topic_id))
			{
				$message .= $this->user->lang('TOPIC_ACTION_DELETED');
			}
			else
			{
				$message .= $this->user->lang('TOPIC_ACTION_ERROR');
			}
		}
		else
		{
			if (empty($topic_action))
			{
				trigger_error($this->user->lang('NO_ACTION_SELECTED'));
			}

			if ($topic_action_time == -1)
			{
				trigger_error($this->user->lang('NO_TIME_SET'));
			}

			$result = $this->manager->perform_preliminary($topic_action, $topic_id, $forum_id);
			if ($result !== true)
			{
				$message .= $result;
			}
			else if ($topic_action_time > 0)
			{
				if ($this->set_topic_action_time($topic_action, $topic_action_time, $topic_id))
				{
					$message .= $this->user->lang('TOPIC_ACTION_SET');
				}
				else
				{
					$message .= $this->user->lang('TOPIC_ACTION_ERROR');
				}

			}
			else if ($topic_action_time == 0)
			{
				$this->set_topic_action_time('', 0, $topic_id);
				$message .= $this->manager->perform_action($topic_action, $topic_id, $forum_id);
			}
		}

		if ($topic_action != 'delete' || $this->manager->had_errors)
		{
			$message .= '<br /><br />' . sprintf($this->user->lang['RETURN_TOPIC'], '<a href="' . append_sid("{$this->phpbb_root_path}viewtopic.$this->php_ext", "f=$forum_id&amp;t=$topic_id") . '">', '</a>');
		}
		$message .= '<br /><br />' . sprintf($this->user->lang['RETURN_FORUM'], '<a href="' . append_sid("{$this->phpbb_root_path}viewforum.$this->php_ext", "f=$forum_id") . '">', '</a>');
		$message .= '<br /><br />' . sprintf($this->user->lang['RETURN_INDEX'], '<a href="' . append_sid("{$this->phpbb_root_path}index.$this->php_ext") . '">', '</a>');
		trigger_error($message);
	}

	public function template_assign_vars($event)
	{
		$topic_data = $event['topic_data'];
		$forum_id = $event['forum_id'];
		$topic_id = $topic_data['topic_id'];

		$this->user->add_lang_ext('rxu/TopicActions', 'topic_actions');

		$action_select = $this->topic_action_select($forum_id, $topic_id);
		$action_select_time = ($action_select) ? $this->topic_action_time_select() : false;

		$this->template->assign_vars(array(
			'TOPIC_ACTION_SELECT'      => $action_select,
			'TOPIC_ACTION_TIME_SELECT' => $action_select_time,
			'TOPIC_ACTION_TIME'        => ($topic_data['topic_action_time'] && ($topic_data['topic_action_time'] > $this->config['topics_last_gc'])) ? sprintf($this->user->lang['TOPIC_ACTION']['DELAY_EXPLAIN'], $this->user->lang['TOPIC_ACTION']['TYPE_NOTICE'][$topic_data['topic_action_type']], $this->user->format_date($topic_data['topic_action_time'])) : '',
		));
	}

	/**
	 * Set Topic Action Time
	 */
	public function set_topic_action_time($action = '', $time = 0, $topic_id = false)
	{
		if (!$topic_id)
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
			WHERE topic_id = ' . $topic_id;

		if ($this->db->sql_query($sql))
		{
			return $sql_ary;
		}

		return false;
	}

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

	private function modify_icon_path($topicrow)
	{
		if (isset($topicrow['TOPIC_ICON_IMG']) && strpos($topicrow['TOPIC_ICON_IMG'], 'trash.png'))
		{
			$this->flag = true;
		}
	}

	public function modify_icon_path_viewtopic($event)
	{
		$topicrow = $event['topic_row'];
		$this->modify_icon_path($topicrow);
	}

	public function modify_icon_path_search($event)
	{
		$topicrow = $event['tpl_ary'];
		$this->modify_icon_path($topicrow);
	}

	public function modify_header()
	{
		if ($this->flag)
		{
			$this->template->assign_vars(array(
				'T_ICONS_PATH' => '',
			));
		}
	}
}
