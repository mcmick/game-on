<?php
/**
 * Created by PhpStorm.
 * User: mmcmurray
 * Date: 7/31/18
 * Time: 12:25 PM
 */


/**
 * The template for displaying map pages
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Twenty_Seventeen
 * @since 1.0
 * @version 1.0
 */

get_header();
$store_name = get_option( 'options_go_store_store_link');
?>
    <script>
        jQuery(document).prop('title', '<?php echo $store_name; ?>');
    </script>
<?php

go_make_store_new();


get_footer();