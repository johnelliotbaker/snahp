<?php
namespace jeb\snahp\Apps\PostCreator;

class PostCreatorHelper
{
    const CACHE_DURATION = 0;
    const CACHE_DURATION_LONG = 0;

    protected $db;
    protected $user;
    protected $template;
    protected $tbl;
    protected $sauth;
    public function __construct($db, $user, $template, $tbl, $sauth)
    {
        $this->db = $db;
        $this->user = $user;
        $this->template = $template;
        $this->tbl = $tbl;
        $this->rxnTbl = $tbl["PostCreator"];
        $this->sauth = $sauth;
        $this->user_id = $this->user->data["user_id"];
    }
}
