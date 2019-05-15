<?php

//Redirect to homepage after logout
add_action('wp_logout','auto_redirect_after_logout');
function auto_redirect_after_logout(){
  wp_redirect( home_url() );
  exit();
}

//remove_menu_page( 'index.php' );
//add_action( 'admin_bar_menu', 'remove_wp_admin_items', 999 );
function remove_wp_admin_items( $wp_admin_bar ) {
	$wp_admin_bar->remove_node( 'wp-logo' );	
	if ( ! get_option('go_dashboard_toggle') && ! current_user_can('administrator') && ! is_admin()){
		$wp_admin_bar->remove_node( 'site-name' );
	}	
}

//remove dashboard
//add_action( 'admin_menu', 'Wps_remove_tools', 99 );
function Wps_remove_tools(){
	if ( ! get_option('go_dashboard_toggle') && ! current_user_can('administrator') ){
		remove_menu_page( 'index.php' ); //dashboard
		
	}
}

function go_display_admin_bar() {
    $is_admin = go_user_is_admin();
	if($is_admin){
	    return true;
    }
	else{
	    return false;
    }
}
add_filter( 'show_admin_bar', 'go_display_admin_bar' );


/**
 * Redirect back to homepage and not allow access to
 * WP admin for Subscribers.
 */
function go_redirect_admin(){
    if ( ! defined('DOING_AJAX') && ! current_user_can('edit_posts') ) {
        wp_redirect( site_url() );
        exit;
    }
}
add_action( 'admin_init', 'go_redirect_admin' );

/*
 * Admin Menu & Admin Bar
 */
//add_action( 'admin_bar_init', 'go_admin_bar' );
function go_admin_bar() {
	global $wp_admin_bar;

	//get options for what to show
	$go_search_switch = get_option( 'options_go_search_toggle' );
	$go_map_switch = get_option( 'options_go_locations_map_toggle' );
	$go_store_switch = get_option( 'options_go_store_toggle' );
    $go_stats_switch = get_option( 'options_go_stats_toggle' );
    $go_blog_switch = get_option('options_go_blogs_toggle');


    $user_id = get_current_user_id();
    $is_admin = go_user_is_admin($user_id);

    if(is_admin_bar_showing()) {

        if (!is_user_logged_in()) {
            $wp_admin_bar->add_node(
                array(
                    'id' => 'go_toolbar_login',
                    'title' => 'Login',
                    'href' => wp_login_url()
                )
            );
        }

        if (is_user_logged_in()) {
            $wp_admin_bar->remove_menu('wp-logo');

            /**
             * If is admin, show the dropdown for view type
             */
            if ($is_admin) {
                $post_type = get_post_type();
                $admin_view = get_user_option('go_admin_view', $user_id);
                if (!empty ($admin_view)) {
                    if ($admin_view == 'all') {
                        $all_selected = 'selected = "selected"';
                    } else {
                        $all_selected = null;
                    }
                    if ($admin_view == 'player') {
                        $player_selected = 'selected = "selected"';
                    } else {
                        $player_selected = null;
                    }
                    if ($admin_view == 'user') {
                        $user_selected = 'selected = "selected"';
                    } else {
                        $user_selected = null;
                    }
                    if ($admin_view == 'guest') {
                        $guest_selected = 'selected = "selected"';
                    } else {
                        $guest_selected = null;
                    }

                } else {
                    $all_selected = null;
                    $player_selected = null;
                    $user_selected = null;
                    $guest_selected = null;
                }
                $content = '<form>
                            View: <select id="go_select_admin_view" onchange="go_update_admin_view(this.value)">
                                <option value="user" ' . $user_selected . '>Player Mode: Locks On</option>
                                <option value="player" ' . $player_selected . '>Admin Mode: No Locks</option>
                                <option value="all" ' . $all_selected . ' >All Stages</option>
                                <option value="guest" ' . $guest_selected . '>Guest</option>
                            </select>
                        </form>';
                if ($post_type == 'tasks') {
                    $wp_admin_bar->add_menu(array('id' => 'go_admin_view_form', 'parent' => 'top-secondary', 'title' => $content));
                }
            }

            /**
             * Get the percentage for the XP Bar/health Bar and the Loot for the totals
             * Show bars and create dropdown
             */
            $xp_toggle = get_option('options_go_loot_xp_toggle');
            $gold_toggle = get_option('options_go_loot_gold_toggle');
            $health_toggle = get_option('options_go_loot_health_toggle');

            $user_loot = go_get_loot($user_id);

            if ($xp_toggle) {
                // the user's current amount of experience (points)
                //$go_current_xp = go_get_user_loot($user_id, 'xp');
                $go_current_xp = $user_loot['xp'];

                $rank = go_get_rank($user_id);
                $rank_num = $rank['rank_num'];
                $current_rank = $rank['current_rank'];
                $current_rank_points = $rank['current_rank_points'];
                $next_rank = $rank['next_rank'];
                $next_rank_points = $rank['next_rank_points'];

                $go_ranks_name = get_option('options_go_loot_xp_levels_name_singular');
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
                if ($percentage <= 0) {
                    $percentage = 0;
                } else if ($percentage >= 100) {
                    $percentage = 100;
                }
                $progress_bar = '<div id="go_admin_bar_progress_bar_border" class="progress-bar-border">' . '<div id="go_admin_bar_progress_bar" class="progress_bar" ' .
                    'style="width: ' . $percentage . '%; background-color: ' . $color . ' ;">' .
                    '</div>' .
                    '<div id="points_needed_to_level_up" class="go_admin_bar_text">' .
                    $pts_to_rank_up_str .
                    '</div>' .
                    '</div>';
            } else {
                $progress_bar = '';
                $go_current_xp = '';
                $current_rank = '';
                $rank_num = '';
                $go_ranks_name = '';
            }

            if ($gold_toggle) {
                // the user's current amount of currency
                //$go_current_gold = go_get_user_loot($user_id, 'gold');
                $go_current_gold = $user_loot['gold'];
                $gold_total = '<div id="go_admin_bar_gold_2" class="admin_bar_loot">' . go_display_shorthand_currency('gold', $go_current_gold, false) . '</div>';
            } else {
                $gold_total = '';
                $go_current_gold = '';
                //$current_rank ='';
                //$rank_num ='';
                //$go_option_ranks ='';
            }

            if ($health_toggle) {
                // the user's current amount of bonus currency,
                // also used for coloring the admin bar
                //$go_current_health = go_get_user_loot($user_id, 'health');
                $go_current_health = $user_loot['health'];
                $health_percentage = intval($go_current_health / 2);
                $name = get_option("options_go_loot_health_abbreviation");;
                if ($health_percentage <= 0) {
                    $health_percentage = 0;
                } else if ($health_percentage >= 100) {
                    $health_percentage = 100;
                }
                $health_bar = '<div id="go_admin_health_bar_border" class="progress-bar-border">' . '<div id="go_admin_bar_health_bar" class="progress_bar" ' . 'style="width: ' . $health_percentage . '%; background-color: red ;">' . '</div>' . '<div id="health_bar_percentage_str" class="go_admin_bar_text">' . $name . ": " . $go_current_health . "%" . '</div>' . '</div>';

            } else {
                $health_bar = '';
                $go_current_health = '';
            }


            $wp_admin_bar->add_node(
                array(
                    'id' => 'go_info',
                    'title' =>
                        '<div style="padding-top:5px;">' .
                        $progress_bar .
                        $gold_total .
                        $health_bar .
                        '</div>',
                    'href' => '#',
                )
            );


            if ($xp_toggle && ($go_current_xp != 0)) {
                $wp_admin_bar->add_node(array('id' => 'go_rank', 'title' => '<div id="go_admin_bar_rank">' . $go_ranks_name . ' ' . $rank_num . ": " . $current_rank . '</div>', 'href' => '#', 'parent' => 'go_info',));

                $wp_admin_bar->add_node(array('id' => 'go_xp', 'title' => '<div id="go_admin_bar_xp">' . go_display_longhand_currency('xp', $go_current_xp) . '</div>', 'href' => '#', 'parent' => 'go_info',));
            }

            if ($gold_toggle && ($go_current_gold != 0)) {
                $wp_admin_bar->add_node(array('id' => 'go_gold', 'title' => '<div id="go_admin_bar_gold">' . go_display_longhand_currency('gold', $go_current_gold, false, true,false) . '</div>', 'href' => '#', 'parent' => 'go_info',));
            }

            if ($health_toggle && $go_current_health) {
                $wp_admin_bar->add_node(array('id' => 'go_health', 'title' => '<div id="go_admin_bar_health">' . go_display_longhand_currency('health', $go_current_health) . '</div>', 'href' => '#', 'parent' => 'go_info',));
            }

            if ($go_stats_switch) {
                //acf_form_head();
                $stats_name = get_option('options_go_stats_name');
                $wp_admin_bar->add_node(

                    array('id' => 'go_stats', 'title' => '<i class="fas fa-chart-area ab-icon" aria-hidden="true"></i><div style="float: right;">' . $stats_name . '</div><div id="go_stats_page"></div><script>  jQuery("#wp-admin-bar-go_stats").one("click", function(){ go_admin_bar_stats_page_button()}); </script>', 'href' => '#',));
            };

            if ($go_blog_switch) {
                //acf_form_head();
                //$stats_name = get_option('options_go_stats_name');

                $user_info = get_userdata($user_id);
                $userloginname = $user_info->user_login;
                $user_blog_link = get_site_url(null, '/user/' . $userloginname);

                $wp_admin_bar->add_node(
                    array('id' => 'go_blog_menu_link', 'title' => '<i class="fas fa-thumbtack ab-icon" aria-hidden="true"></i><div style="float: right;">My Blog</div>', 'href' => $user_blog_link,));
            };
        }

        if ($go_map_switch) {
            $map_url = get_option('options_go_locations_map_map_link');
            $go_map_link = (string)$map_url;
            //$go_map_link = get_permalink(get_page_by_path($go_map_link));
            $go_map_link = get_site_url(null, $go_map_link);
            $name = get_option('options_go_locations_map_name');
            $wp_admin_bar->add_node(
                array(
                    'id' => 'go_map',
                    'title' => '<i class="fas fa-sitemap ab-icon" aria-hidden="true"></i><div id="go_map_page" class="admin_map" style="float: right;" >' . $name . '</div>',
                    'href' => $go_map_link,
                )
            );
        };

        if ($go_store_switch) {
            $go_store_link = get_option('options_go_store_store_link');
            //$go_store_link = get_permalink(get_page_by_path($go_store_link));
            $go_store_link = get_site_url(null, $go_store_link);
            $name = get_option('options_go_store_name');
            $wp_admin_bar->add_node(
                array(
                    'id' => 'go_store',
                    'title' => '<i class="fas fa-store ab-icon" aria-hidden="true"></i><div id="go_store_page" style="float: right;">' . $name . '</div>',
                    'href' => $go_store_link,
                )
            );
        };


        if ($is_admin) {

            //$go_store_link = get_permalink(get_page_by_path($go_store_link));
            $reader_link = get_site_url(null, 'reader');
            $wp_admin_bar->add_node(
                array(
                    'id' => 'go_reader',
                    'title' => '<i class="fas fa-book-reader ab-icon" aria-hidden="true"></i><div id="go_reader_link" style="float: right;">Reader</div>',
                    'href' => $reader_link,
                )
            );
        }

        if ($go_search_switch) {
            $wp_admin_bar->add_node(
                array(
                    'id' => 'go_task_search',
                    'title' => '
                    <form role="search" method="get" id="go_admin_bar_task_search_form" class="searchform" action="' . home_url('/') . '">
                        <div><label class="screen-reader-text" for="s">' . __('Search for:') . '</label>
                            <input type="text" value="' . get_search_query() . '" name="s" id="go_admin_bar_task_search_input" placeholder="Search for ' . strtolower(get_option("go_tasks_plural_name")) . '..."/>
                            <input type="hidden" name="post_type[]" value="tasks"/>
                            <input type="submit" id="go_admin_bar_task_search_submit" value="' . esc_attr__('Search') . '"/>
                        </div>
                    </form>',
                )
            );
        };

        if ($is_admin) {
            $wp_admin_bar->add_node(array('id' => 'go_clipboard', 'title' => '<i class="fas fa-clipboard-list ab-icon" aria-hidden="true"></i><div id="go_clipboard_adminbar" style="float: right;">Clipboard</div>', 'href' => get_admin_url() . 'admin.php?page=go_clipboard',));
        }
        if ($is_admin) {
            $wp_admin_bar->add_group(
                array(
                    'id' => 'go_site_name_menu',
                    'parent' => 'site-name',
                    'meta' => array('class' => 'go_site_name_menu')
                )
            );

            /*
             * Game On Links
             */

            // displays GO options page link
            $wp_admin_bar->add_node(
                array(
                    'id' => 'go_nav_options',
                    'title' => 'Game-On Options',
                    'href' => get_admin_url() . 'admin.php?page=go_options',
                    'parent' => 'go_site_name_menu',
                    'meta' => array('class' => 'go_site_name_menu_item')
                )
            );

            // displays Clipboard link
            $wp_admin_bar->add_node(
                array(
                    'id' => 'go_nav_clipboard',
                    'title' => 'Clipboard',
                    'href' => get_admin_url() . 'admin.php?page=go_clipboard',
                    'parent' => 'go_site_name_menu',
                    'meta' => array('class' => 'go_site_name_menu_item')
                )
            );


            // displays Task edit page link
            $wp_admin_bar->add_node(
                array(
                    'id' => 'go_nav_tasks',
                    'title' => get_option('options_go_tasks_name_plural'),
                    'href' => get_admin_url() . 'edit.php?post_type=tasks',
                    'parent' => 'go_site_name_menu',
                    'meta' => array('class' => 'go_site_name_menu_item')
                )
            );

            // displays chains page link
            $wp_admin_bar->add_node(
                array(
                    'id' => 'go_nav_chains',
                    'title' => get_option('options_go_tasks_name_plural') . ' Maps',
                    'href' => esc_url(get_admin_url()) . 'edit-tags.php?taxonomy=task_chains&post_type=tasks',
                    'parent' => 'go_site_name_menu',
                    'meta' => array('class' => 'go_site_name_menu_item')
                )
            );

            // displays Store Item edit page link
            $wp_admin_bar->add_node(
                array(
                    'id' => 'go_nav_store',
                    'title' => get_option('options_go_store_name') . ' Items',
                    'href' => get_admin_url() . 'edit.php?post_type=go_store',
                    'parent' => 'go_site_name_menu',
                    'meta' => array('class' => 'go_site_name_menu_item')
                )
            );

            // displays Store Categories page link
            $wp_admin_bar->add_node(
                array(
                    'id' => 'go_nav_store_types',
                    'title' => get_option('options_go_store_name') . ' Categories',
                    'href' => esc_url(get_admin_url()) . 'edit-tags.php?taxonomy=store_types&post_type=go_store',
                    'parent' => 'go_site_name_menu',
                    'meta' => array('class' => 'go_site_name_menu_item')
                )
            );

            // displays Badges
            $badges_toggle = get_option('options_go_badges_toggle');
            if ($badges_toggle) {
                $wp_admin_bar->add_node(array('id' => 'go_nav_badges', 'title' => get_option('options_go_badges_name_plural'), 'href' => esc_url(get_admin_url()) . 'edit-tags.php?taxonomy=go_badges', 'parent' => 'go_site_name_menu', 'meta' => array('class' => 'go_site_name_menu_item')));
            }

            // displays Store Categories page link
            $wp_admin_bar->add_node(
                array(
                    'id' => 'go_nav_users',
                    'title' => 'Users',
                    'href' => esc_url(get_admin_url()) . 'users.php',
                    'parent' => 'go_site_name_menu',
                    'meta' => array('class' => 'go_site_name_menu_item')
                )
            );

            // displays Store Categories page link
            $wp_admin_bar->add_node(
                array(
                    'id' => 'go_nav_user_types',
                    'title' => 'User Groups',
                    'href' => esc_url(get_admin_url()) . 'edit-tags.php?taxonomy=user_go_groups',
                    'parent' => 'go_site_name_menu',
                    'meta' => array('class' => 'go_site_name_menu_item')
                )
            );


            /*
             * Default WP Links
             */

            // displays Post edit page link
            $wp_admin_bar->add_node(
                array(
                    'id' => 'go_nav_posts',
                    'title' => 'Posts',
                    'href' => esc_url(get_admin_url()) . 'edit.php',
                    'parent' => 'appearance'
                )
            );

            // displays Page edit page link
            $wp_admin_bar->add_node(
                array(
                    'id' => 'go_nav_pages',
                    'title' => 'Pages',
                    'href' => esc_url(get_admin_url()) . 'edit.php?post_type=page',
                    'parent' => 'appearance'
                )
            );

            // displays Media Library page link
            $wp_admin_bar->add_node(
                array(
                    'id' => 'go_nav_media',
                    'title' => 'Media',
                    'href' => esc_url(get_admin_url()) . 'upload.php',
                    'parent' => 'appearance'
                )
            );

            // displays Plugins page link
            $wp_admin_bar->add_node(
                array(
                    'id' => 'go_nav_plugins',
                    'title' => 'Plugins',
                    'href' => esc_url(get_admin_url()) . 'plugins.php',
                    'parent' => 'appearance'
                )
            );

            // displays Users page link
            /*
            $wp_admin_bar->add_node(
                array(
                    'id' => 'go_nav_users',
                    'title' => 'Users',
                    'href' => esc_url( get_admin_url() ).'users.php',
                    'parent' => 'appearance',
                )
            );
            */
        }

        if (is_user_logged_in()) {
            //displays Timer in admin bar
            $post_id = get_the_ID();
            $timer_on = get_post_meta($post_id, 'go_timer_toggle', true);
            if ($timer_on) {

                $atts = shortcode_atts(array(
                    'id' => '', // ID defined in Shortcode
                    'cats' => '', // Cats defined in Shortcode
                ), '');
                $id = $atts['id'];
                $custom_fields = get_post_custom($id); // Just gathering some data about this task with its post id

                $wp_admin_bar->add_node(
                    array(
                        'id' => 'go_timer',
                        'title' => '<div id="go_timer"><i class="fa fa-clock-o ab-icon" aria-hidden="true"></i><div><span class="days"></span>d : </div><div><span class="hours"></span>h : </div><div><span class="minutes"></span>m : </div><div><span class="seconds"></span>s</div></div>',
                    )
                );
            }
        }
    }

}

add_action( 'admin_bar_menu', 'go_admin_bar_v5', 90);
function go_admin_bar_v5() {
    global $wp_admin_bar;

    $user_id = get_current_user_id();
    $is_admin = go_user_is_admin($user_id);

    if(is_admin_bar_showing()) {

        if (!is_user_logged_in()) {
            $wp_admin_bar->add_node(
                array(
                    'id' => 'go_toolbar_login',
                    'title' => 'Login',
                    'href' => wp_login_url()
                )
            );
        }

        if (is_user_logged_in()) {
           // $wp_admin_bar->remove_menu('wp-logo');

            /**
             * If is admin, show the dropdown for view type
             */
            if ($is_admin) {
                $post_type = get_post_type();
                $admin_view = get_user_option('go_admin_view', $user_id);
                if (!empty ($admin_view)) {
                    if ($admin_view == 'all') {
                        $all_selected = 'selected = "selected"';
                    } else {
                        $all_selected = null;
                    }
                    if ($admin_view == 'player') {
                        $player_selected = 'selected = "selected"';
                    } else {
                        $player_selected = null;
                    }
                    if ($admin_view == 'user') {
                        $user_selected = 'selected = "selected"';
                    } else {
                        $user_selected = null;
                    }
                    if ($admin_view == 'guest') {
                        $guest_selected = 'selected = "selected"';
                    } else {
                        $guest_selected = null;
                    }

                } else {
                    $all_selected = null;
                    $player_selected = null;
                    $user_selected = null;
                    $guest_selected = null;
                }
                $content = '<form>
                            View: <select id="go_select_admin_view" onchange="go_update_admin_view(this.value)">
                                <option value="user" ' . $user_selected . '>Player Mode: Locks On</option>
                                <option value="player" ' . $player_selected . '>Admin Mode: No Locks</option>
                                <option value="all" ' . $all_selected . ' >All Stages</option>
                                <option value="guest" ' . $guest_selected . '>Guest</option>
                            </select>
                        </form>';
                if ($post_type == 'tasks') {
                    $wp_admin_bar->add_menu(array('id' => 'go_admin_view_form', 'parent' => 'top-secondary', 'title' => $content));
                }
            }
        }


        if ($is_admin) {
            $wp_admin_bar->add_node(array('id' => 'go_section_pipe', 'title' => ' | ', 'href' => 'javascript:void(0)',));

            //$go_store_link = get_permalink(get_page_by_path($go_store_link));
            $reader_link = get_site_url(null, 'reader');
            $wp_admin_bar->add_node(
                array(
                    'id' => 'go_reader',
                    'title' => '<i class="fas fa-book-reader ab-icon" aria-hidden="true"></i><div id="go_reader_link" style="float: right;"></div>',
                    'href' => $reader_link,
                )
            );

            $wp_admin_bar->add_node(array('id' => 'go_clipboard', 'title' => '<i class="fas fa-clipboard-list ab-icon" aria-hidden="true"></i><div id="go_clipboard_adminbar" style="float: right;"></div>', 'href' => get_admin_url() . 'admin.php?page=go_clipboard',));
        }

        if (is_admin()){
            $go_map_switch = get_option( 'options_go_locations_map_toggle' );
            $go_store_switch = get_option( 'options_go_store_toggle' );
            if ($go_map_switch) {
                $map_url = get_option('options_go_locations_map_map_link');
                $go_map_link = (string)$map_url;
                //$go_map_link = get_permalink(get_page_by_path($go_map_link));
                $go_map_link = get_site_url(null, $go_map_link);
                $name = get_option('options_go_locations_map_name');
                $wp_admin_bar->add_node(
                    array(
                        'id' => 'go_map',
                        //'title' => '<i class="fas fa-sitemap ab-icon" aria-hidden="true"></i><div id="go_map_page" class="admin_map" style="float: right;" >' . $name . '</div>',
                        'title' => '<i class="fas fa-sitemap ab-icon" aria-hidden="true"></i><div id="go_map_page" class="admin_map" style="float: right;" ></div>',
                        'href' => $go_map_link,
                    )
                );
            };

            if ($go_store_switch) {
                $go_store_link = get_option('options_go_store_store_link');
                //$go_store_link = get_permalink(get_page_by_path($go_store_link));
                $go_store_link = get_site_url(null, $go_store_link);
                $name = get_option('options_go_store_name');
                $wp_admin_bar->add_node(
                    array(
                        'id' => 'go_store',
                        //'title' => '<i class="fas fa-store ab-icon" aria-hidden="true"></i><div id="go_store_page" style="float: right;">' . $name . '</div>',
                        'title' => '<i class="fas fa-store ab-icon" aria-hidden="true"></i><div id="go_store_page" style="float: right;"></div>',
                        'href' => $go_store_link,
                    )
                );
            };
        }

        if ($is_admin && !is_admin()) {
            $wp_admin_bar->add_group(
                array(
                    'id' => 'go_site_name_menu',
                    'parent' => 'site-name',
                    'meta' => array('class' => 'go_site_name_menu')
                )
            );

            /*
             * Game On Links
             */
            // displays GO options page link
            $wp_admin_bar->add_node(
                array(
                    'id' => 'go_nav_options',
                    'title' => 'Game-On Options',
                    'href' => get_admin_url() . 'admin.php?page=go_options',
                    'parent' => 'go_site_name_menu',
                    'meta' => array('class' => 'go_site_name_menu_item')
                )
            );

            // displays Clipboard link
            /*$wp_admin_bar->add_node(
                array(
                    'id' => 'go_nav_clipboard',
                    'title' => 'Clipboard',
                    'href' => get_admin_url() . 'admin.php?page=go_clipboard',
                    'parent' => 'go_site_name_menu',
                    'meta' => array('class' => 'go_site_name_menu_item')
                )
            );*/

            // displays Task edit page link
            $wp_admin_bar->add_node(
                array(
                    'id' => 'go_nav_tasks',
                    'title' => get_option('options_go_tasks_name_plural'),
                    'href' => get_admin_url() . 'edit.php?post_type=tasks',
                    'parent' => 'go_site_name_menu',
                    'meta' => array('class' => 'go_site_name_menu_item')
                )
            );

            // displays chains page link
            $wp_admin_bar->add_node(
                array(
                    'id' => 'go_nav_chains',
                    'title' => get_option('options_go_tasks_name_plural') . ' Maps',
                    'href' => esc_url(get_admin_url()) . 'edit-tags.php?taxonomy=task_chains&post_type=tasks',
                    'parent' => 'go_site_name_menu',
                    'meta' => array('class' => 'go_site_name_menu_item')
                )
            );

            // displays Store Item edit page link
            $wp_admin_bar->add_node(
                array(
                    'id' => 'go_nav_store',
                    'title' => get_option('options_go_store_name') . ' Items',
                    'href' => get_admin_url() . 'edit.php?post_type=go_store',
                    'parent' => 'go_site_name_menu',
                    'meta' => array('class' => 'go_site_name_menu_item')
                )
            );

            // displays Store Categories page link
            $wp_admin_bar->add_node(
                array(
                    'id' => 'go_nav_store_types',
                    'title' => get_option('options_go_store_name') . ' Categories',
                    'href' => esc_url(get_admin_url()) . 'edit-tags.php?taxonomy=store_types&post_type=go_store',
                    'parent' => 'go_site_name_menu',
                    'meta' => array('class' => 'go_site_name_menu_item')
                )
            );

            // displays Badges
            $badges_toggle = get_option('options_go_badges_toggle');
            if ($badges_toggle) {
                $wp_admin_bar->add_node(array('id' => 'go_nav_badges', 'title' => get_option('options_go_badges_name_plural'), 'href' => esc_url(get_admin_url()) . 'edit-tags.php?taxonomy=go_badges', 'parent' => 'go_site_name_menu', 'meta' => array('class' => 'go_site_name_menu_item')));
            }

            // displays Store Categories page link
            $wp_admin_bar->add_node(
                array(
                    'id' => 'go_nav_users',
                    'title' => 'Users',
                    'href' => esc_url(get_admin_url()) . 'users.php',
                    'parent' => 'go_site_name_menu',
                    'meta' => array('class' => 'go_site_name_menu_item')
                )
            );

            // displays User Groups page link
            $wp_admin_bar->add_node(
                array(
                    'id' => 'go_nav_user_types',
                    'title' => 'User Groups',
                    'href' => esc_url(get_admin_url()) . 'edit-tags.php?taxonomy=user_go_groups',
                    'parent' => 'go_site_name_menu',
                    'meta' => array('class' => 'go_site_name_menu_item')
                )
            );


            /*
             * Default WP Links
             */

            // displays Post edit page link
            $wp_admin_bar->add_node(
                array(
                    'id' => 'go_nav_posts',
                    'title' => 'Posts',
                    'href' => esc_url(get_admin_url()) . 'edit.php',
                    'parent' => 'appearance'
                )
            );

            // displays Page edit page link
            $wp_admin_bar->add_node(
                array(
                    'id' => 'go_nav_pages',
                    'title' => 'Pages',
                    'href' => esc_url(get_admin_url()) . 'edit.php?post_type=page',
                    'parent' => 'appearance'
                )
            );

            // displays Media Library page link
            $wp_admin_bar->add_node(
                array(
                    'id' => 'go_nav_media',
                    'title' => 'Media',
                    'href' => esc_url(get_admin_url()) . 'upload.php',
                    'parent' => 'appearance'
                )
            );

            // displays Plugins page link
            $wp_admin_bar->add_node(
                array(
                    'id' => 'go_nav_plugins',
                    'title' => 'Plugins',
                    'href' => esc_url(get_admin_url()) . 'plugins.php',
                    'parent' => 'appearance'
                )
            );

            // displays Users page link
            /*
            $wp_admin_bar->add_node(
                array(
                    'id' => 'go_nav_users',
                    'title' => 'Users',
                    'href' => esc_url( get_admin_url() ).'users.php',
                    'parent' => 'appearance',
                )
            );
            */
        }

    }

}


/**
 * ADD LEADERBOARD PAGE
 */

add_action('init', 'go_leaderboard_rewrite');
function go_leaderboard_rewrite(){
    $page_name = 'leaderboard';
    add_rewrite_rule( $page_name, 'index.php?' . $page_name . '=true', "top");
}

// Query Vars
add_filter( 'query_vars', 'go_leaderboard_query_var' );
function go_leaderboard_query_var( $vars ) {
    $page_name = 'leaderboard';
    $vars[] = $page_name;
    return $vars;
}

/* Template Include */
add_filter('template_include', 'go_leaderboard_template_include', 1, 1);
function go_leaderboard_template_include($template){
    $page_name = 'leaderboard';
    global $wp_query; //Load $wp_query object

    $page_value = ( isset($wp_query->query_vars[$page_name]) ? $wp_query->query_vars[$page_name] : false ); //Check for query var "blah"

    if ($page_value && ($page_value == "true" )) { //Verify "blah" exists and value is "true".

        return plugin_dir_path(__FILE__).'templates/leaderboard.php'; //Load your template or file
    }

    return $template; //Load normal template when $page_value != "true" as a fallback
}



