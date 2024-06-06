<?php

if ( ! class_exists( 'WP_Birdlife_Settings' ) ) {
	class WP_Birdlife_Settings {
		public static $options;
		public static $project_options;

		public function __construct() {
			self::$options         = get_option( 'wp_birdlife_options' );
			self::$project_options = get_option( 'wp_birdlife_options_for_projects' );
			add_action( 'admin_init', array( $this, 'admin_init' ) );
		}

		public function admin_init() {
			register_setting(
				'wp_birdlife_group',
				'wp_birdlife_options',
				array( $this, 'validate_options' )
			);

			register_setting(
				'wp_birdlife_group',
				'wp_birdlife_options_for_projects',
				array( $this, 'validate_options' )
			);

			add_settings_section(
				'wp_birdlife_main_section',
				'Automatic sync',
				null,
				'wp_birdlife_page1'
			);

			add_settings_section(
				'wp_birdlife_main_section_for_projects',
				'Automatic sync',
				null,
				'wp_birdlife_page3'
			);

			add_settings_section(
				'wp_birdlife_secondary_section',
				'Manual sync',
				null,
				'wp_birdlife_page2'
			);

			add_settings_field(
				'wp_birdlife_cron_job_interval',
				'How often do you want BirdLife to sync with ManagePlus?',
				array( $this, 'cron_job_interval_callback' ),
				'wp_birdlife_page1',
				'wp_birdlife_main_section'
			);

			add_settings_field(
				'wp_birdlife_cron_job_interval_for_projects',
				'How often do you want BirdLife Projects to sync with ManagePlus?',
				array( $this, 'cron_job_interval_for_projects_callback' ),
				'wp_birdlife_page3',
				'wp_birdlife_main_section_for_projects'
			);

			add_settings_field(
				'wp_birdlife_last_sync',
				'Last synced:',
				array( $this, 'last_sync_callback' ),
				'wp_birdlife_page1',
				'wp_birdlife_main_section'
			);

			add_settings_field(
				'wp_birdlife_last_sync_for_projects',
				'Last synced:',
				array( $this, 'last_sync_for_projects_callback' ),
				'wp_birdlife_page3',
				'wp_birdlife_main_section_for_projects'
			);
		}

		public function cron_job_interval_callback() {
			?>
        <select id="wp_birdlife_cron_job_interval" name="wp_birdlife_options[wp_birdlife_cron_job_interval]">
            <option value="Hourly" <?php selected( self::$options['wp_birdlife_cron_job_interval'], 'Hourly' ); ?>>
                Hourly
            </option>
            <option value="Daily" <?php selected( self::$options['wp_birdlife_cron_job_interval'], 'Daily' ); ?>>Daily
            </option>
        </select>

        <select id="wp_birdlife_cron_job_interval_for_projects"
                name="wp_birdlife_options_for_projects[wp_birdlife_cron_job_interval_for_projects]"
                style="display: none;">
            <option value="Weekly" <?php selected( self::$project_options['wp_birdlife_cron_job_interval_for_projects'], 'Weekly' ); ?>>
                Weekly
            </option>
            <option value="Monthly" <?php selected( self::$project_options['wp_birdlife_cron_job_interval_for_projects'], 'Monthly' ); ?>>
                Monthly
            </option>
        </select>
			<?php
		}

		public function cron_job_interval_for_projects_callback() {
			?>
        <select id="wp_birdlife_cron_job_interval" name="wp_birdlife_options[wp_birdlife_cron_job_interval]"
                style="display: none;">
            <option value="Hourly" <?php selected( self::$options['wp_birdlife_cron_job_interval'], 'Hourly' ); ?>>
                Hourly
            </option>
            <option value="Daily" <?php selected( self::$options['wp_birdlife_cron_job_interval'], 'Daily' ); ?>>Daily
            </option>
        </select>

        <select id="wp_birdlife_cron_job_interval_for_projects"
                name="wp_birdlife_options_for_projects[wp_birdlife_cron_job_interval_for_projects]">
            <option value="Weekly" <?php selected( self::$project_options['wp_birdlife_cron_job_interval_for_projects'], 'Weekly' ); ?>>
                Weekly
            </option>
            <option value="Monthly" <?php selected( self::$project_options['wp_birdlife_cron_job_interval_for_projects'], 'Monthly' ); ?>>
                Monthly
            </option>
        </select>
			<?php
		}

		public function last_sync_callback() {
			?>
        <p>
					<?php
					$last_sync = get_option( 'wp_birdlife_last_sync' );
					if ( $last_sync !== null ) {
						echo gmdate( 'd. M Y H:i:s', $last_sync + 3600 * ( 2 + (int) date( "I" ) ) );
					}
					?>
        </p>
			<?php
		}

		public function last_sync_for_projects_callback() {
			?>
        <p>
					<?php
					$last_sync = get_option( 'wp_birdlife_last_sync_for_projects' );
					if ( $last_sync !== null ) {
						echo gmdate( 'd. M Y H:i:s', $last_sync + 3600 * ( 2 + (int) date( "I" ) ) );
					}
					?>
        </p>
			<?php
		}

		public function validate_options( $input ) {
			$new_input = array();
			foreach ( $input as $key => $value ) {
				$new_input[ $key ] = sanitize_text_field( $value );
			}

			return $new_input;
		}
	}
}
