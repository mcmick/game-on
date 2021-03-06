<?php
/**
 * Created by PhpStorm.
 * User: mmcmurray
 * Date: 8/4/18
 * Time: 1:29 PM
 */

add_action('pre_get_posts','go_users_own_attachments');
function go_users_own_attachments( $wp_query_obj ) {

    global $current_user, $pagenow;

    $is_attachment_request = ($wp_query_obj->get('post_type')=='attachment');

    if( !$is_attachment_request )
        return;

    if( !is_a( $current_user, 'WP_User') )
        return;

    if( !in_array( $pagenow, array( 'upload.php', 'admin-ajax.php' ) ) )
        return;

    //if( !current_user_can('delete_pages') ) {
    $wp_query_obj->set('author', $current_user->ID);
    //}

    return;
}


$resize = get_site_option( 'options_go_images_resize_toggle' );

if ($resize) {

    add_filter('plupload_default_settings', function ($settings) {
        $settings['resize'] = array(
            'enabled' => true,
            'width' => 3480,
            'height' => 2160,
            'quality' => 80,
            'preserve_headers' => true,
        );
        return $settings;
    });
}

add_filter('gallery_style', 'go_filter_default_gallery');
function go_filter_default_gallery($style){
    return $style;
}