<?php
  
  if ( ! class_exists( 'WP_Birdlife_Project_Post_Type' ) ) {
    class WP_Birdlife_Project_Post_Type {
      function __construct() {
        add_action( 'init', array( $this, 'create_post_type' ) );
        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
      }
      
      public function create_post_type() {
        register_post_type(
          'naturforderung',
          array(
            'label'               => 'Naturförderung projects',
            'description'         => 'Naturförderung projects post type',
            'labels'              => array(
              'name'          => 'Naturförderung MPlus projects',
              'singular_name' => 'Naturförderung MPlus project'
            ),
            'public'              => true,
            'supports'            => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
            'hierarchical'        => true,
            'show_ui'             => true,
            'show_in_menu'        => false,
            'menu_position'       => 6,
            'show_in_admin_bar'   => true,
            'show_in_nav_menus'   => true,
            'can_export'          => true,
            'has_archive'         => true,
            'exclude_from_search' => true,
            'publicly_queryable'  => true,
            'show_in_rest'        => true,
            'menu_icon'           => 'dashicons-editor-bold',
            'rewrite'             => array( 'slug' => 'naturforderung-project' )
          )
        );
      }
      
      public function add_meta_boxes() {
        add_meta_box(
          'wp_birdlife_project_meta_box',
          'ManagePlus Project fields',
          array( $this, 'add_inner_meta_boxes' ),
          'naturforderung',
          'normal',
          'high'
        );
      }
      
      public function add_inner_meta_boxes( $post ) {
        require_once( WP_BIRDLIFE_PATH . '/views/wp-birdlife-project_metabox.php' );
      }
      
    }
  }