<?php
/**
 * Created by PhpStorm.
 * User: mmcmurray
 * Date: 7/31/18
 * Time: 12:25 PM
 */


/**
 * The template for displaying login page
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Twenty_Seventeen
 * @since 1.0
 * @version 1.0
 */


//https://codex.wordpress.org/Customizing_the_Login_Form#Make_a_Custom_Login_Page
if ( ! is_user_logged_in() ) { // Display WordPress login form:
    wp_head();
    ///////////
    echo "<div id ='go_login_center'><div id='go_login_container'>";


    $logo = get_option('options_login_appearance_logo');
    //$url = wp_get_attachment_image($logo, array('250', '250'));
    $url = wp_get_attachment_image_src($logo, 'medium');
    $url = $url[0];

    if ($url) {
        echo "<div><div style=' width: 200px; margin: 0 auto;'><img src='$url' width='100%'</div></div>";
    }

    $login  = (isset($_GET['login']) ) ? $_GET['login'] : 0;
    if ($login === "failed") {
        echo '<p class="error"><strong>ERROR:</strong> Invalid username and/or password.</p>';
    } elseif ($login === "empty") {
        echo '<p class="error"><strong>ERROR:</strong> Username and/or Password is empty.</p>';
    } elseif ($login === "checkemail") {
        echo '<p class="success"> Check your email for instructions on resetting your password.</p>';
    } elseif ($login === "false") {
        echo '<p class="success"> You are logged out now.</p><br><br>';
    }


    $args = array(
        'redirect' => get_home_url('login'),
        'form_id' => 'game-on-login',
        'label_username' => __( 'Username' ),
        'label_password' => __( 'Password' ),
        'label_remember' => __( 'Remember Me' ),
        'label_log_in' => __( 'Log In' ),
        'remember' => true
    );
    wp_login_form( $args );

    do_action( 'login_form' );


    //there is a function that inserts the reset password link
    $background = get_option('options_login_appearance_background_color');

    ?>
    </div>
    <a href="<?php echo home_url();?>">Home</a>
    <?php
    $registration_allowed = get_option('options_allow_registration');
    if ($registration_allowed) {
        ?>|
        <a href="<?php echo home_url('registration'); ?>">Register</a>
        <?php
    }
        ?>
    </div>

    <script>
        jQuery( document ).ready( function()  {
            jQuery('#go_login_center').fadeIn(1000);
            jQuery('body').css('background', '<?php echo $background;?>');
        });
    </script>
    <?php
    wp_footer();
} else { // If logged in:
    $redirect_url = home_url('profile');
    wp_redirect( $redirect_url );
    wp_loginout( home_url() ); // Display "Log Out" link.
}

