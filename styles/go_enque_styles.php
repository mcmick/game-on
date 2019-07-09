<?php


/*
 * Registering Scripts/Styles For The Front-end
 */

add_action( 'wp_enqueue_scripts', 'go_styles' );
function go_styles () {
    global $go_css_version;

	/*
	 * Registering Styles For The Front-end
	 */
		// COMBINED STYLES
		wp_register_style( 'go_frontend', plugin_dir_url( __FILE__ ).'min/go_frontend.css', null, $go_css_version );
        //wp_register_style( 'go_styles', plugin_dir_url( __FILE__ ).'min/go_styles.css', null, $go_css_version );

		// Styles for all GO
        wp_register_style( 'go_styles', plugin_dir_url( __FILE__ ).'min/go_styles.css', null, $go_css_version );


        // Styles dependencies combined
        wp_register_style( 'go_dependencies', plugin_dir_url( __FILE__ ).'min/go_combine_dependencies.css', null, $go_css_version );
		
	/*
	 * Enqueue Styles For The Front-end
	 */

		//COMBINED FILE:
		wp_enqueue_style( 'go_frontend' );

        wp_enqueue_style( 'go_styles' );

        wp_enqueue_style( 'go_dependencies' );
}