<?php

use \Symfony\Component\HttpFoundation\Response;

namespace jeb\snahp\controller;

use jeb\snahp\core\base;
use jeb\snahp\controller\message_parser;

$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include_once($phpbb_root_path . 'common.' . $phpEx);
include_once($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
include_once($phpbb_root_path . 'includes/message_parser.' . $phpEx);
// include_once($phpbb_root_path . 'includes/functions_display.' . $phpEx);

function prn($var) {
    if (is_array($var))
    { foreach ($var as $k => $v) { echo "... $k => "; prn($v); }
    } else { echo "$var<br>"; }
}

class reqs_post extends base
{

    public function __construct()
    {
    }

	public function handle($mode, $fid)
	{
		$this->user->add_lang_ext('jeb/snahp', 'common');
		$this->page_title = $this->user->lang('Post Snahp Request');
        $cfg = [];
        switch ($mode)
        {
        case 'tvshows':
            $cfg['tpl_name'] = 'reqs_post_tvshows.html';
            $cfg['b_feedback'] = false;
            $fid = 4;
            return $this->handle_request_form($cfg, $fid);
            break;
        case 'movies':
            $cfg['tpl_name'] = 'reqs_post_movies.html';
            $cfg['b_feedback'] = false;
            $fid = 5;
            return $this->handle_request_form($cfg, $fid);
            break;
        default:
            trigger_error('Invalid request category.');
            break;
        }
	}

    public function handle_request_form($cfg, $fid)
    {
        global $phpbb_dispatcher;
        $tpl_name = $cfg['tpl_name'];
        if ($tpl_name)
        {
            $this->tpl_name = $tpl_name;
            add_form_key('jeb_snp');
            if ($this->request->is_set_post('submit'))
            {
                if (!check_form_key('jeb_snp'))
                {
                    trigger_error('FORM_INVALID', E_USER_WARNING);
                }
                $mode = 'post';
                $message = $this->request->variable('message', '');
                $subject = $this->request->variable('subject', '');
                if (!$subject)
                    trigger_error('Request entry cannot be empty.');
                $username = '***Request Username***';
                $topic_type = 0;
                $data_ary = [];
                $data_ary['topic_title'] = 'topic_title';
                $data_ary['icon_id'] = 0;
                $data_ary['forum_id'] = $fid;
                $data_ary['topic_time_limit'] = 0;
                $data_ary['enable_bbcode'] = true;
                $data_ary['enable_smilies'] = true;
                $data_ary['enable_urls'] = true;
                $data_ary['enable_sig'] = true;
                $data_ary['bbcode_bitfield'] = '';
                $bbcode_uid = substr(base_convert(unique_id(), 16, 36), 0, BBCODE_UID_LEN);
                $data_ary['bbcode_uid'] = $bbcode_uid;
                $data_ary['post_edit_locked'] = 0;
                $data_ary['icon_id'] = 0;
                $data_ary['enable_indexing'] = true;
                $data_ary['notify_set'] = false;
                $data_ary['notify'] = false;

                $vars = array('message', 'forum_id',);
                extract($phpbb_dispatcher->trigger_event('jeb.snahp.request_post_submit_before', compact($vars)));
                $message_parser = new \parse_message();
                $message_parser->message = $message ? $message : ' ';
                $message_parser->parse($data_ary['enable_bbcode'], ($this->config['allow_post_links']) ? $data_ary['enable_urls'] : false, $data_ary['enable_smilies'], true, true, true, $this->config['allow_post_links']);
                $message = $message_parser->message;
                $data_ary['message'] = $message;
                $data_ary['message_md5'] = md5($data_ary['message']);
                submit_post($mode, $subject, $username, $topic_type, $poll_ary, $data_ary, $update_message = true, $update_search_index = true);
                // meta_refresh(0, 'http://192.168.2.12:888/app.php/snahp/reqs_post/tvshows/5/');
                // trigger_error('error');
                meta_refresh(2, $this->u_action);
                trigger_error($this->user->lang('ACP_SNP_SETTING_SAVED') . adm_back_link($this->u_action));
            }
            $this->template->assign_vars(array(
                'SNP_B_REQUEST' => $this->config['snp_b_request'],
            ));
            return $this->helper->render($tpl_name, 'title');
        }
    }
}
