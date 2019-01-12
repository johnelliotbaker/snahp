<?php
/**
 *
 * snahp. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace jeb\snahp\ucp;
function prn($var) {
    if (is_array($var))
    { foreach ($var as $k => $v) { echo "$k => "; prn($v); }
    } else { echo "$var<br>"; }
}


// CONSTANTS are in
// includes/constants.php
// USERS_TABLE
// TOPICS_TABLE


/**
 * snahp UCP module.
 */
class main_module
{
	var $u_action;

	function main($id, $mode)
	{
		global $db, $request, $template, $user;

        $cfg = array();
        switch ($mode)
        {
        case 'visibility':
            $cfg['tpl_name'] = 'ucp_snp_vis';
            $cfg['b_feedback'] = true;
            $this->handle_visibility($cfg);
            break;
        }
        if (!empty($cfg)){
            $this->handle_mode($cfg);
        }
	}

    function handle_visibility($cfg)
    {
		global $db, $request, $template, $user;
        $this->tpl_name = $cfg['tpl_name'];
        $this->page_title = $user->lang('UCP_SNP_TITLE');
        add_form_key('jeb/snahp');
        $data = [
            'snp_disable_avatar_thanks_link' => $request->variable('snp_disable_avatar_thanks_link', $user->data['snp_disable_avatar_thanks_link']),
            'snp_enable_at_notify'           => $request->variable('snp_enable_at_notify', $user->data['snp_enable_at_notify']),
        ];
        $template_vars = array(
            'S_SNP_DISABLE_AVATAR_THANKS_LINK' => $data['snp_disable_avatar_thanks_link'],
            'S_SNP_ENABLE_AT_NOTIFY'           => $data['snp_enable_at_notify'],
            'S_UCP_ACTION'	=> $this->u_action,
        );
        if ($request->is_set_post('submit'))
        {
            if (!check_form_key('jeb/snahp'))
            {
                trigger_error($user->lang('FORM_INVALID'));
            }
            $sql = 'UPDATE ' . USERS_TABLE . '
                SET ' . $db->sql_build_array('UPDATE', $data) . '
                WHERE user_id = ' . $user->data['user_id'];
            $result = $db->sql_query($sql);
            $db->sql_freeresult($result);
            if ($cfg['b_feedback'])
            {
                meta_refresh(1, $this->u_action);
                $message = $user->lang('UCP_SNP_SAVED') . '<br /><br />' . $user->lang('RETURN_UCP', '<a href="' . $this->u_action . '">', '</a>');
                trigger_error($message);
            }
        }
        $template->assign_vars($template_vars);
    }

    function handle_mode($cfg)
    {
		global $db, $request, $template, $user;
        $this->tpl_name = $cfg['tpl_name'];
        $this->page_title = $user->lang('UCP_SNP_TITLE');
        add_form_key('jeb/snahp');
        if ($request->is_set_post('submit'))
        {
            if (!check_form_key('jeb/snahp'))
            {
                trigger_error($user->lang('FORM_INVALID'));
            }
            if ($cfg['b_feedback'])
            {
                meta_refresh(1, $this->u_action);
                $message = $user->lang('UCP_SNP_SAVED') . '<br /><br />' . $user->lang('RETURN_UCP', '<a href="' . $this->u_action . '">', '</a>');
                trigger_error($message);
            }
        }
        $template->assign_vars([]);
    }
}
