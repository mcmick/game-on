<?php

// Included on every load of this module

/*
//https://stackoverflow.com/questions/25310665/wordpress-how-to-create-a-rewrite-rule-for-a-file-in-a-custom-plugin
add_action('init', 'go_reader_page');
function go_reader_page(){
    $reader_name = 'reader22';
    //add_rewrite_rule( "store", 'index.php?query_type=user_blog&uname=$matches[1]', "top");
    //add_rewrite_rule( "reader", 'index.php?reader=true', "top");
    add_rewrite_rule( $reader_name, 'index.php?' . $reader_name . '=true', "top");
}

// Query Vars
//add_filter( 'query_vars', 'go_reader_register_query_var' );
function go_reader_register_query_var( $vars ) {
    $reader = 'reader22';
    $vars[] = $reader;
    return $vars;
}
*/
function filter_pagetitle($title) {
    //$title = get_bloginfo('name');
    $title = 'yup';
    return $title;
}

/* Template Include */
add_filter('template_include', 'go_reader_template_include', 1, 1);
function go_reader_template_include($template){
    $reader = 'reader';
    global $wp_query; //Load $wp_query object

    $is_admin = go_user_is_admin();

    if ($is_admin) {
        /*
        $page_value = (isset($wp_query->query_vars[$reader]) ? $wp_query->query_vars[$reader] : false); //Check for query var "blah"
        if ($page_value && $page_value == "true") { //Verify "blah" exists and value is "true".
            return plugin_dir_path(__FILE__) . 'templates/go_reader_template.php'; //Load your template or file
        }
        */

        $page_name = (isset($wp_query->query_vars['pagename']) ? $wp_query->query_vars['pagename'] : false);
        if ($page_name == $reader) { //Verify "blah" exists and value is "true".
            return plugin_dir_path(__FILE__) . 'templates/go_reader_template.php'; //Load your template or file
            remove_all_filters( 'wp_title' );
            add_filter('wp_title', 'filter_pagetitle');

        }



    }
        return $template; //Load normal template when $page_value != "true" as a fallback
}


add_action('go_blog_template_after_post', 'go_user_feedback_container', 10, 2);

function go_user_feedback_container($post_id, $show_form = true){
    $admin_user = go_user_is_admin();


    //go_blog_tags_select($post_id);
    ?>
    <div class="feedback_accordion" style="clear: both;">
        <?php go_blog_post_feedback_table($post_id); ?>
        <?php go_blog_post_history_table($post_id); ?>
        <?php
        if ($admin_user && $show_form) {
            ?>
            <h3>Feedback Form</h3>
            <div class="go_feedback_form_container">
            <?php
            go_feedback_form($post_id);
            ?>
            </div>
            <?php

        }
        ?>
    </div>
    <?php
}

function go_blog_post_feedback_table($post_id){
/*
    ?>
    <div class="go_blog_feedback">

        <div class="go_blog_feedback_container">
            <div class="go_feedback_table">
                <?php
                go_feedback_table($post_id);
                ?>
            </div>
        </div>
    </div>
    <?php

*/

    global $wpdb;
    $aTable = "{$wpdb->prefix}go_actions";
    //check last feedback, and if it exists, remove it
    $all_feedback = $wpdb->get_results($wpdb->prepare("SELECT id, result
                FROM {$aTable} 
                WHERE source_id = %d AND action_type LIKE %s
                ORDER BY id DESC",
        $post_id,
        '%feedback%'), ARRAY_A);


    if (count($all_feedback)>0){
        ?>
        <h3>Feedback</h3>
        <div class="go_blog_feedback">
            <div class="go_feedback_table_container">
               <?php go_feedback_table($all_feedback); ?>
            </div>
        </div>

        <?php
    }

}

function go_feedback_table($all_feedback){
    ?>
     <div class="go_feedback_table"  class='go_datatables'>
                        <table id='go_single_task_datatable' class='pretty display'>
                            <thead>
                            <tr>
                                <th class='header' id='go_stats_time'><a href=\"#\">Title</a></th>
                                <th class='header' id='go_stats_mods'><a href=\"#\">Message</a></th>
                                <th class='header' id='go_stats_mods'><a href=\"#\">Percent</a></th>
                                <?php
                                $i=0;
                                foreach($all_feedback as $feedback){
                                    $result = $feedback['result'];
                                    $result = unserialize($result);
                                    $title = $result[2];
                                    $message = $result[3];
                                    $percent = $result[5];


                                //$link = get_permalink($id);

                                ?>
                            <tr>
                                <td ><?php echo $title;?></td>
                                <td ><?php echo $message;?></td>
                                <td ><?php echo $percent;?></td>

                            </tr>
                            <?php
                            }

                            ?></tbody></table>
                </div>

    <?php
}

function go_feedback_form($post_id){
    ?>
        <div class="go_feedback_form">
            <div class="go_feedback_canned_container">
                <?php go_feedback_canned(); ?>
            </div>
            <div class="go_feedback_input">
                <?php go_feedback_input($post_id); ?>
            </div>

        </div>
    <?php
}

function go_post_status_icon($post_id){
    $status = get_post_status($post_id);
    $is_admin = go_user_is_admin();
    $icon ='';
    if ($post_id) {
        if ($status == 'read') {
            if ($is_admin) {
                $icon = '<a href="javascript:;" class="go_status_read_toggle" data-postid="' . $post_id . '"><span class="tooltip"  data-tippy-content="Status is read. Click to mark this post as unread."><i class="far fa-eye fa-2x" aria-hidden="true"></i><i class="fa fa-eye-slash fa-2x" aria-hidden="true" style="display: none;"></i></span></a>';
            }
            else{
                $icon = '<span class="tooltip"  data-tippy-content="Status is read. Click to mark this post as unread."><i class="far fa-eye fa-2x" aria-hidden="true"></i><i class="fa fa-eye-slash fa-2x" aria-hidden="true" style="display: none;"></i></span>';
            }
        } else if ($status == 'reset') {
            $icon = '<span class="tooltip" data-tippy-content="This post has been reset."><i class="fas fa-times-circle fa-2x" aria-hidden="true"></i></span>';
        } else if ($status == 'unread' && $is_admin == true) {
            if ($is_admin) {
                $icon = '<a href="javascript:;" class="go_status_read_toggle" data-postid="' . $post_id . '" ><span class="tooltip" data-tippy-content="Status is unread. Click to mark this post as read."><i class="far fa-eye-slash fa-2x" aria-hidden="true"></i><i class="fa fa-eye fa-2x" aria-hidden="true" style="display: none;"></i></span></a>';
            }
            else{
                $icon = '<span class="tooltip" data-tippy-content="Status is unread. Click to mark this post as read."><i class="far fa-eye-slash fa-2x" aria-hidden="true"></i><i class="fa fa-eye fa-2x" aria-hidden="true" style="display: none;"></i></span>';

            }
        } else if ($status == 'draft') {
            $icon = '<span class="tooltip" data-tippy-content="This post is a draft."><i class="fas fa-pencil-alt fa-2x" aria-hidden="true"></i></span>';
        } else if ($status == 'trash') {
            $icon = '<span class="tooltip" data-tippy-content="This post is in the trash."><i class="fas fa-trash fa-2x" aria-hidden="true"></i></span>';
        }

        $user_statuses = array("read", "reset", "draft", "trash");
        if (!empty($status)) {
            if ((in_array($status, $user_statuses) || ($is_admin && $status == 'unread'))) {
                return '<div class="go_status_icon" >' . $icon . '</div>';
            }
        }
    }
}

function go_blog_is_private($post_id){

    //$blog_meta = get_post_custom($post_id);
    //$status = (isset($blog_meta['go_blog_private_post'][0]) ? $blog_meta['go_blog_private_post'][0] : false);
    $status = get_post_meta($post_id, 'go_blog_private_post', true );
    if ($status) {

        //$status = get_post_status($post_id);
        return '<div class="go_blog_visibility" ><span class="tooltip" data-tippy-content="This is a private post.  It is only viewable by the author and site administrators."><i class="fas fa-user-secret fa-2x" aria-hidden="true"></i></span></div>';
    }
}

function go_blog_favorite($post_id){

        $status = get_post_meta($post_id, 'go_blog_favorite', true );
        if ($status == 'true'){
            $checked = 'checked';
        }else{
            $checked = '';
        }
        //echo "<div style=''><input type='checkbox' class='go_blog_favorite ' value='go_blog_favorite' data-post_id='{$post_id}' {$checked}> Favorite</div>";



        return "<div class='go_favorite_container'><label><input type='checkbox' class='go_blog_favorite ' value='go_blog_favorite' data-post_id='".$post_id."' ".$checked."> <span class='go_favorite_label'></span></label></div>";

}

function go_blog_favorite_toggle(){
    if ( !is_user_logged_in() ) {
        echo "login";
        die();
    }
    //check_ajax_referer( 'go_blog_favorite_toggle' );
    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_blog_favorite_toggle' ) ) {
        echo "refresh";
        die( );
    }
    $post_id = !empty($_POST['blog_post_id']) ? intval($_POST['blog_post_id']) : false;
    $status = !empty($_POST['checked']) ? $_POST['checked'] : false;
    update_post_meta( $post_id, 'go_blog_favorite', $status);


}

function go_blog_tags_select($post_id){
    ?>
    <form name="primaryTagForm" id="primaryTagForm" method="POST" enctype="multipart/form-data" >
        <fieldset>
            <span><label for="go_feedback_go_blog_tags_select">Tags </label><?php go_make_tax_select('_go_blog_tags' , "feedback", 'class'); ?></span>
            <button class="button" type="submit"><?php _e('Tag ', 'framework') ?></button>
        </fieldset>
    </form>
    <?php
}

function go_blog_post_history_table($post_id){

    $revisions = wp_get_post_revisions($post_id);

    if (count($revisions)>1){
        ?>
        <h3>Revision History</h3>
        <div class="go_blog_revisions">
            <div class="go_blog_revisions_container">
                <div class="go_history_table"  class='go_datatables'>
                    <table id='go_single_task_datatable' class='pretty display'>
                    <thead>
                    <tr>
                        <th class='header' id='go_stats_time'><a href=\"#\">Date and Time</a></th>
                        <th class='header' id='go_stats_mods'><a href=\"#\">View</a></th>
                            <?php
                            $i=0;
                            foreach($revisions as $revision){

                                if($i == 0){
                                    $i = 1;
                                    continue;
                                }
                                $id = $revision -> ID ;
                                //$link = get_permalink($id);
                                $time = $revision -> post_date ;
                                $time = go_clipboard_time($time);
                                ?>
                                <tr>
                                    <td ><?php echo $time;?></td>
                        <td><a class="go_blog_revision" blog_post_id="<?php echo $id; ?>" href="javascript:;">View</a> </td>

                                </tr>
                                <?php
                            }

                               ?></tbody></table>
                        </div>
                    </div>
                </div>

                        <?php
    }

}

function go_feedback_canned(){
    echo "<select class='go_feedback_canned'>";
    echo "<option>Canned Feedback</option>";
    $num_preset = get_option('options_go_feedback_canned');
    $i = 0;
    while ($i < $num_preset){
        $title = get_option('options_go_feedback_canned_'.$i.'_title');
        $title = htmlspecialchars($title);
        $message = get_option('options_go_feedback_canned_'.$i.'_message');
        $message = htmlspecialchars($message);
        $radio = get_option('options_go_feedback_canned_'.$i.'_adjust');
        $toggle_assign = get_option('options_go_feedback_canned_'.$i.'_defaults_toggle');
        $xp = get_option('options_go_feedback_canned_'.$i.'_assign_loot_xp');
        $gold = get_option('options_go_feedback_canned_'.$i.'_assign_loot_gold');
        $health = get_option('options_go_feedback_canned_'.$i.'_assign_loot_health');
        $toggle_percent = get_option('options_go_feedback_canned_'.$i.'_percent_toggle');
        $percent = get_option('options_go_feedback_canned_'.$i.'_percent_percent');

        echo "<option class='go_feedback_option' value='{$i}' data-title='{$title}' data-message='{$message}' data-radio='{$radio}' data-toggle_assign='{$toggle_assign}' 
                data-xp='{$xp}' data-gold='{$gold}' data-health='{$health}' data-toggle_percent='{$toggle_percent}' data-percent='{$percent}'>{$title} </option>";
        $i++;
    }
    echo "</select>";
}

function go_feedback_input($post_id){

    ?>
    <div id="go_messages_container">
        <form method="post">
            <div class="go_messages" style="display:flex;">

                <div class="messages_form">
                    <table class="form-table">

                        <tr valign="top">
                            <th scope="row">Title</th>
                            <td style="width: 100%;"><input class="go_title_input" type="text" name="title" value="" style="width: 100%;"/>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">Message</th>
                            <td><textarea name="message" class="widefat go_message_input" cols="50" rows="5"></textarea></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row" colspan="2">Current Awards</th>

                        </tr>
                        <tr>
                            <td colspan="2">
                                <?php
                                //get the current % and the latest loot awarded.
                                $percent = get_post_meta( $post_id, 'go_feedback_percent', true );

                                global $wpdb;
                                $aTable = "{$wpdb->prefix}go_actions";

                                $go_blog_task_id = go_get_task_id($post_id);
                                $result = $wpdb->get_results($wpdb->prepare("SELECT id, uid, xp, gold, health
                                    FROM {$aTable} 
                                    WHERE result = %d AND source_id = %d AND action_type = %s
                                    ORDER BY id DESC LIMIT 1",
                                    $post_id,
                                    $go_blog_task_id,
                                    'task'), ARRAY_A);

                                //get original loot assigned on this stage--this is the baseline
                                $xp = $result[0]['xp'];
                                $gold = $result[0]['gold'];
                                $health = $result[0]['health'];

                                if (($xp && $xp != 0)||($gold && $gold != 0)||($health && $health != 0)) {
                                    echo "Awards: ";
                                }
                                if ($xp && $xp != 0) {
                                    go_display_shorthand_currency('xp', $xp, true);
                                    echo " &nbsp; &nbsp;";
                                }
                                if ($gold && $gold != 0) {
                                    go_display_shorthand_currency('gold', $gold, true);
                                    echo " &nbsp; &nbsp;";
                                }
                                if ($health && $health != 0) {
                                    go_display_shorthand_currency('health', $health, true);
                                    echo " &nbsp; &nbsp;";
                                }
                                if ($percent && $percent != 0) {
                                    if ($percent > 0){
                                        $class = 'up';
                                        $direction = "+";
                                    }else{
                                        $class = 'down';
                                        $direction = "";
                                    }

                                    echo "<br>Adjusted: <span class='go_status_percent ".$class."'> ".$direction.$percent . '%</span>';
                                    echo " &nbsp; &nbsp;";
                                }


                                ?>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row" colspan="2">Adjust Awards</th>

                        </tr>
                        <tr valign="top">

                            <td colspan="2">
                                <input id="loot_option_none_<?php echo $post_id;?>" class="loot_option_none" type="radio" name="loot_option" value="none" checked> <label for="loot_option_none_<?php echo $post_id;?>"> None </label> &nbsp; &nbsp;
                                <input id="loot_option_percent_<?php echo $post_id;?>" class="loot_option_percent" type="radio" name="loot_option" value="percent"><label for="loot_option_percent_<?php echo $post_id;?>"> Percentage Adjustment </label> &nbsp; &nbsp;
                                <input id="loot_option_assign_<?php echo $post_id;?>" class="loot_option_assign" type="radio" name="loot_option" value="assign"><label for="loot_option_assign_<?php echo $post_id;?>"> Assign Loot </label>
                            </td>
                        </tr>
                        <tr class="go_feedback_percent_loot" style="display: none;">
                            <td colspan="2">
                                <div class="go-acf-field go-acf-field-group go_loot_table" data-type="group">
                                    <div class="go-acf-input">
                                        <div class="go-acf-fields -top -border">

                                                <div class="go-acf-input">
                                                    <table class="go-acf-table">

                                                        <tbody>
                                                        <tr class="go-acf-row">
                                                            <td class="go-acf-field go-acf-field-true-false go_reward go_feedback_percent_toggle"
                                                                data-name="xp" data-type="true_false">
                                                                <div class="go-acf-input">
                                                                    <div class="go-acf-true-false">
                                                                        <input value="0" type="hidden">
                                                                        <label>
                                                                            <input name="xp_toggle" type="checkbox" value="1"
                                                                                   class="go-acf-switch-input go_toggle_input go_feedback_toggle">
                                                                            <div class="go-acf-switch"><span class="go-acf-switch-on"
                                                                                                             style="min-width: 36px;">+</span><span
                                                                                        class="go-acf-switch-off"
                                                                                        style="min-width: 36px;">-</span>
                                                                                <div class="go-acf-switch-slider"></div>
                                                                            </div>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td class="go-acf-field go-acf-field-number go_reward go_percent  data-name="
                                                                %
                                                            " data-type="number">
                                                            <div class="go-acf-input">
                                                                <div class="go-acf-input-wrap"><input class="feedback_percent_input go_percent_input" name="percent" type="number"
                                                                                                      value="0" min="0" max="100' step="1" oninput="validity.valid||(value='');">%
                                                                </div>
                                                            </div>
                                                            </td>

                                                        </tr>

                                                        <tr class="go-acf-row">


                                                        </tr>

                                                        </tbody>
                                                    </table>
                                                </div>

                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr class="go_feedback_assign_loot" style="display: none;">

                            <td colspan="2">
                                <div class="go-acf-input go_loot_table">
                                    <div class="go-acf-true-false">
                                        <input value="0" type="hidden">
                                        <label>
                                            <input name="gold_toggle" type="checkbox"
                                                   class="go-acf-switch-input go_messages_toggle_input">
                                            <div class="go-acf-switch"><span class="go-acf-switch-on"
                                                                             style="min-width: 36px;">+</span><span
                                                        class="go-acf-switch-off"
                                                        style="min-width: 36px;">-</span>
                                                <div class="go-acf-switch-slider"></div>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <div  class="go-acf-field go-acf-field-group go_loot_table" data-type="group">
                                    <div class="go-acf-input">
                                        <div class="go-acf-fields -top -border">

                                            <div class="go-acf-input">
                                                <table class="go-acf-table">
                                                    <thead>
                                                    <tr>
                                                        <th>
                                                            <div class="go-acf-th">
                                                                <label><?php echo go_get_loot_short_name('xp');?></label></div>
                                                        </th>
                                                        <th>
                                                            <div class="go-acf-th">
                                                                <label><?php echo go_get_loot_short_name('gold');?></label></div>
                                                        </th>
                                                        <th>
                                                            <div class="go-acf-th">
                                                                <label><?php echo go_get_loot_short_name('health');?></label></div>
                                                        </th>

                                                    </tr>


                                                    </thead>
                                                    <tbody>


                                                    <tr class="go-acf-row">
                                                        <td class="go-acf-field go-acf-field-number go_reward go_xp  data-name="
                                                            xp
                                                        " data-type="number">
                                                        <div class="go-acf-input">
                                                            <div class="go-acf-input-wrap"><input name="xp" type="number"
                                                                                                  value="" min="0" step="1" placeholder="0" class="xp_messages go_messages_xp_input" oninput="validity.valid||(value='');">
                                                            </div>
                                                        </div>
                                                        </td>
                                                        <td class="go-acf-field go-acf-field-number go_reward go_gold"
                                                            data-name="gold" data-type="number">
                                                            <div class="go-acf-input">
                                                                <div class="go-acf-input-wrap"><input name="gold" type="number"
                                                                                                      value="" min="0"
                                                                                                      step=".01" placeholder="0" class="gold_messages go_messages_gold_input" oninput="validity.valid||(value='');"></div>
                                                            </div>
                                                        </td>
                                                        <td class="go-acf-field go-acf-field-number go_reward go_health "
                                                            data-name="health" data-type="number">
                                                            <div class="go-acf-input">
                                                                <div class="go-acf-input-wrap"><input name="health"
                                                                                                      type="number" value=""
                                                                                                      min="0" step=".01" placeholder="0" class="health_messages go_messages_health_input" oninput="validity.valid||(value='');"></div>
                                                            </div>
                                                        </td>
                                                    </tr>

                                                    </tbody>
                                                </table>
                                            </div>

                                        </div>
                                    </div>
                                </div>

                            </td>
                        </tr>

                    </table>
                    <p><input type="button" class="button button-primary go_send_feedback" value="Send" data-postid="<?php echo $post_id;?>"></p>
                </div>


            </div>
        </form>

    </div>

    <?php
}
?>