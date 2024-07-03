<?php
/**
 *
 * @package       Topic Actions
 * @copyright (c) 2013 - 2016 rxu and LavIgor
 * @license       http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace rxu\topicactions\tests\functional;

/**
 * @group functional
 */
class topic_actions_test extends \phpbb_functional_test_case
{
	static protected function setup_extensions()
	{
		return array('rxu/topicactions');
	}

	public function test_topic_page()
	{
		$this->login();

		$this->add_lang_ext('rxu/topicactions', 'topic_actions');

		// Create a topic
		$post = $this->create_topic(2, 'Test Topic 1', 'This is a first test topic posted by the testing framework.');

		// Now test if topic action options exist
		$crawler = self::request('GET', "viewtopic.php?f=2&t={$post['topic_id']}&sid={$this->sid}");

		foreach ($this->lang['TOPIC_ACTION']['TYPE'] as $action => $lang)
		{
			if ($action == 'unlock')
			{
				continue;
			}
			$this->assertStringContainsString($lang, $crawler->filter('form[id="topic_actions"] > fieldset > select[id="topic_action"]
				> option[value="' . $action . '"]')->text());
		}
	}

	public function test_set_topic_action()
	{
		$this->login();

		$this->add_lang_ext('rxu/topicactions', 'topic_actions');

		// Get a topic
		$this->get_db();
		$sql = 'SELECT topic_id FROM ' . TOPICS_TABLE . "
			ORDER BY topic_id DESC";
		$result = $this->db->sql_query_limit($sql, 1);
		$topic_id = (string) $this->db->sql_fetchfield('topic_id');
		$this->db->sql_freeresult($result);

		// Now test scheduling an action for a topic
		$crawler = self::request('GET', "viewtopic.php?f=2&t={$topic_id}&sid={$this->sid}");

		$form = $crawler->selectButton('Go')->form([
			'topic_action'		=> 'delete', // Delete permanently
			'topic_action_time'	=> 3, // 3 days
		]);
		$crawler = self::submit($form);
		$this->assertStringContainsString($this->lang('TOPIC_ACTION_SET'), $crawler->filter('div[class="inner"] > p')->text());

		$crawler = self::request('GET', "viewtopic.php?f=2&t={$topic_id}&sid={$this->sid}");
		$this->assertStringContainsString(sprintf($this->lang['TOPIC_ACTION']['DELAY_EXPLAIN'], $this->lang['TOPIC_ACTION']['TYPE_NOTICE']['delete'], ''),
			$crawler->filter('div[class="rules topic-action-scheduled"] > div')->text());

		// Test deleting scheduled action
		$action_delete_url = $crawler->filter('a[title="' . $this->lang('DELETE_ACTION') . '"]')->attr('href');
		$crawler = self::request('GET', $action_delete_url);
		$this->assertStringContainsString($this->lang('TOPIC_ACTION_DELETED'), $crawler->filter('div[class="inner"] > p')->text());
	}
}
