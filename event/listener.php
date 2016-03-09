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

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \rxu\TopicActions\functions\scheduler */
	protected $scheduler;

	/** @var \rxu\TopicActions\functions\manager */
	protected $manager;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $php_ext;

	public function __construct(\phpbb\config\config $config, \phpbb\template\template $template, \phpbb\user $user, \phpbb\request\request_interface $request, \rxu\TopicActions\functions\scheduler $scheduler, \rxu\TopicActions\functions\manager $manager, $phpbb_root_path, $php_ext)
	{
		$this->config = $config;
		$this->template = $template;
		$this->user = $user;
		$this->request = $request;
		$this->scheduler = $scheduler;
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
			if ($this->scheduler->unset_topic_action($topic_id))
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

			if (!$this->manager->perform_preliminary($topic_action, $topic_id, $forum_id))
			{
				$message .= $this->manager->get_error();
			}
			else if ($topic_action_time > 0)
			{
				if ($this->scheduler->set_topic_action_time($topic_action, $topic_action_time, $topic_id, $forum_id))
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
				$this->scheduler->unset_topic_action($topic_id);
				$result = $this->manager->perform_action($topic_action, $topic_id, $forum_id);
				$message .= ($result) ? $result : $this->manager->get_error();
			}
		}

		if ($topic_action != 'delete' || $this->manager->has_errors())
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

		$action_select = $this->scheduler->topic_action_select($forum_id, $topic_id);
		$action_select_time = ($action_select) ? $this->scheduler->topic_action_time_select() : false;

		$this->template->assign_vars(array(
			'TOPIC_ACTION_SELECT'      => $action_select,
			'TOPIC_ACTION_TIME_SELECT' => $action_select_time,
			'TOPIC_ACTION_TIME'        => ($topic_data['topic_action_time'] && ($topic_data['topic_action_time'] > $this->config['topics_last_gc'])) ? sprintf($this->user->lang['TOPIC_ACTION']['DELAY_EXPLAIN'], $this->user->lang['TOPIC_ACTION']['TYPE_NOTICE'][$topic_data['topic_action_type']], $this->user->format_date($topic_data['topic_action_time'])) : '',
		));
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
				'S_TOPIC_ICONS' => true,
			));
		}
	}
}
