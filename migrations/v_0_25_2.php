<?php

namespace jeb\snahp\migrations;

class v_0_25_2 extends \phpbb\db\migration\migration
{
    public function effectively_installed()
    {
        return false;
    }

    public static function depends_on()
    {
        return ['\jeb\snahp\migrations\v_0_25_1'];
    }

    public function update_schema()
    {
        return [];
    }

    public function revert_schema()
    {
        return [];
    }

    public function update_data()
    {
        return [["config.add", ["snp_digg_broadcast_cooldown", 86400]]];
    }
}
