<?php

if ( ! class_exists( 'WP_Birdlife_Settings' ) ) {
	class WP_Birdlife_Settings {
		public static $options;
		public static $options_for_projects;

		const OPTION_GROUP = 'wp_birdlife_group';
		const OPTION_NAME = 'wp_birdlife_options';
		const OPTION_PROJECTS_NAME = 'wp_birdlife_options_for_projects';
		const PAGE1 = 'wp_birdlife_page1';
		const PAGE2 = 'wp_birdlife_page2';
		const PAGE3 = 'wp_birdlife_page3';

		public function __construct() {
			self::$options              = get_option( self::OPTION_NAME );
			self::$options_for_projects = get_option( self::OPTION_PROJECTS_NAME );
			add_action( 'admin_init', array( $this, 'admin_init' ) );
		}

		public function admin_init() {
			$this->register_settings();
			$this->add_settings_sections();
			$this->add_settings_fields();
		}

		private function register_settings() {
			register_setting(
				self::OPTION_GROUP,
				self::OPTION_NAME,
				array( $this, 'validate_settings' )
			);

			register_setting(
				self::OPTION_GROUP,
				self::OPTION_PROJECTS_NAME,
				array( $this, 'validate_settings' )
			);
		}

		private function add_settings_sections() {
			add_settings_section(
				'main_section',
				'Automatic sync',
				null,
				self::PAGE1
			);

			add_settings_section(
				'main_section_for_projects',
				'Automatic sync',
				null,
				self::PAGE3
			);

			add_settings_section(
				'secondary_section',
				'Manual sync',
				null,
				self::PAGE2
			);
		}

		private function add_settings_fields() {
			add_settings_field(
				'cron_job_time',
				'How often you want BirdLife to sync with ManagePlus?',
				array( $this, 'cron_job_time_callback' ),
				self::PAGE1,
				'main_section'
			);

			add_settings_field(
				'cron_job_time_for_projects',
				'How often you want BirdLife Projects to sync with ManagePlus?',
				array( $this, 'cron_job_time_for_projects_callback' ),
				self::PAGE3,
				'main_section_for_projects'
			);

			add_settings_field(
				'cron_job_last_update',
				'Last Automatic sync:',
				array( $this, 'cron_job_last_update_callback' ),
				self::PAGE1,
				'main_section'
			);

			add_settings_field(
				'cron_job_last_manual_update',
				'Last Manual sync:',
				array( $this, 'cron_job_last_manual_update_callback' ),
				self::PAGE1,
				'main_section'
			);

			add_settings_field(
				'cron_job_for_projects_last_update',
				'Last synced:',
				array( $this, 'cron_job_for_projects_last_update_callback' ),
				self::PAGE3,
				'main_section_for_projects'
			);
		}

		public function cron_job_time_callback() {
			$this->render_select_field(
				'wp_birdlife_cron_job_time',
				self::$options['wp_birdlife_cron_job_time'],
				array( 'Hourly', 'Daily' )
			);

			$this->render_select_field(
				'wp_birdlife_cron_job_time_for_projects',
				self::$options_for_projects['wp_birdlife_cron_job_time_for_projects'],
				array( 'Weekly', 'Monthly' ),
				array( 'style' => 'display: none;' )
			);
		}

		public function cron_job_time_for_projects_callback() {
			$this->render_select_field(
				'wp_birdlife_cron_job_time',
				self::$options['wp_birdlife_cron_job_time'],
				array( 'Hourly', 'Daily' ),
				array( 'style' => 'display: none;' )
			);

			$this->render_select_field(
				'wp_birdlife_cron_job_time_for_projects',
				self::$options_for_projects['wp_birdlife_cron_job_time_for_projects'],
				array( 'Weekly', 'Monthly' )
			);
		}

		private function render_select_field( $name, $selected_value, $options, $attributes = array() ) {
			$attrs = '';
			foreach ( $attributes as $key => $value ) {
				$attrs .= sprintf( ' %s="%s"', $key, esc_attr( $value ) );
			}

			echo '<select id="' . esc_attr( $name ) . '" name="wp_birdlife_options[' . esc_attr( $name ) . ']"' . $attrs . '>';
			foreach ( $options as $option ) {
				$selected = selected( $option, $selected_value, false );
				echo sprintf( '<option value="%s" %s>%s</option>', esc_attr( $option ), $selected, esc_html( $option ) );
			}
			echo '</select>';
		}

		public function cron_job_last_update_callback() {
			$this->render_last_sync_time( 'wp_birdlife_last_sync' );
		}

		public function cron_job_last_manual_update_callback() {
			$this->render_last_sync_time( 'wp_birdlife_last_manual_sync' );
		}

		public function cron_job_for_projects_last_update_callback() {
			$this->render_last_sync_time( 'wp_birdlife_last_sync_for_projects' );
		}

		private function render_last_sync_time( $option_name ) {
			$last_sync = get_option( $option_name );
			if ( $last_sync !== null ) {
				echo '<p>' . gmdate( 'd. M Y H:i:s', $last_sync + 3600 * ( 1 + (int) date( "I" ) ) ) . '</p>';
			}
		}

		public function validate_settings( $input ) {
			$new_input = array();
			foreach ( $input as $key => $value ) {
				$new_input[ $key ] = sanitize_text_field( $value );
			}

			return $new_input;
		}
	}
}
?>
