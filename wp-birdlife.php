<?php
  /**
   * Plugin Name: WP BirdLife
   * Description: WP BirdLife plugin handles ManagePlus API calls to fetch & update events
   * Version: 1.0
   * Requires at least: 5.6
   * Author: Nemanja Tanaskovic
   * Text Domain: wp-birdlife
   * Domain Path: /languages
   */
  
  if ( ! defined( 'ABSPATH' ) ) {
    exit;
  }
  
  if ( ! class_exists( 'WP_Birdlife' ) ) {
    class WP_Birdlife {
      function __construct() {
        $this->define_constants();
        
        require_once( WP_BIRDLIFE_PATH . 'functions/events/booking-event/class.wp-birdlife-book-event.php' );
        $WP_Birdlife_Book_Event = new WP_Birdlife_Book_Event();
        
        require_once( WP_BIRDLIFE_PATH . 'functions/events/class.wp-birdlife-event.php' );
        $WP_Birdlife_Event = new WP_Birdlife_Event();
        
        require_once( WP_BIRDLIFE_PATH . 'functions/projects/class.wp-birdlife-project.php' );
        $WP_Birdlife_Project = new WP_Birdlife_Project();
        
        require_once( WP_BIRDLIFE_PATH . 'functions/events/class.wp-birdlife-helper.php' );
        $WP_Birdlife_Helper = new WP_Birdlife_Helper();
        
        require_once( WP_BIRDLIFE_PATH . 'functions/events/multimedia/class.wp-birdlife-event-multimedia.php' );
        $WP_Birdlife_Helper = new WP_Birdlife_Event_Multimedia();
        
        require_once( WP_BIRDLIFE_PATH . 'functions/events/utils/class.wp-birdlife-event-reference.php' );
        $WP_Birdlife_Helper = new WP_Birdlife_Event_Reference();
        
        require_once( WP_BIRDLIFE_PATH . 'functions/events/free-seats/class.wp-birdlife-free-seats.php' );
        $WP_Birdlife_Helper = new WP_Birdlife_Free_Seats();
        
        require_once( WP_BIRDLIFE_PATH . 'functions/events/new-events/class.wp-birdlife-new-event.php' );
        $WP_Birdlife_New_Event = new WP_Birdlife_New_Event();
        
        require_once( WP_BIRDLIFE_PATH . 'functions/events/update-events/class.wp-birdlife-update-event.php' );
        $WP_Birdlife_Update_Event = new WP_Birdlife_Update_Event();
        
        require_once( WP_BIRDLIFE_PATH . 'functions/projects/update-projects/class.wp-birdlife-update-project.php' );
        $WP_Birdlife_Update_Event = new WP_Birdlife_Update_Project();
        
        require_once( WP_BIRDLIFE_PATH . 'functions/events/class.wp-birdlife-reserved-tn.php' );
        $WP_Birdlife_Reserved_Tn = new WP_Birdlife_Reserved_Tn();
        
        require_once( WP_BIRDLIFE_PATH . 'post-types/class.wp-birdlife-cpt.php' );
        $WP_Birdlife_Post_Type = new WP_Birdlife_Post_Type();
        
        require_once( WP_BIRDLIFE_PATH . 'post-types/class.wp-birdlife-project-cpt.php' );
        $WP_Birdlife_Project_Post_Type = new WP_Birdlife_Project_Post_Type();
        
        require_once( WP_BIRDLIFE_PATH . 'class.wp-birdlife-settings.php' );
        $WP_Birdlife_Settings = new WP_Birdlife_Settings();
        
        add_action( 'admin_menu', array( $this, 'add_menu' ) );
        
        // events
        add_action( 'admin_footer', array( $WP_Birdlife_Event, 'hard_refresh_ajax_script' ) );
        add_action( 'wp_ajax_hard_refresh_action', array( $WP_Birdlife_Event, 'hard_refresh_ajax_handler' ) );
        add_action( 'wp_ajax_get_last_sync', array( $WP_Birdlife_Event, 'get_last_sync' ) );
        
        // projects
        add_action( 'wp_ajax_get_last_sync_for_projects', array(
          $WP_Birdlife_Project,
          'get_last_sync'
        ) );
        add_action( 'admin_footer', array( $WP_Birdlife_Project, 'hard_refresh_ajax_script' ) );
        add_action( 'wp_ajax_projects_hard_refresh_action', array(
          $WP_Birdlife_Project,
          'refresh_ajax_handler'
        ) );
        
        if ( isset( $_POST['submit'] ) and $_POST['action'] == 'anmelden' ) {
          $error = $this->checkBookingError();
          if ( $error !== '' ) {
            ?>
              <div class="fl-builder-content">
                  <div class="fl-row fl-row-fixed-width fl-row-bg-none">
                      <div class="fl-row-content">
                          <div class="fl-row-content-wrap">
                              <div class="fl-module-content fl-node-content">
                                  <div class="alert alert-success"
                                       style="color: #763c3c; background-color: #f0d8d8; border-color: #e9c6c6;">
                                      <div class="alert-padding" style="padding-top: 20px;
                                            padding-bottom: 20px;
                                            padding-left: 20px;
                                            padding-right: 20px;">
                                          <h2 style="color: #763c3c; line-height: 22px; text-align: center;"><?php echo $error ?></h2>
                                      </div>
                                  </div>
                              </div>
                          </div>
                      </div>
                  </div>
              </div>
            <?php
          } else {
            $url = 'https://de1.zetcom-group.de/MpWeb-maZurichBirdlife/ria-ws/application/module/Booking';
            list(
              $second_person_exists,
              $xml,
              $second_xml,
              $first_person_email,
              $second_person_email,
              $event_id,
              $first_name,
              $last_name,
              $street,
              $postal_code,
              $city,
              $second_person_first_name,
              $second_person_last_name,
              $gleiche_adresse_active ) = $WP_Birdlife_Book_Event->create_xml_body_request();
            
            $args = $this->get_manage_plus_api_args( $xml );
            $resp = wp_remote_post( $url, $args );
            
            if ( str_contains( $resp['body'], 'zetcom - Error report' ) ) {
              ?>
                <div class="fl-builder-content">
                    <div class="fl-row fl-row-fixed-width fl-row-bg-none">
                        <div class="fl-row-content">
                            <div class="fl-row-content-wrap">
                                <div class="fl-module-content fl-node-content">
                                    <div class="alert alert-success"
                                         style="color: #763c3c; background-color: #f0d8d8; border-color: #e9c6c6;">
                                        <div class="alert-padding" style="padding-top: 20px;
                                            padding-bottom: 20px;
                                            padding-left: 20px;
                                            padding-right: 20px;">
                                            <h2 style="color: #763c3c; line-height: 22px; text-align: center;">
                                                Service unavailable</h2>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
              <?php
            } else {
              // send email to first person
              global $wpdb;
              
              $wp_birdlife_manage_plus_event_id = $wpdb->get_row(
                $wpdb->prepare( "SELECT * FROM $wpdb->postmeta WHERE meta_key = 'wp_birdlife_manage_plus_event_id' AND meta_value = '" . $event_id . "'" )
              );
              
              $post_id = $wp_birdlife_manage_plus_event_id->post_id;
              $post    = get_post( $post_id );
              $title   = isset( $post->post_title ) ? $post->post_title : '';
              
              $to      = $first_person_email;
              $subject = "Eingangsbestätigung Anmeldung, Kurse für Fortgeschrittene: " . $title;
              if ( $gleiche_adresse_active ) {
                $txt = "Guten Tag.<br><br>Folgende Anmeldung ist bei uns eingegangen: <br><br>Erste Person:<br>" . $first_name . " " . $last_name . ", " . $first_person_email . " <br>" . $street . " <br><br>" . $postal_code . " " . $city . "<br><br>Zweite Person:<br>" . $second_person_first_name . " " . $second_person_last_name . ", " . $second_person_email . "<br>" . $street . " <br><br>" . $postal_code . " " . $city . " <br><br>Sie werden in den nächsten Tagen von uns über den Stand Ihrer Anmeldung informiert. Sollten Sie Fragen haben, stehen wir Ihnen gerne per E-Mail an info@birdlife-zuerich.ch zur Verfügung.<br><br>Mit herzlichen Grüssen<br><br>Birdlife Zürich<br>Wiedingstrasse 78<br>8045 Zürich<br><br></br>info@birdlife-zuerich.ch<br>www.birdlife-zuerich.ch";
              } else {
                $txt = "Guten Tag.<br><br>Folgende Anmeldung ist bei uns eingegangen: <br><br>Erste Person:<br>" . $first_name . " " . $last_name . ", " . $first_person_email . " <br>" . $street . " <br><br>" . $postal_code . " " . $city . "<br><br>Zweite Person:<br>" . $second_person_first_name . " " . $second_person_last_name . ", " . $second_person_email . " <br><br>Sie werden in den nächsten Tagen von uns über den Stand Ihrer Anmeldung informiert. Sollten Sie Fragen haben, stehen wir Ihnen gerne per E-Mail an info@birdlife-zuerich.ch zur Verfügung.<br><br>Mit herzlichen Grüssen<br><br>Birdlife Zürich<br>Wiedingstrasse 78<br>8045 Zürich<br><br></br>info@birdlife-zuerich.ch<br>www.birdlife-zuerich.ch";
              }
              $headers = "Content-Type: text/html; charset=UTF-8\r\nFrom: info@birdlife-zuerich.ch\r\nCc: info@birdlife-zuerich.ch";
              mail( $to, $subject, $txt, $headers );
              // end of send email to first person
              
              if ( $second_person_exists ) {
                $args  = $this->get_manage_plus_api_args( $second_xml );
                $resp1 = wp_remote_post( $url, $args );
                
                if ( str_contains( $resp1['body'], 'zetcom - Error report' ) ) {
                  ?>
                    <div class="fl-builder-content">
                        <div class="fl-row fl-row-fixed-width fl-row-bg-none">
                            <div class="fl-row-content">
                                <div class="fl-row-content-wrap">
                                    <div class="fl-module-content fl-node-content">
                                        <div class="alert alert-success"
                                             style="color: #763c3c; background-color: #f0d8d8; border-color: #e9c6c6;">
                                            <div class="alert-padding" style="padding-top: 20px;
                                            padding-bottom: 20px;
                                            padding-left: 20px;
                                            padding-right: 20px;">
                                                <h2 style="color: #763c3c; line-height: 22px; text-align: center;">
                                                    Service unavailable</h2>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                  <?php
                } else {
                  // send email to second person
                  if ( $second_person_exists ) {
                    $to = $second_person_email;
                    
                    if ( $gleiche_adresse_active ) {
                      $txt = "Guten Tag.<br><br>Sie wurden von " . $first_name . " " . $last_name . ", " . $first_person_email . " für folgenden Kurs angemeldet:<br>" . $title . "<br><br>Angaben zu ihrer Person:<br>" . $second_person_first_name . " " . $second_person_last_name . "<br>" . $second_person_email . "<br>" . $street . " <br><br>" . $postal_code . " " . $city . "<br><br>Sie werden in den nächsten Tagen von uns weitere Informationen erhalten.<br>Wenn dies ein Fehler ist, bitte kontaktieren Sie uns über info@birdlife-zuerich.ch<br><br>Mit herzlichen Grüssen<br>BirdLife Zürich<br>Wiedingstrasse 78<br>8045 Zürich<br><br></br>info@birdlife-zuerich.ch<br>www.birdlife-zuerich.ch";
                    } else {
                      $txt = "Guten Tag.<br><br>Sie wurden von " . $first_name . " " . $last_name . ", " . $first_person_email . " für folgenden Kurs angemeldet:<br>" . $title . "<br><br>Angaben zu ihrer Person:<br>" . $second_person_first_name . " " . $second_person_last_name . "<br>" . $second_person_email . "<br><br>Sie werden in den nächsten Tagen von uns weitere Informationen erhalten.<br>Wenn dies ein Fehler ist, bitte kontaktieren Sie uns über info@birdlife-zuerich.ch<br><br>Mit herzlichen Grüssen<br>BirdLife Zürich<br>Wiedingstrasse 78<br>8045 Zürich<br><br></br>info@birdlife-zuerich.ch<br>www.birdlife-zuerich.ch";
                    }
                    
                    mail( $to, $subject, $txt, $headers );
                  }
                  // end of send email to second person
                  
                  ?>
                    <div class="fl-builder-content">
                        <div class="fl-row fl-row-fixed-width fl-row-bg-none">
                            <div class="fl-row-content">
                                <div class="fl-row-content-wrap">
                                    <div class="fl-module-content fl-node-content">
                                        <div class="alert alert-success"
                                             style="color: #85bb7b;background-color: #85bb7b;border-color: #85bb7b;">
                                            <div class="alert-padding" style="padding-top: 20px;
                                                padding-bottom: 20px;
                                                padding-left: 20px;
                                                padding-right: 20px;">
                                                <h2 style="color: #ffffff;line-height: 22px;text-align: center;">
                                                    Veranstaltung gebucht</h2>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                  <?php
                }
              } else {
                ?>
                  <div class="fl-builder-content">
                      <div class="fl-row fl-row-fixed-width fl-row-bg-none">
                          <div class="fl-row-content">
                              <div class="fl-row-content-wrap">
                                  <div class="fl-module-content fl-node-content">
                                      <div class="alert alert-success"
                                           style="color: #85bb7b;background-color: #85bb7b;border-color: #85bb7b;">
                                          <div class="alert-padding" style="padding-top: 20px;
                                                padding-bottom: 20px;
                                                padding-left: 20px;
                                                padding-right: 20px;">
                                              <h2 style="color: #ffffff;line-height: 22px;text-align: center;">
                                                  Veranstaltung gebucht</h2>
                                          </div>
                                      </div>
                                  </div>
                              </div>
                          </div>
                      </div>
                  </div>
                <?php
              }
            }
          }
        }
      }
      
      public function define_constants() {
        define( 'WP_BIRDLIFE_PATH', plugin_dir_path( __FILE__ ) );
        define( 'WP_BIRDLIFE_URL', plugin_dir_url( __FILE__ ) );
        define( 'WP_BIRDLIFE_VERSION', '1.0.0' );
      }
      
      public static function activate() {
        update_option( 'rewrite_rules', '' );
        unregister_post_type( 'naturkurs' );
      }
      
      public static function deactivate() {
        flush_rewrite_rules();
      }
      
      public static function uninstall() {
        delete_option( 'wp_birdlife_options' );
        delete_option( 'wp_birdlife_options_for_projects' );
        
        $posts = get_posts(
          array(
            'post_type'    => 'naturkurs',
            'number_posts' => - 1,
            'post_status'  => 'any'
          )
        );
        
        foreach ( $posts as $post ) {
          wp_delete_post( $post->ID, true );
        }
      }
      
      public function add_menu() {
        add_menu_page(
          'BirdLife & ManagePlus Settings',
          'BirdLife & ManagePlus',
          'edit_pages',
          'wp_birdlife_admin',
          array( $this, 'wp_birdlife_settings_page' ),
          'dashicons-image-rotate-right'
        );
        
        add_menu_page(
          'Naturförderung projects Settings',
          'Naturförderung projects',
          'edit_pages',
          'wp_birdlife_projects_admin',
          array( $this, 'wp_birdlife_projects_settings_page' ),
          'dashicons-welcome-write-blog'
        );
        
        add_submenu_page(
          'wp_birdlife_projects_admin',
          'Projects',
          'Projects',
          'edit_pages',
          'edit.php?post_type=naturforderung',
          null
        );
        
        add_submenu_page(
          'wp_birdlife_admin',
          'ManagePlus Events',
          'ManagePlus Events',
          'edit_pages',
          'edit.php?post_type=naturkurs',
          null
        );
        
        add_submenu_page(
          'wp_birdlife_admin',
          'Add New Event',
          'Add New Event',
          'edit_pages',
          'post-new.php?post_type=naturkurs',
          null
        );
      }
      
      public function wp_birdlife_settings_page() {
        if ( ! current_user_can( 'edit_pages' ) ) {
          return;
        }
        
        $WP_Birdlife_Event = new WP_Birdlife_Event();
        settings_errors( 'wp_birdlife_options' );
        
        require( WP_BIRDLIFE_PATH . 'views/settings-page.php' );
      }
      
      public function wp_birdlife_projects_settings_page() {
        if ( ! current_user_can( 'edit_pages' ) ) {
          return;
        }
        
        require( WP_BIRDLIFE_PATH . 'views/projects-settings-page.php' );
      }
      
      public function checkBookingError(): string {
        $error = '';
        if ( ! isset( $_POST['first_name'] ) || $_POST['first_name'] === '' ) {
          $error = $error . '[Vorname ungültig]';
        } else if ( ! isset( $_POST['last_name'] ) || $_POST['last_name'] === '' ) {
          $error = $error . '[Nachname ungültig]';
        } else if ( ! isset( $_POST['street'] ) || $_POST['street'] === '' ) {
          $error = $error . '[Strasse Und Nummer ungültig]';
        } else if ( ! isset( $_POST['postal_code'] ) || $_POST['postal_code'] === '' ) {
          $error = $error . '[PLZ ungültig]';
        } else if ( ! isset( $_POST['city'] ) || $_POST['city'] === '' ) {
          $error = $error . '[Ort ungültig]';
        } else if ( ! isset( $_POST['email'] ) || $_POST['email'] === '' ) {
          $error = $error . '[E-Mail ungültig]';
        } else if ( ! isset( $_POST['phone_number'] ) || $_POST['phone_number'] === '' ) {
          $error = $error . '[Telefon ungültig]';
        } else if ( ! isset( $_POST['agb'] ) || $_POST['agb'] === '' ) {
          $error = $error . 'Sie müssen den Nutzungsbedingungen zustimmen!';
        }
        
        return $error;
      }
      
      public function get_manage_plus_api_args( string $xml ): array {
        return array(
          'headers' => array(
            'Authorization' => 'Basic ' . base64_encode( MANAGE_PLUS_USERNAME . ':' . MANAGE_PLUS_PASSWORD ),
            'Content-Type'  => 'application/xml'
          ),
          'body'    => $xml,
          'timeout' => 50
        );
      }
      
      public function generate_german_date( string $wp_birdlife_event_date_from_dat ): array {
        $day_in_week = date( 'w', strtotime( $wp_birdlife_event_date_from_dat ) );
        $day         = 'Mo';
        
        switch ( $day_in_week ) {
          case "2":
            $day = 'Di';
            break;
          case "3":
            $day = 'Mi';
            break;
          case "4":
            $day = 'Do';
            break;
          case "5":
            $day = 'Fr';
            break;
          case "6":
            $day = 'Sa';
            break;
          case "0":
            $day = 'So';
            break;
        }
        
        $month_name     = date( "F", strtotime( $wp_birdlife_event_date_from_dat ) );
        $day_from_date  = date( 'd', strtotime( $wp_birdlife_event_date_from_dat ) );
        $year_from_date = date( 'Y', strtotime( $wp_birdlife_event_date_from_dat ) );
        
        $german_month_name = '';
        
        switch ( $month_name ) {
          case "January":
            $german_month_name = 'Januar';
            break;
          case "February":
            $german_month_name = 'Februar';
            break;
          case "March":
            $german_month_name = 'März';
            break;
          case "April":
            $german_month_name = 'April';
            break;
          case "May":
            $german_month_name = 'Mai';
            break;
          case "June":
            $german_month_name = 'Juni';
            break;
          case "July":
            $german_month_name = 'Juli';
            break;
          case "August":
            $german_month_name = 'August';
            break;
          case "September":
            $german_month_name = 'September';
            break;
          case "October":
            $german_month_name = 'Oktober';
            break;
          case "November":
            $german_month_name = 'November';
            break;
          case "December":
            $german_month_name = 'Dezember';
            break;
        }
        
        return array( $day, $day_from_date, $year_from_date, $german_month_name );
      }
      
    }
  }
  
  if ( class_exists( 'WP_Birdlife' ) ) {
    register_activation_hook( __FILE__, array( 'WP_Birdlife', 'activate' ) );
    register_deactivation_hook( __FILE__, array( 'WP_Birdlife', 'deactivate' ) );
    register_uninstall_hook( __FILE__, array( 'WP_Birdlife', 'uninstall' ) );
    $wp_birdlife = new WP_Birdlife();
  }
