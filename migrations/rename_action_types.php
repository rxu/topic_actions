<?php
/**
 *
 * @package       Topic Actions
 * @copyright (c) 2013 - 2016 rxu and LavIgor
 * @license       http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace rxu\TopicActions\migrations;

class rename_action_types extends \phpbb\db\migration\container_aware_migration
{
	static public function depends_on()
	{
		return array('\rxu\TopicActions\migrations\v_1_0_0');
	}

	public function update_data()
	{
		return array(
			array('custom', array(array($this, 'rename_types'))),
		);
	}

	public function rename_types()
	{
		$sql = 'UPDATE ' . TOPICS_TABLE . "
				SET topic_action_type = 'trash'
				WHERE topic_action_type = 'RECYCLE'";
		$this->db->sql_query($sql);

		$sql = 'UPDATE ' . TOPICS_TABLE . "
				SET topic_action_type = 'trash_lock'
				WHERE topic_action_type = 'RECYCLE_LOCK'";
		$this->db->sql_query($sql);

		$sql = 'UPDATE ' . TOPICS_TABLE . "
				SET topic_action_type = 'delete'
				WHERE topic_action_type = 'DELETE'";
		$this->db->sql_query($sql);

		$cache = $this->container->get('cache');
		$cache->destroy('sql', TOPICS_TABLE);
	}
}
