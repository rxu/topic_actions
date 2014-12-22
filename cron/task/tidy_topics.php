<?php
/**
*
* Topic Actions extension for the phpBB Forum Software package.
*
* @copyright (c) 2013 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace rxu\TopicActions\cron\task;

/**
* Tidy topics cron task.
*
* @package TopicActions
*/
class tidy_topics extends \phpbb\cron\task\base
{
	protected $config;
	protected $db;
	protected $user;

	/**
	* Constructor.
	*
	* @param phpbb_config $config The config
	* @param phpbb_config $config The dbal.conn
	* @param phpbb_user $user The user
	*/
	public function __construct(\phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\user $user, \phpbb\content_visibility $content_visibility, \phpbb\log\log $phpbb_log, $phpbb_root_path, $php_ext)
	{
		$this->config = $config;
		$this->db = $db;
		$this->user = $user;
		$this->content_visibility = $content_visibility;
		$this->phpbb_log = $phpbb_log;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	/**
	* Runs this cron task.
	*
	* @return null
	*/
	public function run()
	{
		$this->cron_tidy_topics();
	}

	/**
	* Returns whether this cron task should run now, because enough time
	* has passed since it was last run.
	*
	* The interval between topics tidying is specified in extension
	* configuration.
	*
	* @return bool
	*/
	public function should_run()
	{
		return $this->config['topics_last_gc'] < time() - $this->config['topics_gc'];
	}

	public function cron_tidy_topics($topic_ids = array())
	{
		$current_time = time();

		$this->user->add_lang_ext('rxu/TopicActions', 'topic_actions');

		$actions = $topics_list = array();
		if(sizeof($this->user->lang['TOPIC_ACTION']['TYPE']))
		{
			$actions = array_keys($this->user->lang['TOPIC_ACTION']['TYPE']);
		}
		else
		{
			return false;
		}

		$where_sql = (sizeof($topic_ids)) ? $this->db->sql_in_set('topic_id', $topic_ids) : ' topic_action_time <= ' . $current_time . ' AND topic_action_time > ' . (int) $this->config['topics_last_gc'];
		$sql = 'SELECT topic_id, topic_action_type FROM ' . TOPICS_TABLE . ' WHERE ' . $where_sql;
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$topics_list[$row['topic_action_type']][] = $row['topic_id'];
		}
		$this->db->sql_freeresult($result);

		if (sizeof($topics_list))
		{
			foreach($actions as $action)
			{
				switch ($action)
				{
					case 'RECYCLE':
					case 'RECYCLE_LOCK':
						if(isset($topics_list[$action]))
						{
							$this->soft_delete_topics($topics_list[$action]);
						}
					break;

					case 'DELETE':
						if(isset($topics_list[$action]))
						{
							include_once($this->phpbb_root_path . 'includes/functions_admin.' . $this->php_ext);
							delete_topics('topic_id', $topics_list[$action]);
						}
					break;

					default:
					break;
				}
			}
		}
		$this->config->set('topics_last_gc', $current_time, true);
	}

	/**
	* Soft delete Topics
	*/
	function soft_delete_topics($topic_ids, $soft_delete_reason = '', $action = 'delete_topic')
	{
		$success_msg = (sizeof($topic_ids) == 1) ? 'TOPIC_DELETED_SUCCESS' : 'TOPICS_DELETED_SUCCESS';

		$sql = 'SELECT topic_id, forum_id, topic_title, topic_first_poster_name, topic_moved_id 
			FROM ' . TOPICS_TABLE . ' 
			WHERE ' . $this->db->sql_in_set('topic_id', $topic_ids);
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$topic_id = (int) $row['topic_id'];
			// Only soft delete non-shadow topics
			if (!$row['topic_moved_id'])
			{
				$return = $this->content_visibility->set_topic_visibility(ITEM_DELETED, $topic_id, $row['forum_id'], $this->user->data['user_id'], time(), $soft_delete_reason);
				if (!empty($return))
				{
					$this->phpbb_log->add('mod', $this->user->data['user_id'], $this->user->ip, 'LOG_SOFTDELETE_TOPIC', time(), array('forum_id' => $row['forum_id'], 'topic_id' => $topic_id, 'topic_title' => $row['topic_title'], 'topic_first_poster_name' => $row['topic_first_poster_name']));
				}
			}
		}
	}
}
