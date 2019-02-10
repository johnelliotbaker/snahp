<?php

namespace jeb\snahp\cron;
use jeb\snahp\core\topic_mover;
use phpbb\db\driver\driver_interface;

function fw($filepath, $var, $bNew=true)
{
    if ($bNew) file_put_contents($filepath, '');
    if (is_array($var))
    {
        foreach ($var as $k => $v)
        {
            file_put_contents($filepath, "$k => ", FILE_APPEND);
            fw($filepath, $v, false);
        }
    }
    else
    {
        file_put_contents($filepath, "$var\n", FILE_APPEND);
    }
}

class graveyard_request extends \phpbb\cron\task\base
{
    protected $config;
    protected $topic_mover;
    protected $container;

    public function __construct(
        \phpbb\config\config $config,
        topic_mover $topic_mover,
        $db,
        $container
    )
    {
        $this->config      = $config;
        $this->topic_mover = $topic_mover;
        $this->db          = $db;
        $this->container   = $container;
    }

    protected function get_topic_data(array $topic_ids)
    {
        if (!function_exists('phpbb_get_topic_data'))
        {
            include('includes/functions_mcp.php');
        }
        return phpbb_get_topic_data($topic_ids);
    }

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

    public function exec_move()
    {
        $requestdata = $this->select_request_closed();
        $a_tid = [];
        foreach ($requestdata as $req)
            $a_tid[] = $req['tid'];
        if (!$a_tid) return false;
        $td = $this->get_topic_data($a_tid);
        $graveyard_fid = unserialize($this->config['snp_cron_graveyard_fid'])['default'];
        $this->topic_mover->move_topics($td, $graveyard_fid);
        $tbl = $this->container->getParameter('jeb.snahp.tables');
        $sql = 'UPDATE ' . $tbl['req'] .
            ' SET b_graveyard = 1 ' .
            ' WHERE ' . $this->db->sql_in_set('tid', $a_tid);
        $this->db->sql_query($sql);
    }

    public function run()
    {
        $this->exec_move();
        $this->config->set('snp_cron_graveyard_last_gc', time());
    }

    public function is_runnable()
    {
        return (int) $this->config['snp_cron_b_graveyard'];
    }

    public function should_run()
    {
        return $this->config['snp_cron_graveyard_last_gc'] < time() - $this->config['snp_cron_graveyard_gc'];
    }
}
