<?php


/**
 *
 */
function go_admin_bar_stats() {
    check_ajax_referer( 'go_admin_bar_stats_' );
    //$user_id = 0;
    //Get the user_id for the stats
    if ( ! empty( $_POST['uid'] ) ) {
        $user_id = (int) $_POST['uid'];
        $current_user = get_userdata( $user_id );
    } else {
        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;
    }

    //is the current user an admin
    $current_user_id = get_current_user_id();
    $is_admin = go_user_is_admin($current_user_id);


    ?>
    <input type="hidden" id="go_stats_hidden_input" value="<?php echo $user_id; ?>"/>
    <?php
    $full_name_toggle = get_option('options_go_full-names_toggle');
    $user_fullname = $current_user->first_name.' '.$current_user->last_name;
    //$user_login =  $current_user->user_login;
    $user_display_name = $current_user->display_name;
    //$user_website = $current_user->user_url;



    $leaderboard_toggle = get_option('options_go_stats_leaderboard_toggle');


    $use_local_avatars = get_option('options_go_avatars_local');
    $use_gravatar = get_option('options_go_avatars_gravatars');
    if ($use_local_avatars){
        $user_avatar_id = get_user_meta( $user_id, 'go_avatar', true );
        $user_avatar = wp_get_attachment_image($user_avatar_id);
    }
    if (empty($user_avatar) && $use_gravatar) {
        $user_avatar = get_avatar( $user_id, 150 );
    }

    //$user_focuses = go_display_user_focuses( $user_id );



    $current_points = go_return_points( $user_id );


    $go_option_ranks = get_option( 'go_ranks' );
    $points_array = $go_option_ranks['points'];

    $max_rank_index = count( $points_array ) - 1;
    $max_rank_points = (int) $points_array[ $max_rank_index ];

    $percentage_of_level = 1;

    // user pnc
    $rank = go_get_rank( $user_id );
    $current_rank = $rank['current_rank'];
    /*
    $current_rank_points = $rank['current_rank_points'];
    $next_rank = $rank['next_rank'];
    $next_rank_points = $rank['next_rank_points'];

    if ( null !== $next_rank_points ) {
        $rank_threshold_diff = ( $next_rank_points - $current_rank_points );
    } else {
        $rank_threshold_diff = 1;
    }
    $pts_to_rank_threshold = ( $current_points - $current_rank_points );

    if ( $max_rank_points === $current_rank_points ) {
        $prestige_name = get_option( 'go_prestige_name' );
        $pts_to_rank_up_str = $prestige_name;
    } else {
        $pts_to_rank_up_str = "{$pts_to_rank_threshold} / {$rank_threshold_diff}";
    }

    $percentage_of_level = ( $pts_to_rank_threshold / $rank_threshold_diff ) * 100;
    if ( $percentage_of_level <= 0 ) {
        $percentage_of_level = 0;
    } else if ( $percentage_of_level >= 100 ) {
        $percentage_of_level = 100;
    }
    */


    /////////////////////////
    ///
    $xp_toggle = get_option('options_go_loot_xp_toggle');
    $gold_toggle = get_option('options_go_loot_gold_toggle');
    $health_toggle = get_option( 'options_go_loot_health_toggle' );

    if ($xp_toggle) {
        // the user's current amount of experience (points)
        $go_current_xp = go_get_user_loot($user_id, 'xp');

        $rank = go_get_rank($user_id);
        $rank_num = $rank['rank_num'];
        $current_rank = $rank['current_rank'];
        $current_rank_points = $rank['current_rank_points'];
        $next_rank = $rank['next_rank'];
        $next_rank_points = $rank['next_rank_points'];

        $go_option_ranks = get_option('options_go_loot_xp_levels_name_singular');
        //$points_array = $go_option_ranks['points'];

        /*
         * Here we are referring to last element manually,
         * since we don't want to modifiy
         * the arrays with the array_pop function.
         */
        //$max_rank_index = count( $points_array ) - 1;
        //$max_rank_points = (int) $points_array[ $max_rank_index ];

        if ($next_rank_points != false) {
            $rank_threshold_diff = $next_rank_points - $current_rank_points;
            $pts_to_rank_threshold = $go_current_xp - $current_rank_points;
            $pts_to_rank_up_str = "L{$rank_num}: {$pts_to_rank_threshold} / {$rank_threshold_diff}";
            $percentage = $pts_to_rank_threshold / $rank_threshold_diff * 100;
            //$color = barColor( $go_current_health, 0 );
            $color = "#39b54a";
        } else {
            $pts_to_rank_up_str = $current_rank;
            $percentage = 100;
            $color = "gold";
        }
        if ( $percentage <= 0 ) {
            $percentage = 0;
        } else if ( $percentage >= 100 ) {
            $percentage = 100;
        }
        $progress_bar = '<div class="go_admin_bar_progress_bar_border progress-bar-border">'.'<div class="go_admin_bar_progress_bar stats_progress_bar" '.
            'style="width: '.$percentage.'%; background-color: '.$color.' ;">'.
            '</div>'.
            '<div class="points_needed_to_level_up ">'.
            $pts_to_rank_up_str.
            '</div>'.
            '</div>';
    }
    else {
        $progress_bar = '';
    }


    if($health_toggle) {
        // the user's current amount of bonus currency,
        // also used for coloring the admin bar
        $go_current_health = go_get_user_loot($user_id, 'health');
        $health_percentage = intval($go_current_health / 2);
        if ($health_percentage <= 0) {
            $health_percentage = 0;
        } else if ($health_percentage >= 100) {
            $health_percentage = 100;
        }
        $health_bar = '<div class="go_admin_health_bar_border progress-bar-border">' . '<div class="go_admin_bar_health_bar stats_progress_bar" ' . 'style="width: ' . $health_percentage . '%; background-color: red ;">' . '</div>' . '<div class="health_bar_percentage_str">' . "Health Mod: " . $go_current_health . "%" . '</div>' . '</div>';

    }
    else{
        $health_bar = '';
    }

    if ($gold_toggle) {
        // the user's current amount of currency
        $go_current_gold = go_get_user_loot($user_id, 'gold');
        $gold_total = '<div  class="go_admin_bar_gold admin_bar_loot">' . go_display_shorthand_currency('gold', $go_current_gold)  . '</div>';
    }
    else{
        $gold_total = '';
    }

    //////////////////



    ?>
    <script>
        go_stats_links();
        jQuery('#wp-admin-bar-go_stats').prop('onclick',null).off('click');
        jQuery("#wp-admin-bar-go_stats").one("click", function(){ go_admin_bar_stats_page_button()});
    </script>
    <div id='go_stats_lay'>
        <div id='go_stats_header'>
            <div class="go_stats_id_card">
                <div class='go_stats_gravatar'><?php echo $user_avatar; ?></div>

                <div class='go_stats_user_info'>
                    <?php
                    if ($full_name_toggle || $is_admin){
                        echo "<h2>{$user_fullname}</h2>{$user_display_name}<br>";
                    }else{
                        echo "<h2>{$user_display_name}</h2>";
                    }
                    go_user_links($user_id, true, false, true, true, true, false);
                    ?>

                </div>
            </div>
            <div class="go_stats_bars">
                <?php
                if ($xp_toggle) {
                    echo '<div class="go_stats_rank"><h3>' . $go_option_ranks . ' ' . $rank_num . ": " . $current_rank . '</h3></div>';
                }
                echo $progress_bar;
                //echo "<div id='go_stats_user_points'><span id='go_stats_user_points_value'>{$current_points}</span> {$points_name}</div><div id='go_stats_user_currency'><span id='go_stats_user_currency_value'>{$current_currency}</span> {$currency_name}</div><div id='go_stats_user_bonus_currency'><span id='go_stats_user_bonus_currency_value'>{$current_bonus_currency}</span> {$bonus_currency_name}</div>{$current_penalty} {$penalty_name}<br>{$current_minutes} {$minutes_name}";
                echo $health_bar;
                ?>
            </div>
            <div class='go_stats_user_loot'>

                <?php

                if ($xp_toggle) {
                    echo '<div class="go_stats_xp">' . go_display_longhand_currency('xp', $go_current_xp) . '</div>';
                }
                if ($gold_toggle) {
                    echo '<div class="go_stats_gold">' . go_display_longhand_currency('gold', $go_current_gold) . '</div>';
                }
                if ($health_toggle) {
                    echo '<div class="go_stats_health">' . go_display_longhand_currency('health', $go_current_health) . '</div>';
                }
                ?>
            </div>
        </div>

        <div id="stats_tabs">
            <ul>
            <?php
            $current_user_id = get_current_user_id();
            $is_admin = go_user_is_admin($current_user_id);
                if ($is_admin){
                    echo '<li class="stats_tabs" tab="about"><a href="#stats_about">ABOUT</a></li>';
                }
            ?>


                <li class="stats_tabs" tab="tasks"><a href="#stats_tasks"><?php echo strtoupper( get_option( 'options_go_tasks_name_plural' ) ); ?></a></li>
                <li class="stats_tabs" tab="store"><a href="#stats_store"><?php echo strtoupper( get_option( 'options_go_store_name' ) ); ?></a></li>
                <li class="stats_tabs" tab="messages"><a href="#stats_messages">MESSAGES</a></li>
                <li class="stats_tabs" tab="history"><a href="#stats_history">HISTORY</a></li>
                <li class="stats_tabs" tab="badges"><a href="#stats_badges"><?php echo strtoupper( get_option( 'options_go_badges_name_plural' ) ); ?></a></li>
                <li class="stats_tabs" tab="groups"><a href="#stats_groups">GROUPS</a></li>

                <?php
                if ($leaderboard_toggle){
                     ?>
                    <li class="stats_tabs" tab="leaderboard"><a href="#stats_leaderboard"><?php echo strtoupper(get_option('options_go_stats_leaderboard_name')); ?></a></li>

                    <?php
                }
                if (!$is_admin){
                    echo '<li class="stats_tabs" tab="about"><a href="#stats_about">ABOUT</a></li>';
                }
            ?>

            </ul>
             <?php
             if($is_admin){
                    echo '<div id="stats_about">';

                            go_stats_about($user_id,  true);
                    echo "</div>";
             }
             ?>

            <div id="stats_tasks"></div>
            <div id="stats_store"></div>
            <div id="stats_messages"></div>
            <div id="stats_history"></div>
            <div id="stats_badges"></div>
            <div id="stats_groups"></div>
            <?php
               if ($leaderboard_toggle){
                     ?>
                    <div id="stats_leaderboard"></div>
                    <?php
                }
             if(!$is_admin){
                    echo '<div id="stats_about"></div>';
             }
             ?>

        </div>


    </div>
    <?php
    die();
}

/**Tasks with Sever Side Processing--in case the tables get too large*/
/*
function go_stats_task_listSSP() {
    check_ajax_referer( 'go_stats_task_list_' );


    echo "<div id='go_task_list' class='go_datatables'><table id='go_tasks_datatable' class='pretty display'>
                   <thead>
						<tr>
							<th class='header' id='go_stats_last_time'><a href=\"#\">Time</a></th>
							<th class='header' id='go_stats_post_name'><a href=\"#\">Title</a></th>
							
						
							<th class='header' id='go_stats_status'><a href=\"#\">Status</a></th>
							<th class='header' id='go_stats_bonus_status'><a href=\"#\">Bonus</a></th>
							<th class='header' id='go_stats_links'><a href=\"#\">Activity</a></th>
							
							<th class='header' id='go_stats_mods'><a href=\"#\">XP</a></th>
							<th class='header' id='go_stats_mods'><a href=\"#\">G</a></th>
							<th class='header' id='go_stats_mods'><a href=\"#\">H</a></th>
							<th class='header' id='go_stats_mods'><a href=\"#\">AP</a></th>
							
						</tr>
						</thead>
						<tfoot>
						<tr>
							<th class='header' id='go_stats_last_time'><a href=\"#\">Time</a></th>
							<th class='header' id='go_stats_post_name'><a href=\"#\">Title</a></th>
							
						
							<th class='header' id='go_stats_status'><a href=\"#\">Status</a></th>
							<th class='header' id='go_stats_bonus_status'><a href=\"#\">Bonus</a></th>
							<th class='header' id='go_stats_links'><a href=\"#\">Activity</a></th>
							
							<th class='header' id='go_stats_mods'><a href=\"#\">XP</a></th>
							<th class='header' id='go_stats_mods'><a href=\"#\">G</a></th>
							<th class='header' id='go_stats_mods'><a href=\"#\">H</a></th>
							<th class='header' id='go_stats_mods'><a href=\"#\">AP</a></th>
							
						</tr>
						</tfoot>
			   
				</table></div>";

    die();
}
function go_tasks_dataloader_ajax()
{

    global $wpdb;
    $go_task_table_name = "{$wpdb->prefix}go_tasks";
    $aColumns = array( 'id', 'uid', 'post_id', 'status', 'bonus_status' ,'xp', 'gold', 'health', 'start_time', 'last_time', 'badges', 'groups' );
    $sIndexColumn = "id";
    $sTable = $go_task_table_name;

    $sLimit = "";
    if ( isset( $_REQUEST['iDisplayStart'] ) && $_REQUEST['iDisplayLength'] != '-1' )
    {
        $sLimit = "LIMIT ".intval( $_REQUEST['iDisplayStart'] ).", ".
            intval( $_REQUEST['iDisplayLength'] );
    }

    $sOrder = "";
    if ( isset( $_REQUEST['iSortCol_0'] ) )
    {
        $sOrder = "ORDER BY  ";
        for ( $i=0 ; $i<intval( $_REQUEST['iSortingCols'] ) ; $i++ )
        {
            if ( $_REQUEST[ 'bSortable_'.intval($_REQUEST['iSortCol_'.$i]) ] == "true" )
            {
                $sOrder .= "`".$aColumns[ intval( $_REQUEST['iSortCol_'.$i] ) ]."` ".
                    ($_REQUEST['sSortDir_'.$i]==='asc' ? 'asc' : 'desc') .", ";
            }
        }

        $sOrder = substr_replace( $sOrder, "", -2 );
        if ( $sOrder == "ORDER BY" )
        {
            $sOrder = "";
        }
    }

    $sWhere = "";
    if ( isset($_REQUEST['sSearch']) && $_REQUEST['sSearch'] != "" )
    {
        $sWhere = "WHERE (";
        for ( $i=0 ; $i<count($aColumns) ; $i++ )
        {
            $sWhere .= "`".$aColumns[$i]."` LIKE '%".esc_sql( $_REQUEST['sSearch'] )."%' OR ";
        }
        $sWhere = substr_replace( $sWhere, "", -3 );
        $sWhere .= ')';
    }

    for ( $i=0 ; $i<count($aColumns) ; $i++ )
    {
        if ( isset($_REQUEST['bSearchable_'.$i]) && $_REQUEST['bSearchable_'.$i] == "true" && $_REQUEST['sSearch_'.$i] != '' )
        {
            if ( $sWhere == "" )
            {
                $sWhere = "WHERE ";
            }
            else
            {
                $sWhere .= " AND ";
            }
            $sWhere .= "`".$aColumns[$i]."` LIKE '%".esc_sql($_REQUEST['sSearch_'.$i])."%' ";
        }
    }

    $sQuery = "
  SELECT SQL_CALC_FOUND_ROWS `".str_replace(" , ", " ", implode("`, `", $aColumns))."`
  FROM   $sTable
  $sWhere
  $sOrder
  $sLimit
  ";
    $rResult = $wpdb->get_results($sQuery, ARRAY_A);

    $sQuery = "
  SELECT FOUND_ROWS()
 ";
    $rResultFilterTotal = $wpdb->get_results($sQuery, ARRAY_N);
    $iFilteredTotal = $rResultFilterTotal [0];

    $sQuery = "
  SELECT COUNT(`".$sIndexColumn."`)
  FROM   $sTable
 ";
    $rResultTotal = $wpdb->get_results($sQuery, ARRAY_N);
    $iTotal = $rResultTotal [0];

    $output = array(
        "sEcho" => intval($_REQUEST['sEcho']),
        "iTotalRecords" => $iTotal,
        "iTotalDisplayRecords" => $iFilteredTotal,
        "aaData" => array()
    );

    foreach($rResult as $task){//output a row for each task
        $row = array();
        ///////////
        ///
        $post_id = $task[post_id];
        $custom_fields = get_post_custom( $post_id );
        $post_name = get_the_title($post_id);
        $post_link = get_post_permalink($post_id);
        $status = $task[status];
        $total_stages = (isset($custom_fields['go_stages'][0]) ?  $custom_fields['go_stages'][0] : null);


        $bonus_switch = (isset($custom_fields['bonus_switch'][0]) ?  $custom_fields['bonus_switch'][0] : null);
        $bonus_status = null;
        $total_bonus_stages = null;
        if ($bonus_switch) {
            $bonus_status = $task[bonus_status];
            $total_bonus_stages = (isset($custom_fields['go_bonus_limit'][0]) ? $custom_fields['go_bonus_limit'][0] : null);
        }
        $xp = $task[xp];
        $gold = $task[gold];
        $health = $task[health];
        //$start_time = $task->start_time;
        $last_time = $task[last_time];
        $time  = date("m/d/y g:i A", strtotime($last_time));
        $unix_time = strtotime($last_time);


        $go_actions_table_name = "{$wpdb->prefix}go_actions";
        $actions = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT *
                        FROM {$go_actions_table_name}
                        WHERE source_id = %d
                        ORDER BY id DESC",
                $post_id
            )
        );

        $next_bonus_stage = null;
        $i = 0;
        $links = array();
        foreach ($actions as $action){
            $check_type = $action->check_type;
            $result = $action->result;
            $action_time = $action->TIMESTAMP;
            $action_time = date("m/d/y g:i A", strtotime($action_time));
            $action_stage = $action->stage;
            if ($action->action_type == 'task'){
                $loop_bonus_status = $action->bonus_status; //get the bonus status if it exists
                $stage = $action->stage ; //get the stage

                if (!isset($loop_bonus_status) && $loop_bonus_status > 0 ){//the last bonus submitted
                    $links[] = go_result_link($check_type, $result, $action_stage, $action_time);
                    $next_bonus_stage = $loop_bonus_status -1;
                }
                else if ($next_bonus_stage > 0 && $loop_bonus_status == $next_bonus_stage ){ //get the previous bonus stage
                    $links[] = go_result_link($check_type, $result, $action_stage, $action_time);
                    $next_bonus_stage = $loop_bonus_status -1;
                }
                else if ($next_bonus_stage <= 0 || $next_bonus_stage == null) {
                    if (!isset($next_stage) && $stage > 0 ){ //it's not a bonus and it's not the last one completed
                        $links[] = go_result_link($check_type, $result, $action_stage, $action_time);
                        $next_stage = $stage - 1;
                    }
                    else if ($next_stage > 0 && $stage == $next_stage){
                        $links[] = go_result_link($check_type, $result, $action_stage, $action_time);
                        $next_stage = $stage - 1;
                    }
                }

            }
        }
        $links = array_reverse($links);
        $links = $comma_separated = implode(" ", $links);


        $row[] = "{$unix_time}";
        $row[] = "<a href='{$post_link}' >{$post_name}</a>";
        $row[] = "{$status} / {$total_stages}";
        $row[] = "{$bonus_status} / {$total_bonus_stages}";
        $row[] = "{$links} <a href='javascript:;' class='go_stats_body_activity_single_task' data-postID='{$post_id}' onclick='go_stats_single_task_activity_list({$post_id});'> ALL</a>";
        $row[] = "{$xp}";
        $row[] = "{$gold}";
        $row[] = "{$health}";
        $output['aaData'][] = $row;
    }


    echo json_encode( $output );
    die();
}
*/
/**
* @param null $user_id
* @param bool $not_ajax
*/
function go_stats_about($user_id = null, $not_ajax = false) {

    if ( ! empty( $_POST['user_id'] ) && empty($user_id) ) {
        $user_id = (int) $_POST['user_id'];
    }

    if (!$not_ajax){
        check_ajax_referer( 'go_stats_about' );
    }

    echo "<div id='go_stats_about' class='go_datatables'>";
    $headshot_id = get_user_meta($user_id, 'go_headshot', true) ;
    $headshot = wp_get_attachment_image($headshot_id);
    ?>
    <div class='go_stats_gravatar'><?php echo $headshot; ?></div>
    <?php

    $num_of_qs = get_option('options_go_user_profile_questions');

     for ($i = 0; $i < $num_of_qs; $i++) {
         $q_title = get_option('options_go_user_profile_questions_' . $i . '_title');
         $q_answer = get_user_meta($user_id, 'question_' . $i, true);

         echo "<h4>{$q_title}</h4>";
         echo "<p>{$q_answer}</p>";
     }

    echo "</div>";

    //die();
}

/**Non server side processing (SSP)*/
function go_stats_task_list($user_id = null, $not_ajax = false) {
    global $wpdb;
    $go_task_table_name = "{$wpdb->prefix}go_tasks";
    if ( ! empty( $_POST['user_id'] ) ) {
        $user_id = (int) $_POST['user_id'];
    }
    $current_user = get_current_user_id();
    $is_admin = go_user_is_admin($current_user);

    if (!$not_ajax){
        check_ajax_referer( 'go_stats_task_list_' );
    }

    $tasks = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT *
			FROM {$go_task_table_name}
			WHERE uid = %d
			ORDER BY last_time DESC",
            $user_id

        )
    );

    $xp_toggle = get_option('options_go_loot_xp_toggle');
    $gold_toggle = get_option('options_go_loot_gold_toggle');
    $health_toggle = get_option('options_go_loot_health_toggle');

    $xp_abbr = get_option( "options_go_loot_xp_abbreviation" );
    $gold_abbr = get_option( "options_go_loot_gold_abbreviation" );
    $health_abbr = get_option( "options_go_loot_health_abbreviation" );

    $tasks_name = get_option( "options_go_tasks_name_plural" );



    echo "<div id='go_task_list' class='go_datatables'> <div class='table-responsive'><table id='go_tasks_datatable' class='pretty display'>
                   <thead>
						<tr>";
    if ($is_admin){
        echo "<th></th><th class='header go_tasks_reset_multiple'  style='color: red; text-align: center;'><a href='#'><i class='fa fa-times-circle' aria-hidden='true'></i></a></th>
    <th class='header go_tasks_reset' ><a href='#'>Reset</a></th>";
    }

    echo"
							
							<th class='header go_tasks_timestamps' ><a href=\"#\">Time</a></th>
							<th class='header' ><a href=\"#\">Title</a></th>


							<th class='header' ><a href=\"#\">Status</a></th>
							<th class='header' ><a href=\"#\">Bonus</a></th>
							<th class='header' ><a href=\"#\">Activity</a></th>";

        if ($xp_toggle){
            ?>
            <th class='header' id='go_stats_mods'><a href=\"#\"><?php echo "$xp_abbr"; ?></a></th>
            <?php
        }
        if ($gold_toggle){
            ?>
            <th class='header' id='go_stats_mods'><a href=\"#\"><?php echo "$gold_abbr"; ?></a></th>
            <?php
        }
        if ($health_toggle){
            ?>
            <th class='header' id='go_stats_mods'><a href=\"#\"><?php echo "$health_abbr"; ?></a></th>
            <?php
        }


    echo "
            <th class='header' ><a href='#'>Other</a></th>
						</tr>
						</thead>
			    <tbody>
	";
    foreach ( $tasks as $task ) {
        unset($next_stage);
        $post_id = $task->post_id;
        $custom_fields = get_post_custom( $post_id );
        $post_name = get_the_title($post_id);
        $post_link = get_post_permalink($post_id);
        $status = $task->status;
        $total_stages = (isset($custom_fields['go_stages'][0]) ?  $custom_fields['go_stages'][0] : null);


        $bonus_switch = (isset($custom_fields['bonus_switch'][0]) ?  $custom_fields['bonus_switch'][0] : null);
        $bonus_status = null;
        $total_bonus_stages = null;
        if ($bonus_switch) {
            $bonus_status = $task->bonus_status;
            $total_bonus_stages = (isset($custom_fields['go_bonus_limit'][0]) ? $custom_fields['go_bonus_limit'][0] : null);
        }
        $xp = $task->xp;
        $gold = $task->gold;
        $health = $task->health;
        //$start_time = $task->start_time;
        $last_time = $task->last_time;
        $time  = date("m/d/y g:i A", strtotime($last_time));

        $badge_ids = $task->badges;
        $group_ids = $task->groups;

        $badges_names = array();

        $badges_toggle = get_option('options_go_badges_toggle');
        if ($badges_toggle) {
            $badge_ids = unserialize($badge_ids);
            $badges_name_sing = get_option('options_go_badges_name_singular');

            if (!empty($badge_ids)) {
                $badges_names[] = "<b>" . $badges_name_sing . ":</b>";
                foreach ($badge_ids as $badge_id) {
                    $term = get_term($badge_id, "go_badges");
                    if (!empty($term)) {
                        $badge_name = $term->name;
                        $badges_names[] = $badge_name;
                    }
                }
            }
        }


        $group_ids = unserialize($group_ids);
        if (!empty($group_ids)){
            if (!empty($badge_ids)) {
                $badges_names[] = "<br>";
            }
            $badges_names[] = "<b>Group:</b>";
            foreach ($group_ids as $group_id) {
                $term = get_term($group_id, "user_go_groups");
                if (!empty($term)) {
                    $group_name = $term->name;
                    $badges_names[] = $group_name;
                }
            }
        }
        $badges_names = implode("<br>" , $badges_names);





        $go_actions_table_name = "{$wpdb->prefix}go_actions";
        $actions = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT *
			FROM {$go_actions_table_name}
			WHERE source_id = %d and uid = %d
			ORDER BY id DESC",
                $post_id,
                $user_id
            )
        );

        $next_bonus_stage = null;
        $i = 0;
        $links = array();
        $bonus_needs_close = false;
        $bonus_links = array();
        foreach ($actions as $action){
            $i++;
            $check_type = $action->check_type;
            $result = $action->result;
            $action_time = $action->TIMESTAMP;
            $action_time = date("m/d/y g:i A", strtotime($action_time));
            if ($action->action_type == 'task'){


                $loop_bonus_status = $action->bonus_status; //get the bonus status if it exists
                $stage = $action->stage ; //get the stage

                if ($loop_bonus_status > 0){//it is a bonus stage
                    $last_bonus = (isset($last_bonus) ?  false : true);//if this is the first encounter set to true, after that false
                    if($last_bonus){

                        $links[] = '<a href="#" data-featherlight="#bonus_lightbox_' . $post_id. '"><i class="fa fa-star" aria-hidden="true"></i></a>';
                        echo "<div style='display:none;'><div id=\"bonus_lightbox_" . $post_id. "\" class='go_featherlight_content' >";
                        //echo " <a class='tooltip bonus_layer' target='_blank'><span class=\"dashicons dashicons-admin-site\"></span><span class=\"tooltiptext\">";
                        $bonus_needs_close = true;
                    }
                    if ($last_bonus || ($next_bonus_stage > 0 && $loop_bonus_status == $next_bonus_stage )){
                        //$links[] = go_result_link($check_type, $result, $action_stage, $action_time);
                        $next_bonus_stage = $loop_bonus_status -1;
                        $bonus_links[]= go_bonus_result_link($check_type, $result, $loop_bonus_status, $action_time, true);
                    }
                }
                else if ($bonus_needs_close ){
                    $bonus_links = array_reverse( $bonus_links );
                    $bonus_links  = implode("<br>", $bonus_links);
                    echo $bonus_links;
                    echo "</div></div>";
                    //echo "</span></a>";
                    //$bonus_links = ob_get_contents();
                    //ob_end_clean();
                    //$links[] = $bonus_links;
                    $bonus_needs_close = false;
                }
                else if ($next_bonus_stage <= 0 || $next_bonus_stage == null) {//if it's not a bonus and it's not the last one completed
                    $next_stage = (isset($next_stage) ?  $next_stage : $stage );
                    if ($next_stage > 0 && $stage == $next_stage){
                        $action_stage = $action->stage;
                        $links[] = go_result_link($check_type, $result, $action_stage, $action_time, false);
                        $next_stage = $stage - 1;
                    }
                }

            }
        }
        $links = array_reverse($links);
        $link_count = count($links);
        $links = $comma_separated = implode(" ", $links);


        $status_order = $status/$total_stages;
        if ($status == -1){
            $status_print = "reset";
        }else{
            $status_print = $status . " / " . $total_stages;
        }




        if (!empty($total_bonus_stages)){
            $bonus_status_order = $bonus_status/$total_bonus_stages;
            $bonus_str = $bonus_status . " / " . $total_bonus_stages;
        }else{
            $bonus_status_order = 0;
            $bonus_str = null;
        }
        echo " <tr id='postID_{$post_id}'>";

	    if ($is_admin){
            echo " <td></td><td style='text-align: center;'><input class='go_checkbox' type='checkbox' name='go_selected' value='{$post_id}'/></td><td id='{$post_id}' class='go_reset_task' value='{$post_id}'  style='color: red; text-align: center;'><a href='#'><i class='fa fa-times-circle' aria-hidden='true'></i></a></td>";
	    }
	    echo"
			        
                        <td data-order='{$last_time}'>{$time}</td>
					    <td><a href='{$post_link}' >{$post_name}<a></td>

					    <td data-order='{$status_order}'>$status_print</td>
					    <td data-order='{$bonus_status_order}'>$bonus_str</td>
					    <td data-order='{$link_count}'>{$links} <a href='javascript:;' class='go_stats_body_activity_single_task' data-postID='{$post_id}' onclick='go_stats_single_task_activity_list({$post_id});'> ALL</a></td>
        ";
        if ($xp_toggle){
            echo"<td>{$xp}</td>";
        }
        if ($gold_toggle){
            echo"<td>{$gold}</td>";
        }
        if ($health_toggle){
            echo"<td>{$health}</td>";
        }
        echo "
        <td>{$badges_names}</td>
        </tr>
					";


    }
    echo "</tbody>
				</table></div></div>";

    die();
}

/**
* @param $post_id
*/
function go_stats_single_task_activity_list($post_id) {
    global $wpdb;
    $go_task_table_name = "{$wpdb->prefix}go_actions";
    if ( ! empty( $_POST['user_id'] ) ) {
        $user_id = (int) $_POST['user_id'];
    }
    check_ajax_referer( 'go_stats_single_task_activity_list' );
    $post_id = (int) $_POST['postID'];

    $actions = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * 
			FROM {$go_task_table_name} 
			WHERE uid = %d and source_id = %d",
            $user_id,
            $post_id
        )
    );
    $post_title = get_the_title($post_id);
    echo "<div id='go_task_list_single' class='go_datatables'><h3>$post_title</h3>

            <table id='go_single_task_datatable' class='pretty display'>
                   <thead>
						<tr>
						
							<th class='header' id='go_stats_time'><a href=\"#\">Time</a></th>
							<th class='header' id='go_stats_action'><a href=\"#\">Action</a></th>
							<th class='header' id='go_stats_post_name'><a href=\"#\">Stage</a></th>
							<th class='header' id='go_stats_mods'><a href=\"#\">Modifiers</a></th>
							
							<th class='header' id='go_stats_mods'><a href=\"#\">XP</a></th>
							<th class='header' id='go_stats_mods'><a href=\"#\">G</a></th>
							<th class='header' id='go_stats_mods'><a href=\"#\">H</a></th>
							<th class='header' id='go_stats_mods'><a href=\"#\">AP</a></th>
							<th class='header' id='go_stats_mods'><a href=\"#\">Total<br> XP</a></th>
							<th class='header' id='go_stats_mods'><a href=\"#\">Total<br> G</a></th>
							<th class='header' id='go_stats_mods'><a href=\"#\">Total<br> H</a></th>
							<th class='header' id='go_stats_mods'><a href=\"#\">Total<br> AP</a></th>
						</tr>
						</thead>
			    <tbody>
						";
    foreach ( $actions as $action ) {
        $action_type = $action->action_type;
        $source_id = $action->source_id;
        $TIMESTAMP = $action->TIMESTAMP;
        $time  = date("m/d/y g:i A", strtotime($TIMESTAMP));
        $stage = $action->stage;
        $bonus_status = $action->bonus_status;
        $result = $action->result;
        $quiz_mod = $action->quiz_mod;
        $late_mod = $action->late_mod;
        $timer_mod = $action->timer_mod;
        $health_mod = $action->global_mod;
        $xp = $action->xp;
        $gold = $action->gold;
        $health = $action->health;
        $xp_total = $action->xp_total;
        $gold_total = $action->gold_total;
        $health_total = $action->health_total;

        $post_title = get_the_title($source_id);


        if ($action_type == 'admin'){
            $type = "Admin";
        }
        if ($action_type == 'reset'){
            $type = "Reset";
        }

        if ($action_type == 'store'){
            $store_qnty = $stage;
            $type = strtoupper( get_option( 'options_go_store_name' ) );
            $post_title = "Qnt: " . $store_qnty . " of " . $post_title ;
        }

        if ($action_type == 'task'){
            $type = strtoupper( get_option( 'options_go_tasks_name_singular' ) );
            if ($bonus_status == 0) {
                //$type = strtoupper( get_option( 'options_go_tasks_name_singular' ) );
                $type = 'Continue';
                $post_title = " Stage: " . $stage;
            }
        }

        if ($action_type == 'undo_task'){
            $type = strtoupper( get_option( 'options_go_tasks_name_singular' ) );
            if ($bonus_status == 0) {
                $type = "Undo";
                $post_title = " Stage: " . $stage;
            }
        }
        if ($result == 'undo_bonus'){
            $type = "Undo Bonus";
            $post_title = $post_title . " Bonus: " . $bonus_status ;
        }

        $quiz_mod_int = intval($quiz_mod);
        if (!empty($quiz_mod_int)){
            $quiz_mod = "<i class=\"fa fa-check-circle-o\" aria-hidden=\"true\"></i> ". $late_mod;
        }
        else{
            $quiz_mod = null;
        }

        $late_mod_int = intval($late_mod);
        if (!empty($late_mod_int)){
            $late_mod = "<i class=\"fa fa-calendar\" aria-hidden=\"true\"></i> ". $late_mod;
        }
        else{
            $late_mod = null;
        }

        $timer_mod_int = intval($timer_mod);
        if (!empty($timer_mod_int)){
            $timer_mod = "<i class=\"fa fa-hourglass\" aria-hidden=\"true\"></i> ". $timer_mod;
        }
        else{
            $timer_mod = null;
        }

        $health_mod_int = intval($health_mod);
        if (!empty($health_mod_int)){
            $health_abbr = get_option( "options_go_loot_health_abbreviation" );
            $health_mod_str = $health_abbr . ": ". $health_mod;
        }
        else{
            $health_mod_str = null;
        }

        echo " 			
			        <tr id='postID_{$source_id}'>
			            <td data-order='{$TIMESTAMP}'>{$time}</td>
					    <td>{$type} </td>
					    <td>{$post_title} </td>
					    <td>{$health_mod_str}   {$timer_mod}   {$late_mod}   {$quiz_mod}</td>
					    
					    <td>{$xp}</td>
					    <td>{$gold}</td>
					    <td>{$health}</td>
					    <td>{$xp_total}</td>
					    <td>{$gold_total}</td>
					    <td>{$health_total}</td>
					</tr>
					";


    }
    echo "</tbody>
				</table></div>";

    die();
}

//store items
/**
 *
 */
function go_stats_item_list() {

    global $wpdb;
    $go_task_table_name = "{$wpdb->prefix}go_actions";
    if ( ! empty( $_POST['user_id'] ) ) {
        $user_id = (int) $_POST['user_id'];
    }
    check_ajax_referer( 'go_stats_item_list_' );

    $actions = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * 
			FROM {$go_task_table_name} 
			WHERE uid = %d and action_type = %s",
            $user_id,
            'store'
        )
    );
    $store_name = get_option('options_go_store_name');
    $xp_toggle = get_option('options_go_loot_xp_toggle');
    $gold_toggle = get_option('options_go_loot_gold_toggle');
    $health_toggle = get_option('options_go_loot_health_toggle');
    $xp_name = get_option('options_go_loot_xp_name');
    $gold_name = get_option('options_go_loot_gold_name');
    $health_name = get_option('options_go_loot_health_name');
    $xp_abbr = get_option( "options_go_loot_xp_abbreviation" );
    $gold_abbr = get_option( "options_go_loot_gold_abbreviation" );
    $health_abbr = get_option( "options_go_loot_health_abbreviation" );
    $badges_name = ucfirst(get_option('options_go_badges_name_plural'));
    $badges_name_sing = ucfirst(get_option('options_go_badges_name_singular'));



    ?>
    <div id='go_item_list' class='go_datatables'>
    <table id='go_store_datatable' class='pretty display'>
    <thead>
    <tr>

        <th class='header' id='go_stats_time'><a href='#'>Time</a></th>
        <th class='header' id='go_stats_action'><a href='#'>Item</a></th>
        <th class='header' id='go_stats_action'><a href='#'>QTY</a></th>
        <?php
        if ($xp_toggle){
            ?>
            <th class='header' id='go_stats_mods'><a href='#'><?php echo "$xp_abbr"; ?></a></th>
            <?php
        }
        if ($gold_toggle){
            ?>
            <th class='header' id='go_stats_mods'><a href='#'><?php echo "$gold_abbr"; ?></a></th>
            <?php
        }
        if ($health_toggle){
            ?>
            <th class='header' id='go_stats_mods'><a href='#'><?php echo "$health_abbr"; ?></a></th>
            <?php
        }
       
        ?>
        <th class='header' id='go_stats_mods'><a href='#'><?php echo "Other"; ?></a></th>

    </tr>
    </thead>
    <tbody>

    <?php

    foreach ( $actions as $action ) {
        $action_type = $action->action_type;
        $source_id = $action->source_id;
        $TIMESTAMP = $action->TIMESTAMP;
        $time  = date("m/d/y g:i A", strtotime($TIMESTAMP));
        $stage = $action->stage;
        $bonus_status = $action->bonus_status;
        $result = $action->result;
        $quiz_mod = $action->quiz_mod;
        $late_mod = $action->late_mod;
        $timer_mod = $action->timer_mod;
        $health_mod = $action->global_mod;
        $xp = $action->xp;
        $gold = $action->gold;
        $health = $action->health;
        $xp_total = $action->xp_total;
        $gold_total = $action->gold_total;
        $health_total = $action->health_total;
        $badge_ids = $action->badges;
        $group_ids = $action->groups;

        $badges_names = array();

        $badges_toggle = get_option('options_go_badges_toggle');
        if ($badges_toggle) {
            $badge_ids = unserialize($badge_ids);

            if (!empty($badge_ids)) {
                $badges_names[] = "<b>" . $badges_name_sing . ":</b>";
                foreach ($badge_ids as $badge_id) {
                    $term = get_term($badge_id, "go_badges");
                    $badge_name = $term->name;
                    $badges_names[] = $badge_name;
                }
            }
        }


        $group_ids = unserialize($group_ids);
        if (!empty($group_ids)){
            if (!empty($badge_ids)) {
                $badges_names[] = "<br>";
            }
            $badges_names[] = "<b>Group:</b>";
            foreach ($group_ids as $group_id) {
                $term = get_term($group_id, "user_go_groups");
                $group_name = $term->name;
                $badges_names[] = $group_name;
            }
        }
        $badges_names = implode("<br>" , $badges_names);

        $post_title = get_the_title($source_id);





        echo " 			
			        <tr id='postID_{$source_id}'>
			            <td data-order='{$TIMESTAMP}'>{$time}</td>
					    <td>{$post_title} </td>
                        <td>{$stage} </td>";

        if ($xp_toggle){
            echo"<td>{$xp}</td>";
        }
        if ($gold_toggle){
            echo"<td>{$gold}</td>";
        }
        if ($health_toggle){
            echo"<td>{$health}</td>";
        }

        echo"<td>{$badges_names}</td>
        </tr>";
    }
    echo "</tbody>
				</table></div>";

    die();
}

//History table
/**
 *
 */
function go_stats_activity_list() {
    check_ajax_referer( 'go_stats_activity_list_' );

    $xp_abbr = get_option( "options_go_loot_xp_abbreviation" );
    $gold_abbr = get_option( "options_go_loot_gold_abbreviation" );
    $health_abbr = get_option( "options_go_loot_health_abbreviation" );

    $xp_toggle = get_option('options_go_loot_xp_toggle');
    $gold_toggle = get_option('options_go_loot_gold_toggle');
    $health_toggle = get_option('options_go_loot_health_toggle');

    echo "<div id='go_activity_list' class='go_datatables'><table id='go_activity_datatable' class='pretty display'>
                   <thead>
						<tr>
						
							<th class='header'><a href=\"#\">Time</a></th>
							<th class='header'><a href=\"#\">Type</a></th>
							
							<th class='header'><a href=\"#\">Item</a></th>
							<th class='header'><a href=\"#\">Action</a></th>
							<th class='header'><a href=\"#\">Modifiers</a></th>";


        if ($xp_toggle){
            ?>
            <th class='header'><a href=\"#\"><?php echo "$xp_abbr"; ?></a></th>
            <?php
        }
        if ($gold_toggle){
            ?>
            <th class='header'><a href=\"#\"><?php echo "$gold_abbr"; ?></a></th>
            <?php
        }
        if ($health_toggle){
            ?>
            <th class='header'><a href=\"#\"><?php echo "$health_abbr"; ?></a></th>
            <?php
        }
        if ($xp_toggle){
            ?>
            <th class='header'><a href=\"#\">Total<br><?php echo "$xp_abbr"; ?></a></th>
            <?php
        }
        if ($gold_toggle){
            ?>
            <th class='header'><a href=\"#\">Total<br><?php echo "$gold_abbr"; ?></a></th>
            <?php
        }
        if ($health_toggle){
            ?>
            <th class='header'><a href=\"#\">Total<br><?php echo "$health_abbr"; ?></a></th>
            <?php
        }


	echo "<th class='header'><a href='#'>Other</a></th>
					</tr>
						</thead>

				</table></div>";

    die();
}


/**
 * go_activity_dataloader_ajax
 * Called for Server Side Processing from the JS
 */
function go_activity_dataloader_ajax(){

    global $wpdb;
    $go_action_table_name = "{$wpdb->prefix}go_actions";

    $aColumns = array( 'id', 'uid', 'action_type', 'source_id', 'TIMESTAMP' ,'stage', 'bonus_status', 'check_type', 'result', 'quiz_mod', 'late_mod', 'timer_mod', 'global_mod', 'xp', 'gold', 'health', 'xp_total', 'gold_total', 'health_total', 'badges', 'groups' );

    $sIndexColumn = "id";
    $sTable = $go_action_table_name;

    $sLimit = '';

    if ( isset( $_GET['start'] ) && $_GET['length'] != '-1' )
    {
        $sLimit = "LIMIT ".intval( $_GET['start'] ).", ".
            intval( $_GET['length'] );
    }

    $sOrder = "ORDER BY TIMESTAMP desc"; //always in reverse order


    $sWhere = "";
    $search_val = $_GET['search']['value'];

    if ( isset($search_val) && $search_val != "" )
    {
        $sWhere = "WHERE (";
        for ( $i=0 ; $i<count($aColumns) ; $i++ )
        {
            $sWhere .= "`".$aColumns[$i]."` LIKE '%".esc_sql( $search_val )."%' OR ";
        }
        $sWhere = substr_replace( $sWhere, "", -3 );


        $posts_table_name = "{$wpdb->prefix}posts";

        $tWhere = " WHERE post_title LIKE '%".$search_val."%'";
        $task_id_query = "
                SELECT ID
          FROM $posts_table_name
          $tWhere
        
        ";
        $task_ids = $wpdb->get_results($task_id_query, ARRAY_A);
        if(is_array($task_ids)){
            $sWhere .= "OR ";

            foreach ($task_ids as $task_id){
                $sWhere .= "`source_id` LIKE '%".esc_sql( $task_id['ID'] )."%' OR ";
            }
            $sWhere = substr_replace( $sWhere, "", -3 );
        }
        $sWhere .= ')';

    }


     //add the filter by UID
    $user_id = $_GET['user_id'];
    if($user_id != ''){
        if ( $sWhere == "" )
        {
            $sWhere = "WHERE ";
        }
        else
        {
            $sWhere .= " AND ";
        }
         $sWhere .= "uid = ".$user_id . " AND NOT action_type = 'admin_notification'";
    }

    $totalWhere = " WHERE uid = ".$user_id . " AND NOT action_type = 'admin_notification'";


    $sQuery = "
  SELECT SQL_CALC_FOUND_ROWS `".str_replace(" , ", " ", implode("`, `", $aColumns))."`
  FROM   $sTable
  $sWhere
  $sOrder
  $sLimit
  ";
    $rResult = $wpdb->get_results($sQuery, ARRAY_A);

    $sQuery = "
  SELECT FOUND_ROWS()
 ";
    $rResultFilterTotal = $wpdb->get_results($sQuery, ARRAY_N);
    $iFilteredTotal = $rResultFilterTotal [0];

    $sQuery = "
  SELECT COUNT(`".$sIndexColumn."`)
  FROM   $sTable
  $totalWhere
 ";
    $rResultTotal = $wpdb->get_results($sQuery, ARRAY_N);
    $iTotal = $rResultTotal [0];

    $output = array(
        "iTotalRecords" => $iTotal,
        "iTotalDisplayRecords" => $iFilteredTotal,
        "aaData" => array()
    );

    foreach($rResult as $action){//output a row for each task
        $row = array();
        ///////////
        ///
        $action_type = $action['action_type'];
        $source_id = $action['source_id'];
        $TIMESTAMP = $action['TIMESTAMP'];
        //$time  = date("m/d/y g:i A", strtotime($TIMESTAMP));
        $time = $TIMESTAMP;
        $stage = $action['stage'];
        $bonus_status = $action['bonus_status'];
        $result = $action['result'];
        $quiz_mod = $action['quiz_mod'];
        $late_mod = $action['late_mod'];
        $timer_mod = $action['timer_mod'];
        $health_mod = $action['global_mod'];
        $xp = $action['xp'];
        $gold = $action['gold'];
        $health = $action['health'];
        $xp_total = $action['xp_total'];
        $gold_total = $action['gold_total'];
        $health_total = $action['health_total'];
        $badge_ids = $action['badges'];
        $group_ids = $action['groups'];


        $badges_names = array();

        $badges_toggle = get_option('options_go_badges_toggle');
        if ($badges_toggle) {
            $badge_ids = unserialize($badge_ids);
            $badges_name_sing = get_option('options_go_badges_name_singular');

            if (!empty($badge_ids)) {
                $badges_names[] = "<b>" . $badges_name_sing . ":</b>";
                foreach ($badge_ids as $badge_id) {
                    $term = get_term($badge_id, "go_badges");
                    $badge_name = $term->name;
                    $badges_names[] = $badge_name;
                }
            }
        }


        $group_names = array();
        $group_ids = unserialize($group_ids);
        if (!empty($group_ids)){
            if (!empty($badge_ids)) {
                $badges_names[] = "<br>";
            }
            $group_names[] = "<b>Group:</b>";
            foreach ($group_ids as $group_id) {
                $term = get_term($group_id, "user_go_groups");
                $group_name = $term->name;
                $group_names[] = $group_name;
            }
        }
        $badges_names = implode("<br>" , $badges_names);
        $group_names = implode("<br>" , $group_names);

        $post_title = get_the_title($source_id);


        if ($action_type == 'message' || $action_type == 'reset'){
            $type = ucfirst($action_type);
            $result_array = unserialize($result);
            $title = $result_array[0];
            $message = $result_array[1];
            $message = $title . ": <br>" . $message;
            $action = "<span class='tooltip' ><span class='tooltiptext'>{$message}</span>See Message</span>";

             if (!empty($badge_ids)) {
                $badge_dir = $result_array[2];

                //$badges_name = get_option('options_go_badges_name_plural');

                if ($badge_dir == "badges+"){
                    $badge_dir = "<b>Add </b> ";
                }else if ($badge_dir == "badges-"){
                    $badge_dir = "<b>Remove </b> ";
                }else{
                    $badge_dir = "";
                }
            }
            else{
                $badge_dir = "";
            }

            if (!empty($group_ids)){
                $groups_dir = $result_array[3];
                if ($groups_dir == "groups+"){
                    $group_dir = "<b>Add </b> ";
                }else if ($groups_dir == "groups-") {
                    $group_dir = "<b>Remove </b> ";
                }else{
                    $group_dir = "";
                }
            }else{
                $group_dir = "";
            }

        }else{
            $badge_dir = "";
            $group_dir = "";
        }

        $badges_names = $badge_dir . $badges_names . $group_dir . $group_names;

        if ($action_type == 'store'){
            $store_qnty = $stage;
            $type = ucfirst( get_option( 'options_go_store_name' ) );
            $action = "Qnt: " . $store_qnty ;
        }

        if ($action_type == 'task'){
            $type = ucfirst( get_option( 'options_go_tasks_name_singular' ) );
            if ($bonus_status == 0) {
                //$type = strtoupper( get_option( 'options_go_tasks_name_singular' ) );
                //$type = "Continue";
                $action = "Stage: " . $stage;
            }
            else{
                //$type = strtoupper( get_option( 'options_go_tasks_name_singular' ) );
                // $type = "Continue";
                $action = "Bonus: " . $bonus_status;
            }
        }

        if ($action_type == 'undo_task'){
            $type = ucfirst( get_option( 'options_go_tasks_name_singular' ) );
            if ($bonus_status == 0) {
                //$type = strtoupper( get_option( 'options_go_tasks_name_singular' ) ) . " Undo";
                //$type = "Undo";
                $action = "Undo Stage: " . $stage;
            }
        }
        if ($result == 'undo_bonus'){
            //$type = strtoupper( get_option( 'options_go_tasks_name_singular' ) ) . " Undo Bonus";
            //$type = "Undo Bonus";
            $action = "Undo Bonus: " . $bonus_status ;
        }

        $quiz_mod_int = intval($quiz_mod);
        if (!empty($quiz_mod_int)){
            $quiz_mod = "<i class=\"fa fa-check-circle-o\" aria-hidden=\"true\"></i> ". $quiz_mod;
        }
        else{
            $quiz_mod = null;
        }

        $late_mod_int = intval($late_mod);
        if (!empty($late_mod_int)){
            $late_mod = "<i class=\"fa fa-calendar\" aria-hidden=\"true\"></i> ". $late_mod;
        }
        else{
            $late_mod = null;
        }

        $timer_mod_int = intval($timer_mod);
        if (!empty($timer_mod_int)){
            $timer_mod = "<i class=\"fa fa-hourglass\" aria-hidden=\"true\"></i> ". $timer_mod;
        }
        else{
            $timer_mod = null;
        }

        $health_mod_int = $health_mod;
        if (!empty($health_mod_int)){
            $health_abbr = get_option( "options_go_loot_health_abbreviation" );
            $health_mod_str = $health_abbr . ": ". $health_mod;
        }
        else{
            $health_mod_str = null;
        }
        //$unix_time = strtotime($TIMESTAMP);
        $row[] = "{$TIMESTAMP}";
        $row[] = "{$type}";
        $row[] = "{$post_title}";
        $row[] = "{$action}";
        $row[] = "{$health_mod_str}   {$timer_mod}   {$late_mod}   {$quiz_mod}";

        $xp_toggle = get_option('options_go_loot_xp_toggle');
        $gold_toggle = get_option('options_go_loot_gold_toggle');
        $health_toggle = get_option('options_go_loot_health_toggle');

        if ($xp_toggle){
            $row[] = "{$xp}";
        }
        if ($gold_toggle){
            $row[] = "{$gold}";
        }
        if ($health_toggle){
            $row[] = "{$health}";
        }
        if ($xp_toggle){
            $row[] = "{$xp_total}";
        }
        if ($gold_toggle){
            $row[] = "{$gold_total}";
        }
        if ($health_toggle){
            $row[] = "{$health_total}";
        }
        $row[] = "{$badges_names}";
        $output['aaData'][] = $row;
    }


    echo json_encode( $output );
    die();
}

/**
 *
 */
function go_stats_messages() {
    check_ajax_referer( 'go_stats_messages' );

    $xp_abbr = get_option( "options_go_loot_xp_abbreviation" );
    $gold_abbr = get_option( "options_go_loot_gold_abbreviation" );
    $health_abbr = get_option( "options_go_loot_health_abbreviation" );

    $xp_toggle = get_option('options_go_loot_xp_toggle');
    $gold_toggle = get_option('options_go_loot_gold_toggle');
    $health_toggle = get_option('options_go_loot_health_toggle');

    echo "<div id='go_messages' class='go_datatables'><table id='go_messages_datatable' class='pretty display'>
                   <thead>
						<tr>
						
							<th class='header'><a href=\"#\">Time</a></th>
							<th class='header'><a href=\"#\">Title</a></th>					
							<th class='header'><a href=\"#\">Message</a></th>
							<th class='header'><a href=\"#\">Modifier</a></th>";


        if ($xp_toggle){
            ?>
            <th class='header'><a href=\"#\"><?php echo "$xp_abbr"; ?></a></th>
            <?php
        }
        if ($gold_toggle){
            ?>
            <th class='header'><a href=\"#\"><?php echo "$gold_abbr"; ?></a></th>
            <?php
        }
        if ($health_toggle){
            ?>
            <th class='header'><a href=\"#\"><?php echo "$health_abbr"; ?></a></th>
            <?php
        }



	echo "<th class='header'><a href='#'>Other</a></th>
					</tr>
						</thead>

				</table></div>";

    die();
}

/**
 * go_messages_dataloader_ajax
 * Called for Server Side Processing from the JS
 */
function go_messages_dataloader_ajax(){

    global $wpdb;
    $go_action_table_name = "{$wpdb->prefix}go_actions";

    $aColumns = array( 'id', 'uid', 'action_type', 'TIMESTAMP' , 'result', 'global_mod', 'xp', 'gold', 'health', 'badges', 'groups' );

    $sIndexColumn = "id";
    $sTable = $go_action_table_name;

    $sLimit = '';

    if ( isset( $_GET['start'] ) && $_GET['length'] != '-1' )
    {
        $sLimit = "LIMIT ".intval( $_GET['start'] ).", ".
            intval( $_GET['length'] );
    }

    $sOrder = "ORDER BY TIMESTAMP desc"; //always in reverse order


    $search_val = $_GET['search']['value'];

    $user_id = $_GET['user_id'];

    $sWhere = "WHERE uid = ".$user_id . " AND (action_type = 'message' OR action_type = 'reset') ";

    if ( isset($search_val) && $search_val != "" )
    {

        $sWhere .= " AND (";
        for ( $i=0 ; $i<count($aColumns) ; $i++ )
        {
            $sWhere .= "`".$aColumns[$i]."` LIKE '%".esc_sql( $search_val )."%' OR ";
        }
        $sWhere = substr_replace( $sWhere, "", -3 );//removes the last OR


        $sWhere .= ')';

    }

    $totalWhere = " WHERE uid = ".$user_id . " AND (action_type = 'message' OR action_type = 'reset') ";

    $sQuery = "
      SELECT SQL_CALC_FOUND_ROWS `".str_replace(" , ", " ", implode("`, `", $aColumns))."`
      FROM   $sTable
      $sWhere
      $sOrder
      $sLimit
      ";

    $rResult = $wpdb->get_results($sQuery, ARRAY_A);

    $sQuery = "SELECT FOUND_ROWS()";

    $rResultFilterTotal = $wpdb->get_results($sQuery, ARRAY_N);

    $iFilteredTotal = $rResultFilterTotal [0];

    $sQuery = "
      SELECT COUNT(`".$sIndexColumn."`)
      FROM   $sTable
      $totalWhere
     ";

    $rResultTotal = $wpdb->get_results($sQuery, ARRAY_N);

    $iTotal = $rResultTotal [0];

    $output = array(
        "iTotalRecords" => $iTotal,
        "iTotalDisplayRecords" => $iFilteredTotal,
        "aaData" => array()
    );

    foreach($rResult as $action){//output a row for each task
        $row = array();
        ///////////
        ///
        $action_type = $action['action_type'];
        $TIMESTAMP = $action['TIMESTAMP'];
        $result = $action['result'];
        $health_mod = $action['global_mod'];
        $xp = $action['xp'];
        $gold = $action['gold'];
        $health = $action['health'];
        $badge_ids = $action['badges'];
        $group_ids = $action['groups'];

        $badges_names = array();
        $badges_toggle = get_option('options_go_badges_toggle');

        if ($badges_toggle) {
            $badge_ids = unserialize($badge_ids);
            $badges_name_sing = get_option('options_go_badges_name_singular');

            if (!empty($badge_ids)) {
                $badges_names[] = "<b>" . $badges_name_sing . ":</b>";
                foreach ($badge_ids as $badge_id) {
                    $term = get_term($badge_id, "go_badges");
                    $badge_name = $term->name;
                    $badges_names[] = $badge_name;
                }
            }
        }


        $group_names = array();
        $group_ids = unserialize($group_ids);
        if (!empty($group_ids)){
            if (!empty($badge_ids)) {
                $badges_names[] = "<br>";
            }
            $group_names[] = "<b>Group:</b>";
            foreach ($group_ids as $group_id) {
                $term = get_term($group_id, "user_go_groups");
                $group_name = $term->name;
                $group_names[] = $group_name;
            }
        }
        $badges_names = implode("<br>" , $badges_names);
        $group_names = implode("<br>" , $group_names);


            $type = ucfirst($action_type);
            $result_array = unserialize($result);
            $title = $result_array[0];
            $message = $result_array[1];
            //$message = $title . ": <br>" . $message;
            //$action = "<span class='tooltip' ><span class='tooltiptext'>{$message}</span>See Message</span>";

             if (!empty($badge_ids)) {
                $badge_dir = $result_array[2];

                //$badges_name = get_option('options_go_badges_name_plural');

                if ($badge_dir == "badges+"){
                    $badge_dir = "<b>Add </b> ";
                }else if ($badge_dir == "badges-"){
                    $badge_dir = "<b>Remove </b> ";
                }else{
                    $badge_dir = "";
                }
            }
            else{
                $badge_dir = "";
            }

            if (!empty($group_ids)){
                $groups_dir = $result_array[3];
                if ($groups_dir == "groups+"){
                    $group_dir = "<b>Add </b> ";
                }else if ($groups_dir == "groups-") {
                    $group_dir = "<b>Remove </b> ";
                }else{
                    $group_dir = "";
                }
            }else{
                $group_dir = "";
            }

        $badges_names = $badge_dir . $badges_names . $group_dir . $group_names;



        $health_mod_int = $health_mod;
        if (!empty($health_mod_int)){
            $health_abbr = get_option( "options_go_loot_health_abbreviation" );
            $health_mod_str = $health_abbr . ": ". $health_mod;
        }
        else{
            $health_mod_str = null;
        }
        //$unix_time = strtotime($TIMESTAMP);
        $row[] = "{$TIMESTAMP}";
        $row[] = "{$title}";
        $row[] = "{$message}";
        $row[] = "{$health_mod_str} ";

        $xp_toggle = get_option('options_go_loot_xp_toggle');
        $gold_toggle = get_option('options_go_loot_gold_toggle');
        $health_toggle = get_option('options_go_loot_health_toggle');

        if ($xp_toggle){
            $row[] = "{$xp}";
        }
        if ($gold_toggle){
            $row[] = "{$gold}";
        }
        if ($health_toggle){
            $row[] = "{$health}";
        }
        $row[] = "{$badges_names}";
        $output['aaData'][] = $row;
    }


    echo json_encode( $output );
    die();
}

/**
* @param $user_id
*/
function go_stats_badges_list($user_id) {
    global $wpdb;
    $go_loot_table_name = "{$wpdb->prefix}go_loot";
    if ( ! empty( $_POST['user_id'] ) ) {
        $user_id = (int) $_POST['user_id'];
    }
    check_ajax_referer( 'go_stats_badges_list_' );
    $badges_array = $wpdb->get_var ("SELECT badges FROM {$go_loot_table_name} WHERE uid = {$user_id}");
    $badges_array = unserialize($badges_array);
    if (empty($badges_array)){
        $badges_array = array();
    }
    $args = array(
        'hide_empty' => false,
        'orderby' => 'name',
        'order' => 'ASC',
        'parent' => '0'
    );

    /* Get all task chains with no parents--these are the badge categories.  */
    $taxonomy = 'go_badges';
    //$badges_name = get_option('options_go_badges_name_plural');

    $rows = get_terms( $taxonomy, $args);//the rows
    echo"<div id='go_badges_list' class='go_datatables'> ";


    /* For each Store Category with no parent, get all the children. */
    $chainParentNum = 0;
    echo '<div id="go_stats_badges">';
    //for each row
    foreach ( $rows as $row ) {
        $chainParentNum++;
        $row_id = $row->term_id;//id of the row
        //$custom_fields = get_term_meta( $row_id );
        /*
        $cat_hidden = (isset($custom_fields['go_hide_store_cat'][0]) ?  $custom_fields['go_hide_store_cat'][0] : null);
        if( $cat_hidden == true){
            continue;
        }
        */
        $column_args=array(
            'hide_empty' => false,
            'orderby' => 'order',
            'order' => 'ASC',
            'parent' => $row_id,

        );

        $badges = get_terms($taxonomy,$column_args);

        if(empty($badges)){
                    continue;
        }

        echo 	"<div class='parent_cat'>
                        <div id='row_$chainParentNum' class='badges_row_container'>
						    <h3>$row->name</h3>
						</div>
					    <div class='badges_row'>
						";//row title and row container



        /*Loop for each chain.  Prints the chain name then looks up children (quests). */
        $badge_blocks = '';
        foreach ( $badges as $badge) {
            $badge_id = $badge->term_id;
            $badge_assigned = in_array($badge_id, $badges_array);
            if ($badge_assigned){
                $badge_class = 'go_badge_earned';
            }else{
                $badge_class = 'go_badge_needed';
            }
            $badge_img_id = get_term_meta( $badge_id, 'my_image' );
            $badge_description = term_description( $badge_id );
            /*
            $cat_hidden = (isset($custom_fields['go_hide_store_cat'][0]) ?  $custom_fields['go_hide_store_cat'][0] : null);
            if( $cat_hidden == true){
                continue;
            }
            */

            $badge_obj = get_term( $badge_id);
            $badge_name = $badge_obj->name;
            //$badge_img_id =(isset($custom_fields['my_image'][0]) ?  $custom_fields['my_image'][0] : null);
            if (isset($badge_img_id[0])){
                $badge_img = wp_get_attachment_image($badge_img_id[0], array( 100, 100 ));
            }else{
                $badge_img = null;
            }

            //$badge_attachment = wp_get_attachment_image( $badge_img_id, array( 100, 100 ) );
            //$img_post = get_post( $badge_id );
            if ( ! empty( $badge_obj ) ) {
                echo"<div class='go_badge_wrap'>
                        <div class='go_badge_container {$badge_class}'><figure class=go_badge title='{$badge_name}'>";

                        if (!empty($badge_description)){
                            echo "<span class='tooltip' ><span class='tooltiptext'>{$badge_description}</span>{$badge_img}</span>";
                        }else{
                            echo "$badge_img";
                        }
                echo "        
               <figcaption>{$badge_name}</figcaption>
                            </figure>
                        </div>
                       </div>";

            }
        }
        echo "</div></div>";
    }
    echo "</div></div>";
    die();

}
//add_shortcode('go_make_badges', 'go_make_badges');

/**
* @param $user_id
*/
function go_stats_groups_list($user_id) {
    global $wpdb;
    $go_loot_table_name = "{$wpdb->prefix}go_loot";
    if ( ! empty( $_POST['user_id'] ) ) {
        $user_id = (int) $_POST['user_id'];
    } else {
        $user_id = get_current_user_id();
    }
    check_ajax_referer( 'go_stats_groups_list_' );
    $badges_array = $wpdb->get_var ("SELECT groups FROM {$go_loot_table_name} WHERE uid = {$user_id}");
    $badges_array = unserialize($badges_array);
    $args = array(
        'hide_empty' => true,
        'orderby' => 'name',
        'order' => 'ASC',
        'parent' => '0'
    );

    /* Get all task chains with no parents--these are the sections of the store.  */
    $taxonomy = 'user_go_groups';


    $rows = get_terms( $taxonomy, $args);//the rows
    echo"<div id='go_groups_list' class='go_datatables'>";


    /* For each Store Category with no parent, get all the children.  These are the store rows.*/
    $chainParentNum = 0 ;
    echo '<div id="go_groups">';
    //for each row
    foreach ( $rows as $row ) {
        $chainParentNum++;
        $row_id = $row->term_id;//id of the row
        //$custom_fields = get_term_meta( $row_id );
        /*
        $cat_hidden = (isset($custom_fields['go_hide_store_cat'][0]) ?  $custom_fields['go_hide_store_cat'][0] : null);
        if( $cat_hidden == true){
            continue;
        }
        */

        $column_args=array(
            'hide_empty' => false,
            'orderby' => 'order',
            'order' => 'ASC',
            'parent' => $row_id,

        );

        $badges = get_terms($taxonomy,$column_args);

        if(empty($badges)){
            continue;
        }

        echo 	"<div class='parent_cat'>
                            <div id='row_$chainParentNum' class='groups_row_container'>
                                <h3>$row->name</h3>
                            </div>
                            <div class='groups_row'>
                            ";//row title and row container



        /*Loop for each chain.  Prints the chain name then looks up children (quests). */
        $badge_blocks = '';
        foreach ( $badges as $badge) {
            $badge_id = $badge->term_id;
            $badge_assigned = in_array($badge_id, $badges_array);
            if ($badge_assigned){
                $badge_class = 'go_group_earned';
            }else{
                $badge_class = 'go_group_needed';
            }
            $custom_fields = get_term_meta( $badge_id );
            /*
            $cat_hidden = (isset($custom_fields['go_hide_store_cat'][0]) ?  $custom_fields['go_hide_store_cat'][0] : null);
            if( $cat_hidden == true){
                continue;
            }
            */

            $badge_obj = get_term( $badge_id);
            $badge_name = $badge_obj->name;
            $badge_img_id =(isset($custom_fields['my_image'][0]) ?  $custom_fields['my_image'][0] : null);
            $badge_img = wp_get_attachment_image($badge_img_id, array( 100, 100 ));

            //$badge_attachment = wp_get_attachment_image( $badge_img_id, array( 100, 100 ) );
            //$img_post = get_post( $badge_id );
            if ( ! empty( $badge_obj ) ) {
                echo"<div class='go_group_wrap'>
                            <div class='go_group_container {$badge_class}'>
                                    <p>{$badge_name}</p>
                            </div>
                        </div>";

            }
        }
        echo "</div></div>";
    }
    echo "</div></div>";
    die();
}
//add_shortcode('go_make_groups', 'go_make_groups');

/**Leaderboard Stuff Below
 *
 */

/**
 *
 */
function go_stats_lite(){
    //$user_id = 0;
    if ( ! empty( $_POST['uid'] ) ) {
        $user_id = (int) $_POST['uid'];
        $current_user = get_userdata( $user_id );
    } else {
        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;
    }
    check_ajax_referer( 'go_stats_lite' );

    ?>
    <input type="hidden" id="go_stats_hidden_input" value="<?php echo $user_id; ?>"/>
    <?php
    $user_fullname = $current_user->first_name.' '.$current_user->last_name;
    $user_login =  $current_user->user_login;
    $user_display_name = $current_user->display_name;
    $user_website = $current_user->user_url;

    $use_local_avatars = get_option('options_go_avatars_local');
    $use_gravatar = get_option('options_go_avatars_gravatars');
    if ($use_local_avatars){
        $user_avatar_id = get_user_meta( $user_id, 'go_avatar', true );
        $user_avatar = wp_get_attachment_image($user_avatar_id);
    }
    if (empty($user_avatar) && $use_gravatar) {
        $user_avatar = get_avatar( $user_id, 150 );
    }

    //$user_focuses = go_display_user_focuses( $user_id );



    $current_points = go_return_points( $user_id );


    $go_option_ranks = get_option( 'go_ranks' );
    $points_array = $go_option_ranks['points'];

    $max_rank_index = count( $points_array ) - 1;
    $max_rank_points = (int) $points_array[ $max_rank_index ];

    $percentage_of_level = 1;

    // user pnc
    $rank = go_get_rank( $user_id );
    $current_rank = $rank['current_rank'];
    $current_rank_points = $rank['current_rank_points'];
    $next_rank = $rank['next_rank'];
    $next_rank_points = $rank['next_rank_points'];

    if ( null !== $next_rank_points ) {
        $rank_threshold_diff = ( $next_rank_points - $current_rank_points );
    } else {
        $rank_threshold_diff = 1;
    }
    $pts_to_rank_threshold = ( $current_points - $current_rank_points );

    if ( $max_rank_points === $current_rank_points ) {
        $prestige_name = get_option( 'go_prestige_name' );
        $pts_to_rank_up_str = $prestige_name;
    } else {
        $pts_to_rank_up_str = "{$pts_to_rank_threshold} / {$rank_threshold_diff}";
    }

    $percentage_of_level = ( $pts_to_rank_threshold / $rank_threshold_diff ) * 100;
    if ( $percentage_of_level <= 0 ) {
        $percentage_of_level = 0;
    } else if ( $percentage_of_level >= 100 ) {
        $percentage_of_level = 100;
    }



    /////////////////////////
    ///
    $xp_toggle = get_option('options_go_loot_xp_toggle');
    $gold_toggle = get_option('options_go_loot_gold_toggle');
    $health_toggle = get_option( 'options_go_loot_health_toggle' );

    if ($xp_toggle) {
        // the user's current amount of experience (points)
        $go_current_xp = go_get_user_loot($user_id, 'xp');

        $rank = go_get_rank($user_id);
        $rank_num = $rank['rank_num'];
        $current_rank = $rank['current_rank'];
        $current_rank_points = $rank['current_rank_points'];
        $next_rank = $rank['next_rank'];
        $next_rank_points = $rank['next_rank_points'];

        $go_option_ranks = get_option('options_go_loot_xp_levels_name_singular');
        //$points_array = $go_option_ranks['points'];

        /*
         * Here we are referring to last element manually,
         * since we don't want to modifiy
         * the arrays with the array_pop function.
         */
        //$max_rank_index = count( $points_array ) - 1;
        //$max_rank_points = (int) $points_array[ $max_rank_index ];

        if ($next_rank_points != false) {
            $rank_threshold_diff = $next_rank_points - $current_rank_points;
            $pts_to_rank_threshold = $go_current_xp - $current_rank_points;
            $pts_to_rank_up_str = "L{$rank_num}: {$pts_to_rank_threshold} / {$rank_threshold_diff}";
            $percentage = $pts_to_rank_threshold / $rank_threshold_diff * 100;
            //$color = barColor( $go_current_health, 0 );
            $color = "#39b54a";
        } else {
            $pts_to_rank_up_str = $current_rank;
            $percentage = 100;
            $color = "gold";
        }
        if ( $percentage <= 0 ) {
            $percentage = 0;
        } else if ( $percentage >= 100 ) {
            $percentage = 100;
        }
        $progress_bar = '<div class="go_admin_bar_progress_bar_border progress-bar-border">'.'<div class="go_admin_bar_progress_bar stats_progress_bar" '.
            'style="width: '.$percentage.'%; background-color: '.$color.' ;">'.
            '</div>'.
            '<div class="points_needed_to_level_up">'.
            $pts_to_rank_up_str.
            '</div>'.
            '</div>';
    }
    else {
        $progress_bar = '';
    }


    if($health_toggle) {
        // the user's current amount of bonus currency,
        // also used for coloring the admin bar
        $go_current_health = go_get_user_loot($user_id, 'health');
        $health_percentage = intval($go_current_health / 2);
        if ($health_percentage <= 0) {
            $health_percentage = 0;
        } else if ($health_percentage >= 100) {
            $health_percentage = 100;
        }
        $health_bar = '<div class="go_admin_health_bar_border progress-bar-border">' . '<div class="go_admin_bar_health_bar stats_progress_bar" ' . 'style="width: ' . $health_percentage . '%; background-color: red ;">' . '</div>' . '<div class="health_bar_percentage_str ">' . "Health Mod: " . $go_current_health . "%" . '</div>' . '</div>';

    }
    else{
        $health_bar = '';
    }

    if ($gold_toggle) {
        // the user's current amount of currency
        $go_current_gold = go_get_user_loot($user_id, 'gold');
        $gold_total = '<div class="go_admin_bar_gold admin_bar_loot">' . go_display_shorthand_currency('gold', $go_current_gold)  . '</div>';
    }
    else{
        $gold_total = '';
    }

   
    //////////////////



    ?>
    <div id='go_stats_lite_wrapper'>
    <div id='go_stats_lay_lite' class='go_datatables'>
        <div id='go_stats_header_lite'>
            <div class="go_stats_id_card">
                <div class='go_stats_gravatar'><?php echo $user_avatar; ?></div>

                <div class='go_stats_user_info'>
                    <?php
                    $current_user_id = get_current_user_id();
                    $is_admin = go_user_is_admin($current_user_id);
                    $full_name_toggle = get_option('options_go_full-names_toggle');
                    if ($full_name_toggle || $is_admin){
                        echo "<h2>{$user_fullname}</h2>{$user_display_name}<br>";
                    }else{
                        echo "<h2>{$user_display_name}</h2>";
                    }?>
                    <?php
                    go_user_links($user_id, true, false, true, true, true, false);
                    ?>

                </div>

            </div>
            <div class="go_stats_bars">
                <?php
                if ($xp_toggle) {
                    echo '<div class="go_stats_rank"><h3>' . $go_option_ranks . ' ' . $rank_num . ": " . $current_rank . '</h3></div>';
                }
                echo $progress_bar;
                //echo "<div id='go_stats_user_points'><span id='go_stats_user_points_value'>{$current_points}</span> {$points_name}</div><div id='go_stats_user_currency'><span id='go_stats_user_currency_value'>{$current_currency}</span> {$currency_name}</div><div id='go_stats_user_bonus_currency'><span id='go_stats_user_bonus_currency_value'>{$current_bonus_currency}</span> {$bonus_currency_name}</div>{$current_penalty} {$penalty_name}<br/>{$current_minutes} {$minutes_name}";
                echo $health_bar;
                ?>
            </div>
            <div class='go_stats_user_loot'>

                <?php

                if ($xp_toggle) {
                    echo '<div class="go_stats_xp">' . go_display_longhand_currency('xp', $go_current_xp) . '</div>';
                }
                if ($gold_toggle) {
                    echo '<div class="go_stats_gold">' . go_display_longhand_currency('gold', $go_current_gold) . '</div>';
                }
                if ($health_toggle) {
                    echo '<div class="go_stats_health">' . go_display_longhand_currency('health', $go_current_health) . '</div>';
                }
                ?>
            </div>
        </div>
    </div>
    <?php
    //////////////////////////////
    /// /////////////////////////
    /// Table
    global $wpdb;
    $go_task_table_name = "{$wpdb->prefix}go_tasks";

    $tasks = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT *
			FROM {$go_task_table_name}
			WHERE uid = %d
			ORDER BY id ASC",
            $user_id
        )
    );
    echo "<div id='go_task_list_lite' class='go_datatables'><table id='go_tasks_datatable_lite' class='pretty display'>
                   <thead>
						<tr>
							<th class='header' id='go_stats_post_name'><a href=\"#\">Title</a></th>
							<th class='header' id='go_stats_status'><a href=\"#\">Status</a></th>
							<th class='header' id='go_stats_bonus_status'><a href=\"#\">Bonus</a></th>
							<th class='header' id='go_stats_links'><a href=\"#\">Activity</a></th>
						</tr>
						</thead>
			    <tbody>
						";
    foreach ( $tasks as $task ) {

        $post_id = $task->post_id;
        $custom_fields = get_post_custom( $post_id );
        $post_name = get_the_title($post_id);
        $post_link = get_post_permalink($post_id);
        $status = $task->status;
        $total_stages = (isset($custom_fields['go_stages'][0]) ?  $custom_fields['go_stages'][0] : null);


        $bonus_switch = (isset($custom_fields['bonus_switch'][0]) ?  $custom_fields['bonus_switch'][0] : null);
        $bonus_status = null;
        $total_bonus_stages = null;
        if ($bonus_switch) {
            $bonus_status = $task->bonus_status;
            $total_bonus_stages = (isset($custom_fields['go_bonus_limit'][0]) ? $custom_fields['go_bonus_limit'][0] : null);
        }
        $xp = $task->xp;
        $gold = $task->gold;
        $health = $task->health;
        //$start_time = $task->start_time;
        $last_time = $task->last_time;
        $time  = date("m/d/y g:i A", strtotime($last_time));


        $go_actions_table_name = "{$wpdb->prefix}go_actions";
        $actions = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT *
			FROM {$go_actions_table_name}
			WHERE source_id = %d
			ORDER BY id DESC",
                $post_id
            )
        );

        $next_bonus_stage = null;
        $i = 0;
        $links = array();
        foreach ($actions as $action){
            $i++;
            $check_type = $action->check_type;
            $result = $action->result;
            $action_time = $action->TIMESTAMP;
            $action_time = date("m/d/y g:i A", strtotime($action_time));
            $action_stage = $action->stage;
            if ($action->action_type == 'task'){
                $loop_bonus_status = $action->bonus_status; //get the bonus status if it exists
                $stage = $action->stage ; //get the stage

                if ($loop_bonus_status > 0){//it is a bonus stage
                    $last_bonus = (isset($last_bonus) ?  false : true);//if this is the first encounter set to true, after that false
                    if ($last_bonus || ($next_bonus_stage > 0 && $loop_bonus_status == $next_bonus_stage )){
                        //$links[] = go_result_link($check_type, $result, $action_stage, $action_time);
                        $next_bonus_stage = $loop_bonus_status -1;
                        $links[] = go_result_link($check_type, $result, $loop_bonus_status, $action_time, true);
                    }
                }
                else if ($next_bonus_stage <= 0 || $next_bonus_stage == null) {//if it's not a bonus and it's not the last one completed
                    $next_stage = (isset($next_stage) ?  $next_stage : $stage );
                    if ($next_stage > 0 && $stage == $next_stage){
                        $action_stage = $action->stage;
                        $links[] = go_result_link($check_type, $result, $action_stage, $action_time, false);
                        $next_stage = $stage - 1;
                    }
                }

            }


        }
        $links = array_reverse($links);
        $link_count = count($links);
        $links = $comma_separated = implode(" ", $links);


        $status_order = $status/$total_stages;
        $bonus_status_order = $bonus_status/$total_bonus_stages;
        if (!empty($total_bonus_stages)){
            $bonus_str = $bonus_status . " / " . $total_bonus_stages;
        }else{
            $bonus_str = null;
        }
        echo "
			        <tr id='postID_{$post_id}'>

					    <td><a href='{$post_link}' >{$post_name}<a></td>

					    <td data-order='{$status_order}'>{$status} / {$total_stages}</td>
					    <td data-order='{$bonus_status_order}'>{$bonus_str}</td>
					    <td data-order='{$link_count}'>{$links} </td>

					</tr>
					";


    }
    echo "</tbody>
<tfoot>
						<tr>
							
							<th><a href=\"#\">Title</a></th>


							<th><a href=\"#\">Status</a></th>
							<th><a href=\"#\">Bonus</a></th>
							<th><a href=\"#\">Activity</a></th>

						</tr>
						</tfoot>
				</table></div></div>";

    die();
}

/**
 *
 */
function go_stats_leaderboard() {

    check_ajax_referer('go_stats_leaderboard_');
    if (!empty($_POST['user_id'])) {
        $current_user_id = (int)$_POST['user_id'];
    }
    // prepares tab titles
    $xp_name = get_option("options_go_loot_xp_name");
    $gold_name = get_option("options_go_loot_gold_name");
    $health_name = get_option("options_go_loot_health_name");
    $badges_name = get_option('options_go_badges_name_singular') . " Count";



    $xp_toggle = get_option('options_go_loot_xp_toggle');
    $gold_toggle = get_option('options_go_loot_gold_toggle');
    $health_toggle = get_option('options_go_loot_health_toggle');

    $badges_toggle = get_option('options_go_badges_toggle');

    ?>

        <div id="go_leaderboard_wrapper" class="go_datatables">
            <div id="go_leaderboard_filters">
                <span>Section:<?php go_make_tax_select('user_go_sections'); ?></span>
                <span>Group:<?php go_make_tax_select('user_go_groups'); ?></span>
            </div>

            <div id="go_leaderboard_flex">

                <div id="go_leaderboard" class="go_leaderboard_layer">

                    <table id='go_leaders_datatable' class='pretty display'>
                        <thead>
                        <tr>
                            <th></th>
                            <th class='header'><a href="#">Name</a></th>
                            <th class='header'><a href="#">Links</a></th>
                            <?php
                            if ($xp_toggle) {
                                echo "<th class='header'><a href='#'>" . $xp_name . "</a></th>";
                            }
                            if ($gold_toggle) {
                                echo "<th class='header'><a href='#'>" . $gold_name . "</a></th>";
                            }
                            if ($health_toggle) {
                                echo "<th class='header'><a href='#'>" . $health_name . "</a></th>";
                            }
                            if ($badges_toggle) {
                                echo "<th class='header'><a href='#'>" . $badges_name . "</a></th>";
                            }
                            ?>

                        </tr>
                        </thead>
                        <tbody></table>
                </div>

            </div>
        </div>


    <?php
    die();

}

function go_stats_leaderboard_dataloader_ajax() {
    global $wpdb;

    //FIRST GET THE SEARCH PARAMETERS FROM OTHER TABLES
    $search_val = $_GET['search']['value'];

    //USER IDS
    //if there is a search value, get the user ids that match by name
    if (isset($search_val) && $search_val != "") {
        $user_ids = go_get_user_ids_search($search_val);
    }
    if (empty($user_ids)){
        $user_ids[] = 'none';
    }

    //Group IDS
    //if there is a search value, get the group that match by name
    if (isset($search_val) && $search_val != "") {
        $group_ids = go_get_term_ids_search($search_val, 'user_go_groups');
    }
    if (empty($term_ids)){
        $group_ids[] = 'none';
    }
    //END GET PARAMETERS

    //CREATE THE QUERY
    //FILTER VALUES
    $section = intval($_GET['section']);
    $group = intval($_GET['group']);

    $go_totals_table_name = "{$wpdb->prefix}go_loot";

    //columns that will be returned
    $aColumns = array( 'id', 'uid', 'xp', 'gold', 'health', 'badges', 'groups', 'badge_count' );

    //columns that will be searched
    $sColumns = array( 'xp', 'gold', 'health', 'badge_count' );

    $sIndexColumn = "id";
    $sTable = $go_totals_table_name;

    $sLimit = '';
    if ( isset( $_GET['start'] ) && $_GET['length'] != '-1' )
    {
        $sLimit = "LIMIT ".intval( $_GET['start'] ).", ".
            intval( $_GET['length'] );
    }

    $order_dir = $_GET['order'][0]['dir'];
    $order_col = $_GET['order'][0]['column'];
    if ($order_col == 2){
        $order_col = 'xp';
    }
    else if ($order_col == 3){
        $order_col = 'gold';
    }
    else if ($order_col == 4){
        $order_col = 'health';
    }
    else if ($order_col == 5){
        $order_col = 'badge_count';
    }

    $sOrder = "ORDER BY " . $order_col . " " . $order_dir; //always in reverse order

    $sWhere = "";
    if ( isset($search_val) && $search_val != "" )
    {
        $search_val = $_GET['search']['value'];

        $sWhere = "WHERE ";


        //search these columns
        $sWhere .= " (";
        for ( $i=0 ; $i<count($sColumns) ; $i++ )
        {
            $sWhere .= "`".$sColumns[$i]."` LIKE '%".esc_sql( $search_val )."%' OR ";
        }
        $sWhere = substr_replace( $sWhere, "", -3 );
        $sWhere .= ')';

        //search for User IDs
        $sColumns = array('uid');
        $sWhere .= " OR (";
        for ($i = 0; $i < count($user_ids); $i++) {
            $search_val = $user_ids[$i];
            $sWhere .= "`" . $sColumns[0] . "` LIKE '%" . esc_sql($search_val) . "%' OR ";
        }
        $sWhere = substr_replace($sWhere, "", -3);//removes the last OR
        $sWhere .= ')';

        //search for group IDs
        $sColumns = array('groups');
        $sWhere .= " OR (";
        for ($i = 0; $i < count($group_ids); $i++) {
            $search_val = $group_ids[$i];
            $sWhere .= "`" . $sColumns[0] . "` LIKE '%" . esc_sql($search_val) . "%' OR ";
        }
        $sWhere = substr_replace($sWhere, "", -3);//removes the last OR
        $sWhere .= ')';

        //end create sWhere statement
        $sWhere .= ")";


    }

    $sQuery = "
    SELECT SQL_CALC_FOUND_ROWS `".str_replace(" , ", " ", implode("`, `", $aColumns))."`
    FROM   $sTable
    $sWhere
    $sOrder
    $sLimit
    ";

    $rResult = $wpdb->get_results($sQuery, ARRAY_A);

    $sQuery = "SELECT FOUND_ROWS()";

    $rResultFilterTotal = $wpdb->get_results($sQuery, ARRAY_N);

    $iFilteredTotal = $rResultFilterTotal [0];

    $sQuery = "
    SELECT COUNT(`".$sIndexColumn."`)
    FROM   $sTable
    ";

    $rResultTotal = $wpdb->get_results($sQuery, ARRAY_N);

    $iTotal = $rResultTotal [0];

    $output = array(
        "iTotalRecords" => $iTotal,
        "iTotalDisplayRecords" => $iFilteredTotal,
        "aaData" => array()
    );
    $num = 0;
    foreach($rResult as $action) {//output a row for each message
        //The message content
        $num++;
        $row = array();
        $user_id = $action['uid'];
        $xp = $action['xp'];
        $gold = $action['gold'];
        $health = $action['health'];
        $group_ids = $action['groups'];
        $badge_count = $action['badge_count'];

        //FILTERS
        //groups filter
        $user_group_ids = $group_ids;
        $group_ids_array = unserialize($user_group_ids);

        //if the group filter is on
        if($group != 'none') {
            $is_array = is_array($group_ids_array); //does this user have groups set as an array
            //if not array then filter
            if (!$is_array){
                continue;
            }

            //if there is a match then continue, if not
            $in_array = in_array($group, $group_ids_array);
            if (!$in_array) {
                continue;
            }
        }

        $num_sections = get_user_meta($user_id, 'go_section_and_seat', true);
        if (empty($num_sections)){
            $num_sections =1;
        }
        $user_periods= array();
        for ($i = 0; $i < $num_sections; $i++) {
            $user_period_option = "go_section_and_seat_" . $i . "_user-section";
            $user_period = intval(get_user_meta($user_id, $user_period_option, true));
            $user_periods[] = $user_period;
        }
        if ($section != 'none') {
            $in_array = in_array($section, $user_periods);
            if (!$in_array) {
                continue;
            }
        }

        $user_data_key = get_userdata($user_id);
        $user_display_name = $user_data_key->display_name;

        $user_fullname = $user_data_key->first_name.' '.$user_data_key->last_name;
        $full_name_toggle = get_option('options_go_full-names_toggle');

        //$user_name = "<a href='javascript:;' class='go_stats_lite' data-UserId='{$user_id}' onclick='go_stats_lite({$user_id});'>$user_display_name</a>";
        ob_start();
        go_user_links($user_id, true, true, true, true, true, true);
        $links = ob_get_clean();
        $row[] = "{$num}";
        if ($full_name_toggle){
            $row[] = "{$user_fullname}";
        }else{
            $row[] = "{$user_display_name}";
        }
        $row[] = "{$links}";
        $xp_toggle = get_option('options_go_loot_xp_toggle');
        $gold_toggle = get_option('options_go_loot_gold_toggle');
        $health_toggle = get_option('options_go_loot_health_toggle');

        if ($xp_toggle){
            $row[] = "{$xp}";
        }
        if ($gold_toggle){
            $row[] = "{$gold}";
        }
        if ($health_toggle){
            $row[] = "{$health}";
        }
        $badges_toggle = get_option('options_go_badges_toggle');
        if ($badges_toggle) {
            $row[] = "{$badge_count}";
        }
        $output['aaData'][] = $row;
    }

    $output['iTotalDisplayRecords'] =  count($output['aaData']);

    echo json_encode( $output );
    die();

}


?>