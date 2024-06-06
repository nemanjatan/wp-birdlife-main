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
			if ( isset( $_POST['wp_birdlife_nonce'] ) && ! wp_verify_nonce( $_POST['wp_birdlife_nonce'], 'wp_birdlife_nonce' ) ) {
				return;
			}

			if ( isset( $_POST['post_type'] ) && $_POST['post_type'] === 'naturkurs' ) {
				if ( ! current_user_can( 'edit_page', $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
					return;
				}
			}

			if ( isset( $_POST['action'] ) && $_POST['action'] == 'editpost' ) {
				$fields = [
					'wp_birdlife_event_registration_until_date',
					'wp_birdlife_event_external_link',
					'wp_birdlife_event_place',
					'wp_birdlife_event_phone',
					'wp_birdlife_event_information_registration',
					'wp_birdlife_event_email',
					'wp_birdlife_event_credits',
					'wp_birdlife_event_online_date',
					'wp_birdlife_event_num_min_lnu',
					'wp_birdlife_event_num_max_lnu',
					'wp_birdlife_event_course_description_short',
					'wp_birdlife_event_cost',
					'wp_birdlife_event_course_multiple_events',
					'wp_birdlife_event_program',
					'wp_birdlife_event_time_to_tim',
					'wp_birdlife_event_overnight_place',
					'wp_birdlife_event_time_from_tim',
					'wp_birdlife_event_materials',
					'wp_birdlife_event_approved_notes',
					'wp_birdlife_event_approved_text',
					'wp_birdlife_event_approved_decision_date',
					'wp_birdlife_event_approved_date',
					'wp_birdlife_event_notes',
					'wp_birdlife_event_description',
					'wp_birdlife_event_date_to_dat',
					'wp_birdlife_event_date_from_dat',
					'wp_birdlife_event_registration_start_dat',
					'wp_birdlife_event_leader',
					'wp_birdlife_event_information',
					'wp_birdlife_event_dating',
					'wp_birdlife_event_offer',
					'wp_birdlife_event_number_participants',
					'wp_birdlife_event_number_groups',
					'wp_birdlife_event_region',
					'wp_birdlife_event_course_description',
					'wp_birdlife_event_organizer',
					'wp_birdlife_event_course_costs',
					'wp_birdlife_event_equipment',
					'wp_birdlife_event_course_additional',
					'wp_birdlife_event_neues_feld',
				];

				foreach ( $fields as $field ) {
					$old_value = get_post_meta( $post_id, $field, true );
					$new_value = $_POST[ $field ] ?? '';

					if ( ! empty( $new_value ) ) {
						update_post_meta( $post_id, $field, $new_value, $old_value );
					}
				}
			}
		}
	}
}
