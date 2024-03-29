<?php
namespace jeb\snahp\mcp;

use jeb\snahp\core\base;

class main_module extends base
{
    public $page_title;
    public $tpl_name;
    public $u_action;

    public function main($id, $mode)
    {
        global $config, $request, $template, $user, $phpbb_container;
        $user->add_lang_ext("jeb/snahp", "common");
        $this->page_title = $user->lang("MCP_SNP_TITLE");
        $cfg = [];
        $this->def = $phpbb_container->getParameter("jeb.snahp.req")["def"];
        $this->tbl = $phpbb_container->getParameter("jeb.snahp.tables");
        switch ($mode) {
            case "dibs":
                $cfg["tpl_name"] = "@jeb_snahp/mcp/mcp_snp_list_dibs";
                $cfg["b_feedback"] = false;
                $this->handle_list_dibs($cfg);
                break;
            case "ban":
                $cfg["tpl_name"] = "@jeb_snahp/mcp/mcp_snp_dibs_ban";
                $cfg["b_feedback"] = false;
                $this->handle_dibs_ban($cfg);
                break;
            case "request":
                $cfg["tpl_name"] = "@jeb_snahp/mcp/mcp_snp_list_request";
                $cfg["b_feedback"] = false;
                $this->handle_list_request($cfg);
                break;
            case "topic_bump":
                $cfg["tpl_name"] = "@jeb_snahp/mcp/mcp_snp_list_topic_bump";
                $cfg["b_feedback"] = false;
                $this->handle_list_topic_bump($cfg);
                break;
            case "invite":
                $cfg["tpl_name"] = "@jeb_snahp/mcp/invite/mcp_snp_invite";
                $cfg["b_feedback"] = false;
                $this->handle_invite($cfg);
                break;
            case "scripts":
                $cfg["tpl_name"] = "@jeb_snahp/mcp/scripts/base";
                // $cfg['tpl_name'] = '@jeb_snahp/mcp/invite/mcp_snp_invite';
                $cfg["b_feedback"] = false;
                $this->handle_scripts($cfg);
                break;
        }
        if (!empty($cfg)) {
            $this->handle_default($cfg);
        }
    }

    public function handle_scripts($cfg)
    {
        global $config,
            $request,
            $template,
            $user,
            $db,
            $phpbb_container,
            $auth,
            $helper;
        $tpl_name = $cfg["tpl_name"];
        if ($tpl_name) {
            $data[] = [
                "ID" => 1,
                "PURPOSE" => "Search",
                "NAME" => "Search Exclusion",
                "DESCRIPTION" =>
                    "Exclude certain keywords from being searched.",
                "LINK" =>
                    '<a href="/app.php/snahp/search_util/handle_common_words/" target="_blank"><i class="icon fa-external-link-square fa-fw" aria-hidden="true"></i></a>',
            ];
            $data[] = [
                "ID" => 2,
                "PURPOSE" => "Move Topics",
                "NAME" => "Mass Topic Mover",
                "DESCRIPTION" =>
                    "Move multiple topics from one forum to another (especially to graveyard) with some filtering.",
                "LINK" =>
                    '<a href="/app.php/snahp/mcp/handle_mass_move/" target="_blank"><i class="icon fa-external-link-square fa-fw" aria-hidden="true"></i></a>',
            ];
            $data[] = [
                "ID" => 3,
                "PURPOSE" => "Search",
                "NAME" => "Power Search",
                "DESCRIPTION" => "Use mysql to search topic titles.",
                "LINK" =>
                    '<a href="/app.php/snahp/search_util/handle_mysql_search/" target="_blank"><i class="icon fa-external-link-square fa-fw" aria-hidden="true"></i></a>',
            ];
            $data[] = [
                "ID" => 4,
                "PURPOSE" => "Analytics",
                "NAME" => "Statistics",
                "DESCRIPTION" => "Forum statistics",
                "LINK" =>
                    '<a href="/app.php/snahp/analytics/handle/stats/" target="_blank"><i class="icon fa-external-link-square fa-fw" aria-hidden="true"></i></a>',
            ];
            $data[] = [
                "ID" => 5,
                "PURPOSE" => "Achievements",
                "NAME" => "Achievements",
                "DESCRIPTION" => "Update Achievements",
                "LINK" =>
                    '<a href="/app.php/snahp/achievements/update/" target="_blank"><i class="icon fa-external-link-square fa-fw" aria-hidden="true"></i></a>',
            ];
            $data[] = [
                "ID" => 6,
                "PURPOSE" => "Reputations",
                "NAME" => "Reputations Giveaways",
                "DESCRIPTION" => "Mass set available reputations points",
                "LINK" =>
                    '<a href="/app.php/snahp/reputation/mcp_rep_giveaways/" target="_blank"><i class="icon fa-external-link-square fa-fw" aria-hidden="true"></i></a>',
            ];
            $data[] = [
                "ID" => 7,
                "PURPOSE" => "Analytics",
                "NAME" => "Common Thanks",
                "DESCRIPTION" => "List users who thanked a list of topics",
                "LINK" =>
                    '<a href="/app.php/snahp/analytics/handle/common_thanks/" target="_blank"><i class="icon fa-external-link-square fa-fw" aria-hidden="true"></i></a>',
            ];
            $data[] = [
                "ID" => 8,
                "PURPOSE" => "Economy",
                "NAME" => "User Account Manager",
                "DESCRIPTION" => "Manage User Assets and Inventory",
                "LINK" =>
                    '<a href="/app.php/snahp/economy/uam/user_manager/" target="_blank"><i class="icon fa-external-link-square fa-fw" aria-hidden="true"></i></a>',
            ];
            $data[] = [
                "ID" => 9,
                "PURPOSE" => "Economy",
                "NAME" => "Transaction History",
                "DESCRIPTION" => "View Economy System Transaction Logs",
                "LINK" =>
                    '<a href="/app.php/snahp/economy/mcp/dashboard/overview/" target="_blank"><i class="icon fa-external-link-square fa-fw" aria-hidden="true"></i></a>',
            ];
            $data[] = [
                "ID" => 10,
                "PURPOSE" => "Economy",
                "NAME" => "Product Class Editor",
                "DESCRIPTION" => "Modify User Upgrade Specifications",
                "LINK" =>
                    '<a href="/app.php/snahp/economy/pce/edit/" target="_blank"><i class="icon fa-external-link-square fa-fw" aria-hidden="true"></i></a>',
            ];
            $data[] = [
                "ID" => 11,
                "PURPOSE" => "Thanks",
                "NAME" => "Top Thanks Given",
                "DESCRIPTION" => "Show Users With Most Thanks Given",
                "LINK" =>
                    '<a href="/app.php/snahp/thanks/handle/top_thanks_given/" target="_blank"><i class="icon fa-external-link-square fa-fw" aria-hidden="true"></i></a>',
            ];
            $data[] = [
                "ID" => 11,
                "PURPOSE" => "Analytics",
                "NAME" => "Locker Common Thanks",
                "DESCRIPTION" => "Common Thanks for Lockers",
                "LINK" =>
                    '<a href="/app.php/locker/common_thanks/" target="_blank"><i class="icon fa-external-link-square fa-fw" aria-hidden="true"></i></a>',
            ];
            $data[] = [
                "ID" => 12,
                "PURPOSE" => "Move Topics",
                "NAME" => "Mass Topic Mover (w/ search)",
                "DESCRIPTION" => "Mass Topic Mover with Keyword Search",
                "LINK" =>
                    '<a href="/app.php/snahp/mcp/handle_mass_move_v2/" target="_blank"><i class="icon fa-external-link-square fa-fw" aria-hidden="true"></i></a>',
            ];
            $data[] = [
                "ID" => 13,
                "PURPOSE" => "User Block",
                "NAME" => "User Block MCP",
                "DESCRIPTION" => "Manage User Blocks",
                "LINK" =>
                    '<a href="/app.php/snahp/block/mcp/" target="_blank"><i class="icon fa-external-link-square fa-fw" aria-hidden="true"></i></a>',
            ];
            $data[] = [
                "ID" => 14,
                "PURPOSE" => "Mute User",
                "NAME" => "Mute User",
                "DESCRIPTION" => "Options to Mute Users",
                "LINK" =>
                    '<a href="/app.php/snahp/mute_user/mcp/manage/" target="_blank"><i class="icon fa-external-link-square fa-fw" aria-hidden="true"></i></a>',
            ];
            $data[] = [
                "ID" => 15,
                "PURPOSE" => "Move Topics (Username)",
                "NAME" => "Mass Topic Mover (w/ username)",
                "DESCRIPTION" => "Mass Move Topics Based on Username",
                "LINK" =>
                    '<a href="/app.php/snahp/mass_mover/mcp/main/" target="_blank"><i class="icon fa-external-link-square fa-fw" aria-hidden="true"></i></a>',
            ];
            foreach ($data as $entry) {
                $template->assign_block_vars("postrow", $entry);
            }
            $this->tpl_name = $tpl_name;
            $template->assign_vars([
                "B_ENABLE" => $config["snp_b_request"],
                "U_ACTION" => $this->u_action,
            ]);
        }
    }

    public function handle_invite($cfg)
    {
        global $config,
            $request,
            $template,
            $user,
            $db,
            $phpbb_container,
            $auth,
            $helper;
        $helper = new \jeb\snahp\core\invite_helper(
            $phpbb_container,
            $user,
            $auth,
            $request,
            $db,
            $config,
            $helper,
            $template
        );
        $tpl_name = $cfg["tpl_name"];
        if ($tpl_name) {
            $this->tpl_name = $tpl_name;
            add_form_key("jeb_snp");
            if ($request->is_set_post("submit")) {
                if (!check_form_key("jeb_snp")) {
                    trigger_error("FORM_INVALID", E_USER_WARNING);
                }
                $id = $request->variable("invitation_id", "0");
                $inviter_id = $request->variable("user_id", "0");
                if ($inviter_id) {
                    $data["n_available"] = $request->variable(
                        "current_available",
                        "0"
                    );
                    $data["b_redeem_enable"] = $request->variable(
                        "can_redeem",
                        "0"
                    );
                    $data["b_ban"] = $request->variable("banned", "0");
                    $data["ban_msg_public"] = $request->variable(
                        "public_ban_message",
                        ""
                    );
                    $data["ban_msg_private"] = $request->variable(
                        "internal_ban_message",
                        ""
                    );
                    $a_user_id = [$inviter_id];
                    $helper->update_invite_users($a_user_id, $data);
                }
                // meta_refresh(0, $this->u_action . "&user_id={$inviter_id}");
                // // trigger_error('');
            }
            $template->assign_vars([
                "B_ENABLE" => $config["snp_b_request"],
                "B_GENERATE" => false,
                "U_ACTION" => $this->u_action,
            ]);
        }
    }

    public function select_topics()
    {
        global $db;
        $sql = "SELECT * from " . TOPICS_TABLE;
        $result = $db->sql_query($sql);
        $data = [];
        while ($row = $db->sql_fetchrow($result)) {
            $data[] = $row;
        }
        $db->sql_freeresult($result);
        return $data;
    }

    public function select_groups()
    {
        global $db;
        $sql = "SELECT * from " . GROUPS_TABLE;
        $result = $db->sql_query($sql);
        $data = [];
        while ($row = $db->sql_fetchrow($result)) {
            $data[] = $row;
        }
        $db->sql_freeresult($result);
        return $data;
    }

    public function select_request_users($uid)
    {
        $sql = "SELECT * FROM " . $this->req_users_tbl . " WHERE user_id=$uid";
        $result = $db->sql_query($sql);
        $row = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);
        return $row;
    }

    public function handle_default($cfg)
    {
        global $config, $request, $template, $user, $db;
        // prn(array_keys($GLOBALS)); // Lists all available globals
        $tpl_name = $cfg["tpl_name"];
        if ($tpl_name) {
            $this->tpl_name = $tpl_name;
            add_form_key("jeb_snp");
            if ($request->is_set_post("submit")) {
                if (!check_form_key("jeb_snp")) {
                    trigger_error("FORM_INVALID", E_USER_WARNING);
                }
                // trigger_error('Handling default');
            }
            $template->assign_vars([
                "U_ACTION" => $this->u_action,
            ]);
        }
    }

    public function select_topic_bump_for_pagi($per_page, $start, $options)
    {
        global $db, $table_prefix;
        $p = $table_prefix;
        $sql =
            "SELECT " .
            "1 as total " .
            "FROM " .
            $p .
            "snahp_bump_topic AS topic_bump " .
            "LEFT JOIN " .
            USERS_TABLE .
            " AS users ON topic_bump.poster_uid=users.user_id " .
            "LEFT JOIN " .
            TOPICS_TABLE .
            " AS topics ON topic_bump.tid=topics.topic_id " .
            '
                    WHERE topic_id>0 ' .
            $options["bump_username"] .
            " " .
            $options["sortkey"] .
            '
                    LIMIT 1000';
        $result = $db->sql_query($sql, 600);
        $total = count($db->sql_fetchrowset($result));
        $db->sql_freeresult($result);
        $sql =
            "SELECT " .
            "users.username, topic_bump.*, topics.* " .
            "FROM " .
            $p .
            "snahp_bump_topic AS topic_bump " .
            "LEFT JOIN " .
            USERS_TABLE .
            " AS users ON topic_bump.poster_uid=users.user_id " .
            "LEFT JOIN " .
            TOPICS_TABLE .
            " AS topics ON topic_bump.tid=topics.topic_id " .
            '
                    WHERE topic_id>0 ' .
            $options["bump_username"] .
            " " .
            $options["sortkey"];
        $result = $db->sql_query_limit($sql, $per_page, $start);
        $data = $db->sql_fetchrowset($result);
        $db->sql_freeresult($result);
        return [$data, $total];
    }

    public function select_requests_for_pagi($per_page, $start)
    {
        global $db, $table_prefix;
        $p = $table_prefix;
        $sql =
            "SELECT " .
            "users.username, request.*, topics.* " .
            "FROM " .
            $p .
            "snahp_request AS request " .
            "LEFT JOIN " .
            USERS_TABLE .
            " AS users ON request.requester_uid=users.user_id " .
            "LEFT JOIN " .
            TOPICS_TABLE .
            " AS topics ON request.tid=topics.topic_id " .
            '
                    WHERE request.status<>19
                    ORDER BY id DESC';
        $result = $db->sql_query_limit($sql, $per_page, $start);
        $data = $db->sql_fetchrowset($result);
        $db->sql_freeresult($result);
        return $data;
    }

    public function select_undibs_for_pagi($per_page, $start)
    {
        global $db, $table_prefix;
        $p = $table_prefix;
        $sql =
            "SELECT " .
            "users.username, dibs.*, topics.* " .
            "FROM " .
            $p .
            "snahp_dibs AS dibs " .
            "LEFT JOIN " .
            USERS_TABLE .
            " AS users ON dibs.dibber_uid=users.user_id " .
            "LEFT JOIN " .
            TOPICS_TABLE .
            " AS topics ON dibs.tid=topics.topic_id " .
            "WHERE status=5 " .
            "ORDER BY undib_time DESC";
        $result = $db->sql_query_limit($sql, $per_page, $start);
        $data = $db->sql_fetchrowset($result);
        $db->sql_freeresult($result);
        return $data;
    }

    public function select_total($tbl, $condition)
    {
        global $db;
        $sql_arr = [
            "SELECT" => "count(*) as total",
            "FROM" => [$tbl => "t"],
            "WHERE" => $condition,
        ];
        $sql = $db->sql_build_query("SELECT", $sql_arr);
        $result = $db->sql_query($sql);
        $row = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);
        return $row["total"];
    }

    public function select_user_modal($mode, $data)
    {
        global $db;
        switch ($mode) {
            case "user_id":
                $where = 'user_id="' . $data . '"';
                break;
            case "username":
                $where = 'username_clean="' . $data . '"';
                break;
            default:
                return false;
        }
        $sql_arr = [
            "SELECT" => "*",
            "FROM" => [USERS_TABLE => "users"],
            "WHERE" => $where,
        ];
        $sql = $db->sql_build_query("SELECT", $sql_arr);
        $result = $db->sql_query($sql);
        $row = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);
        return $row;
    }

    public function enable_dibber($mode, $data, $b_enable = true)
    {
        global $db;
        switch ($mode) {
            case "user_id":
                $where = ' WHERE user_id="' . $data . '"';
                break;
            case "username":
                $data = utf8_clean_string($data);
                $where = ' WHERE username_clean="' . $data . '"';
                break;
            default:
                return false;
        }
        if ($where && ($row = $this->select_user_modal($mode, $data))) {
            $status = $b_enable ? 1 : 0;
            $sql_arr = [
                "snp_req_dib_enable" => $status,
            ];
            $sql =
                "UPDATE " .
                USERS_TABLE .
                ' SET
                ' .
                $db->sql_build_array("UPDATE", $sql_arr) .
                $where;
            $db->sql_query($sql);
            return true;
        }
        return false;
    }

    public function handle_list_dibs($cfg)
    {
        global $config, $request, $template, $user, $db, $phpbb_container;
        $tpl_name = $cfg["tpl_name"];
        if ($tpl_name) {
            $this->tpl_name = $tpl_name;
            add_form_key("jeb_snp");
            if ($request->is_set_post("submit")) {
                if (!check_form_key("jeb_snp")) {
                    trigger_error("FORM_INVALID", E_USER_WARNING);
                }
                meta_refresh(2, $this->u_action);
                trigger_error(
                    $user->lang("MCP_SNP_SETTING_SAVED") .
                        adm_back_link($this->u_action)
                );
            }
            $per_page = $config["posts_per_page"];
            $start = $request->variable("start", 0);
            $dibdata = $this->select_undibs_for_pagi($per_page, $start);
            $total = $this->select_total(
                $this->tbl["dibs"],
                "status=" . $this->def["undib"]
            );
            $pagination = $phpbb_container->get("pagination");
            $base_url = $this->u_action;
            $pagination->generate_template_pagination(
                $base_url,
                "pagination",
                "start",
                $total,
                $per_page,
                $start
            );
            foreach ($dibdata as $row) {
                $tid = $row["tid"];
                $topic_time = $user->format_date($row["topic_time"]);
                $undib_time = $user->format_date($row["undib_time"]);
                $u_details = "/viewtopic.php?t=" . $tid;
                $group = [
                    "DIBBER_USERNAME" => $row["username"],
                    "DIB_TIME" => $row["dib_time"],
                    "UNDIB_TIME" => $undib_time,
                    "TOPIC_TITLE" => $row["topic_title"],
                    "TOPIC_POSTER" => $row["topic_first_poster_name"],
                    "TOPIC_TIME" => $topic_time,
                    "U_VIEW_DETAILS" => $u_details,
                ];
                $template->assign_block_vars("postrow", $group);
            }
            $template->assign_vars([
                "B_ENABLE" => $config["snp_b_request"],
                "U_ACTION" => $this->u_action,
            ]);
        }
    }

    public function handle_dibs_ban($cfg)
    {
        global $config, $request, $template, $user, $db, $phpbb_container;
        $tpl_name = $cfg["tpl_name"];
        if ($tpl_name) {
            $this->tpl_name = $tpl_name;
            add_form_key("jeb_snp");
            if ($request->is_set_post("submit")) {
                if (!check_form_key("jeb_snp")) {
                    trigger_error("FORM_INVALID", E_USER_WARNING);
                }
                $a_username = $request->variable("ban", "");
                $a_username = preg_split("#" . PHP_EOL . "|,#", $a_username);
                $b_enable = $request->variable("banexclude", "1");
                foreach ($a_username as $username) {
                    $b_success = $this->enable_dibber(
                        "username",
                        $username,
                        $b_enable
                    );
                    if (!$b_success) {
                        prn("Could not process " . $username);
                    }
                }
            }
            $per_page = $config["posts_per_page"];
            $start = $request->variable("start", 0);
            $reqdata = $this->select_requests_for_pagi($per_page, $start);
            $total = $this->select_total($this->tbl["req"], "TRUE");
            $pagination = $phpbb_container->get("pagination");
            $base_url = $this->u_action;
            $pagination->generate_template_pagination(
                $base_url,
                "pagination",
                "start",
                $total,
                $per_page,
                $start
            );
            foreach ($reqdata as $row) {
                $tid = $row["tid"];
                $topic_time = $user->format_date($row["topic_time"]);
                $u_details = "/viewtopic.php?t=" . $tid;
                $group = [
                    "TOPIC_TITLE" => $row["topic_title"],
                    "TOPIC_POSTER" => $row["topic_first_poster_name"],
                    "TOPIC_TIME" => $topic_time,
                    "U_VIEW_DETAILS" => $u_details,
                ];
                $template->assign_block_vars("postrow", $group);
            }
            $template->assign_vars([
                "B_ENABLE" => $config["snp_b_request"],
                "U_ACTION" => $this->u_action,
            ]);
        }
    }

    public function handle_list_topic_bump($cfg)
    {
        global $config, $request, $template, $user, $db, $phpbb_container;
        $tpl_name = $cfg["tpl_name"];
        if ($tpl_name) {
            $this->tpl_name = $tpl_name;
            add_form_key("jeb_snp");
            if ($request->is_set_post("submit")) {
                if (!check_form_key("jeb_snp")) {
                    trigger_error("FORM_INVALID", E_USER_WARNING);
                }
                meta_refresh(2, $this->u_action);
                trigger_error(
                    $user->lang("MCP_SNP_SETTING_SAVED") .
                        adm_back_link($this->u_action)
                );
            }
            $per_page = $config["posts_per_page"];
            $start = $request->variable("start", 0);
            // Sorting
            $sortkey = $request->variable("sortkey", "bumpdate");
            switch ($sortkey) {
                case "count":
                    $options["sortkey"] = "ORDER BY topic_bump.n_bump DESC";
                    break;
                case "bumpdate":
                default:
                    $options["sortkey"] = "ORDER BY topic_bump.topic_time DESC";
                    break;
            }
            // Filtering by username
            $bump_username = strtolower(
                $request->variable("bump_username", "")
            );
            $options["bump_username"] = "";
            if ($bump_username) {
                $options["bump_username"] =
                    ' AND users.username_clean="' . $bump_username . '"';
            }
            [$reqdata, $total] = $this->select_topic_bump_for_pagi(
                $per_page,
                $start,
                $options
            );
            $pagination = $phpbb_container->get("pagination");
            $base_url =
                $this->u_action .
                "&sortkey=$sortkey&bump_username=$bump_username";
            $pagination->generate_template_pagination(
                $base_url,
                "pagination",
                "start",
                $total,
                $per_page,
                $start
            );
            foreach ($reqdata as $row) {
                $tid = $row["tid"];
                $bump_time = $user->format_date($row["topic_time"]);
                $topic_time = $user->format_date($row["prev_topic_time"]);
                $u_details = "/viewtopic.php?t=" . $tid;
                $status = $row["status"];
                $status = $status == 1 ? "ON" : "OFF";
                $group = [
                    "TOPIC_TITLE" => $row["topic_title"],
                    "USER_ID" => $row["topic_poster"],
                    "USERNAME" => $row["topic_first_poster_name"],
                    "USER_COLOUR" => $row["topic_first_poster_colour"],
                    "TOPIC_TIME" => $topic_time,
                    "BUMP_TIME" => $bump_time,
                    "TOPIC_ID" => $row["topic_id"],
                    "FORUM_ID" => $row["forum_id"],
                    "BUMP_STATUS" => $status,
                    "N_BUMP" => $row["n_bump"],
                    "U_VIEW_DETAILS" => $u_details,
                ];
                $template->assign_block_vars("postrow", $group);
            }
            $template->assign_vars([
                "B_ENABLE" => $config["snp_b_request"],
                "SORTKEY" => $sortkey,
                "BUMP_USERNAME" => $bump_username,
                "U_ACTION" => $this->u_action,
                "BASE_URL" => $base_url,
            ]);
        }
    }

    public function handle_list_request($cfg)
    {
        global $config, $request, $template, $user, $db, $phpbb_container;
        $tpl_name = $cfg["tpl_name"];
        if ($tpl_name) {
            $this->tpl_name = $tpl_name;
            add_form_key("jeb_snp");
            if ($request->is_set_post("submit")) {
                if (!check_form_key("jeb_snp")) {
                    trigger_error("FORM_INVALID", E_USER_WARNING);
                }
                meta_refresh(2, $this->u_action);
                trigger_error(
                    $user->lang("MCP_SNP_SETTING_SAVED") .
                        adm_back_link($this->u_action)
                );
            }
            $per_page = $config["posts_per_page"];
            $start = $request->variable("start", 0);
            $reqdata = $this->select_requests_for_pagi($per_page, $start);
            $total = $this->select_total(
                $this->tbl["req"],
                "status<>" . $this->def["deleted"]
            );
            $pagination = $phpbb_container->get("pagination");
            $base_url = $this->u_action;
            $pagination->generate_template_pagination(
                $base_url,
                "pagination",
                "start",
                $total,
                $per_page,
                $start
            );
            foreach ($reqdata as $row) {
                $tid = $row["tid"];
                $topic_time = $user->format_date($row["topic_time"]);
                $u_details = "/viewtopic.php?t=" . $tid;
                $group = [
                    "TOPIC_TITLE" => $row["topic_title"],
                    "TOPIC_POSTER" => $row["topic_first_poster_name"],
                    "TOPIC_TIME" => $topic_time,
                    "U_VIEW_DETAILS" => $u_details,
                ];
                $template->assign_block_vars("postrow", $group);
            }
            $template->assign_vars([
                "B_ENABLE" => $config["snp_b_request"],
                "U_ACTION" => $this->u_action,
            ]);
        }
    }
}
