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
			$this->include_files();
			$this->initialize_classes();
			$this->add_actions();
		}

		private function define_constants() {
			define( 'WP_BIRDLIFE_PATH', plugin_dir_path( __FILE__ ) );
			define( 'WP_BIRDLIFE_URL', plugin_dir_url( __FILE__ ) );
			define( 'WP_BIRDLIFE_VERSION', '1.0.0' );
		}

		private function include_files() {
			$files = [
				'functions/events/booking-event/class.wp-birdlife-book-event.php',
				'functions/events/class.wp-birdlife-event.php',
				'functions/projects/class.wp-birdlife-project.php',
				'functions/events/class.wp-birdlife-helper.php',
				'functions/events/multimedia/class.wp-birdlife-event-multimedia.php',
				'functions/events/utils/class.wp-birdlife-event-reference.php',
				'functions/events/free-seats/class.wp-birdlife-free-seats.php',
				'functions/events/new-events/class.wp-birdlife-new-event.php',
				'functions/events/update-events/class.wp-birdlife-update-event.php',
				'functions/projects/update-projects/class.wp-birdlife-update-project.php',
				'functions/events/class.wp-birdlife-reserved-tn.php',
				'post-types/class.wp-birdlife-cpt.php',
				'post-types/class.wp-birdlife-project-cpt.php',
				'class.wp-birdlife-settings.php',
			];

			foreach ( $files as $file ) {
				require_once( WP_BIRDLIFE_PATH . $file );
			}
		}

		private function initialize_classes() {
			$this->WP_Birdlife_Book_Event        = new WP_Birdlife_Book_Event();
			$this->WP_Birdlife_Event             = new WP_Birdlife_Event();
			$this->WP_Birdlife_Project           = new WP_Birdlife_Project();
			$this->WP_Birdlife_Helper            = new WP_Birdlife_Helper();
			$this->WP_Birdlife_Event_Multimedia  = new WP_Birdlife_Event_Multimedia();
			$this->WP_Birdlife_Event_Reference   = new WP_Birdlife_Event_Reference();
			$this->WP_Birdlife_Free_Seats        = new WP_Birdlife_Free_Seats();
			$this->WP_Birdlife_New_Event         = new WP_Birdlife_New_Event();
			$this->WP_Birdlife_Update_Event      = new WP_Birdlife_Update_Event();
			$this->WP_Birdlife_Update_Project    = new WP_Birdlife_Update_Project();
			$this->WP_Birdlife_Reserved_Tn       = new WP_Birdlife_Reserved_Tn();
			$this->WP_Birdlife_Post_Type         = new WP_Birdlife_Post_Type();
			$this->WP_Birdlife_Project_Post_Type = new WP_Birdlife_Project_Post_Type();
			$this->WP_Birdlife_Settings          = new WP_Birdlife_Settings();
		}

		private function add_actions() {
			add_action( 'admin_menu', array( $this, 'add_menu' ) );

			// events
			add_action( 'admin_footer', array( $this->WP_Birdlife_Event, 'hard_refresh_ajax_script' ) );
			add_action( 'wp_ajax_hard_refresh_action', array( $this->WP_Birdlife_Event, 'hard_refresh_ajax_handler' ) );
			add_action( 'wp_ajax_get_last_sync', array( $this->WP_Birdlife_Event, 'get_last_sync' ) );

			// projects
			add_action( 'admin_footer', array( $this->WP_Birdlife_Project, 'hard_refresh_ajax_script' ) );
			add_action( 'wp_ajax_projects_hard_refresh_action', array( $this->WP_Birdlife_Project, 'refresh_ajax_handler' ) );
			add_action( 'wp_ajax_get_last_sync_for_projects', array( $this->WP_Birdlife_Project, 'get_last_sync' ) );

			if ( isset( $_POST['submit'] ) && $_POST['action'] == 'anmelden' ) {
				$this->handle_booking_submission();
			}
		}

		private function handle_booking_submission() {
			$error = $this->checkBookingError();

			if ( wpa_check_is_spam( $_POST ) ) {
				$error = "Not allowed!";
			}

			if ( $error !== '' ) {
				$this->display_error_message( $error );
			} else {
				$this->process_booking();
			}
		}

		private function display_error_message( $error ) {
			?>
        <div class="fl-builder-content">
            <div class="fl-row fl-row-fixed-width fl-row-bg-none">
                <div class="fl-row-content">
                    <div class="fl-row-content-wrap">
                        <div class="fl-module-content fl-node-content">
                            <div class="alert alert-success"
                                 style="color: #763c3c; background-color: #f0d8d8; border-color: #e9c6c6;">
                                <div class="alert-padding" style="padding: 20px;">
                                    <h2 style="color: #763c3c; line-height: 22px; text-align: center;"><?php echo $error; ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
			<?php
		}

		private function process_booking() {
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
				$gleiche_adresse_active,
				$newsletter
				) = $this->WP_Birdlife_Book_Event->create_xml_body_request();

			$args = $this->get_manage_plus_api_args( $xml );
			$resp = wp_remote_post( $url, $args );

			if ( str_contains( $resp['body'], 'zetcom - Error report' ) ) {
				$this->display_service_unavailable_message();
			} else {
				$this->handle_successful_booking(
					$second_person_exists,
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
					$gleiche_adresse_active,
					$newsletter
				);
			}
		}

		private function display_service_unavailable_message() {
			?>
        <div class="fl-builder-content">
            <div class="fl-row fl-row-fixed-width fl-row-bg-none">
                <div class="fl-row-content">
                    <div class="fl-row-content-wrap">
                        <div class="fl-module-content fl-node-content">
                            <div class="alert alert-success"
                                 style="color: #763c3c; background-color: #f0d8d8; border-color: #e9c6c6;">
                                <div class="alert-padding" style="padding: 20px;">
                                    <h2 style="color: #763c3c; line-height: 22px; text-align: center;">Service
                                        unavailable</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
			<?php
		}

		private function handle_successful_booking(
			$second_person_exists,
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
			$gleiche_adresse_active,
			$newsletter
		) {
			$this->send_confirmation_email( $first_person_email, $second_person_exists, $second_person_email, $event_id, $first_name, $last_name, $street, $postal_code, $city, $second_person_first_name, $second_person_last_name, $gleiche_adresse_active, $newsletter );

			if ( $second_person_exists ) {
				$args  = $this->get_manage_plus_api_args( $second_xml );
				$resp1 = wp_remote_post( $url, $args );

				if ( str_contains( $resp1['body'], 'zetcom - Error report' ) ) {
					$this->display_service_unavailable_message();
				} else {
					$this->send_confirmation_email_to_second_person( $first_name, $last_name, $first_person_email, $second_person_first_name, $second_person_last_name, $second_person_email, $street, $postal_code, $city, $gleiche_adresse_active );
					$this->display_booking_success_message();
				}
			} else {
				$this->display_booking_success_message();
			}
		}

		private function send_confirmation_email(
			$first_person_email,
			$second_person_exists,
			$second_person_email,
			$event_id,
			$first_name,
			$last_name,
			$street,
			$postal_code,
			$city,
			$second_person_first_name,
			$second_person_last_name,
			$gleiche_adresse_active,
			$newsletter
		) {
			global $wpdb;

			$wp_birdlife_manage_plus_event_id = $wpdb->get_row(
				$wpdb->prepare( "SELECT * FROM $wpdb->postmeta WHERE meta_key = 'wp_birdlife_manage_plus_event_id' AND meta_value = %s", $event_id )
			);

			$post_id = $wp_birdlife_manage_plus_event_id->post_id;
			$post    = get_post( $post_id );
			$title   = isset( $post->post_title ) ? $post->post_title : '';

			if ( $newsletter ) {
				$this->subscribe_to_newsletter( $first_person_email, $first_name, $last_name );
			}

			$to      = $first_person_email;
			$subject = "Eingangsbestätigung Anmeldung: " . $title;
			$txt     = $this->generate_email_body( $first_name, $last_name, $first_person_email, $street, $postal_code, $city, $second_person_first_name, $second_person_last_name, $second_person_email, $gleiche_adresse_active );

			$headers = "Content-Type: text/html; charset=UTF-8\r\nFrom: kurse@birdlife-zuerich.ch\r\nCc: kurse@birdlife-zuerich.ch";
			mail( $to, $subject, $txt, $headers );
		}

		private function subscribe_to_newsletter( $email, $first_name, $last_name ) {
			$url     = 'https://api.brevo.com/v3/contacts';
			$apiKey  = BREVO_API_KEY;
			$headers = [
				'accept: application/json',
				'content-type: application/json',
				"api-key: $apiKey"
			];
			$data    = [
				'email'      => $email,
				'listIds'    => [ 2 ],
				'attributes' => [
					'NACHNAME' => $last_name,
					'NAME'     => $last_name,
					'VORNAME'  => $first_name
				],
			];

			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, $url );
			curl_setopt( $ch, CURLOPT_POST, true );
			curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $data ) );
			curl_exec( $ch );
			curl_close( $ch );
		}

		private function generate_email_body(
			$first_name,
			$last_name,
			$first_person_email,
			$street,
			$postal_code,
			$city,
			$second_person_first_name,
			$second_person_last_name,
			$second_person_email,
			$gleiche_adresse_active
		) {
			if ( $gleiche_adresse_active ) {
				return "Guten Tag.<br><br>Folgende Anmeldung ist bei uns eingegangen: <br><br>Erste Person:<br>" . $first_name . " " . $last_name . ", " . $first_person_email . " <br>" . $street . " <br><br>" . $postal_code . " " . $city . "<br><br>Zweite Person:<br>" . $second_person_first_name . " " . $second_person_last_name . ", " . $second_person_email . "<br>" . $street . " <br><br>" . $postal_code . " " . $city . " <br><br>Sie werden in den nächsten Tagen von uns über den Stand Ihrer Anmeldung informiert. Sollten Sie Fragen haben, stehen wir Ihnen gerne per E-Mail an kurse@birdlife-zuerich.ch zur Verfügung.<br><br>Mit herzlichen Grüssen<br><br>Birdlife Zürich<br>Wiedingstrasse 78<br>8045 Zürich<br><br></br>kurse@birdlife-zuerich.ch<br>www.birdlife-zuerich.ch";
			} else {
				return "Guten Tag.<br><br>Folgende Anmeldung ist bei uns eingegangen: <br><br>Erste Person:<br>" . $first_name . " " . $last_name . ", " . $first_person_email . " <br>" . $street . " <br><br>" . $postal_code . " " . $city . "<br><br>Zweite Person:<br>" . $second_person_first_name . " " . $second_person_last_name . ", " . $second_person_email . " <br><br>Sie werden in den nächsten Tagen von uns über den Stand Ihrer Anmeldung informiert. Sollten Sie Fragen haben, stehen wir Ihnen gerne per E-Mail an kurse@birdlife-zuerich.ch zur Verfügung.<br><br>Mit herzlichen Grüssen<br><br>Birdlife Zürich<br>Wiedingstrasse 78<br>8045 Zürich<br><br></br>kurse@birdlife-zuerich.ch<br>www.birdlife-zuerich.ch";
			}
		}

		private function send_confirmation_email_to_second_person(
			$first_name,
			$last_name,
			$first_person_email,
			$second_person_first_name,
			$second_person_last_name,
			$second_person_email,
			$street,
			$postal_code,
			$city,
			$gleiche_adresse_active
		) {
			$to      = $second_person_email;
			$subject = "Eingangsbestätigung Anmeldung: " . $title;
			$txt     = $this->generate_email_body_for_second_person( $first_name, $last_name, $first_person_email, $second_person_first_name, $second_person_last_name, $second_person_email, $street, $postal_code, $city, $gleiche_adresse_active );

			$headers = "Content-Type: text/html; charset=UTF-8\r\nFrom: kurse@birdlife-zuerich.ch\r\nCc: kurse@birdlife-zuerich.ch";
			mail( $to, $subject, $txt, $headers );
		}

		private function generate_email_body_for_second_person(
			$first_name,
			$last_name,
			$first_person_email,
			$second_person_first_name,
			$second_person_last_name,
			$second_person_email,
			$street,
			$postal_code,
			$city,
			$gleiche_adresse_active
		) {
			if ( $gleiche_adresse_active ) {
				return "Guten Tag.<br><br>Sie wurden von " . $first_name . " " . $last_name . ", " . $first_person_email . " für folgenden Kurs angemeldet:<br>" . $title . "<br><br>Angaben zu ihrer Person:<br>" . $second_person_first_name . " " . $second_person_last_name . "<br>" . $second_person_email . "<br>" . $street . " <br><br>" . $postal_code . " " . $city . "<br><br>Sie werden in den nächsten Tagen von uns weitere Informationen erhalten.<br>Wenn dies ein Fehler ist, bitte kontaktieren Sie uns über kurse@birdlife-zuerich.ch<br><br>Mit herzlichen Grüssen<br>BirdLife Zürich<br>Wiedingstrasse 78<br>8045 Zürich<br><br></br>kurse@birdlife-zuerich.ch<br>www.birdlife-zuerich.ch";
			} else {
				return "Guten Tag.<br><br>Sie wurden von " . $first_name . " " . $last_name . ", " . $first_person_email . " für folgenden Kurs angemeldet:<br>" . $title . "<br><br>Angaben zu ihrer Person:<br>" . $second_person_first_name . " " . $second_person_last_name . "<br>" . $second_person_email . "<br><br>Sie werden in den nächsten Tagen von uns weitere Informationen erhalten.<br>Wenn dies ein Fehler ist, bitte kontaktieren Sie uns über kurse@birdlife-zuerich.ch<br><br>Mit herzlichen Grüssen<br>BirdLife Zürich<br>Wiedingstrasse 78<br>8045 Zürich<br><br></br>kurse@birdlife-zuerich.ch<br>www.birdlife-zuerich.ch";
			}
		}

		private function display_booking_success_message() {
			?>
        <div class="fl-builder-content">
            <div class="fl-row fl-row-fixed-width fl-row-bg-none">
                <div class="fl-row-content">
                    <div class="fl-row-content-wrap">
                        <div class="fl-module-content fl-node-content">
                            <div class="alert alert-success"
                                 style="color: #85bb7b; background-color: #85bb7b; border-color: #85bb7b;">
                                <div class="alert-padding" style="padding: 20px;">
                                    <h2 style="color: #ffffff; line-height: 22px; text-align: center;">Veranstaltung
                                        gebucht</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
			<?php
		}

		private function checkBookingError(): string {
			$error = '';
			if ( ! isset( $_POST['first_name'] ) || $_POST['first_name'] === '' ) {
				$error .= '[Vorname ungültig]';
			} else if ( ! isset( $_POST['last_name'] ) || $_POST['last_name'] === '' ) {
				$error .= '[Nachname ungültig]';
			} else if ( ! isset( $_POST['street'] ) || $_POST['street'] === '' ) {
				$error .= '[Strasse Und Nummer ungültig]';
			} else if ( ! isset( $_POST['postal_code'] ) || $_POST['postal_code'] === '' ) {
				$error .= '[PLZ ungültig]';
			} else if ( ! isset( $_POST['city'] ) || $_POST['city'] === '' ) {
				$error .= '[Ort ungültig]';
			} else if ( ! isset( $_POST['email'] ) || $_POST['email'] === '' ) {
				$error .= '[E-Mail ungültig]';
			} else if ( ! isset( $_POST['phone_number'] ) || $_POST['phone_number'] === '' ) {
				$error .= '[Telefon ungültig]';
			} else if ( ! isset( $_POST['agb'] ) || $_POST['agb'] === '' ) {
				$error .= 'Sie müssen den Nutzungsbedingungen zustimmen!';
			}

			return $error;
		}

		private function get_manage_plus_api_args( string $xml ): array {
			return [
				'headers' => [
					'Authorization' => 'Basic ' . base64_encode( MANAGE_PLUS_USERNAME . ':' . MANAGE_PLUS_PASSWORD ),
					'Content-Type'  => 'application/xml'
				],
				'body'    => $xml,
				'timeout' => 50
			];
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
				[
					'post_type'    => 'naturkurs',
					'number_posts' => - 1,
					'post_status'  => 'any'
				]
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
				[ $this, 'wp_birdlife_settings_page' ],
				'dashicons-image-rotate-right'
			);

			add_menu_page(
				'Naturförderung projects Settings',
				'Naturförderung projects',
				'edit_pages',
				'wp_birdlife_projects_admin',
				[ $this, 'wp_birdlife_projects_settings_page' ],
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

			settings_errors( 'wp_birdlife_options' );
			require( WP_BIRDLIFE_PATH . 'views/settings-page.php' );
		}

		public function wp_birdlife_projects_settings_page() {
			if ( ! current_user_can( 'edit_pages' ) ) {
				return;
			}

			require( WP_BIRDLIFE_PATH . 'views/projects-settings-page.php' );
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

			return [ $day, $day_from_date, $year_from_date, $german_month_name ];
		}
	}
}

if ( class_exists( 'WP_Birdlife' ) ) {
	register_activation_hook( __FILE__, [ 'WP_Birdlife', 'activate' ] );
	register_deactivation_hook( __FILE__, [ 'WP_Birdlife', 'deactivate' ] );
	register_uninstall_hook( __FILE__, [ 'WP_Birdlife', 'uninstall' ] );
	$wp_birdlife = new WP_Birdlife();
}
?>
