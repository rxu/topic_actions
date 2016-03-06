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
 *
 * @todo: Check for missing actions.
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

	/** @var bool Whether one of last actions ended with errors */
	public $had_errors = false;

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
	 * Loads actions given by name, checks permissions
	 * and puts them into $this->actions.
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
	 * Performs the action with the specified name.
	 *
	 * @param string $name     Name of the action
	 * @param int    $topic_id Topic ID
	 * @param int    $forum_id Forum ID (can be optional)
	 * @return string Success message or error string
	 */
	public function perform_action($name, $topic_id, $forum_id = 0)
	{
		$action = $this->find_action($name);

		if (!$action->check_auth($forum_id, $topic_id))
		{
			$this->had_errors = true;
			return $this->user->lang('TOPIC_ACTION_NO_PERMISSION');
		}

		if (!$action->perform($forum_id, $topic_id))
		{
			$this->had_errors = true;
			$error = $action->get_error();
			return ($error) ? $error : $this->user->lang('TOPIC_ACTION_ERROR');
		}

		return $this->user->lang($action->success_lang_key);
	}

	/**
	 * Executes the action with the specified name without checking permissions.
	 *
	 * @param string $name     Name of the action
	 * @param int    $topic_id Topic ID
	 * @param int    $forum_id Forum ID (can be optional)
	 * @return string Success message or error string
	 */
	public function execute_action($name, $topic_id, $forum_id = 0)
	{
		$action = $this->find_action($name);

		if (!$action->perform($forum_id, $topic_id))
		{
			$this->had_errors = true;
			$error = $action->get_error();
			return ($error) ? $error : $this->user->lang('TOPIC_ACTION_ERROR');
		}

		return true;
	}

	/**
	 * Performs the preliminary action of the action with the specified name.
	 *
	 * @param string $name     Name of the action
	 * @param int    $topic_id Topic ID
	 * @param int    $forum_id Forum ID (can be optional)
	 * @return true|string True on success, error string otherwise
	 */
	public function perform_preliminary($name, $topic_id, $forum_id = 0)
	{
		$action = $this->find_action($name);

		if (!$action->check_auth($forum_id, $topic_id))
		{
			$this->had_errors = true;
			return $this->user->lang('TOPIC_ACTION_NO_PERMISSION');
		}

		if (!$action->preliminary_action($forum_id, $topic_id))
		{
			$this->had_errors = true;
			$error = $action->get_error();
			return ($error) ? $error : $this->user->lang('TOPIC_ACTION_ERROR');
		}

		return true;
	}

	/**
	 * Find all actions and return their names.
	 *
	 * @param int $topic_id Topic ID
	 * @param int $forum_id Forum ID (can be optional)
	 * @return array List of the names of all actions
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
