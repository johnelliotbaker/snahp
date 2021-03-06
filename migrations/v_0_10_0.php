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

class v_0_10_0 extends \phpbb\db\migration\migration
{

    static public function depends_on()
    {
        return array('\jeb\snahp\migrations\v_0_9_0');
    }

    public function update_data()
    {
        return array(
            array('config.add', array('snp_don_b_show_navlink', 0)),
            array('config.add', array('snp_don_url', 'https://forum.snahp.it/viewforum.php?f=7')),
            array('module.add', array(
                'acp',
                'ACP_SNP_TITLE',
                array(
                    'module_basename'	=> '\jeb\snahp\acp\main_module',
                    'modes'				=> array('donation'),
                ),
            )),
        );
    }
}
