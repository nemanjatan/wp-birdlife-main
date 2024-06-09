<?php
ignore_user_abort( true );
/**
 * Astra Child Theme functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Astra Child
 * @since 1.0.0
 */

/**
 * Define Constants
 */
define( 'CHILD_THEME_ASTRA_CHILD_VERSION', '1.0.0' );

/**
 * Enqueue styles
 */
function child_enqueue_styles() {

	wp_enqueue_style( 'astra-child-theme-css', get_stylesheet_directory_uri() . '/style.css', array( 'astra-theme-css' ), CHILD_THEME_ASTRA_CHILD_VERSION, 'all' );

}

add_action( 'wp_enqueue_scripts', 'child_enqueue_styles', 15 );

function my_update_jquery() {

	if ( ! is_admin() ) {

		wp_deregister_script( 'jquery' );

		wp_register_script( 'jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js', false, false, true );

		wp_enqueue_script( 'jquery' );

	}

}

add_action( 'wp_enqueue_scripts', 'my_update_jquery' );

remove_filter( 'the_content', 'convert_smilies', 20 );

add_action( 'wp_head', 'expose_ajax_url' );

function expose_ajax_url() {
	echo '<script type="text/javascript">
       var ajaxurl = "' . admin_url( 'admin-ajax.php' ) . '";
     </script>';
}

function fl_builder_loop_query_args_filter( $args ) {
	if ( isset( $_GET['sf_paged'] ) ) {
		global $wp_the_query;

		//this get pagination working within the query (not sure what offset is for exactly, but removing it get paged working)
		$args['paged'] = intval( $_GET['sf_paged'] );
		unset( $args['offset'] );

		//then the pagination needs fixing, which is taken from global $wp_the_query
		global $wp_the_query;
		$wp_the_query->set( 'paged', intval( $_GET['sf_paged'] ) );
	}

	return $args;
}

add_filter( 'fl_builder_loop_query_args', 'fl_builder_loop_query_args_filter' );

function my_scripts_method() {
	wp_enqueue_script(
		'custom-script',
		get_stylesheet_directory_uri() . '/js/custom_script.js',
		array( 'jquery' )
	);
}

add_action( 'wp_enqueue_scripts', 'my_scripts_method' );

add_filter( 'naturforderung_post_type_args', 'naturforderung_rewrite_slug' );
function naturforderung_rewrite_slug( $args ) {
	$args['rewrite']['slug'] = 'naturforderung-project';

	return $args;
}