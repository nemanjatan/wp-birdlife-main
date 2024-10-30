<?php
  
  if ( ! class_exists( 'WP_Birdlife_Settings' ) ) {
    class WP_Birdlife_Settings {
      public static $options;
      public static $options_for_projects;
      
      public function __construct() {
        self::$options              = get_option( 'wp_birdlife_options' );
        self::$options_for_projects = get_option( 'wp_birdlife_options_for_projects' );
        add_action( 'admin_init', array( $this, 'admin_init' ) );
      }
      
      public function admin_init() {
        register_setting(
          'wp_birdlife_group',
          'wp_birdlife_options',
          array( $this, 'wp_birdlife_validate' )
        );
        
        register_setting(
          'wp_birdlife_group',
          'wp_birdlife_options_for_projects',
          array( $this, 'wp_birdlife_validate' )
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
          'wp_birdlife_cron_job_time',
          'How often you want BirdLife to sync with ManagePlus?',
          array( $this, 'wp_birdlife_cron_job_time_callback' ),
          'wp_birdlife_page1',
          'wp_birdlife_main_section'
        );
        
        add_settings_field(
          'wp_birdlife_cron_job_time_for_projects',
          'How often you want BirdLife Projects to sync with ManagePlus?',
          array( $this, 'wp_birdlife_cron_job_for_projects_time_callback' ),
          'wp_birdlife_page3',
          'wp_birdlife_main_section_for_projects'
        );
        
        add_settings_field(
          'wp_birdlife_cron_job_last_update',
          'Last synced:',
          array( $this, 'wp_birdlife_cron_job_last_update_callback' ),
          'wp_birdlife_page1',
          'wp_birdlife_main_section'
        );
        
        add_settings_field(
          'wp_birdlife_cron_job_for_projects_last_update',
          'Last synced:',
          array( $this, 'wp_birdlife_cron_job_for_projects_last_update_callback' ),
          'wp_birdlife_page3',
          'wp_birdlife_main_section_for_projects'
        );
      }
      
      public function wp_birdlife_cron_job_time_callback() {
        ?>
          <select
                  id="wp_birdlife_cron_job_time"
                  name="wp_birdlife_options[wp_birdlife_cron_job_time]">
              <option value="Hourly"
                <?php isset( self::$options['wp_birdlife_cron_job_time'] ) ? selected( 'Hourly', self::$options['wp_birdlife_cron_job_time'], true ) : ''; ?>>
                  Hourly
              </option>
              <option value="Daily"
                <?php isset( self::$options['wp_birdlife_cron_job_time'] ) ? selected( 'Daily', self::$options['wp_birdlife_cron_job_time'], true ) : ''; ?>>
                  Daily
              </option>
          </select>

          <select
                  id="wp_birdlife_cron_job_time_for_projects"
                  name="wp_birdlife_options_for_projects[wp_birdlife_cron_job_time_for_projects]"
                  style="display: none;">
              <option value="Weekly"
                <?php isset( self::$options_for_projects['wp_birdlife_cron_job_time_for_projects'] ) ? selected( 'Weekly', self::$options_for_projects['wp_birdlife_cron_job_time_for_projects'], true ) : ''; ?>>
                  Weekly
              </option>
              <option value="Monthly"
                <?php isset( self::$options_for_projects['wp_birdlife_cron_job_time_for_projects'] ) ? selected( 'Monthly', self::$options_for_projects['wp_birdlife_cron_job_time_for_projects'], true ) : ''; ?>>
                  Monthly
              </option>
          </select>
        <?php
      }
      
      public function wp_birdlife_cron_job_for_projects_time_callback() {
        ?>
          <select
                  id="wp_birdlife_cron_job_time"
                  name="wp_birdlife_options[wp_birdlife_cron_job_time]"
                  style="display: none;">
              <option value="Hourly"
                <?php isset( self::$options['wp_birdlife_cron_job_time'] ) ? selected( 'Hourly', self::$options['wp_birdlife_cron_job_time'], true ) : ''; ?>>
                  Hourly
              </option>
              <option value="Daily"
                <?php isset( self::$options['wp_birdlife_cron_job_time'] ) ? selected( 'Daily', self::$options['wp_birdlife_cron_job_time'], true ) : ''; ?>>
                  Daily
              </option>
          </select>

          <select
                  id="wp_birdlife_cron_job_time_for_projects"
                  name="wp_birdlife_options_for_projects[wp_birdlife_cron_job_time_for_projects]">
              <option value="Weekly"
                <?php isset( self::$options_for_projects['wp_birdlife_cron_job_time_for_projects'] ) ? selected( 'Weekly', self::$options_for_projects['wp_birdlife_cron_job_time_for_projects'], true ) : ''; ?>>
                  Weekly
              </option>
              <option value="Monthly"
                <?php isset( self::$options_for_projects['wp_birdlife_cron_job_time_for_projects'] ) ? selected( 'Monthly', self::$options_for_projects['wp_birdlife_cron_job_time_for_projects'], true ) : ''; ?>>
                  Monthly
              </option>
          </select>
        <?php
      }
      
      public function wp_birdlife_cron_job_last_update_callback() {
        ?>
          <p>
            <?php
              $wp_birdlife_last_sync = get_option( 'wp_birdlife_last_sync' );
              if ( $wp_birdlife_last_sync !== null ) {
                echo gmdate( 'd. M Y H:i:s', $wp_birdlife_last_sync + 3600 * ( 2 + (int) date( "I" ) ) );
              }
            ?>
          </p>
        <?php
      }
      
      public function wp_birdlife_cron_job_for_projects_last_update_callback() {
        ?>
          <p>
            <?php
              $wp_birdlife_last_sync = get_option( 'wp_birdlife_last_sync_for_projects' );
              if ( $wp_birdlife_last_sync !== null ) {
                echo gmdate( 'd. M Y H:i:s', $wp_birdlife_last_sync + 3600 * ( 2 + (int) date( "I" ) ) );
              }
            ?>
          </p>
        <?php
      }
      
      public function wp_birdlife_validate( $input ) {
        $new_input = array();
        foreach ( $input as $key => $value ) {
          $new_input[ $key ] = sanitize_text_field( $value );
        }
        
        return $new_input;
      }
    }
  }
