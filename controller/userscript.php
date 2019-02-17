<?php
namespace jeb\snahp\controller;
use \Symfony\Component\HttpFoundation\Response;
use jeb\snahp\core\base;

class userscript extends base
{
    protected $prefix;

    public function __construct($prefix)
    {
        $this->prefix = $prefix;
    }

    public function handle($mode)
    {
        switch ($mode)
        {
        case 'bump_topic':
            $cfg['tpl_name'] = '';
            $cfg['base_url'] = '/app.php/snahp/userscript/bump_topic/';
            $cfg['title'] = 'Bump Topic';
            return $this->handle_bump_topic($cfg);
            break;
        default:
            break;
        }
        trigger_error('showing favorite.');
    }

    public function handle_bump_topic($cfg)
    {
        $this->reject_anon();
        $tid = $this->request->variable('t', '');
        if (!$tid)
        {
            trigger_error('No topic_id was provided.');
        }
        $topicdata = $this->select_topic($tid);
        if (!$topicdata)
        {
            trigger_error('That topic does not exist.');
        }
        $time = time();
        $data = [
            'topic_last_post_time' => $time,
            'topic_time' => $time,
        ];
        $this->update_topic($tid, $data);
        $title = $topicdata['topic_title'];
        $strn = "$title has been bumped to " . $this->user->format_date($time);
        $return_url = '/viewtopic.php?t='.$tid;
        meta_refresh(2, $return_url);
        trigger_error($strn);
    }

    public function handle_thanks_given($cfg)
    {
        $this->reject_anon();
        $tpl_name = $cfg['tpl_name'];
        if ($tpl_name)
        {
            $base_url = $cfg['base_url'];
            $pagination = $this->container->get('pagination');
            $per_page = $this->config['posts_per_page'];
            $start = $this->request->variable('start', 0);
            [$data, $total] = $this->select_thanks_given($per_page, $start);
            $pagination->generate_template_pagination(
                $base_url, 'pagination', 'start', $total, $per_page, $start
            );
            foreach ($data as $row)
            {
                $tid = $row['topic_id'];
                $pid = $row['post_id'];
                $post_time = $this->user->format_date($row['post_time']);
                $u_details = "/viewtopic.php?t=$tid&p=$pid#p$pid";
                $poster_id = $row['poster_id'];
                $poster_name = $row['username'];
                $poster_colour = $row['user_colour'];
                $thanks_time = $this->user->format_date($row['thanks_time']);
                $group = array(
                    'FORUM_ID'       => $row['forum_id'],
                    'TOPIC_ID'       => $row['topic_id'],
                    'POST_SUBJECT'   => $row['post_subject'],
                    'POST_TIME'      => $post_time,
                    'POSTER_ID'      => $poster_id,
                    'POSTER_NAME'    => $poster_name,
                    'POSTER_COLOUR'  => $poster_colour,
                    'THANKS_TIME'    => $thanks_time,
                    'U_VIEW_DETAILS' => $u_details,
                );
                $this->template->assign_block_vars('postrow', $group);
            }
            $this->template->assign_var('TITLE', $cfg['title']);
            return $this->helper->render($tpl_name, $cfg['title']);
        }
    }

    public function handle_favorite($cfg)
    {
        $this->reject_anon();
        $tpl_name = $cfg['tpl_name'];
        if ($tpl_name)
        {
            $base_url = $cfg['base_url'];
            $fid_listings = $this->config['snp_fid_listings'];
            $pagination = $this->container->get('pagination');
            $per_page = $this->config['posts_per_page'];
            $start = $this->request->variable('start', 0);
            $sort_mode = $cfg['sort_mode'];
            [$data, $total] = $this->select_one_day($fid_listings, $per_page, $start, $sort_mode);
            $pagination->generate_template_pagination(
                $base_url, 'pagination', 'start', $total, $per_page, $start
            );
            foreach ($data as $row)
            {
                $tid = $row['topic_id'];
                $topic_time = $this->user->format_date($row['topic_time']);
                $u_details = '/viewtopic.php?t=' . $tid;
                $poster_id = $row['topic_poster'];
                $poster_name = $row['topic_first_poster_name'];
                $poster_colour = $row['topic_first_poster_colour'];
                $lp_id = $row['topic_last_poster_id'];
                $lp_name = $row['topic_last_poster_name'];
                $lp_colour = $row['topic_last_poster_colour'];
                $lp_subject = $row['topic_last_post_subject'];
                $lp_time = $this->user->format_date($row['topic_last_post_time']);
                $group = array(
                    'FORUM_ID'       => $row['forum_id'],
                    'TOPIC_ID'       => $row['topic_id'],
                    'TOPIC_TITLE'    => $row['topic_title'],
                    'TOPIC_TIME'     => $topic_time,
                    'POSTER_ID'      => $poster_id,
                    'POSTER_NAME'    => $poster_name,
                    'POSTER_COLOUR'  => $poster_colour,
                    'LP_ID'          => $lp_id,
                    'LP_NAME'        => $lp_name,
                    'LP_COLOUR'      => $lp_colour,
                    'LP_SUBJECT'     => $lp_subject,
                    'LP_TIME'        => $lp_time,
                    'TOPIC_VIEWS'    => $row['topic_views'],
                    'FORUM_NAME'     => $row['forum_name'],
                    'REPLIES'        => $row['topic_posts_approved'] - 1,
                    'U_VIEW_DETAILS' => $u_details,
                );
                $this->template->assign_block_vars('postrow', $group);
            }
            $this->template->assign_var('TITLE', $cfg['title']);
            return $this->helper->render($tpl_name, $cfg['title']);
        }
    }

}