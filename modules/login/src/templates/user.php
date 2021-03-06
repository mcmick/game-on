<?php
/**
 * Created by PhpStorm.
 * User: mcmurray
 * Date: 2019-07-01
 * Time: 13:49
 */

/**
 * The template for displaying user join page
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 */

/*
if (is_user_member_of_blog()){
    $redirect_url = get_home_url();
    wp_redirect($redirect_url);
}*/

/**
 * Logic for if this page is going to redirect
 * of load join, profile, or register page
 *
 */
$this_page =  go_get_page_uri();


if (is_user_logged_in()) {
    if (is_user_member_of_blog()) {
        //if logged in and member then this is a profile page
        if ($this_page != 'profile'){
           wp_redirect(site_url('profile'));
           exit;
        }
    }
    else {
        //if logged in, but not a member of this blog, this is a join page
         if ($this_page != 'join'){
            wp_redirect(site_url('join'));
           exit;
        }
    }
}
else{
    //else not logged in and this is a register page.
     if ($this_page != 'register'){
            wp_redirect(site_url('register'));
            exit;
        }
}


$registrations_allowed = false;
if(get_option('options_allow_registration')){
    $google_only = intval(get_option('options_google-only'));
    if(!$google_only){
        $registrations_allowed = true;
    };
};
$is_valid_email = true;

acf_form_head();

/*
?>
    <script>
        var ajaxurl = MyAjax.ajaxurl;
    </script>
<?php
*/


get_header();


?>
<div id='go_profile_wrapper' style='max-width: 600px; margin: 20px auto 100px auto;'>

<?php
//
/*
 if ( isset($_COOKIE['my_active_user_variable']) && ! empty( $_COOKIE['my_active_user_variable'] ) ) {
    $user = json_decode(stripslashes($_COOKIE['my_active_user_variable']));
    unset($_COOKIE['my_active_user_variable']);
    setcookie('my_active_user_variable');
    //print_r($user);
    echo '<div class="acf-notice">Welcome '. $user->user_login .'.<br>Your assigned password is: ' . $user->user_password . '<br>Please change your password and complete your profile now.</div>';
    // Unset the session variable since we don't needed anymore

}
*/
$security_options = get_option('options_security');
if(!is_array($security_options)){
    $security_options[]= $security_options;
}
$block_form = false;
if ($this_page == 'profile'){
    $redirect = false;
    $button_text = "Update";
    echo"<h2 style='padding-top:10px;'>Profile</h2>";

    $updated  = (isset($_GET['updated']) ) ? $_GET['updated'] : 0;
    if ($updated === "true") {
        echo '<p class="success">Your Profile was updated.</p><br><br>';
    }

    //need avatar and course, section and seat.

    echo '<div class="go_user_actions"><a href="/wp-login.php?action=logout" class="go_logout">Logout</a> – ';
    echo '<a href="#" class="go_password_change_modal">Change Password</a> – ';
    echo '<span id="go_save_archive" style=""><a href="javascript:void(0)">Create Blog Archive</a></span></div>';
}
else if (($this_page == 'register') || ($this_page == 'join')){
    $redirect = true;
    $key = !empty($_GET['key']) ? $_GET['key'] : null;
    $activate = !empty($_GET['activate']) ? $_GET['activate'] : false;

    //add check if key has already been used
    //take to login, join, register, or profile page if it has.
    //add check for if key is valid
    //give error if it is not valid

    if($activate){
        $this_page = 'activate';
        echo "<div><div class='go_password_activate_container'>";

        $groups = array();
        $fields = array('field_5cd3638830f17', 'field_5cd363d130f18', 'field_5cd52c8f46296');
        $form =  go_acf_user_form_func($groups, $fields, false, 'activate_user', 'Activate Account');
        echo $form;
        $block_form = true;
        echo "</div></div>";
    }


    if ($this_page == 'register') {
        $button_text = "Register";
        echo "<h3 style='padding-top:10px;'>Register for a new account</h3>";
    }
    else if ($this_page == 'join'){
        $button_text = "Join";
        echo "<h3 style='padding-top:10px;'>Join Game</h3>";
    }


    if ($this_page == 'register') {
        if(!$registrations_allowed) {
            echo "<div>This game is currently closed to new players.</div>";
            $block_form = true;
        }
            //$limit_domains_toggle = get_option('options_limit_domains_toggle');
            //print message at top of register page if domains are restricted

            //if ($limit_domains_toggle) {
            if(in_array('domains', $security_options)){
                $message = go_domain_restrictions_message();
                if (!empty($message)) {
                    echo "<div style='max-width: 800px;'>";
                    echo $message;
                    echo "</div>";
                }
            }
    }
    else if ($this_page == 'join') {
        ?>
        <div>Welcome <span style="font-size: 1.2em;" <?php echo go_get_firstname_function(); ?></span>. </div>
        <?php
        //$limit_domains_toggle = get_option('options_limit_domains_toggle');
       // if($limit_domains_toggle) {
        if(in_array('domains', $security_options)){
            $domains = go_get_domain_restrictions();
            $current_user = wp_get_current_user();
            $email = $current_user->user_email;
            $is_valid_email = validate_email_against_domains($email);
            if ($is_valid_email){
                echo "<div> Fill out the form below to join this game.</div>";
            }else{
                if(!$is_valid_email) {
                    $registrations_allowed = false;
                    $message = go_domain_restrictions_message();
                    if (!empty($message)) {
                        $block_form = true;
                        echo "<div style='max-width: 800px; '>";
                        echo $message;
                        $go_login_link = get_site_url(null, 'login');
                        $go_logout_link = wp_loginout( 'logout', false );
                        echo $go_logout_link . " and register with a valid email.";
                        echo "</div>";
                    }
                }
            }
        }
    }
//
    //If multisite, register settings are always from blog #1. NEW combine main and sub site settings.
    //if(is_gameful()) {
    //            $main_site_id = get_network()->site_id;
    //            switch_to_blog($main_site_id);
    //        }
    //$restored = 0;
}


if(!$block_form) {//registrations are allowed or this is a profile page

    $groups = array();
    $fields = array();

    //on single site installs, register and join are the same page.

     if (!is_user_member_of_blog()) {

        //if (get_option('options_registration_code_toggle')) {
        if(in_array('code', $security_options)){
            $fields[] = 'field_5cd9f85e5f788';//membership code
        }
    }

    if ($this_page == 'register' ) {
        $fields[] = 'field_5cd1d1de5491b';//first name
        $fields[] = 'field_5cd1d21168754';//last name
        $fields[] = 'field_5cd4be08e7077';//email
        $fields[] = 'field_5cd4fa743159f';//username
        $fields[] = 'field_5cd3638830f17';//password
        $fields[] = 'field_5cd363d130f18';//validate
        $fields[] = 'field_5cd52c8f46296';//test strength
    }




    if ($this_page === 'join' || $this_page === 'register'){
        $prefix = 'register';
    }else{
        $prefix = 'profile';
    }

    if ($this_page === 'profile') {
        if (get_option('options_' . $prefix . '_fields_email_toggle')) {
            $fields[] = 'field_5cd4be08e7077';//email
        }

        if (get_option('options_' . $prefix . '_fields_first_toggle')) {
            $fields[] = 'field_5cd1d1de5491b';//first
        }

        if (get_option('options_' . $prefix . '_fields_last_toggle')) {
            $fields[] = 'field_5cd1d21168754';//last
        }
    }

   // if (get_option('options_' . $prefix . '_fields_display_name_toggle')) {
        $fields[] = 'field_5cd1d13769aa9';//display name
   // }

    if (get_option('options_' . $prefix . '_fields_avatar_toggle')) {
        if (get_option('options_' . $prefix . '_fields_avatar_required')) {
            $fields[] = 'field_5d1aeaa4330cd';//avatar
        } else {
            $fields[] = 'field_5cd4f7b4366c6';//avatar
        }
    }

    if (get_option('options_' . $prefix . '_fields_website_toggle')) {
        if (get_option('options_' . $prefix . '_fields_website_required')) {
            $fields[] = 'field_5d1aeb63330ce';//website
        } else {
            $fields[] = 'field_5cd4f996c0d86';//website
        }
    }

    //sections are on
    if (get_option('options_' . $prefix . '_fields_section_toggle')) {
        //multiple on, multiple sections allowed
        if (get_option('options_' . $prefix . '_fields_section_allow_multiple')) {
            //section is required
            if (get_option('options_' . $prefix . '_fields_section_required')) {
                //are seats toggled on
                if (get_option('options_' . $prefix . '_fields_seat_toggle')) {
                    //are seats required
                    if (get_option('options_' . $prefix . '_fields_seat_required')) {
                        //show with both required
                        $fields[] = 'field_5cd4f7b43672b';//multiple yes, both section and seat required
                    } else {
                        $fields[] = 'field_5d1bca108c581';//multiple yes, only sections required
                    }

                } else {
                    $fields[] = 'field_5d1bc17d82db8';//multiple yes, no seat, sections required
                }
            } //section is not required
            else {
                //are seats toggled on
                if (get_option('options_' . $prefix . '_fields_seat_toggle')) {
                    //are seats required
                    if (get_option('options_' . $prefix . '_fields_seat_required')) {
                        //show with both required
                        $fields[] = 'field_5d1bcaa48c58a';//Mulitple yes, only seat required
                    } else {
                        $fields[] = 'field_5d1bca2e8c584';//Mulitple yes, nothing required
                    }

                } else {
                    $fields[] = 'field_5d1bc9e48c57d';//multiple yes, no seat, nothing required
                }
            }
        } //mulitple off, only one section allowed
        else {

            if (get_option('options_' . $prefix . '_fields_section_required')) {
                //are seats toggled on
                if (get_option('options_' . $prefix . '_fields_seat_toggle')) {
                    //are seats required
                    if (get_option('options_' . $prefix . '_fields_seat_required')) {
                        //show with both required
                        $fields[] = 'field_5d1bd3f76f8a3';//multiple no, both section and seat required
                    } else {
                        $fields[] = 'field_5d1bd3f46f8a0';//multiple no, only section required
                    }

                } else {
                    $fields[] = 'field_5d1bc8be8c576';//multiple no, no seat, section required
                }
            } //section is not required
            else {
                //are seats toggled on
                if (get_option('options_' . $prefix . '_fields_seat_toggle')) {
                    //are seats required
                    if (get_option('options_' . $prefix . '_fields_seat_required')) {
                        //show with both required
                        $fields[] = 'field_5d1bd3f26f89d';//Mulitple no, only seat required
                    } else {
                        $fields[] = 'field_5d1bd3ee6f89a';//Mulitple no, nothing required
                    }

                } else {
                    $fields[] = 'field_5d1bc9fa8c57f';//multiple no, no seat, nothing required
                }
            }
        }

    } else {//if sections are not on, check if seats are on and show a single seat option

        if (get_option('options_' . $prefix . '_fields_seat_toggle')) {
            if (get_option('options_' . $prefix . '_fields_seat_required')) {
                $fields[] = 'field_5d1bc9918c578';//seat only, required
            } else {
                $fields[] = 'field_5d1bc9ae8c57b';//seat only, not required
            }
        }
    }

    /*
    if (get_option('options_use_recaptcha')) {
        $fields[] = 'field_5d1ae5f929200';//recaptcha
    }*/

//
    //$fields = array('field_5cd9f85e5f788', 'field_5cd4fa743159f', 'field_5cd4be08e7077', 'field_5cd1d1de5491b', 'field_5cd1d21168754', 'field_5cd1d13769aa9', 'field_5cd4f7b4366c6', 'field_5cd4f7b43672b', 'field_5cd3638830f17', 'field_5cd363d130f18', 'field_5cd52c8f46296');
    //$fields = array('field_5cd1d13769aa9', 'field_5cd4be08e7077', 'field_5cd3638830f17', 'field_5cd363d130f18', 'field_5cd52c8f46296');

        $uid = get_current_user_id();
        $post_id = 'user_'.$uid;
    $form = go_acf_user_form_func($groups, $fields, $redirect, $post_id, $button_text);

    echo $form;
}


echo "</div>";

/*
if ($this_page == 'register'){
    //  If multisite, register settings are always from blog #1, so switch back to current blog.
    if(is_gameful()) {
            restore_current_blog();
        }
}*/

wp_footer();