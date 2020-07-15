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
 * Trash topic action.
 */
class trash extends base
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

	/**
	 * Constructor.
	 *
	 * @param \phpbb\auth\auth          $auth
	 * @param \phpbb\content_visibility $content_visibility
	 * @param \phpbb\log\log_interface  $log
	 * @param \phpbb\user               $user
	 * @param string                    $phpbb_root_path Root path
	 * @param string                    $php_ext
	 */
	public function __construct(\phpbb\auth\auth $auth, \phpbb\content_visibility $content_visibility, \phpbb\log\log_interface $log, \phpbb\user $user, $phpbb_root_path, $php_ext)
	{
		$this->auth = $auth;
		$this->content_visibility = $content_visibility;
		$this->log = $log;
		$this->user = $user;

		parent::__construct($phpbb_root_path, $php_ext);
	}

	/**
	 * {@inheritdoc}
	 */
	public function check_auth($forum_id, $topic_id)
	{
		return (bool) $this->auth->acl_get('m_softdelete', $forum_id);
	}

	/**
	 * Performs Soft delete Topics immediately.
	 * DO NOT CALL THIS FUNCTION DIRECTLY!!!
	 * This function should be called in manager class only.
	 *
	 * @param int $forum_id Forum ID
	 * @param int $topic_id Topic ID
	 * @return bool
	 */
	public function perform($forum_id, $topic_id)
	{
		if (!$row = $this->get_topic_data($topic_id))
		{
			$this->set_error($this->user->lang('NO_TOPIC_SELECTED'));
			return false;
		}
		$soft_delete_reason = '';

		// Only soft delete non-shadow topics
		if (!$row['topic_moved_id'])
		{
			$return = $this->content_visibility->set_topic_visibility(ITEM_DELETED, $topic_id, $row['forum_id'], $this->user->data['user_id'], time(), $soft_delete_reason);
			if (!empty($return))
			{
				$this->log->add(
					'mod',
					$this->user->data['user_id'],
					$this->user->ip,
					'LOG_SOFTDELETE_TOPIC',
					false,
					[
						'forum_id'                => $row['forum_id'],
						'topic_id'                => $topic_id,
						'topic_title'             => $row['topic_title'],
						'topic_first_poster_name' => $row['topic_first_poster_name'],
					]
				);
				return true;
			}
		}
		return false;
	}
}
