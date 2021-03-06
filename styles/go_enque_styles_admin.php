<?php


add_action( 'admin_enqueue_scripts', 'go_admin_styles' );
function go_admin_styles () {
    global $go_css_version;

    if(is_gameful() && is_main_site() && !is_user_logged_in()  && is_front_page()){
        return;
    }


    /*
     * Registering Styles For Admin Pages
     */

    wp_register_style( 'go_admin', plugin_dir_url( __FILE__ ).'min/go_admin.css', null, $go_css_version );

    // Styles for all GO
    wp_register_style( 'go_styles', plugin_dir_url( __FILE__ ).'min/go_styles.css', null, $go_css_version );

    // Styles dependencies combined
    wp_register_style( 'go_dependencies', plugin_dir_url( __FILE__ ).'min/go_combine_dependencies.css', null, $go_css_version );

    /*
     * Enqueueing Styles For Admin Pages
     */
    /*
     * Combined styles for every admin page. Even if only needed on some pages, include in one file if possible.
     */
    //Combined File
    wp_enqueue_style( 'go_admin' );

    wp_enqueue_style( 'go_styles' );

    wp_enqueue_style( 'go_dependencies' );


}
