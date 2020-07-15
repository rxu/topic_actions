<?php
/**
 *
 * @package       Topic Actions
 * @copyright (c) 2013 - 2016 rxu and LavIgor
 * @license       http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace rxu\topicactions\cron\task;

/**
 * Tidy topics cron task.
 *
 * @package topicactions
 */
class tidy_topics extends \phpbb\cron\task\base
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\user */
	protected $user;

	/** @var \rxu\topicactions\functions\scheduler */
	protected $scheduler;

	/** @var \rxu\topicactions\functions\manager */
	protected $manager;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\config\config                  $config
	 * @param \phpbb\db\driver\driver_interface     $db
	 * @param \phpbb\user                           $user
	 * @param \rxu\topicactions\functions\scheduler $scheduler
	 * @param \rxu\topicactions\functions\manager   $manager
	 */
	public function __construct(\phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\user $user, \rxu\topicactions\functions\scheduler $scheduler, \rxu\topicactions\functions\manager $manager)
	{
		$this->config = $config;
		$this->db = $db;
		$this->user = $user;
		$this->scheduler = $scheduler;
		$this->manager = $manager;
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

	public function cron_tidy_topics($topic_ids = [])
	{
		$this->user->add_lang_ext('rxu/topicactions', 'topic_actions');
		$current_time = time();
		$topics_list = [];

		$where_sql = (sizeof($topic_ids)) ? $this->db->sql_in_set('topic_id', $topic_ids) : 'topic_action_time <= ' . $current_time . ' AND topic_action_time > ' . (int) $this->config['topics_last_gc'];
		$sql = 'SELECT forum_id, topic_id, topic_action_type FROM ' . TOPICS_TABLE . ' WHERE ' . $where_sql;
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$topics_list[] = $row;
		}
		$this->db->sql_freeresult($result);

		if (sizeof($topics_list))
		{
			foreach ($topics_list as $row)
			{
				// @todo: Log errors to error log.
				$this->manager->execute_action($row['topic_action_type'], $row['topic_id'], $row['forum_id']);
				$this->scheduler->unset_topic_action($row['topic_id']);
			}
		}
		$this->config->set('topics_last_gc', $current_time, true);
	}
}
