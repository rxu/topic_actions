<?php
/**
*
* @package TopicActions
* @copyright (c) 2014 rxu
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

namespace rxu\TopicActions\migrations;

class v_1_0_0 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['topic_actions_version']) && version_compare($this->config['topic_actions_version'], '1.0.0', '>=');
	}

	static public function depends_on()
	{
			return array('\phpbb\db\migration\data\v310\dev');
	}

	public function update_schema()
	{
		return 	array(
			'add_columns' => array(
				$this->table_prefix . 'topics' => array(
					'topic_action_time' => array('INT:11', '0'),
					'topic_action_type' => array('VCHAR:100', ''),
				),
			),
		);
	}

	public function revert_schema()
	{
		return 	array(
			'drop_columns' => array(
				$this->table_prefix . 'topics' => array(
					'topic_action_time',
					'topic_action_type',
				),
			),
		);
	}

	public function update_data()
	{
		return array(
			// Add configs
			array('config.add', array('topics_gc', '3600')),
			array('config.add', array('topics_last_gc', '0', '1')),

			// Current version
			array('config.add', array('topic_actions_version', '1.0.0')),

			// Add custom icon
			array('custom', array(array($this, 'add_icon'))),
		);
	}

	public function revert_data()
	{
		return array(
			// Remove custom icon
			array('custom', array(array($this, 'remove_icon'))),
		);
	}

	public function add_icon()
	{
		global $cache;

		$sql = 'SELECT MAX(icons_order) as max_order FROM ' . ICONS_TABLE;
		$result = $this->db->sql_query($sql);
		$max_order = (int) $this->db->sql_fetchfield('max_order');

		$sql_insert = array(
			'icons_url' => 'ext/rxu/TopicActions/icon/trash.png',
			'icons_width' => 16,
			'icons_height' => 16,
			'icons_order' => $max_order + 1,
			'display_on_posting'	=> 0,
		);
		$sql = 'INSERT INTO ' . ICONS_TABLE . ' ' . $this->db->sql_build_array('INSERT', $sql_insert);
		$this->db->sql_query($sql);

		$cache->destroy('_icons');
		$cache->destroy('sql', ICONS_TABLE);
	}

	public function remove_icon()
	{
		global $cache;

		$sql = 'SELECT icons_id FROM ' . ICONS_TABLE . ' WHERE icons_url ' . $this->db->sql_like_expression($this->db->any_char . 'trash.png');
		$result = $this->db->sql_query($sql);
		$icon_id = (int) $this->db->sql_fetchfield('icons_id');
		$this->db->sql_freeresult($result);
		
		if ($icon_id)
		{
			$sql = 'DELETE FROM ' . ICONS_TABLE . ' WHERE icons_id = ' . $icon_id;
			$this->db->sql_query($sql);

			$sql = 'UPDATE ' . TOPICS_TABLE . ' SET icon_id = 0 WHERE icon_id = ' . $icon_id;
			$this->db->sql_query($sql);

			$cache->destroy('_icons');
			$cache->destroy('sql', ICONS_TABLE);
		}
	}
}
