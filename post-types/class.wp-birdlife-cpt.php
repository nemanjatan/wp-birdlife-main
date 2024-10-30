<?php
  
  if ( ! class_exists( 'WP_Birdlife_Post_Type' ) ) {
    class WP_Birdlife_Post_Type {
      function __construct() {
        add_action( 'init', array( $this, 'create_post_type' ) );
        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
        add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );
      }
      
      public function create_post_type() {
        register_post_type(
          'naturkurs',
          array(
            'label'               => 'ManagePlus Events',
            'description'         => 'ManagePlus Events post type',
            'labels'              => array(
              'name'          => 'ManagePlus Events',
              'singular_name' => 'ManagePlus Event'
            ),
            'public'              => true,
            'supports'            => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
            'hierarchical'        => true,
            'show_ui'             => true,
            'show_in_menu'        => false,
            'menu_position'       => 5,
            'show_in_admin_bar'   => true,
            'show_in_nav_menus'   => true,
            'can_export'          => true,
            'has_archive'         => true,
            'exclude_from_search' => true,
            'publicly_queryable'  => true,
            'show_in_rest'        => true,
            'menu_icon'           => 'dashicons-editor-bold'
          )
        );
      }
      
      public function add_meta_boxes() {
        add_meta_box(
          'wp_birdlife_meta_box',
          'ManagePlus fields',
          array( $this, 'add_inner_meta_boxes' ),
          'naturkurs',
          'normal',
          'high'
        );
      }
      
      public function add_inner_meta_boxes( $post ) {
        require_once( WP_BIRDLIFE_PATH . '/views/wp-birdlife_metabox.php' );
      }
      
      public function save_post( $post_id ) {
        if ( isset( $_POST['wp_birdlife_nonce'] ) ) {
          if ( ! wp_verify_nonce( $_POST['wp_birdlife_nonce'], 'wp_birdlife_nonce' ) ) {
            return;
          }
        }
        
        if ( isset( $_POST['post_type'] ) && $_POST['post_type'] === 'naturkurs' ) {
          if ( ! current_user_can( 'edit_page', $post_id ) ) {
            return;
          } elseif ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
          }
        }
        
        if ( isset ( $_POST['action'] ) && $_POST['action'] == 'editpost' ) {
          $old_event_registration_until_date = get_post_meta( $post_id, 'wp_birdlife_event_registration_until_date', true );
          $new_event_registration_until_date = $_POST['wp_birdlife_event_registration_until_date'];
          
          if ( ! empty( $new_event_registration_until_date ) ) {
            update_post_meta( $post_id, 'wp_birdlife_event_registration_until_date', $new_event_registration_until_date, $old_event_registration_until_date );
          }
          
          $old_event_external_link = get_post_meta( $post_id, 'wp_birdlife_event_external_link', true );
          $new_event_external_link = $_POST['wp_birdlife_event_external_link'];
          
          if ( ! empty( $new_event_external_link ) ) {
            update_post_meta( $post_id, 'wp_birdlife_event_external_link', $new_event_external_link, $old_event_external_link );
          }
          
          $old_event_place = get_post_meta( $post_id, 'wp_birdlife_event_place', true );
          $new_event_place = $_POST['wp_birdlife_event_place'];
          
          if ( ! empty( $new_event_place ) ) {
            update_post_meta( $post_id, 'wp_birdlife_event_place', $new_event_place, $old_event_place );
          }
          
          $old_event_phone = get_post_meta( $post_id, 'wp_birdlife_event_phone', true );
          $new_event_phone = $_POST['wp_birdlife_event_phone'];
          
          if ( ! empty( $new_event_phone ) ) {
            update_post_meta( $post_id, 'wp_birdlife_event_phone', $new_event_phone, $old_event_phone );
          }
          
          $old_event_information_registration = get_post_meta( $post_id, 'wp_birdlife_event_information_registration', true );
          $new_event_information_registration = $_POST['wp_birdlife_event_information_registration'];
          
          if ( ! empty( $new_event_information_registration ) ) {
            update_post_meta( $post_id, 'wp_birdlife_event_information_registration', $new_event_information_registration, $old_event_information_registration );
          }
          
          $old_event_email = get_post_meta( $post_id, 'wp_birdlife_event_email', true );
          $new_event_email = $_POST['wp_birdlife_event_email'];
          
          if ( ! empty( $new_event_email ) ) {
            update_post_meta( $post_id, 'wp_birdlife_event_email', $new_event_email, $old_event_email );
          }
          
          $old_event_credits = get_post_meta( $post_id, 'wp_birdlife_event_credits', true );
          $new_event_credits = $_POST['wp_birdlife_event_credits'];
          
          if ( ! empty( $new_event_credits ) ) {
            update_post_meta( $post_id, 'wp_birdlife_event_credits', $new_event_credits, $old_event_credits );
          }
          
          $old_event_online_date = get_post_meta( $post_id, 'wp_birdlife_event_online_date', true );
          $new_event_online_date = $_POST['wp_birdlife_event_online_date'];
          
          if ( ! empty( $new_event_online_date ) ) {
            update_post_meta( $post_id, 'wp_birdlife_event_online_date', $new_event_online_date, $old_event_online_date );
          }
          
          $old_event_num_min_lnu = get_post_meta( $post_id, 'wp_birdlife_event_num_min_lnu', true );
          $new_event_num_min_lnu = $_POST['wp_birdlife_event_num_min_lnu'];
          
          if ( ! empty( $new_event_num_min_lnu ) ) {
            update_post_meta( $post_id, 'wp_birdlife_event_num_min_lnu', $new_event_num_min_lnu, $old_event_num_min_lnu );
          }
          
          $old_event_num_max_lnu = get_post_meta( $post_id, 'wp_birdlife_event_num_max_lnu', true );
          $new_event_num_max_lnu = $_POST['wp_birdlife_event_num_max_lnu'];
          
          if ( ! empty( $new_event_num_max_lnu ) ) {
            update_post_meta( $post_id, 'wp_birdlife_event_num_max_lnu', $new_event_num_max_lnu, $old_event_num_max_lnu );
          }
          
          $old_event_course_description_short = get_post_meta( $post_id, 'wp_birdlife_event_course_description_short', true );
          $new_event_course_description_short = $_POST['wp_birdlife_event_course_description_short'];
          
          if ( ! empty( $new_event_course_description_short ) ) {
            update_post_meta( $post_id, 'wp_birdlife_event_course_description_short', $new_event_course_description_short, $old_event_course_description_short );
          }
          
          $old_event_cost = get_post_meta( $post_id, 'wp_birdlife_event_cost', true );
          $new_event_cost = $_POST['wp_birdlife_event_cost'];
          
          if ( ! empty( $new_event_cost ) ) {
            update_post_meta( $post_id, 'wp_birdlife_event_cost', $new_event_cost, $old_event_cost );
          }
          
          $old_event_course_multiple_events = get_post_meta( $post_id, 'wp_birdlife_event_course_multiple_events', true );
          $new_event_course_multiple_events = $_POST['wp_birdlife_event_course_multiple_events'];
          
          if ( ! empty( $new_event_course_multiple_events ) ) {
            update_post_meta( $post_id, 'wp_birdlife_event_course_multiple_events', $new_event_course_multiple_events, $old_event_course_multiple_events );
          }
          
          $old_event_program = get_post_meta( $post_id, 'wp_birdlife_event_program', true );
          $new_event_program = $_POST['wp_birdlife_event_program'];
          
          if ( ! empty( $new_event_program ) ) {
            update_post_meta( $post_id, 'wp_birdlife_event_program', $new_event_program, $old_event_program );
          }
          
          $old_event_time_to_tim = get_post_meta( $post_id, 'wp_birdlife_event_time_to_tim', true );
          $new_event_time_to_tim = $_POST['wp_birdlife_event_time_to_tim'];
          
          if ( ! empty( $new_event_time_to_tim ) ) {
            update_post_meta( $post_id, 'wp_birdlife_event_time_to_tim', $new_event_time_to_tim, $old_event_time_to_tim );
          }
          
          $old_event_overnight_place = get_post_meta( $post_id, 'wp_birdlife_event_overnight_place', true );
          $new_event_overnight_place = $_POST['wp_birdlife_event_overnight_place'];
          
          if ( ! empty( $new_event_overnight_place ) ) {
            update_post_meta( $post_id, 'wp_birdlife_event_overnight_place', $new_event_overnight_place, $old_event_overnight_place );
          }
          
          $old_event_time_from_tim = get_post_meta( $post_id, 'wp_birdlife_event_time_from_tim', true );
          $new_event_time_from_tim = $_POST['wp_birdlife_event_time_from_tim'];
          
          if ( ! empty( $new_event_time_from_tim ) ) {
            update_post_meta( $post_id, 'wp_birdlife_event_time_from_tim', $new_event_time_from_tim, $old_event_time_from_tim );
          }
          
          $old_event_materials = get_post_meta( $post_id, 'wp_birdlife_event_materials', true );
          $new_event_materials = $_POST['wp_birdlife_event_materials'];
          
          if ( ! empty( $new_event_materials ) ) {
            update_post_meta( $post_id, 'wp_birdlife_event_materials', $new_event_materials, $old_event_materials );
          }
          
          $old_event_approved_notes = get_post_meta( $post_id, 'wp_birdlife_event_approved_notes', true );
          $new_event_approved_notes = $_POST['wp_birdlife_event_approved_notes'];
          
          if ( ! empty( $new_event_approved_notes ) ) {
            update_post_meta( $post_id, 'wp_birdlife_event_approved_notes', $new_event_approved_notes, $old_event_approved_notes );
          }
          
          $old_event_approved_text = get_post_meta( $post_id, 'wp_birdlife_event_approved_text', true );
          $new_event_approved_text = $_POST['wp_birdlife_event_approved_text'];
          
          if ( ! empty( $new_event_approved_text ) ) {
            update_post_meta( $post_id, 'wp_birdlife_event_approved_text', $new_event_approved_text, $old_event_approved_text );
          }
          
          $old_event_approved_decision_date = get_post_meta( $post_id, 'wp_birdlife_event_approved_decision_date', true );
          $new_event_approved_decision_date = $_POST['wp_birdlife_event_approved_decision_date'];
          
          if ( ! empty( $new_event_approved_decision_date ) ) {
            update_post_meta( $post_id, 'wp_birdlife_event_approved_decision_date', $new_event_approved_decision_date, $old_event_approved_decision_date );
          }
          
          $old_event_approved_date = get_post_meta( $post_id, 'wp_birdlife_event_approved_date', true );
          $new_event_approved_date = $_POST['wp_birdlife_event_approved_date'];
          
          if ( ! empty( $new_event_approved_date ) ) {
            update_post_meta( $post_id, 'wp_birdlife_event_approved_date', $new_event_approved_date, $old_event_approved_date );
          }
          
          $old_event_notes = get_post_meta( $post_id, 'wp_birdlife_event_notes', true );
          $new_event_notes = $_POST['wp_birdlife_event_notes'];
          
          if ( ! empty( $new_event_notes ) ) {
            update_post_meta( $post_id, 'wp_birdlife_event_notes', $new_event_notes, $old_event_notes );
          }
          
          $old_event_description = get_post_meta( $post_id, 'wp_birdlife_event_description', true );
          $new_event_description = $_POST['wp_birdlife_event_description'];
          
          if ( ! empty( $new_event_description ) ) {
            update_post_meta( $post_id, 'wp_birdlife_event_description', $new_event_description, $old_event_description );
          }
          
          $old_event_date_to_dat = get_post_meta( $post_id, 'wp_birdlife_event_date_to_dat', true );
          $new_event_date_to_dat = $_POST['wp_birdlife_event_date_to_dat'];
          
          if ( ! empty( $new_event_date_to_dat ) ) {
            update_post_meta( $post_id, 'wp_birdlife_event_date_to_dat', $new_event_date_to_dat, $old_event_date_to_dat );
          }
          
          $old_event_date_from_dat = get_post_meta( $post_id, 'wp_birdlife_event_date_from_dat', true );
          $new_event_date_from_dat = $_POST['wp_birdlife_event_date_from_dat'];
          
          if ( ! empty( $new_event_date_from_dat ) ) {
            update_post_meta( $post_id, 'wp_birdlife_event_date_from_dat', $new_event_date_from_dat, $old_event_date_from_dat );
          }
          
          $old_event_registration_start_dat = get_post_meta( $post_id, 'wp_birdlife_event_registration_start_dat', true );
          $new_event_registration_start_dat = $_POST['wp_birdlife_event_registration_start_dat'];
          
          if ( ! empty( $new_event_registration_start_dat ) ) {
            update_post_meta( $post_id, 'wp_birdlife_event_registration_start_dat', $new_event_registration_start_dat, $old_event_registration_start_dat );
          }
          
          $old_event_leader = get_post_meta( $post_id, 'wp_birdlife_event_leader', true );
          $new_event_leader = $_POST['wp_birdlife_event_leader'];
          
          if ( ! empty( $new_event_leader ) ) {
            update_post_meta( $post_id, 'wp_birdlife_event_leader', $new_event_leader, $old_event_leader );
          }
          
          $old_event_information = get_post_meta( $post_id, 'wp_birdlife_event_information', true );
          $new_event_information = $_POST['wp_birdlife_event_information'];
          
          if ( ! empty( $new_event_information ) ) {
            update_post_meta( $post_id, 'wp_birdlife_event_information', $new_event_information, $old_event_information );
          }
          
          $old_event_dating = get_post_meta( $post_id, 'wp_birdlife_event_dating', true );
          $new_event_dating = $_POST['wp_birdlife_event_dating'];
          
          if ( ! empty( $new_event_dating ) ) {
            update_post_meta( $post_id, 'wp_birdlife_event_dating', $new_event_dating, $old_event_dating );
          }
          
          $old_event_offer = get_post_meta( $post_id, 'wp_birdlife_event_offer', true );
          $new_event_offer = $_POST['wp_birdlife_event_offer'];
          
          if ( ! empty( $new_event_offer ) ) {
            update_post_meta( $post_id, 'wp_birdlife_event_offer', $new_event_offer, $old_event_offer );
          }
          
          $old_event_number_participants = get_post_meta( $post_id, 'wp_birdlife_event_number_participants', true );
          $new_event_number_participants = $_POST['wp_birdlife_event_number_participants'];
          
          if ( ! empty( $new_event_number_participants ) ) {
            update_post_meta( $post_id, 'wp_birdlife_event_number_participants', $new_event_number_participants, $old_event_number_participants );
          }
          
          $old_event_number_groups = get_post_meta( $post_id, 'wp_birdlife_event_number_groups', true );
          $new_event_number_groups = $_POST['wp_birdlife_event_number_groups'];
          
          if ( ! empty( $new_event_number_groups ) ) {
            update_post_meta( $post_id, 'wp_birdlife_event_number_groups', $new_event_number_groups, $old_event_number_groups );
          }
          
          $old_event_region = get_post_meta( $post_id, 'wp_birdlife_event_region', true );
          $new_event_region = $_POST['wp_birdlife_event_region'];
          
          if ( ! empty( $new_event_region ) ) {
            update_post_meta( $post_id, 'wp_birdlife_event_region', $new_event_region, $old_event_region );
          }
          
          $old_event_course_description = get_post_meta( $post_id, 'wp_birdlife_event_course_description', true );
          $new_event_course_description = $_POST['wp_birdlife_event_course_description'];
          
          if ( ! empty( $new_event_course_description ) ) {
            update_post_meta( $post_id, 'wp_birdlife_event_course_description', $new_event_course_description, $old_event_course_description );
          }
          
          $old_event_organizer = get_post_meta( $post_id, 'wp_birdlife_event_organizer', true );
          $new_event_organizer = $_POST['wp_birdlife_event_organizer'];
          
          if ( ! empty( $new_event_organizer ) ) {
            update_post_meta( $post_id, 'wp_birdlife_event_organizer', $new_event_organizer, $old_event_organizer );
          }
          
          $old_event_course_costs = get_post_meta( $post_id, 'wp_birdlife_event_course_costs', true );
          $new_event_course_costs = $_POST['wp_birdlife_event_course_costs'];
          
          if ( ! empty( $new_event_course_costs ) ) {
            update_post_meta( $post_id, 'wp_birdlife_event_course_costs', $new_event_course_costs, $old_event_course_costs );
          }
          
          $old_event_equipment = get_post_meta( $post_id, 'wp_birdlife_event_equipment', true );
          $new_event_equipment = $_POST['wp_birdlife_event_equipment'];
          
          if ( ! empty( $new_event_equipment ) ) {
            update_post_meta( $post_id, 'wp_birdlife_event_equipment', $new_event_equipment, $old_event_equipment );
          }
          
          $old_event_course_additional = get_post_meta( $post_id, 'wp_birdlife_event_course_additional', true );
          $new_event_course_additional = $_POST['wp_birdlife_event_course_additional'];
          
          if ( ! empty( $new_event_course_additional ) ) {
            update_post_meta( $post_id, 'wp_birdlife_event_course_additional', $new_event_course_additional, $old_event_course_additional );
          }
          
          $old_event_neues_feld = get_post_meta( $post_id, 'wp_birdlife_event_neues_feld', true );
          $new_event_neues_feld = $_POST['wp_birdlife_event_neues_feld'];
          
          if ( ! empty( $new_event_neues_feld ) ) {
            update_post_meta( $post_id, 'wp_birdlife_event_neues_feld', $new_event_neues_feld, $old_event_neues_feld );
          }
          
        }
      }
    }
  }
