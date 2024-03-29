<?php
/**
 *
 * snahp. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace jeb\snahp\migrations;

class v_0_13_0 extends \phpbb\db\migration\migration
{
    public function effectively_installed()
    {
        return false;
    }

    public static function depends_on()
    {
        return ['\jeb\snahp\migrations\v_0_12_1'];
    }

    public function update_data()
    {
        $default_cron_interval = 60 * 60 * 24;
        $snp_cron_graveyard_fid = [
            "default" => 23,
        ];
        return [
            ["config.add", ["snp_cron_b_graveyard", 0]],
            [
                "config.add",
                ["snp_cron_graveyard_fid", serialize($snp_cron_graveyard_fid)],
            ],
            ["config.add", ["snp_cron_graveyard_last_gc", 0]],
            ["config.add", ["snp_cron_graveyard_gc", $default_cron_interval]],
            ["config.add", ["snp_fid_listings", 4]],
        ];
    }
}
