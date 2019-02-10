<?php

/**
 * undocumented function
 *
 * @return void
 */
namespace jeb\snahp\core;

use phpbb\template\context;
use phpbb\user;
use phpbb\auth\auth;
use phpbb\request\request_interface;
use phpbb\db\driver\driver_interface;
use phpbb\config\config;
use phpbb\controller\helper;
use phpbb\language\language;
use phpbb\template\template;
use phpbb\notification\manager;

function prn($var) {
    if (is_array($var))
    { foreach ($var as $k => $v) { echo "... $k => "; prn($v); }
    } else { echo "$var<br>"; }
}


abstract class base
{
    protected $template_context;
    protected $container;
    protected $user;
    protected $auth;
    protected $request;
    protected $db;
    protected $config;
    protected $helper;
    protected $language;
    protected $template;
    protected $table_prefix;
    protected $notification;
	protected $u_action;
    protected $allowed_directive = ['table', 'tr', 'td', 'a', 'img', 'span'];

    public function set_template_context(context $ctx)
    {
        $this->template_context = $ctx;
    }
    public function set_container($container)
    {
        $this->container = $container;
    }
    public function set_user(user $user)
    {
        $this->user = $user;
    }
    public function set_auth(auth $auth)
    {
        $this->auth = $auth;
    }
    public function set_request(request_interface $request)
    {
        $this->request = $request;
    }
    public function set_db($db)
    {
        $this->db = $db;
    }
    public function set_config(config $config)
    {
        $this->config = $config;
    }
    public function set_helper(helper $helper)
    {
        $this->helper = $helper;
    }
    public function set_language(language $language)
    {
        $this->language = $language;
    }
    public function set_template(template $template)
    {
        $this->template = $template;
    }
    public function set_table_prefix($table_prefix)
    {
        $this->table_prefix = $table_prefix;
        $this->table_prefix = 'phpbb_';
    }
    public function set_notification(manager $manager)
    {
        $this->notification = $manager;
    }

    // DATABASE Functions
    // ALL SUBFORUM ID
    public function select_subforum($parent_id)
    {
        $sql = 'SELECT left_id, right_id FROM ' . FORUMS_TABLE . ' WHERE forum_id=' . $parent_id;
        $result = $this->db->sql_query($sql);
        $row = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);
        $parent_left_id = $row['left_id'];
        $parent_right_id = $row['right_id'];
        $sql = 'SELECT * FROM ' . FORUMS_TABLE . ' WHERE parent_id = ' . $parent_id . ' OR (left_id BETWEEN ' . $parent_left_id . ' AND ' . $parent_right_id . ')';
        $result = $this->db->sql_query($sql);
        $data = [];
        while($row = $this->db->sql_fetchrow($result))
        {
            $data[] = $row['forum_id'];
        }
        $this->db->sql_freeresult($result);
        return $data;
    }

    // FAVORITE CONTENTS
    public function select_one_day($parent_id, $per_page, $start, $sort_mode)
    {
        $a_fid = $this->select_subforum($parent_id);
        // Get total for pagination. This is very expensive.
        // $sql = 'SELECT COUNT(*) as total FROM ' . TOPICS_TABLE .
        //     ' WHERE ' . $this->db->sql_in_set('forum_id', $a_fid) .
        //     ' ORDER BY topic_id DESC';
        // $result = $this->db->sql_query($sql);
        // $row = $this->db->sql_fetchrow($result);
        // $this->db->sql_freeresult($result);
        $total = 300;
        // $total = min($row['total'], $total);
        switch ($sort_mode)
        {
        case 'views':
            $order_by = 't.topic_views DESC';
            break;
        case 'replies':
            $order_by = 't.topic_posts_approved DESC';
            break;
        case 'id':
        default:
            $order_by = 't.topic_id DESC';
            break;
        }
        $sql_array = [
            'SELECT'	=> '
                    t.forum_id, topic_id, topic_title, topic_views, topic_time, 
                    topic_visibility, topic_posts_approved,
                    topic_poster, topic_first_poster_name, topic_first_poster_colour,
                    topic_last_poster_id, topic_last_poster_name, topic_last_poster_colour,
                    topic_last_post_subject, topic_last_post_time,
                    forum_name',
            'FROM'		=> [ TOPICS_TABLE	=> 't', ],
            'LEFT_JOIN'	=> [
                [
                    'FROM'	=> [FORUMS_TABLE => 'f'],
                    'ON'	=> 't.forum_id=f.forum_id',
                ],
            ],
            'WHERE'		=> $this->db->sql_in_set('t.forum_id', $a_fid),
            'ORDER_BY' => $order_by,
        ];
        $sql = $this->db->sql_build_query('SELECT', $sql_array);
        $result = $this->db->sql_query_limit($sql, $per_page, $start);
        $data = [];
        while($row = $this->db->sql_fetchrow($result))
        {
            $data[] = $row;
        }
        $this->db->sql_freeresult($result);
        return [$data, $total];
    }

    // TOPIC
    public function select_topic($tid)
    {
        $sql = 'SELECT * FROM ' . TOPICS_TABLE ." WHERE topic_id=$tid";
        $result = $this->db->sql_query($sql);
        $row = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);
        return $row;
    }

    public function update_topic($tid, $data)
    {
        $sql = 'UPDATE ' . TOPICS_TABLE . '
            SET ' . $this->db->sql_build_array('UPDATE', $data) . '
            WHERE topic_id=' . $tid;
        $this->db->sql_query($sql);
    }

    public function update_topic_title($fid, $tid, $pid, $ptn, $repl)
    {
        $topicdata = $this->select_topic($tid);
        if (!$topicdata)
        {
            return 'That topic doesn\'t exist';
        }
        $topic_last_pid = $topicdata['topic_last_post_id'];
        $topic_title = $topicdata['topic_title'];
        $topic_title = preg_replace($ptn, '', $topic_title);
        $topic_title = implode(' ', [$repl, $topic_title]);
        $this->update_topic($tid, [
            'topic_title' => $topic_title,
            'topic_last_post_subject' => $topic_title,
        ]);
        if ($postdata = $this->select_post($pid, 'post_subject'))
        {
            $post_subject = $postdata['post_subject'];
            $post_subject = preg_replace($ptn, '', $post_subject);
            $post_subject = implode(' ', [$repl, $post_subject]);
            $this->update_post([$topic_last_pid, $pid], ['post_subject' => $post_subject,]);
            $this->update_forum_last_post($fid);
        }
        return false;
    }

    // Forum
    public function select_forum($pid, $field='*')
    {
        $sql = 'SELECT '. $field . ' FROM ' . FORUMS_TABLE ." WHERE forum_id=$pid";
        $result = $this->db->sql_query($sql);
        $row = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);
        return $row;
    }

    public function update_forum_last_post($fid)
    {
        include_once('includes/functions_posting.php');
        $type = 'forum';
        $ids = [$fid];
        update_post_information($type, $ids, $return_update_sql = false);
    }

    // POST
    public function select_post($pid, $field='*')
    {
        $sql = 'SELECT '. $field . ' FROM ' . POSTS_TABLE ." WHERE post_id=$pid";
        $result = $this->db->sql_query($sql);
        $row = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);
        return $row;
    }

    public function update_post($pid, $data)
    {
        $sql = 'UPDATE ' . POSTS_TABLE . '
            SET ' . $this->db->sql_build_array('UPDATE', $data) . '
            WHERE ' . $this->db->sql_in_set('post_id', $pid);
        $this->db->sql_query($sql);
    }

    // USERS
    public function select_user_by_username($username)
    {
        $sql = 'SELECT * FROM ' . USERS_TABLE ." WHERE username_clean='$username'";
        $result = $this->db->sql_query($sql);
        $row = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);
        return $row;
    }

    public function select_user($user_id)
    {
        $sql = 'SELECT * FROM ' . USERS_TABLE ." WHERE user_id=$user_id";
        $result = $this->db->sql_query($sql);
        $row = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);
        return $row;
    }

    // GROUP
    public function select_group($gid)
    {
        $sql = 'SELECT * FROM ' . GROUPS_TABLE . " WHERE group_id=$gid";
        $result = $this->db->sql_query($sql);
        $row = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);
        return $row;
    }

    // REQUEST USERS
    public function select_request_users_by_username($username)
    {
        $username = utf8_clean_string($username);
        $userdata = $this->select_user_by_username($username);
        $user_id = $userdata['user_id'];
        $tbl = $this->container->getParameter('jeb.snahp.tables');
        $sql = 'SELECT * FROM ' . $tbl['requsr'] ." WHERE user_id=$user_id";
        $result = $this->db->sql_query($sql);
        $row = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);
        return $row;
    }

    public function select_request_users($user_id)
    {
        $tbl = $this->container->getParameter('jeb.snahp.tables');
        $sql = 'SELECT * FROM ' . $tbl['requsr'] ." WHERE user_id=$user_id";
        $result = $this->db->sql_query($sql);
        $row = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);
        return $row;
    }

    public function update_request_users($user_id, $data)
    {
        $tbl = $this->container->getParameter('jeb.snahp.tables');
        $sql = 'UPDATE ' . $tbl['requsr'] . '
            SET ' . $this->db->sql_build_array('UPDATE', $data) . '
            WHERE user_id = ' . $user_id;
        $this->db->sql_query($sql);
    }

    // REQUEST
    public function select_request_closed()
    {
        $tbl = $this->container->getParameter('jeb.snahp.tables');
        $def = $this->container->getParameter('jeb.snahp.req')['def'];
        $def_closed = $def['set']['closed'];
        $sql = 'SELECT * FROM ' . $tbl['req'] .
            ' WHERE ' . $this->db->sql_in_set('status', $def_closed) .
            ' AND b_graveyard = 0';
        $result = $this->db->sql_query($sql);
        $data = [];
        while ($row = $this->db->sql_fetchrow($result))
            $data[] = $row;
        $this->db->sql_freeresult($result);
        return $data;
    }

    public function select_request_open_by_uid($uid)
    {
        $tbl = $this->container->getParameter('jeb.snahp.tables');
        $def = $this->container->getParameter('jeb.snahp.req')['def'];
        $def_closed = $def['set']['closed'];
        $sql = 'SELECT * FROM ' . $tbl['req'] .
            ' WHERE ' . $this->db->sql_in_set('status', $def_closed, true) .
            ' AND b_graveyard = 0 AND requester_uid=' . $uid;
        $result = $this->db->sql_query_limit($sql, 20);
        $data = [];
        while ($row = $this->db->sql_fetchrow($result))
        {
            $data[] = $row;
        }
        $this->db->sql_freeresult($result);
        return $data;
    }

    public function select_request($tid)
    {
        $tbl = $this->container->getParameter('jeb.snahp.tables');
        $sql = 'SELECT * FROM ' . $tbl['req'] . " WHERE tid=$tid";
        $result = $this->db->sql_query($sql);
        $row = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);
        return $row;
    }

    public function update_request($tid, $data)
    {
        $tbl = $this->container->getParameter('jeb.snahp.tables');
        $sql = 'UPDATE ' . $tbl['req'] . '
            SET ' . $this->db->sql_build_array('UPDATE', $data) . '
            WHERE tid=' . $tid;
        $this->db->sql_query($sql);
    }

    // Dibs
    public function insert_dibs($data)
    {
        $tbl = $this->container->getParameter('jeb.snahp.tables');
        $sql = 'INSERT INTO ' . $tbl['dibs'] .
            $this->db->sql_build_array('INSERT', $data);
        $this->db->sql_query($sql);
    }

    public function update_dibs($tid, $data)
    {
        $tbl = $this->container->getParameter('jeb.snahp.tables');
        $sql = 'UPDATE ' . $tbl['dibs'] . '
            SET ' . $this->db->sql_build_array('UPDATE', $data) . '
            WHERE tid=' . $tid . '
            ORDER BY id DESC';
        $this->db->sql_query($sql);
    }

    public function select_dibs($tid)
    {
        $tbl = $this->container->getParameter('jeb.snahp.tables');
        $sql_ary = [
            'SELECT'   => '*',
            'FROM'     => [$tbl['dibs'] => 'dibs'],
            'WHERE'    => 'tid=' . $tid,
            'ORDER_BY' => 'id DESC',
        ];
        $sql    = $this->db->sql_build_query('SELECT', $sql_ary);
        $result = $this->db->sql_query($sql);
        $row    = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);
        return $row;
    }

    public function select_total($tbl, $condition)
    {
        $sql_ary = [
            'SELECT'   => 'count(*) as total',
            'FROM'     => [$tbl => 'dibs'],
            'WHERE'    => $condition,
        ];
        $sql    = $this->db->sql_build_query('SELECT', $sql_ary);
        $result = $this->db->sql_query($sql);
        $row    = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);
        return $row['total'];
    }

    public function select_last_undib($undibber_uid, $tid)
    {
        $tbl = $this->container->getParameter('jeb.snahp.tables');
        $def = $this->container->getParameter('jeb.snahp.req')['def'];
        $sql_ary = [
            'SELECT'   => '*',
            'FROM'     => [$tbl['dibs'] => 'dibs'],
            'WHERE'    => 'status=' . $def['undib'] . '
                        AND undibber_uid=' . $undibber_uid . '
                        AND tid=' . $tid,
            'ORDER_BY' => 'id DESC'
        ];
        $sql    = $this->db->sql_build_query('SELECT', $sql_ary);
        $result = $this->db->sql_query($sql);
        $row    = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);
        return $row;
    }

    public function reject_anon()
    {
        $uid = $this->user->data['user_id'];
        if ($uid == ANONYMOUS)
            trigger_error('You must login before venturing forth.');
    }

    public function is_mod()
    {
        return $this->auth->acl_gets('a_', 'm_');
    }

    public function reject_non_moderator()
    {
        if (!$this->is_mod())
            trigger_error('Only moderators can access this page.');
    }

    public function get_dt($time)
    {
        return $this->user->format_date($time);
    }

    public function add_tag($strn)
    {
        $ptn = '#((\(|\[|\{)(request)(\)|\]|\}))#is';
        $strn = preg_replace($ptn, '<span class="btn open">\1</span>', $strn);
        $ptn = '#((\(|\[|\{)(solved)(\)|\]|\}))#is';
        $strn = preg_replace($ptn, '<span class="btn solve">\1</span>', $strn);
        $ptn = '#((\(|\[|\{)(accepted)(\)|\]|\}))#is';
        $strn = preg_replace($ptn, '<span class="btn dib">\1</span>', $strn);
        $ptn = '#((\(|\[|\{)(fulfilled)(\)|\]|\}))#is';
        $strn = preg_replace($ptn, '<span class="btn fulfill">\1</span>', $strn);
        $ptn = '#((\(|\[|\{)(closed)(\)|\]|\}))#is';
        $strn = preg_replace($ptn, '<span class="btn terminate">\1</span>', $strn);
        return $strn;
    }

    public function remove_tag($strn)
    {
        $ptn = '#<span[^>]*"(btn){1}.*">([^<]*)</span>#is';
        $strn = preg_replace($ptn, '', $strn);
        return $strn;
    }

    public function validate_curly_tags($html)
    {
        preg_match_all('#{([a-z]+)(?: .*)?(?<![/|/ ])}#iU', $html, $result);
        $openedtags = $result[1];   #put all closed tags into an array
        preg_match_all('#{/([a-z]+)}#iU', $html, $result);
        $closedtags = $result[1];
        $len_opened = count($openedtags);
        $len_closed = count($closedtags);
        if ($len_closed != $len_opened) {
            return False;
        }
        $openedtags = array_reverse($openedtags);
        for ($i=0; $i < $len_opened; $i++)
        {
            if (!in_array($openedtags[$i], $closedtags))
            {
                return False;
            }
            else
            {
                unset($closedtags[array_search($openedtags[$i], $closedtags)]);
            }
        }
        return True;
    }

    public function interpolate_curly_tags($strn)
    {
        $valid = $this->validate_curly_tags($strn) ? 1 : 0;
        if (!$valid) return $strn;
        $ptn = '/{([^}]*)}/is';
        $strn = preg_replace_callback($ptn, function($m) {
            $allowed_directive = $this->allowed_directive;
            $sub = $m[1];
            $b_open = False;
            if ($sub && $sub[0] == '/')
            {
                $sub = substr($sub, 1);
            }
            else
            {
                $b_open = True;
            }
            preg_match('/(\w+)/is', $sub, $match);
            if ($match && in_array($match[0], $allowed_directive))
            {
                switch ($match[0])
                {
                case 'table':
                    if ($b_open)
                    {
                        $tag = '<div class="request_table container"><div class="request_table wrapper">';
                        $tag .= "<$m[1]>";
                    }
                    else
                    {
                        $tag = "<$m[1]>";
                        $tag .= '</div></div>';
                    }
                    return $tag;
                    break;
                case 'iframe':
                    $ptn = '#src=("|\')([^("|\')]*)("|\')#is';
                    $text = $m[1];
                    preg_match($ptn, $m[1], $match);
                    if (!$match)
                    {
                        return "<$m[1]>";
                    }
                    $url = parse_url($match[2]);
                    $allowed_hosts = ['www.youtube.com', 'streamable.com'];
                    if (in_array($url['host'], $allowed_hosts))
                    {
                        return "<$m[1]>";
                    }
                    return '';
                    break;
                default:
                    return "<$m[1]>";
                }
            }
        }, $strn);
        $ptn = '#(.*)(<table.*</table>)(.*)#is';
        preg_match($ptn, $strn, $match);
        if ($match)
        {
            $table = $match[2];
            $table = str_replace('<br>', '', $table);
            $strn = $match[1];
            $strn .= $table;
            $strn .= $match[3];
        }
        return $strn;
    }

}

