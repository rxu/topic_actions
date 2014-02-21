<?php
/**
 *
 * @package topic_actions
 * @copyright (c) 2014 Ruslan Uzdenov (rxu)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace rxu\topic_actions\event;

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
    exit;
}

/**
* Event listener
*/
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface
{
	public $flag;

    public function __construct(\phpbb\config\config $config, \phpbb\db\driver\driver $db, \phpbb\auth\auth $auth, \phpbb\template\template $template, \phpbb\user $user, $phpbb_root_path, $php_ext)
    {
        $this->template = $template;
        $this->user = $user;
		$this->auth = $auth;
		$this->db = $db;
		$this->config = $config;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
		$this->flag = false;
    }

	static public function getSubscribedEvents()
	{
		return array(
			'core.modify_quickmod_options'		=> 'action_type',
			'core.modify_quickmod_actions'		=> 'topic_action',
			'core.viewtopic_modify_page_title'	=> 'template_assign_vars',
			'core.viewforum_modify_topicrow'	=> 'modify_icon_path_viewtopic',
			'core.search_modify_tpl_ary'		=> 'modify_icon_path_search',
			'core.page_footer'					=> 'modify_header',
		);
	}

	public function action_type($event)
	{
		$module = $event['module'];
//		$action = $event['action'];
//		$break = $event['break'];
		$module->load('mcp', 'main', 'quickmod');
	}

	public function topic_action($event)
	{
		global $action, $quickmod;

		if ($action == 'topic_action')
		{
			$this->user->add_lang_ext('rxu/topic_actions', 'topic_actions');
			$this->user->add_lang(array('viewtopic'));

			$forum_id = (!$quickmod) ? 0 : request_var('f', 0);
			$topic_id = (!$quickmod) ? 0 : request_var('t', 0);
			$topic_action = request_var('topic_action', '');
			$topic_action_time = request_var('topic_action_time', -1);
			$delete_action = request_var('delete_action', false);

			$time_is_set = $tidy_result = false;

			if (!$topic_id)
			{
				trigger_error('NO_TOPIC_SELECTED');
			}

			if($delete_action)
			{
				$time_is_set = $this->set_topic_action_time('', 0, $topic_id);
			}
			else
			{
				if(empty($topic_action))
				{
					trigger_error($this->user->lang['TOPIC_ACTION']['NO_ACTION_SELECTED']);
				}

				if($topic_action_time == -1)
				{
					trigger_error($this->user->lang['TOPIC_ACTION']['NO_TIME_SET']);
				}
				else if($topic_action_time > 0)
				{	
					if ($topic_action == 'RECYCLE_LOCK')
					{
						$this->lock_topic('lock', $topic_id);
					}					
					$time_is_set = $this->set_topic_action_time($topic_action, $topic_action_time, $topic_id);
				}
				else if($topic_action_time == 0)
				{
					switch ($topic_action)
					{
						case 'RECYCLE':
						case 'RECYCLE_LOCK':
							$this->set_topic_action_time('', 0, $topic_id);
							$this->soft_delete_topics($topic_id);
						break;
						
						case 'DELETE':
							$this->set_topic_action_time('', 0, $topic_id);
							delete_topics('topic_id', $topic_id);
						break;
						
						default:
						break;
					}
				}
			}

			$message = ($delete_action && $time_is_set) ? $this->user->lang['TOPIC_ACTION']['TOPIC_ACTION_DELETED'] : (($time_is_set) ? $this->user->lang['TOPIC_ACTION']['TOPIC_ACTION_SET'] : (($topic_action_time == 0) ? $this->user->lang['TOPIC_ACTION']['TOPIC_ACTION_PERFORMED'] : $this->user->lang['TOPIC_ACTION']['NO_ACTION_SELECTED']));
			if($topic_action != 'DELETE')
			{
				$message .= '<br /><br />' . sprintf($this->user->lang['RETURN_TOPIC'], '<a href="' . append_sid("{$this->phpbb_root_path}viewtopic.$this->php_ext", "f=$forum_id&amp;t=$topic_id") . '">', '</a>');
			}
			$message .= '<br /><br />' . sprintf($this->user->lang['RETURN_FORUM'], '<a href="' . append_sid("{$this->phpbb_root_path}viewforum.$this->php_ext", "f=$forum_id") . '">', '</a>');
			$message .= '<br /><br />' . sprintf($this->user->lang['RETURN_INDEX'], '<a href="' . append_sid("{$this->phpbb_root_path}index.$this->php_ext") . '">', '</a>');
			trigger_error($message);
		}
	}

	public function template_assign_vars()
	{
		global $topic_data, $forum_id;

		$this->user->add_lang_ext('rxu/topic_actions', 'topic_actions');

		$this->template->assign_vars(array(
			'TOPIC_ACTION_SELECT'		=> ($this->auth->acl_get('m_', $forum_id)) ? $this->topic_action_select() : '',
			'TOPIC_ACTION_TIME_SELECT'	=> ($this->auth->acl_get('m_', $forum_id)) ? $this->topic_action_time_select() : '',
			'TOPIC_ACTION_TIME'			=> ($topic_data['topic_action_time'] && ($topic_data['topic_action_time'] > $this->config['topics_last_gc'])) ? sprintf($this->user->lang['TOPIC_ACTION']['DELAY_EXPLAIN'], $this->user->lang['TOPIC_ACTION']['TYPE'][$topic_data['topic_action_type']], $this->user->format_date($topic_data['topic_action_time'])) : '', 			
		));
	}

	/**
	* Soft delete Topics
	*/
	function soft_delete_topics($topic_ids, $soft_delete_reason = '', $action = 'delete_topic')
	{
		global $phpbb_container;

		$success_msg = (sizeof($topic_ids) == 1) ? 'TOPIC_DELETED_SUCCESS' : 'TOPICS_DELETED_SUCCESS';

		if (!is_array($topic_ids))
		{
			$topic_ids = array($topic_ids);
		}
		$data = get_topic_data($topic_ids);

		foreach ($data as $topic_id => $row)
		{
			// Only soft delete non-shadow topics
			if (!$row['topic_moved_id'])
			{
				$phpbb_content_visibility = $phpbb_container->get('content.visibility');
				$return = $phpbb_content_visibility->set_topic_visibility(ITEM_DELETED, $topic_id, $row['forum_id'], $this->user->data['user_id'], time(), $soft_delete_reason);
				if (!empty($return))
				{
					add_log('mod', $row['forum_id'], $topic_id, 'LOG_SOFTDELETE_TOPIC', $row['topic_title'], $row['topic_first_poster_name']);
				}
			}
		}
	}

	/**
	* Set Topic Action Time
	*/
	public function set_topic_action_time($action = '', $time = 0, $topic_id = false)
	{
		if(!$topic_id)
		{
			return false;
		}

		$sql = 'SELECT icons_id FROM ' . ICONS_TABLE . ' 
			WHERE icons_url ' . $this->db->sql_like_expression($this->db->any_char . 'trash.png');
		$result = $this->db->sql_query($sql);
		$icon_id = (int) $this->db->sql_fetchfield('icons_id');
		$this->db->sql_freeresult($result);

		$sql_ary = array(
			'topic_action_time'	=> ($time > 0) ? (time() + $time * 86400) : 0,
			'topic_action_type'	=> $action,
			'icon_id'			=> ($time > 0 && $icon_id) ? $icon_id : 0,
		);

		$sql = 'UPDATE ' . TOPICS_TABLE . ' SET  ' . $this->db->sql_build_array('UPDATE', $sql_ary) . ' 
			WHERE topic_id = ' . $topic_id;
		
		if($this->db->sql_query($sql))
		{
			return $sql_ary;
		}

		return false;
	}

	public function topic_action_select($default = 0)
	{
		$this->user->add_lang_ext('rxu/topic_actions', 'topic_actions');

		$topic_action_select = '';
		$actions = array();
		if(sizeof($this->user->lang['TOPIC_ACTION']['TYPE']))
		{
			$actions = array_keys($this->user->lang['TOPIC_ACTION']['TYPE']);
		}
		else
		{
			return false;
		}

		foreach($actions as $key=>$action)
		{
			$selected = ($key == $default) ? ' selected="selected"' : '';
			$topic_action_select .= '<option value="' . $action . '"' . $selected . '>' . $this->user->lang['TOPIC_ACTION']['TYPE'][$action] . '</option>';
		}

		$topic_action_select = (!empty($topic_action_select)) ? '<select id="topic_action" name="topic_action">' . $topic_action_select . '</select>' : '';

		return $topic_action_select;
	}

	public function topic_action_time_select($default = 0)
	{
		$this->user->add_lang_ext('rxu/topic_actions', 'topic_actions');

		$topic_action_time_select = '';
		$actions = array();
		if(sizeof($this->user->lang['TOPIC_ACTION']['TIME']))
		{
			$actions = array_keys($this->user->lang['TOPIC_ACTION']['TIME']);
		}
		else
		{
			return false;
		}
		
		foreach($actions as $key=>$days)
		{
			$selected = ($key == $default) ? ' selected="selected"' : '';
			$topic_action_time_select .= '<option value="' . $days . '"' . $selected . '>' . $this->user->lang['TOPIC_ACTION']['TIME'][$days] . '</option>';
		}

		$topic_action_time_select = (!empty($topic_action_time_select)) ? '<select id="topic_action_time" name="topic_action_time">' . $topic_action_time_select . '</select>' : '';

		return $topic_action_time_select;
	}

	/**
	* Lock/Unlock Topic/Post
	*/
	function lock_topic($action, $ids)
	{
		if ($action == 'lock' || $action == 'unlock')
		{
			$table = TOPICS_TABLE;
			$sql_id = 'topic_id';
			$set_id = 'topic_status';
			$l_prefix = 'TOPIC';
		}
		else
		{
			$table = POSTS_TABLE;
			$sql_id = 'post_id';
			$set_id = 'post_edit_locked';
			$l_prefix = 'POST';
		}

		if(!is_array($ids))
		{
			$ids = array($ids);
		}

		$orig_ids = $ids;

		if (!check_ids($ids, $table, $sql_id, array('m_lock')))
		{
			// Make sure that for f_user_lock only the lock action is triggered.
			if ($action != 'lock')
			{
				return;
			}

			$ids = $orig_ids;

			if (!check_ids($ids, $table, $sql_id, array('f_user_lock')))
			{
				return;
			}
		}
		unset($orig_ids);

		$sql = "UPDATE $table
			SET $set_id = " . (($action == 'lock' || $action == 'lock_post') ? ITEM_LOCKED : ITEM_UNLOCKED) . '
			WHERE ' . $this->db->sql_in_set($sql_id, $ids);
		$this->db->sql_query($sql);

		$data = ($action == 'lock' || $action == 'unlock') ? get_topic_data($ids) : get_post_data($ids);

		foreach ($data as $id => $row)
		{
			add_log('mod', $row['forum_id'], $row['topic_id'], 'LOG_' . strtoupper($action), $row['topic_title']);
		}
	}

	public function modify_icon_path_viewtopic($event)
	{
		$topicrow = $event['topic_row'];
		if(isset($topicrow['TOPIC_ICON_IMG']) && strpos($topicrow['TOPIC_ICON_IMG'], 'trash.png'))
		{
			$this->flag = true;
		}
	}

	public function modify_icon_path_search($event)
	{
		$topicrow = $event['tpl_ary'];
		if(isset($topicrow['TOPIC_ICON_IMG']) && strpos($topicrow['TOPIC_ICON_IMG'], 'trash.png'))
		{
			$this->flag = true;
		}
	}

	public function modify_header()
	{
		if ($this->flag)
		{
			$this->template->assign_vars(array(
				'T_ICONS_PATH'			=> '',
			));
		}
	}
}
