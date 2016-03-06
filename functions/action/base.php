<?php
/**
 *
 * @package       Topic Actions
 * @copyright (c) 2013 - 2016 rxu and LavIgor
 * @license       http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace rxu\TopicActions\functions\action;

/**
 * Topic action base class. Provides sensible defaults for topic actions,
 * making writing topic actions easier.
 */
abstract class base
{
	private $name;
	private $errors;
	public $success_lang_key = 'TOPIC_ACTION_PERFORMED';

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $php_ext;

	/**
	 * Constructor.
	 *
	 * @param string $phpbb_root_path Root path
	 * @param string $php_ext
	 */
	public function __construct($phpbb_root_path, $php_ext)
	{
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * Returns the name of the action.
	 *
	 * @return string Name of the action
	 */
	public function get_name()
	{
		return $this->name;
	}

	/**
	 * Sets the name of the action.
	 *
	 * @param string $name Name of the action
	 */
	public function set_name($name)
	{
		$this->name = $name;
	}

	/**
	 * Returns the error string of the action.
	 *
	 * @return string Error string (empty if no errors occurred)
	 */
	public function get_error()
	{
		return implode('<br>', $this->errors);
	}

	/**
	 * Adds an error to errors array of the action.
	 *
	 * @param string $error Error text
	 */
	public function set_error($error)
	{
		$this->errors[] = $error;
	}

	/**
	 * Returns whether this action and the
	 * corresponding preliminary action (if exists)
	 * can be done according to user permissions.
	 *
	 * @param int $forum_id Forum ID
	 * @param int $topic_id Topic ID
	 * @return bool
	 */
	abstract public function check_auth($forum_id, $topic_id);

	/**
	 * Performs the action immediately.
	 * DO NOT CALL THIS FUNCTION DIRECTLY!!!
	 * This function should be called in manager class only.
	 *
	 * @param int $forum_id Forum ID
	 * @param int $topic_id Topic ID
	 * @return bool
	 */
	abstract public function perform($forum_id, $topic_id);

	/**
	 * Performs the preliminary action.
	 * DO NOT CALL THIS FUNCTION DIRECTLY!!!
	 * This function should be called in manager class only.
	 *
	 * @param int $forum_id Forum ID
	 * @param int $topic_id Topic ID
	 * @return bool
	 */
	public function preliminary_action($forum_id, $topic_id)
	{
		// No default action.
		return true;
	}

	/**
	 * Helper function that retrieves the row with topic data.
	 *
	 * @param int $topic_id Topic ID
	 * @return bool|array
	 */
	protected function get_topic_data($topic_id)
	{
		if (!function_exists('phpbb_get_topic_data'))
		{
			include($this->phpbb_root_path . 'includes/functions_mcp.' . $this->php_ext);
		}

		$data = phpbb_get_topic_data(array($topic_id));

		if (sizeof($data))
		{
			return array_shift($data);
		}
		return false;
	}
}
