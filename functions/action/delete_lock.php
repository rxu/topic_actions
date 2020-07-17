<?php
/**
 *
 * @package       Topic Actions
 * @copyright (c) 2013 - 2016 rxu and LavIgor
 * @license       http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace rxu\topicactions\functions\action;

/**
 * Delete topic action.
 */
class delete_lock extends delete
{
	public $success_lang_key = 'TOPIC_DELETED_SUCCESS';

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\content_visibility */
	protected $content_visibility;

	/** @var \phpbb\log\log_interface */
	protected $log;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\auth\auth          $auth
	 * @param \phpbb\content_visibility $content_visibility
	 * @param \phpbb\log\log_interface  $log
	 * @param \phpbb\user               $user
	 * @param \phpbb\db\driver\driver_interface $db
	 * @param string                    $phpbb_root_path Root path
	 * @param string                    $php_ext
	 */
	public function __construct(\phpbb\auth\auth $auth, \phpbb\content_visibility $content_visibility, \phpbb\log\log_interface $log, \phpbb\user $user, \phpbb\db\driver\driver_interface $db, $phpbb_root_path, $php_ext)
	{
		$this->db = $db;

		parent::__construct($auth, $content_visibility, $log, $user, $phpbb_root_path, $php_ext);
	}

	/**
	 * {@inheritdoc}
	 */
	public function check_auth($forum_id, $topic_id)
	{
		if (!$row = $this->get_topic_data($topic_id))
		{
			return false;
		}
		return (bool) $this->auth->acl_get('m_delete', $forum_id) && ($this->auth->acl_get('m_lock', $forum_id) || $this->auth->acl_get('f_user_lock', $forum_id) && $row['topic_poster'] == $this->user->data['user_id']);
	}

	/**
	 * Performs the lock topic action.
	 * DO NOT CALL THIS FUNCTION DIRECTLY!!!
	 * This function should be called in manager class only.
	 *
	 * @param int $forum_id Forum ID
	 * @param int $topic_id Topic ID
	 * @return bool
	 */
	public function preliminary_action($forum_id, $topic_id)
	{
		if (!$row = $this->get_topic_data($topic_id))
		{
			$this->set_error($this->user->lang('NO_TOPIC_SELECTED'));
			return false;
		}

		// Check if the topic is already locked.
		if ($row['topic_status'] == ITEM_LOCKED)
		{
			return true; // Not an error
		}

		$sql = "UPDATE " . TOPICS_TABLE . "
			SET topic_status = " . ITEM_LOCKED . '
			WHERE topic_id = ' . (int) $topic_id;
		$this->db->sql_query($sql);

		$this->log->add(
			'mod',
			$this->user->data['user_id'],
			$this->user->ip,
			'LOG_LOCK',
			false,
			[
				'forum_id'    => $row['forum_id'],
				'topic_id'    => $row['topic_id'],
				'topic_title' => $row['topic_title'],
			]
		);
		return true;
	}
}
