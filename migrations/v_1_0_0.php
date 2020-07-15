<?php
/**
 *
 * @package       Topic Actions
 * @copyright (c) 2013 - 2016 rxu and LavIgor
 * @license       http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace rxu\topicactions\migrations;

class v_1_0_0 extends \phpbb\db\migration\container_aware_migration
{
	public function effectively_installed()
	{
		return isset($this->config['topic_actions_version']) && version_compare($this->config['topic_actions_version'], '1.0.0', '>=');
	}

	static public function depends_on()
	{
		return ['\phpbb\db\migration\data\v310\dev'];
	}

	public function update_schema()
	{
		return [
			'add_columns' => [
				$this->table_prefix . 'topics' => [
					'topic_action_time' => ['INT:11', '0'],
					'topic_action_type' => ['VCHAR:100', ''],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_columns' => [
				$this->table_prefix . 'topics' => [
					'topic_action_time',
					'topic_action_type',
				],
			],
		];
	}

	public function update_data()
	{
		return [
			// Add configs
			['config.add', ['topics_gc', '3600']],
			['config.add', ['topics_last_gc', '0', '1']],

			// Current version
			['config.add', ['topic_actions_version', '1.0.0']],

			// Add custom icon
			['custom', [[$this, 'add_icon']]],
		];
	}

	public function revert_data()
	{
		return [
			// Remove custom icon
			['custom', [[$this, 'remove_icon']]],
		];
	}

	public function add_icon()
	{
		$sql = 'SELECT MAX(icons_order) as max_order FROM ' . ICONS_TABLE;
		$result = $this->db->sql_query($sql);
		$max_order = (int) $this->db->sql_fetchfield('max_order');
		$this->db->sql_freeresult($result);

		$sql_insert = [
			'icons_url'          => 'ext/rxu/topicactions/icon/trash.png',
			'icons_width'        => 16,
			'icons_height'       => 16,
			'icons_order'        => $max_order + 1,
			'display_on_posting' => 0,
		];
		$sql = 'INSERT INTO ' . ICONS_TABLE . ' ' . $this->db->sql_build_array('INSERT', $sql_insert);
		$this->db->sql_query($sql);

		$this->clear_icons_cache();
	}

	public function remove_icon()
	{
		$sql = 'SELECT icons_id FROM ' . ICONS_TABLE . ' WHERE icons_url ' . $this->db->sql_like_expression($this->db->get_any_char() . 'trash.png');
		$result = $this->db->sql_query($sql);
		$icon_id = (int) $this->db->sql_fetchfield('icons_id');
		$this->db->sql_freeresult($result);

		if ($icon_id)
		{
			$sql = 'DELETE FROM ' . ICONS_TABLE . ' WHERE icons_id = ' . $icon_id;
			$this->db->sql_query($sql);

			$sql = 'UPDATE ' . TOPICS_TABLE . ' SET icon_id = 0 WHERE icon_id = ' . $icon_id;
			$this->db->sql_query($sql);

			$this->clear_icons_cache();
		}
	}

	protected function clear_icons_cache()
	{
		$cache = $this->container->get('cache');
		$cache->destroy('_icons');
		$cache->destroy('sql', ICONS_TABLE);
	}
}
