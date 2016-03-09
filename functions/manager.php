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
 * Topic actions manager class.
 *
 * Finds installed topic actions, stores action objects, provides action selection.
 */
class manager
{
	/**
	 * Set of \rxu\TopicActions\functions\action\base objects.
	 * Array holding all actions that have been found.
	 *
	 * @var array
	 */
	protected $actions = array();

	/** @var \phpbb\user */
	protected $user;

	/** @var array Array of occurred errors */
	private $errors = array();

	/**
	 * Constructor. Loads all available actions.
	 *
	 * @param array|\Traversable $actions Provides an iterable set of action names
	 * @param \phpbb\user        $user    User object
	 */
	public function __construct($actions, $user)
	{
		$this->user = $user;
		$this->load_actions($actions);
	}

	/**
	 * Loads actions given by name and puts them into $this->actions.
	 *
	 * @param array|\Traversable $actions Array of instances of \rxu\TopicActions\functions\action\base
	 *
	 * @return null
	 */
	protected function load_actions($actions)
	{
		foreach ($actions as $action)
		{
			$this->actions[] = $action;
		}
	}

	/**
	 * Returns the error string.
	 *
	 * @return string Error string (empty if no errors occurred)
	 */
	public function get_error()
	{
		return implode('<br>', $this->errors);
	}

	/**
	 * Returns whether there were any errors.
	 *
	 * @return bool
	 */
	public function has_errors()
	{
		return (bool) sizeof($this->errors);
	}

	/**
	 * Finds an action by name.
	 *
	 * If there is no action with the specified name, null is returned.
	 *
	 * @param string $name Name of the action to look up
	 * @return \rxu\TopicActions\functions\action\base    An action corresponding to the given name, or null
	 */
	protected function find_action($name)
	{
		foreach ($this->actions as $action)
		{
			if ($action->get_name() == $name)
			{
				return $action;
			}
		}
		return null;
	}

	/**
	 * Checks permissions for the specified action.
	 * Also checks whether the requested action exists.
	 * This helper function can also be used to return the action object.
	 *
	 * @param string $name     Name of the action
	 * @param int    $topic_id Topic ID
	 * @param int    $forum_id Forum ID (can be optional)
	 * @param bool   $check    True if we need to check only,
	 *                         false to return the action if checks passed
	 * @return bool|string|\rxu\TopicActions\functions\action\base
	 */
	protected function _check_auth($name, $topic_id, $forum_id = 0, $check = false)
	{
		$action = $this->find_action($name);

		if (!$action)
		{
			$this->errors[] = $this->user->lang('TOPIC_ACTION_ERROR');
			return false;
		}

		if (!$action->check_auth($forum_id, $topic_id))
		{
			$this->errors[] = $this->user->lang('TOPIC_ACTION_NO_PERMISSION');
			return false;
		}

		return ($check) ? true : $action;
	}

	/**
	 * Checks permissions for the specified action.
	 * Also checks whether the requested action exists.
	 *
	 * @param string $name     Name of the action
	 * @param int    $topic_id Topic ID
	 * @param int    $forum_id Forum ID (can be optional)
	 * @return bool|string
	 */
	public function check_auth($name, $topic_id, $forum_id = 0)
	{
		return $this->_check_auth($name, $topic_id, $forum_id, true);
	}

	/**
	 * Performs the action with the specified name.
	 *
	 * @param string $name     Name of the action
	 * @param int    $topic_id Topic ID
	 * @param int    $forum_id Forum ID (can be optional)
	 * @return string|false Success message or false in case of an error
	 */
	public function perform_action($name, $topic_id, $forum_id = 0)
	{
		$action = $this->_check_auth($name, $topic_id, $forum_id);

		if (!$action)
		{
			return false;
		}

		if (!$action->perform($forum_id, $topic_id))
		{
			$error = $action->get_error();
			$this->errors[] = ($error) ? $error : $this->user->lang('TOPIC_ACTION_ERROR');
			return false;
		}

		return $this->user->lang($action->success_lang_key);
	}

	/**
	 * Executes the action with the specified name without checking permissions.
	 *
	 * @param string $name     Name of the action
	 * @param int    $topic_id Topic ID
	 * @param int    $forum_id Forum ID (can be optional)
	 * @return bool Whether the action was executed without errors
	 */
	public function execute_action($name, $topic_id, $forum_id = 0)
	{
		$action = $this->find_action($name);

		if (!$action)
		{
			$this->errors[] = $this->user->lang('TOPIC_ACTION_ERROR');
			return false;
		}

		if (!$action->perform($forum_id, $topic_id))
		{
			$error = $action->get_error();
			$this->errors[] = ($error) ? $error : $this->user->lang('TOPIC_ACTION_ERROR');
			return false;
		}

		return true;
	}

	/**
	 * Performs the preliminary action of the action with the specified name.
	 *
	 * @param string $name     Name of the action
	 * @param int    $topic_id Topic ID
	 * @param int    $forum_id Forum ID (can be optional)
	 * @return bool Whether the preliminary action was performed without errors
	 */
	public function perform_preliminary($name, $topic_id, $forum_id = 0)
	{
		$action = $this->_check_auth($name, $topic_id, $forum_id);

		if (!$action)
		{
			return false;
		}

		if (!$action->preliminary_action($forum_id, $topic_id))
		{
			$error = $action->get_error();
			$this->errors[] = ($error) ? $error : $this->user->lang('TOPIC_ACTION_ERROR');
			return false;
		}

		return true;
	}

	/**
	 * Find all permitted actions and return their names.
	 *
	 * @param int $topic_id Topic ID
	 * @param int $forum_id Forum ID (can be optional)
	 * @return array List of the names of all permitted actions
	 */
	public function get_actions($topic_id, $forum_id = 0)
	{
		$actions = array();
		foreach ($this->actions as $action)
		{
			if ($action->check_auth($forum_id, $topic_id))
			{
				$actions[] = $action->get_name();
			}
		}
		return $actions;
	}
}
