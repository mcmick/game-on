<?php
/**
 * Created by PhpStorm.
 * User: mmcmurray
 * Date: 10/13/18
 * Time: 10:11 PM
 */

/**
 * TASK SHORTCODE
 * This is the file that displays content in a post/page with a task.
 * This file interprets and executes the shortcode in a post's body.
 * @param $atts
 * @param null $content
 */
function go_task_shortcode($atts, $content = null ) {
    global $wpdb;

    /**
     * Get Post ID from shortcode
     */
    $atts = shortcode_atts( array(
        'id' => '', // ID defined in Shortcode
    ), $atts);
    $post_id = $atts['id'];

    // abort if the post ID is invalid
    if ( ! $post_id ) {
        return;
    }

    /**
     * Enqueue go_tasks.js that is only needed on task pages
     * https://www.thewpcrowd.com/wordpress/enqueuing-scripts-only-when-widget-or-shortcode/
     */
    wp_enqueue_script( 'go_tasks','','','', true );


    /**
     * Variables
     */
    // the current user's id
    $user_id = get_current_user_id();
    //$is_logged_in = is_user_member_of_blog( $user_id );
    $is_logged_in = ! empty( $user_id ) && (is_user_member_of_blog( ) || is_super_admin())? true : false;
    //$is_logged_in = ! empty( $user_id ) && $user_id > 0 ? true : false;
    $go_task_table_name = "{$wpdb->prefix}go_tasks";
    $is_unlocked_type = go_master_unlocked($user_id, $post_id);
    if ($is_unlocked_type == 'password' || $is_unlocked_type == 'master password') {
        $is_unlocked = true;
    }
    else { $is_unlocked = false;}
    //Get all the custom fields
    //$custom_fields = go_post_meta( $post_id ); // Just gathering some data about this task with its post id
    $custom_fields = go_post_meta($post_id); //0--name, 1--status, 2--permalink, 3--metadata


    /**
     * Get options needed for task display
     */
    $task_name = strtolower( get_option( 'options_go_tasks_name_singular' ) );
    $uc_task_name = ucwords($task_name);
    $badges_name = get_option('options_go_badges_name_plural');
    //$badge_name = get_option( 'options_go_naming_other_badges' );
    $go_lightbox_switch = get_option( 'go_video_lightbox_toggle_switch' );
    if($go_lightbox_switch === false){
        $go_lightbox_switch = 1;
    }
    $go_video_unit = get_option ('go_video_width_type_control');
    if ($go_video_unit == '%'){
        $percent = get_option( 'go_video_width_percent_control' );
        if($percent === false){
            $percent = 100;
        }
        $go_fitvids_maxwidth = $percent."%";
    }else{
        $pixels = get_option( 'go_video_width_px_control' );
        if($pixels === false){
            $pixels = 400;
        }
        $go_fitvids_maxwidth = $pixels."px";
    }

    $admin_name = 'an administrator';
    $is_admin = go_user_is_admin();



    //user status
    $status = go_get_status($post_id, $user_id);

    //ADD BACK IN, BUT CHECK TO SEE WHAT YOU NEED
    /**
     * Localize Task Script
     * All the variables are set.
     */
    /**
     *prepares nonces for AJAX requests sent from this post
     */
    $task_shortcode_nonces = array(
        //'go_task_abandon' => wp_create_nonce( 'go_task_abandon_' . $post_id . '_' . $user_id ),
        'go_check_quiz_answers' => wp_create_nonce( 'go_check_quiz_answers' ),
        'go_save_quiz_result' => wp_create_nonce( 'go_save_quiz_result' ),
        //'go_test_point_update' => wp_create_nonce( 'go_test_point_update'),
        'go_task_change_stage' => wp_create_nonce( 'go_task_change_stage' ),
    );

    //$redirect_url = go_get_user_redirect($user_id);
    //$redirect_url = $_SERVER['HTTP_REFERER'];
    $redirect_url = (isset($_SERVER['HTTP_REFERER']) ?  $_SERVER['HTTP_REFERER'] : go_get_user_redirect($user_id));


    wp_localize_script(
        'go_frontend',
        'go_task_data',
        array(
            //'go_taskabandon_nonce'	=>  $task_shortcode_nonces['go_task_abandon'],
            'url'	=> get_site_url(), //ok
            //'status'	=>  $status,
            'userID'	=>  $user_id, //ok
            'ID'	=>  $post_id, //ok
            //'homeURL'	=>  home_url(),
            'redirectURL'	=> $redirect_url, //ok //for the timer abandon
            'admin_name'	=>  $admin_name, //ok
            'go_check_quiz_answers'	=>  $task_shortcode_nonces['go_check_quiz_answers'], //ok
            'go_task_change_stage'	=>  $task_shortcode_nonces['go_task_change_stage'],
            'go_save_quiz_result'	=>  $task_shortcode_nonces['go_save_quiz_result'],//ok

        )
    );

    /**
     * Start wrapper
     */
    //The wrapper for the content
    echo "<div id='go_wrapper' data-lightbox='{$go_lightbox_switch}' data-maxwidth='{$go_fitvids_maxwidth}' >";

    if(!empty($custom_fields['go_featured_image'][0])){
        $image_id = $custom_fields['go_featured_image'][0];
        $image_url = wp_get_attachment_image_src($image_id, 'large');
        $full = wp_get_attachment_image_src($image_id, 'full');
//
        echo '<span><a href="#" data="1" data-featherlight="' . $full[0] . '"><img style="max-height:350px; padding-bottom: 17px;" data-type="file" src="' . $image_url[0] .'"></a></span>';
    }
    /**
     * GUEST ACCESS
     * Determine if guests can access this content
     * then calls function to print guest content
     */
    if ($is_logged_in == false) {
        go_display_visitor_content( $custom_fields, $post_id, $task_name, $badges_name, $uc_task_name);
        echo "</div>";
        return null;
    }

    /**
     * Admin Views & Locks
     */
    //$admin_view = ($is_admin ?  get_user_option('go_admin_view', $user_id) : null);
    $admin_view = go_get_admin_view($user_id);
    if ($is_admin && $admin_view == 'all') {
        go_display_visitor_content( $custom_fields, $post_id, $task_name, $badges_name, $uc_task_name, true);
        echo "</div>";
        return null;
    }

    /**
     * LOCKS
     */
    if (!$is_unlocked) { //if not previously unlocked with a password
        if (!$is_admin) {
            $task_is_locked = go_display_locks($post_id, $user_id, $is_admin, $task_name, $badges_name, $custom_fields, $is_logged_in, $uc_task_name);
            if ($task_is_locked) {
                //Print the bottom of the page
                go_new_pagination( $post_id, $custom_fields );
                //go_hidden_footer();
                echo "</div>";
                return null;
            }
        }
    }
    else if ($is_unlocked){
        if ($is_unlocked_type === 'master password'){
            echo "<div class='go_checks_and_buttons'><i class='fas fa-unlock fa-2x'></i> Unlocked by the master password.</div>";
        }
        else if ($is_unlocked_type === 'password'){
            echo "<div class='go_checks_and_buttons'><i class='fas fa-unlock fa-2x'></i> Unlocked by the $task_name password.</div>";
        }
    }

    /**
     * Due date mods
     */
   // go_due_date_mods ($custom_fields, $is_logged_in, $task_name );


    /**
     * Encounter
     * if this is the first time encountering this task, then create a row in the task database.
     */
    if ($status === null ){
        $status = -1;
        //just a double check that the row doesn't already exist
        $row_exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT ID 
					FROM {$go_task_table_name} 
					WHERE uid = %d and post_id = %d LIMIT 1",
                $user_id,
                $post_id
            )
        );
        //create the row
        $time = current_time( 'mysql');
        if ( $row_exists == null ) {
            $wpdb->insert($go_task_table_name, array('uid' => $user_id, 'post_id' => $post_id, 'status' => 0, 'last_time' => $time, 'xp' => 0, 'gold' => 0, 'health' => 0, 'start_time' => $time));
        }
    }
    /**
     * Display Rewards before task content
     * This is the list of rewards at the top of the task.
     */
    go_display_rewards( $custom_fields, $task_name, $user_id, 'top', $post_id );

    /**
     * Timer
     */
    $locks_status = go_display_timer ($custom_fields, $is_logged_in, $user_id, $post_id, $task_name );
    if ($locks_status){

        //echo "</div>";
        go_new_pagination ( $post_id, $custom_fields );
        //go_hidden_footer ();
        echo "</div>";
        return null;
    }

    /**
     * Entry reward
     * Note: If the timer is on, the reward entry is given when the timer is started.
     *
     */
    if ($status === -1 || $status === -2){
        go_update_stage_table($user_id, $post_id, $custom_fields, -1, null, true, 'entry_reward', null, null, null);
        $status = 0;
    }


    /**
     * MAIN CONTENT
     */


    //Print stage content
    //Including stages, checks for understanding and buttons
    go_print_messages ( $status, $custom_fields, $user_id, $post_id);

    echo "</div>";

    //echo "</div></div>";
    //Print the bottom of the page
    go_new_pagination( $post_id, $custom_fields );//3 Queries
    //go_hidden_footer();

    //Print comments//
    if ( get_post_type() == 'tasks' ) {
        comments_template();
        //wp_list_comments();
    }
}
add_shortcode( 'go_task','go_task_shortcode' );

/**
 * LOCKS
 * prevents all visitors both logged in and out from accessing the task content,
 * if they do not meet the requirements.
 * The task_locks function will set the output for the locks
 * and set the task_is_locked variable to true if it is locked.
 *
 * @param $post_id
 * @param $user_id
 * @param $is_admin
 * @param $task_name
 * @param $badge_name
 * @param $custom_fields
 * @param $is_logged_in
 * @param $uc_task_name
 * @return bool
 */
function go_display_locks ($post_id, $user_id, $is_admin, $task_name, $badge_name, $custom_fields, $is_logged_in, $uc_task_name){

    $task_is_locked = false;
    if ($custom_fields['go-location_map_toggle'][0] == true && !empty($custom_fields['go-location_map_loc'][0])){
        $on_map = true;
    }
    else{
        $on_map = false;
    }
    $lock_toggle = (isset($custom_fields['go_lock_toggle'][0]) ?  $custom_fields['go_lock_toggle'][0] : null);
    $go_sched_toggle = (isset($custom_fields['go_sched_toggle'][0]) ?  $custom_fields['go_sched_toggle'][0] : null);
    if ($lock_toggle == true || $go_sched_toggle == true || $on_map == true) {
        $task_is_locked = go_task_locks($post_id, false, $user_id, $task_name, $custom_fields);
    }

    //if it is locked, show master password field and stop printing of the task.
    if ($is_logged_in) {
        $go_password_lock = (isset($custom_fields['go_password_lock'][0]) ? $custom_fields['go_password_lock'][0] : null);
        if ($go_password_lock == true) {
            $task_is_locked = true;
        }

        //Get option (show password field) from custom fields
        if ($go_password_lock) {
            //Show password unlock
            echo "<div class='go_lock'><h3>Unlock {$uc_task_name}</h3><input id='go_result' class='clickable' type='password' placeholder='Enter Password'>";
            go_buttons($custom_fields, null, null, null, 'unlock', false, null, null, false);
            echo "</div>";

        } else if ($task_is_locked == true) {
            //if ($is_logged_in) { //add of show password field is on
            ?>
            <div id="go_admin_override" style="overflow: auto; width: 100%;">
                <div style="float: right; font-size: .8em;">Admin Override</div>
            </div>
            <?php
            //Show password unlock
            echo "<div class='go_lock go_password' style='display: none;'><h3>Admin Override</h3><p>This field is not for users. Do not ask for this password. It is not part of the gameplay.</p><input id='go_result' class='clickable' type='password' placeholder='Enter Password'>";
            go_buttons($custom_fields, null, null, null, 'unlock', false, null, null, false);
            echo "</div>";

            //}
        }
    }
    return $task_is_locked;

}


/**
 * VISITOR CONTENT
 */

/**
 * Logic to decide if locks should be used for visitors
 * based on options and task settings.
 * @param $custom_fields
 * @param $post_id
 * @param $task_name
 * @param $badge_name
 * @param $uc_task_name
 * @param $show_all
 *
 * @return null
 */
function go_display_visitor_content ( $custom_fields, $post_id, $task_name, $badge_name, $uc_task_name, $show_all = false ){


        if ($custom_fields['go-guest-view'][0] == "global"){
            $guest_access = get_option('options_go_guest_global');
        }
        else {
            $guest_access = $custom_fields['go-guest-view'][0];
        }
    if(!$show_all){
        if ($guest_access == "blocked" ) {
            echo "<div><h2 class='go_error_red'>You must be logged in to view this content.</h2></div>";
            return null;
        }
    }


    go_display_rewards( $custom_fields, $task_name, null, 'top', $post_id);
    go_due_date_mods ($custom_fields, false, $task_name );

    $task_is_locked = false;
    if ($guest_access == "regular" || $show_all  ) {
        //echo "Regular";
        $task_is_locked = go_display_locks($post_id, null, false, $task_name, $badge_name, $custom_fields, false, $uc_task_name);

    }


    if (( $guest_access == "regular" && !$task_is_locked) || $guest_access == "open" || $show_all){
        if(!$show_all) {
            echo "<script> 
            jQuery( document ).ready( function() { 
               new Noty({
                    type: 'error',
                    layout: 'topCenter',
                    text: 'You are viewing this page as a Guest. <br>You can view the content, but gameplay is disabled.',
                    theme: 'sunset',
                    visibilityControl: true,  
                }).show();               
            });</script>";
        }

        echo "<script> 
            jQuery( document ).ready( function() { 
               //jQuery('#go_all_content input, #go_all_content textarea').removeClass('summernote');
               jQuery('#go_all_content input, #go_all_content textarea').attr('disabled', 'disabled');
            });</script>";
        echo "<div id='go_all_content'>";
        go_hidden_blog_post();


        go_display_visitor_messages($custom_fields, $post_id);
        go_print_outro (null, $post_id, $custom_fields, 1, 1, true);

        // displays the chain pagination list so that visitors can still navigate chains easily
        go_new_pagination( $post_id, $custom_fields );

        //go_hidden_footer();
        echo "</div>";

    }
    return null;



}
/**
 * Print the stage content for visitors
 * @param $custom_fields
 */
function go_display_visitor_messages( $custom_fields, $post_id ) {
    //Print messages
    $i = 0;
    $stage_count = $custom_fields['go_stages'][0];
    while (  $stage_count > $i) {
        go_print_1_message ( $custom_fields, $i );
        go_checks_for_understanding ($custom_fields, $i, $i, null, $post_id, null, null, null, true);

        $i++;
    }
}


/**
 * Used to create a hidden post for the guest views.  It makes it so the other blog forms don't load MCE.
 */
function go_hidden_blog_post(){
    echo "<div style='display:none;'>";
    wp_editor('', 'go_blog_post' );
    echo "</div>";
}

/**
 * ADMIN CONTENT
 */

/**
 * Logic for which type of admin content to show based
 * on the drop down selection at the top of the tasks pages on frontend.
 * @param $is_admin
 * @param $admin_view
 * @param $custom_fields
 * @param $is_logged_in
 * @param $task_name
 * @return string
 */
/*
function go_admin_content ($post_id, $is_admin, $admin_view, $custom_fields, $is_logged_in, $task_name, $status, $uid, $task_id, $badge_name){

    if ($is_admin && $admin_view == 'all') {

        go_display_all_admin_content($custom_fields, $is_logged_in, $task_name, $status, $uid, $task_id);
        $admin_flag = 'stop';
        return $admin_flag;
    }
    else if ($is_admin && $admin_view == 'guest') {
        go_display_visitor_content( $custom_fields, $post_id, $task_name, $badge_name, $task_name);
        $admin_flag = 'stop';
        return $admin_flag;
    }
    else if (!$is_admin || $admin_view == 'user') {
        $admin_flag = 'locks';
        return $admin_flag;
    }
    else if (!$is_admin || $admin_view == 'player') {
        $admin_flag = 'no_locks';
        return $admin_flag;
    }
}*/

/**
 * If the dropdown is "all" do this.
 * @param $custom_fields
 * @param $is_logged_in
 * @param $task_name
 */
function go_display_all_admin_content( $custom_fields, $is_logged_in, $task_name, $status, $user_id, $post_id ) {
    echo "<script> 
        jQuery( document ).ready( function() { 
           new Noty({
                type: 'error',
                layout: 'topCenter',
                text: 'You are in \"All Stage\" view mode. Gameplay is disabled.',
                theme: 'sunset',
                visibilityControl: true,  
            }).show();
           
           jQuery('#go_all_content input, #go_all_content textarea').attr('disabled', 'disabled');
        });
        
        </script>";

    echo "<div id='go_all_content'>";
    go_hidden_blog_post();
    go_display_rewards( $custom_fields, $task_name , $user_id, 'top', $post_id);
    go_due_date_mods ($custom_fields, $is_logged_in, $task_name );
    //Print messages
    $i = 0;
    $stage_count = $custom_fields['go_stages'][0];
    while (  $stage_count > $i) {
        go_print_1_message ( $custom_fields, $i );
        go_checks_for_understanding ($custom_fields, $i, $i, $user_id, $post_id, null, null, null, true);

        $i++;
    }
    go_print_outro ($user_id, $post_id, $custom_fields, $stage_count, $status, true);
    /*
    $bonus_count = $custom_fields['go_bonus_limit'][0];
    if ($bonus_count > 0){
        go_print_bonus_stage ($user_id, $post_id, $custom_fields, true);
    }
    */

    // displays the chain pagination list so that visitors can still navigate chains easily
    go_new_pagination( $post_id, $custom_fields);
    //go_hidden_footer();
    echo "</div>";
}



/**
 * DUE DATE MODIFIER MESSAGE
 * @param $custom_fields
 * @param $is_logged_in
 * @param $task_name
 */
function go_due_date_mods ($custom_fields, $is_logged_in, $task_name ){
    $uc_task_name = ucwords($task_name);
    if ($custom_fields['go_due_dates_toggle'][0] == true && $is_logged_in) {
        echo '<div class="go_late_mods"><h3 class="go_error_red">Due Date</h3>';
        echo "<ul>";
        $num_loops = $custom_fields['go_due_dates_mod_settings'][0];
        for ($i = 0; $i < $num_loops; $i++) {
            $mod_date = 'go_due_dates_mod_settings_'.$i.'_date';
            $mod_date = $custom_fields[$mod_date][0];
            $mod_date_timestamp = strtotime($mod_date);
            $mod_date = date('F j, Y \a\t g:i a\.' ,$mod_date_timestamp);
            //$mod_date_timestamp = $mod_date_timestamp + (3600 * get_option('gmt_offset'));
            $current_timestamp = current_time( 'timestamp' );
            ////$current_time = current_time( 'mysql' );
            $mod_percent = 'go_due_dates_mod_settings_'.$i.'_mod';
            $mod_percent = $custom_fields[$mod_percent][0];
            if ($current_timestamp > $mod_date_timestamp){
                echo '<li>The rewards on this '. $task_name . '  were reduced by<br>';
            }
            else {
                echo '<li>The rewards on this ' . $uc_task_name . ' will be reduced <br>';
            }
            echo "" . $mod_percent . "% on " . $mod_date . "</li>";
        }
        echo "</ul></div>";
    }
}

/**
 * MESSAGES
 * Determines what stages to print
 * @param $status
 * @param $custom_fields
 * @param $user_id
 * @param $post_id
 *
 */
function go_print_messages ( $status, $custom_fields, $user_id, $post_id){
    //Print messages
    $i = 0;
    $stage_count = $custom_fields['go_stages'][0];
    while ( $i <= $status && $stage_count > $i) {
        go_print_1_message ( $custom_fields, $i );
        //Print Checks for Understanding for the last stage message printed and buttons
        go_checks_for_understanding ($custom_fields, $i, $status, $user_id, $post_id, null, null, null, false);
        //go_checks_for_understanding ($custom_fields, $i, $status, $user_id, $post_id, $bonus, $bonus_status, $repeat_max)
        $i++;
    }
    if ($i <= $status){
        go_print_outro ($user_id, $post_id, $custom_fields, $stage_count, $status, false);
    }
}

/**
 * Prints a single stage content
 * @param $custom_fields
 * @param $i
 */
function go_print_1_message ( $custom_fields, $i ){

    $heading = (isset($custom_fields['go_stages_' . $i . '_heading'][0]) ?  $custom_fields['go_stages_' . $i . '_heading'][0] : "");
    $key = 'go_stages_' . $i . '_content';
    //$content = $custom_fields[$key][0];
    $content = (isset($custom_fields[$key][0]) ?  $custom_fields[$key][0] : null);
    $message = ( ! empty( $content ) ? $content : '' ); // Completion Message
    //adds oembed to content
    //if(isset($GLOBALS['wp_embed']))
    //    $message  = $GLOBALS['wp_embed']->autoembed($message );
    //echo "<div id='message_" . $i . "' class='go_stage_message'  style='display: none;'>".do_shortcode(wpautop( $message  ) )."</div>";

    $message  = apply_filters( 'go_awesome_text', $message );
    echo "<div id='message_" . $i . "' class='go_stage_message'  style='display: none;'>";
    if(!empty($heading)){
      echo "<h2>".$heading."</h2>";
    }
    echo  $message ."</div>";

}

/**
 *Bonus Loot
 */
function go_bonus_loot ($custom_fields, $user_id) {
    $bonus_loot = strtolower( get_option( 'options_go_loot_bonus_loot_name' ) );
    $bonus_loot_uc = ucwords($bonus_loot);
    //$mystery_box_url =
    echo "
		<div id='go_bonus_loot'>
		<hr>
        <p>Click the box to try and claim " . $bonus_loot . ".</p>
        ";
    $url = plugin_dir_url((dirname(dirname(dirname(__FILE__)))));
    $url = $url . "media/mysterybox_inner_glow_sm.gif";
    echo "<div id='go_bonus_loot_container' style='display:flex;'>
            <div id='go_bonus_loot_mysterybox' style='width: 150px; text-align: center;'>
                <link href='http://fonts.googleapis.com/css?family=Pacifico' rel='stylesheet' type='text/css'>
                <img id='go_bonus_button' class='go_bonus_button'src=" . $url . " > 
		    </div>
		    <div id='go_bonus_loot_possibilites'>
                Click the mystery box and you might find: ";
    go_print_bonus_loot_possibilities($custom_fields,$user_id);
    echo "</div>
        </div>
    </div>";
}

/**
 * @param $task_id
 * @param null $user_id
 * @return int|null
 */
function go_get_bonus_status($task_id, $user_id = null ) {
    global $wpdb;
    $go_task_table_name = "{$wpdb->prefix}go_tasks";

    if ( empty( $task_id ) ) {
        return null;
    }

    if ( empty( $user_id ) ) {
        $user_id = get_current_user_id();
    } else {
        $user_id = (int) $user_id;
    }

    $task_count = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT bonus_status
			FROM {$go_task_table_name} 
			WHERE uid = %d AND post_id = %d",
            $user_id,
            $task_id
        )
    );

    if ( null !== $task_count && ! is_int( $task_count ) ) {
        $task_count = (int) $task_count;
    }

    return $task_count;
}

/**
 * go_print_bonus_stage
 * @param $user_id
 * @param $post_id
 * @param $custom_fields
 * @param $task_name
 * @param $all_content
 */
function go_print_bonus_stage ($user_id, $post_id, $custom_fields, $all_content){
    $bonus_status = go_get_bonus_status($post_id, $user_id);
    $content = (isset($custom_fields['go_bonus_stage_content'][0]) ?  $custom_fields['go_bonus_stage_content'][0] : null);
    $content  = apply_filters( 'go_awesome_text', $content );

    $bonus_stage_name =  get_option( 'options_go_tasks_bonus_stage' );
    $repeat_max = (isset($custom_fields['go_bonus_limit'][0]) ?  $custom_fields['go_bonus_limit'][0] : null);

    echo "
        <div id='bonus_stage' >
            <h3>" . ucwords($bonus_stage_name)   . "</h3>
            ". $content . "
            <h3>This ".$bonus_stage_name." can be submitted ".$repeat_max." times.</h3>
        </div>
    ";

    $i = 0;

    if (!$all_content) {
        while ($i <= $bonus_status && $repeat_max > $i) {


            //Print Checks for Understanding for the last stage message printed and buttons
            go_checks_for_understanding($custom_fields, $i, null, $user_id, $post_id, true, $bonus_status, $repeat_max, $all_content);
            $i++;
        }
    }else{
        go_checks_for_understanding($custom_fields, $i, null, $user_id, $post_id, true, $bonus_status, $repeat_max, $all_content);
    }

    //if ($bonus_status == $i ) {
    //}

}

/**
 * @param $user_id
 * @param $post_id
 * @param $custom_fields
 * @param $stage_count
 * @param $status
 */
function go_print_outro ($user_id, $post_id, $custom_fields, $stage_count, $status, $all_content){
    global $wpdb;
    $go_task_table_name = "{$wpdb->prefix}go_tasks";
    //$custom_fields = go_post_meta( $post_id );
    $task_name = strtolower( get_option( 'options_go_tasks_name_singular' ) );
    $outro_message = (isset($custom_fields['go_outro_message'][0]) ?  $custom_fields['go_outro_message'][0] : null);
    //$outro_message = do_shortcode($outro_message);
    $outro_message  = apply_filters( 'go_awesome_text', $outro_message );
    echo "<div id='outro' class='go_checks_and_buttons'>";
    echo "    
        <h3>" . ucwords($task_name) . " Complete!</h3>";
     if (!empty($outro_message)){
        echo '<p>' . $outro_message . '</p>';
     }

     if (!$all_content) {
        go_display_rewards( $custom_fields, $task_name, $user_id, 'bottom', $post_id);
    }else{
        go_display_rewards( $custom_fields, $task_name, $user_id, 'top', $post_id);
    }

    $bonus_status = go_get_bonus_status($post_id, $user_id);
    if ($bonus_status <= 0){
        go_buttons($custom_fields, null, $stage_count, $status, 'show_bonus', false, null, null, true);
    }
    echo "</div>";
    if ($bonus_status > 0 || $all_content){
        go_print_bonus_stage ($user_id, $post_id, $custom_fields, $all_content);
    }
}

function go_print_bonus_loot_possibilities($custom_fields, $user_id){

    $rows = go_get_bonus_loot_rows($custom_fields, false, $user_id);

    if (count($rows) >0) {
        echo "<ul style='margin: 0 0 0 2em;'>";
        foreach ($rows as $row) {
            $title = (isset($row['title']) ? $row['title'] : null);
            echo "<li>";
            echo $title . " : ";
            $prev = false;
            //$message = (isset($row['title']) ? $row['title'] : null);
            if (go_get_loot_toggle( 'xp')){
                $loot = (isset($row['xp']) ? $row['xp'] : null);
                if ($loot > 0) {
                    go_display_shorthand_currency ( 'xp', $loot, true );
                    $prev = true;
                    //$name = go_get_loot_short_name('xp');
                   // echo " " . $loot . " " . $name;
                }
            }
            if (go_get_loot_toggle( 'gold')){
                $loot = (isset($row['gold']) ? $row['gold'] : null);
                if ($loot > 0) {
                    if ($prev){
                        echo ", ";
                    }
                    go_display_shorthand_currency ( 'gold', $loot,  true );
                    $prev = true;
                    //$name = go_get_loot_short_name('gold');
                   //echo " " . $loot . " " . $name;
                }
            }
            if (go_get_loot_toggle( 'health')){
                $loot = (isset($row['health']) ? $row['health'] : null);
                if ($loot > 0) {
                    if ($prev){
                        echo ", ";
                    }
                    go_display_shorthand_currency ( 'health', $loot,  true );
                    //$name = go_get_loot_short_name('health');
                    //echo " " . $loot . " " . $name;
                }
            }

            //$drop = get_option($drop);



            echo "</li>";
        }
        echo "</ul>";
    }

}

/**
 * @param $badges
 */
function go_display_task_badges_and_groups($badge_ids, $group_ids) {

    //the serialzed array in the outro
    if (is_serialized($badge_ids)) {
        $badge_ids_array = unserialize($badge_ids);//legacy badges saved as serialized array
    }
    //for the single badge at the top
    if (is_numeric($badge_ids)){
        $badge_ids_array[] = $badge_ids;
    }

    //the serialzed array in the outro
    if (is_serialized($group_ids)) {
        $group_ids_array = unserialize($group_ids);//legacy badges saved as serialized array
    }
    //for the single badge at the top
    if (is_numeric($group_ids)){
        $group_ids_array[] = $group_ids;
    }

    if(!empty($badge_ids_array) || !empty($group_ids_array)){
        echo "<div style='display:flex; justify-content: space-evenly;'>";
    }
    if(!empty($badge_ids_array)) {
        foreach ($badge_ids_array as $badge_id) {
            if (is_numeric($badge_id) && $badge_id != 0) {
                go_print_single_badge($badge_id);
            }
        }

    }
    if(!empty($group_ids_array)) {
        foreach ($group_ids_array as $group_id) {
            if (is_numeric($group_id) && $group_id != 0) {
                go_print_single_badge( $group_id, 'group');
            }
        }
    }
    if(!empty($badge_ids_array) || !empty($group_ids_array)){
        echo "</div>";
    }
}


/**
 * @param $custom_fields
 * @param $user_id
 * @param $post_id
 * @param $task_name
 * @param $top
 */
function go_display_rewards($custom_fields, $task_name, $user_id, $position, $post_id = null ) {


    $task_name = ucwords($task_name);
    $stage_count = $custom_fields['go_stages'][0];

    $stage_name = ucwords(get_option('options_go_tasks_stage_name_singular'));

    $xp_toggle = get_option( 'options_go_loot_xp_toggle' );
    $gold_toggle = get_option( 'options_go_loot_gold_toggle' );
    $health_toggle = get_option( 'options_go_loot_health_toggle' );
    $badges_toggle = get_option( 'options_go_badges_toggle' );
    $groups_toggle = get_option('options_go_groups_toggle');

    $xp_loot = 0;
    $gold_loot = 0;
    $health_loot = 0;
    $badges = array();
    $groups = array();
    echo "<div id='go_task_top'>";
    if($position === 'top'){
        echo "<span>This is a {$stage_count} {$stage_name} {$task_name}. Upon completion you will receive:</span>";


        $loot = go_task_loot($post_id);

        $xp_loot = $loot['xp'];
        $gold_loot = $loot['gold'];
        $health_loot = $loot['health'];
        $badges = $loot['badges'];
        $groups = $loot['groups'];

    }
    else if($position === 'bottom') {
        echo "<span>You earned:</span>";
        global $wpdb;
        $go_task_table_name = "{$wpdb->prefix}go_tasks";
        $loot = $wpdb->get_results("SELECT * FROM {$go_task_table_name} WHERE uid = {$user_id} AND post_id = {$post_id}");
        $loot = $loot[0];

        if ($xp_toggle) {
            $xp_loot = $loot->xp;
        }
        if ($gold_toggle) {
            $gold_loot = $loot->gold;
        }
        if ($health_toggle) {
            $health_loot = $loot->health;
        }
        if ($badges_toggle) {
            $badges = $loot->badges;
        }
        if ($groups_toggle) {
            $groups = $loot->groups;
        }


    }


    go_print_rewards($position, $user_id, $xp_loot, $gold_loot, $health_loot);
    /*echo "<div id='go_task_rewards'>
                    <div id='go_task_rewards_loot'><ul style='margin-bottom:0px'>";

                    if(get_option( 'options_go_loot_xp_toggle' )  && ($xp_loot > 0)){
                        echo "<li>";
                        //echo "{$xp_loot} {$xp_name} ";
                        //echo "<br>";
                        go_display_longhand_currency ( 'xp', $xp_loot, true );
                    }
                    if(get_option( 'options_go_loot_gold_toggle' ) && ($gold_loot > 0)){
                        echo "<li>";

                        go_display_longhand_currency ( 'gold', $gold_loot, true, false, false );
                        if($position === 'top') {
                            if (get_option('options_go_loot_health_toggle')) {
                                $health_mod = go_get_health_mod($user_id);
                                $health_abbr = get_option('options_go_loot_health_abbreviation');
                                $health_percent = $health_mod * 100;

                                echo "x $health_percent% ($health_abbr Modifier) = ";


                                if (get_option('options_go_loot_gold_toggle') && ($gold_loot > 0)) {
                                    //echo "<br>{$gold_loot} {$gold_name} ";
                                    $gold_loot = $gold_loot * $health_mod;
                                    go_display_longhand_currency('gold', $gold_loot, true, false, false);
                                }
                            }
                        }

                    }
                    if(get_option( 'options_go_loot_health_toggle' )  && ($health_loot > 0)){
                        echo "<li> ";

                        go_display_longhand_currency ( 'health', $health_loot, true, false );

                    }
                    echo "</ul></div>";

    echo "</div>";*/

    go_display_task_badges_and_groups($badges, $groups);

    /*
    $bonus_radio =(isset($custom_fields['bonus_loot_toggle'][0]) ? $custom_fields['bonus_loot_toggle'][0] : null);//is bonus set default, custom or off
    if ($bonus_radio == "1" || $bonus_radio == "default") {
        echo "<div id='go_bonus_loot_possibilites' style='font-size: .9em;'>";
        echo "Complete the {$task_name} for a chance at a bonus of: ";
        go_print_bonus_loot_possibilities($custom_fields, $user_id);
        echo "</div>";
    }
    */

    if($position === 'bottom'){
        $bonus_loot_radio = (isset($custom_fields['bonus_loot_toggle'][0]) ? $custom_fields['bonus_loot_toggle'][0] : false);//number of loot drops;
        if ($bonus_loot_radio == true || $bonus_loot_radio == 'default' ) {
            global $wpdb;
            $go_actions_table_name = "{$wpdb->prefix}go_actions";
            $previous_bonus_attempt = $wpdb->get_results($wpdb->prepare("SELECT * 
                FROM {$go_actions_table_name} 
                WHERE source_id = %d AND uid = %d AND action_type = %s
                ORDER BY id DESC LIMIT 1", $post_id, $user_id, 'bonus_loot'));
            //ob_start();
            if(empty($previous_bonus_attempt)) {
                go_bonus_loot($custom_fields, $user_id);
            }else{
                $result = $previous_bonus_attempt[0]->result;
                $bonus_xp =$previous_bonus_attempt[0]->xp;
                $bonus_gold = $previous_bonus_attempt[0]->gold;
                $bonus_health = $previous_bonus_attempt[0]->health;
                go_print_bonus_result($user_id, $post_id, $result, $bonus_xp, $bonus_gold, $bonus_health);
            }
        }
    }
    echo "</div>";




}

function go_print_rewards($position, $user_id = null, $xp_loot, $gold_loot, $health_loot){
    if(empty($user_id)){
        $user_id = get_current_user_id();
    }
    echo "<div id='go_task_rewards'>
                    <div id='go_task_rewards_loot'><ul style='margin-bottom:0px'>";

    if(get_option( 'options_go_loot_xp_toggle' )  && ($xp_loot > 0)){
        echo "<li>";
        //echo "{$xp_loot} {$xp_name} ";
        //echo "<br>";
        go_display_longhand_currency ( 'xp', $xp_loot, true );
    }
    if(get_option( 'options_go_loot_gold_toggle' ) && ($gold_loot > 0)){
        echo "<li>";

        go_display_longhand_currency ( 'gold', $gold_loot, true, false, false );
        if($position === 'top') {
            if (get_option('options_go_loot_health_toggle')) {
                $health_mod = go_get_health_mod($user_id);
                $health_abbr = get_option('options_go_loot_health_abbreviation');
                $health_percent = $health_mod * 100;

                echo "x $health_percent% ($health_abbr Modifier) = ";


                if (get_option('options_go_loot_gold_toggle') && ($gold_loot > 0)) {
                    //echo "<br>{$gold_loot} {$gold_name} ";
                    $gold_loot = $gold_loot * $health_mod;
                    go_display_longhand_currency('gold', $gold_loot, true, false, false);
                }
            }
        }

    }
    if(get_option( 'options_go_loot_health_toggle' )  && ($health_loot > 0)){
        echo "<li> ";

        go_display_longhand_currency ( 'health', $health_loot, true, false );

    }
    echo "</ul></div>";

    echo "</div>";
}


/**
 * Outputs the task chain navigation links for the specified task and user.
 *
 * Outputs a link to the next and previous tasks, if they exist. That is, the first task in the
 * chain will not have a "previous" link, and the last task will not have a "next" link. If the
 * task is the last in the chain, the final chain message (stored in the `go_mta_final_chain_message`
 * meta data) will be displayed.
 *
 * @since 3.0.0
 *
 * @param int $task_id The task ID.
 * @param int $user_id Optional. The user ID.
 */


function go_new_pagination ( $task_id, $custom_fields = null ) {

    if ( empty( $task_id ) ) {
        return;
    } else {
        $task_id = (int) $task_id;
    }

    if(empty($custom_fields)){
        $custom_fields = go_post_meta($task_id);
    }

    $chain_id = (isset($custom_fields['go-location_map_loc'][0]) ?  $custom_fields['go-location_map_loc'][0] : null);

    if (!empty($chain_id)) {
        $chain_order = go_get_chain_posts($chain_id, 'task_chains', false);
        if ( empty( $chain_order ) || ! is_array( $chain_order ) ) {
            return;
        }
        $this_task_order = array_search($task_id, $chain_order);
        if ($this_task_order == 0) {
            $prev_task = null;
        }
        else {
            $prev_found = false;
            $i = (int)$this_task_order;
            while(!$prev_found){
                $i--;
                if($i >= 0) {
                    $prev_task = $chain_order[$i];
                    $is_hidden = get_post_meta($prev_task, 'go-location_map_options_hidden');
                    $is_hidden = (isset($is_hidden[0]) ? $is_hidden[0] : false);
                    if ($is_hidden) {
                        $is_locked = go_task_locks($prev_task, true);
                        if ($is_locked) {
                            continue;
                        }
                    }
                }
                if (is_int($prev_task)){
                    $prev_title = go_the_title($prev_task);
                    $prev_link = go_post_permalink($prev_task);
                }
                $prev_found = true;
            }
        }

        $count = count($chain_order);
        $i = (int)$this_task_order;
        if ($count > $i){
            $next_found = false;
            while(!$next_found && $count > ($i+1)) {
                $i++;
                $next_task = $chain_order[$i];
                $is_hidden = get_post_meta($next_task, 'go-location_map_options_hidden');
                $is_hidden = (isset($is_hidden[0]) ?  $is_hidden[0] : false);
                if($is_hidden){
                    $is_locked = go_task_locks($next_task, true);
                    if($is_locked) {
                        continue;
                    }
                }
                if (is_int($next_task)) {
                    $next_title = go_the_title($next_task);
                    $next_link = go_post_permalink($next_task);
                }
                $next_found = true;
            }
        }

    } else {
        return false;
    }

    echo"<div id='go_task_pagination' style='height: 100px;'>";
    if (isset($prev_link)){
        echo "<div style='float: left;'><p>Previous:<br><a href='$prev_link'>$prev_title</a></p></div> ";
    }
    if (isset($next_link)){
        echo "<div style='float: right;'><p>Next Up:<br><a href='$next_link'>$next_title</a></p></div>";
    }
    echo "</div>";

}

