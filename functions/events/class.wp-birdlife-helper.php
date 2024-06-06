<?php

if ( ! class_exists( 'WP_Birdlife_Helper' ) ) {
	class WP_Birdlife_Helper {

		private const LOG_FILE_PATH = WP_BIRDLIFE_PATH . 'functions/events/logs/birdlife_helper_log.txt';

		public function set_meta_keys( $item, $helper ): array {
			$this->log_message( "Setting meta keys for event ID: {$item['id']}" );

			$meta_inputs = [];
			$post_title  = '';

			$fields = [
				'wp_birdlife_event_last_modified',
				'wp_birdlife_event_registration_until_date',
				'wp_birdlife_event_title',
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
				'wp_birdlife_event_boking_template_id_lnu',
				'wp_birdlife_event_approved_notes',
				'wp_birdlife_event_approved_text',
				'wp_birdlife_event_approved_decision_date',
				'wp_birdlife_event_approved_date',
				'wp_birdlife_event_notes',
				'wp_birdlife_event_description',
				'wp_birdlife_event_date_to_dat',
				'wp_birdlife_event_date_from_dat',
				'wp_birdlife_event_registration_start_dat',
				'wp_birdlife_event_date_from_dat_timestamp',
				'wp_birdlife_event_leader',
				'wp_birdlife_event_information',
				'wp_birdlife_event_dating',
				'wp_birdlife_event_offer',
				'wp_birdlife_event_number_participants',
				'wp_birdlife_event_id_num',
				'wp_birdlife_event_number_groups',
				'wp_birdlife_event_region',
				'wp_birdlife_event_course_description',
				'wp_birdlife_event_organizer',
				'wp_birdlife_event_course_costs',
				'wp_birdlife_event_equipment',
				'wp_birdlife_event_course_additional',
				'wp_birdlife_event_neues_feld',
				'wp_birdlife_event_status',
				'wp_birdlife_event_currency_voc',
				'wp_birdlife_event_project_ref',
				'wp_birdlife_event_pro_species_grp_id',
				'wp_birdlife_event_pro_species_grp_name',
				'wp_birdlife_event_pro_record_type_voc',
				'wp_birdlife_event_type_voc',
				'wp_birdlife_event_category_voc',
				'wp_birdlife_event_category_voc_parent',
				'wp_birdlife_event_featured_image',
				'wp_birdlife_event_featured_image_photocredit_txt',
				'leitung',
				'number_of_involved',
				'number_of_notes',
				'wp_birdlife_event_confirmed_tn',
				'wp_birdlife_event_reserved_tn'
			];

			foreach ( $fields as $field ) {
				if ( ! empty( $item[ $field ] ) ) {
					$meta_inputs[ $field ] = $item[ $field ];
				}
			}

			if ( ! empty( $item['wp_birdlife_event_title'] ) ) {
				$post_title = $item['wp_birdlife_event_title'];
			}

			$meta_inputs['wp_birdlife_manage_plus_event_id'] = $item['id'];
			$meta_inputs                                     = $this->calculate_free_seats( $meta_inputs );

			if ( ! empty( $item['wp_birdlife_event_date_from_dat'] ) ) {
				list( $day, $day_from_date, $year_from_date, $german_month_name ) = $helper->generate_german_date( $item['wp_birdlife_event_date_from_dat'] );
				$formatted_date = $day . ' ' . $day_from_date . '. ' . $german_month_name . ' ' . $year_from_date;

				$meta_inputs['wp_birdlife_event_date_from_dat']            = '<li>' . $formatted_date . '</li>';
				$meta_inputs['wp_birdlife_event_date_from_dat_naturkurse'] = $formatted_date;
				$meta_inputs['wp_birdlife_event_date_from_dat_timestamp']  = strtotime( '+1 day', strtotime( $item['wp_birdlife_event_date_from_dat'] ) );
			}

			if ( empty( $item['wp_birdlife_event_featured_image'] ) ) {
				$meta_inputs['wp_birdlife_event_featured_image']        = 'https://birdlife-zuerich.ch/wp-content/uploads/2022/10/default-img.png';
				$meta_inputs['wp_birdlife_event_featured_image_exists'] = 'no';
			} else {
				$meta_inputs['wp_birdlife_event_featured_image_exists'] = 'yes';
			}

			return [ $meta_inputs, $post_title ];
		}

		private function calculate_free_seats( $meta_inputs ) {
			$this->log_message( "Calculating free seats." );

			if ( ! empty( $meta_inputs['wp_birdlife_event_num_max_lnu'] ) ) {
				$free_seats = $meta_inputs['wp_birdlife_event_num_max_lnu'];

				if ( $free_seats > 0 ) {
					if ( ! empty( $meta_inputs['wp_birdlife_event_confirmed_tn'] ) ) {
						$free_seats -= $meta_inputs['wp_birdlife_event_confirmed_tn'];
					}

					if ( $free_seats > 0 && ! empty( $meta_inputs['wp_birdlife_event_reserved_tn'] ) ) {
						$free_seats -= $meta_inputs['wp_birdlife_event_reserved_tn'];
					}
				}

				$free_seats = max( $free_seats, 0 );

				if ( $free_seats > 3 ) {
					$free_seats = 'freie Plätze';
				} elseif ( $free_seats > 0 && $free_seats <= 3 ) {
					$free_seats = 'Letzte Plätze frei';
				} else {
					$free_seats = 'ausgebucht';
				}

				$meta_inputs['wp_birdlife_event_free_seats'] = $free_seats;
			}

			return $meta_inputs;
		}

		public function set_metabox_values_from_api( array $data_field, array $module_item_arr ): array {
			$this->log_message( "Setting metabox values from API." );

			if ( ! empty( $data_field['@attributes']['name'] ) ) {
				$data_field_name = $data_field['@attributes']['name'];
				$field_map       = [
					'EvtRegistrationUntilDat'       => 'wp_birdlife_event_registration_until_date',
					'EvtCourseLabelTxt'             => 'wp_birdlife_event_title',
					'EvtExternalLinkTxt'            => 'wp_birdlife_event_external_link',
					'EvtPlaceTxt'                   => 'wp_birdlife_event_place',
					'EvtPhoneTxt'                   => 'wp_birdlife_event_phone',
					'EvtInformationRegistrationClb' => 'wp_birdlife_event_information_registration',
					'EvtEmailTxt'                   => 'wp_birdlife_event_email',
					'EvtCreditsNum'                 => 'wp_birdlife_event_credits',
					'EvtOnlineDat'                  => 'wp_birdlife_event_online_date',
					'EvtNumMinLnu'                  => 'wp_birdlife_event_num_min_lnu',
					'EvtNumMaxLnu'                  => 'wp_birdlife_event_num_max_lnu',
					'EvtCourseDescriptionShortClb'  => 'wp_birdlife_event_course_description_short',
					'EvtCostTxt'                    => 'wp_birdlife_event_cost',
					'EvtCourseMultipleEventsClb'    => 'wp_birdlife_event_course_multiple_events',
					'EvtProgramClb'                 => 'wp_birdlife_event_program',
					'EvtTimeToTim'                  => 'wp_birdlife_event_time_to_tim',
					'EvtOvernightPlaceClb'          => 'wp_birdlife_event_overnight_place',
					'EvtTimeFromTim'                => 'wp_birdlife_event_time_from_tim',
					'EvtMaterialsClb'               => 'wp_birdlife_event_materials',
					'EvtBokingTemplateIdLnu'        => 'wp_birdlife_event_boking_template_id_lnu',
					'EvtApprovedNotesClb'           => 'wp_birdlife_event_approved_notes',
					'EvtApprovedTxt'                => 'wp_birdlife_event_approved_text',
					'EvtApprovedDecisionDateDat'    => 'wp_birdlife_event_approved_decision_date',
					'EvtApprovedDateDat'            => 'wp_birdlife_event_approved_date',
					'EvtNotesClb'                   => 'wp_birdlife_event_notes',
					'EvtDescriptionClb'             => 'wp_birdlife_event_description',
					'EvtDateToDat'                  => 'wp_birdlife_event_date_to_dat',
					'EvtDateFromDat'                => [
						'wp_birdlife_event_date_from_dat',
						'wp_birdlife_event_date_from_dat_naturkurse'
					],
					'EvtRegistrationStartDat'       => 'wp_birdlife_event_registration_start_dat',
					'EvtLeaderClb'                  => 'wp_birdlife_event_leader',
					'EvtInformationClb'             => 'wp_birdlife_event_information',
					'EvtDatingClb'                  => 'wp_birdlife_event_dating',
					'EvtOfferClb'                   => 'wp_birdlife_event_offer',
					'EvtNumberParticipantsLnu'      => 'wp_birdlife_event_number_participants',
					'EvtIDNum'                      => 'wp_birdlife_event_id_num',
					'EvtNumberGroupsLnu'            => 'wp_birdlife_event_number_groups',
					'EvtRegionTxt'                  => 'wp_birdlife_event_region',
					'EvtCourseDescriptionClb'       => 'wp_birdlife_event_course_description',
					'EvtOrganizerTxt'               => 'wp_birdlife_event_organizer',
					'EvtCourseCostsTxt'             => 'wp_birdlife_event_course_costs',
					'EvtEquipmentClb'               => 'wp_birdlife_event_equipment',
					'EvtCourseAdditionalClb'        => 'wp_birdlife_event_course_additional',
					'EvtNeuesFeldTxt'               => 'wp_birdlife_event_neues_feld',
				];

				if ( isset( $field_map[ $data_field_name ] ) ) {
					if ( is_array( $field_map[ $data_field_name ] ) ) {
						foreach ( $field_map[ $data_field_name ] as $key ) {
							$module_item_arr[ $key ] = $data_field['value'];
						}
					} else {
						$module_item_arr[ $field_map[ $data_field_name ] ] = $data_field['value'];
					}
				}
			}

			return $module_item_arr;
		}

		public function set_project_metabox_values_from_api( array $data_field, array $module_item_arr ): array {
			$this->log_message( "Setting project metabox values from API." );

			if ( ! empty( $data_field['@attributes']['name'] ) ) {
				$data_field_name = $data_field['@attributes']['name'];
				$field_map       = [
					'ProTitleTxt'         => 'wp_birdlife_project_pro_title_txt',
					'ProDescriptionClb'   => 'wp_birdlife_project_pro_description_clb',
					'ProContactDetailTxt' => 'wp_birdlife_project_pro_contact_detail_txt',
					'ProPlaceTxt'         => 'wp_birdlife_project_pro_place_txt',
					'ProDateFromDat'      => 'wp_birdlife_project_pro_date_from_dat',
					'ProDateToDat'        => 'wp_birdlife_project_pro_date_to_dat',
					'ProKeyWordsGrp'      => 'wp_birdlife_project_pro_key_words_grp'
				];

				if ( isset( $field_map[ $data_field_name ] ) ) {
					$module_item_arr[ $field_map[ $data_field_name ] ] = $data_field['value'];
				}
			}

			return $module_item_arr;
		}

		public function checkBookingError(): string {
			$this->log_message( "Checking booking error." );
			$error = '';

			$fields = [
				'first_name'   => '[Vorname ungültig]',
				'last_name'    => '[Nachname ungültig]',
				'street'       => '[Strasse Und Nummer ungültig]',
				'postal_code'  => '[PLZ ungültig]',
				'city'         => '[Ort ungültig]',
				'email'        => '[E-Mail ungültig]',
				'phone_number' => '[Telefon ungültig]',
				'agb'          => 'Sie müssen den Nutzungsbedingungen zustimmen!'
			];

			foreach ( $fields as $field => $message ) {
				if ( ! isset( $_POST[ $field ] ) || $_POST[ $field ] === '' ) {
					$error .= $message;
				}
			}

			return $error;
		}

		public function get_manage_plus_api_args_no_body(): array {
			return [
				'headers' => [
					'Authorization' => 'Basic ' . base64_encode( MANAGE_PLUS_USERNAME . ':' . MANAGE_PLUS_PASSWORD ),
					'Content-Type'  => 'application/xml'
				],
				'timeout' => 50
			];
		}

		public function get_manage_plus_api_args( string $xml ): array {
			return [
				'headers' => [
					'Authorization' => 'Basic ' . base64_encode( MANAGE_PLUS_USERNAME . ':' . MANAGE_PLUS_PASSWORD ),
					'Content-Type'  => 'application/xml'
				],
				'body'    => $xml,
				'timeout' => 50
			];
		}

		public function generate_german_date( string $wp_birdlife_event_date_from_dat ): array {
			$this->log_message( "Generating German date for: $wp_birdlife_event_date_from_dat" );

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

		private function log_message( $message ) {
			$logDir = __DIR__ . '/logs';
			if ( ! is_dir( $logDir ) ) {
				mkdir( $logDir, 0777, true );
			}
			$logFile          = $logDir . "/birdlife_helper_log.txt";
			$currentDateTime  = date( 'Y-m-d H:i:s' );
			$formattedMessage = $currentDateTime . " - " . $message . "\n";
			file_put_contents( $logFile, $formattedMessage, FILE_APPEND );
		}
	}
}
