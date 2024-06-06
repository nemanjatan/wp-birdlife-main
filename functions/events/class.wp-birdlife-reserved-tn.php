<?php

if ( ! class_exists( 'WP_Birdlife_Reserved_Tn' ) ) {
	class WP_Birdlife_Reserved_Tn {

		private const BOOKING_SEARCH_URL = 'https://de1.zetcom-group.de/MpWeb-maZurichBirdlife/ria-ws/application/module/Booking/search/';
		private const BOOKING_SEARCH_XML_PATH = WP_BIRDLIFE_PATH . 'xml/booking-event/book-event-search-all-fields.xml';

		private function get_booking_search_url() {
			return self::BOOKING_SEARCH_URL;
		}

		private function get_number_of_events( $helper, $url, $xml ) {
			$this->log_message( "Getting number of events from URL: $url" );

			$args          = $helper->get_manage_plus_api_args( $xml );
			$response      = wp_remote_post( $url, $args );
			$response_body = $response['body'];

			$parsed_xml  = simplexml_load_string( $response_body );
			$json        = json_encode( $parsed_xml );
			$parsed_json = json_decode( $json, true );

			$total_size = $parsed_json['modules']['module']['@attributes']['totalSize'] ?? 0;

			return ceil( $total_size / 10 );
		}

		private function update_offset( $xml, $offset ) {
			$this->log_message( "Updating offset to: $offset" );

			return str_replace( "{{offset}}", $offset, $xml );
		}

		public function fetch_reserved_tn( $event_id ) {
			$this->log_message( "Fetching reserved TN for event ID: $event_id" );

			$helper = new WP_Birdlife_Helper();
			$url    = $this->get_booking_search_url();
			$xml    = file_get_contents( self::BOOKING_SEARCH_XML_PATH );
			$xml    = str_replace( "{{offset}}", "0", $xml );
			$xml    = str_replace( "{{event_id}}", $event_id, $xml );

			$number_of_iterations = $this->get_number_of_events( $helper, $url, $xml );

			$entry_count = 0;

			if ( $number_of_iterations == 0 ) {
				$this->log_message( "No events found for event ID: $event_id" );

				return $entry_count;
			}

			for ( $i = 0; $i < $number_of_iterations; $i ++ ) {
				$offset = $i * 10;
				$xml    = file_get_contents( self::BOOKING_SEARCH_XML_PATH );
				$xml    = $this->update_offset( $xml, $offset );
				$xml    = str_replace( "{{event_id}}", $event_id, $xml );

				$args          = $helper->get_manage_plus_api_args( $xml );
				$response      = wp_remote_post( $url, $args );
				$response_body = $response['body'];

				$parsed_xml  = simplexml_load_string( $response_body );
				$json        = json_encode( $parsed_xml );
				$parsed_json = json_decode( $json, true );

				$module_items = $parsed_json['modules']['module']['moduleItem'] ?? [];

				$entry_count += $this->count_entries( $module_items );
			}

			return $entry_count;
		}

		private function count_entries( $module_items ) {
			$this->log_message( "Counting 'Eingang' in module items" );

			$entry_count = 0;

			if ( isset( $module_items['systemField'][0]['value'] ) ) {
				$vocabulary_reference = $module_items['vocabularyReference'] ?? [];
				$entry_count          += $this->process_vocabulary_reference( $vocabulary_reference );
			} else {
				foreach ( $module_items as $module_item ) {
					$vocabulary_reference = $module_item['vocabularyReference'] ?? [];
					$entry_count          += $this->process_vocabulary_reference( $vocabulary_reference );
				}
			}

			return $entry_count;
		}

		private function process_vocabulary_reference( $vocabulary_reference ) {
			$this->log_message( "Processing vocabulary reference" );

			$entry_count = 0;

			if ( isset( $vocabulary_reference['@attributes']['name'] ) && $vocabulary_reference['@attributes']['name'] === 'BokStatusVoc' ) {
				if ( isset( $vocabulary_reference['vocabularyReferenceItem']['@attributes']['name'] ) && $vocabulary_reference['vocabularyReferenceItem']['@attributes']['name'] === 'registration' ) {
					if ( isset( $vocabulary_reference['vocabularyReferenceItem']['formattedValue'] ) && $vocabulary_reference['vocabularyReferenceItem']['formattedValue'] === 'Entry' ) {
						$entry_count ++;
					}
				}
			} else {
				foreach ( $vocabulary_reference as $voc_ref ) {
					if ( isset( $voc_ref['@attributes']['name'] ) && $voc_ref['@attributes']['name'] === 'BokStatusVoc' ) {
						if ( isset( $voc_ref['vocabularyReferenceItem']['@attributes']['name'] ) && $voc_ref['vocabularyReferenceItem']['@attributes']['name'] === 'registration' ) {
							if ( isset( $voc_ref['vocabularyReferenceItem']['formattedValue'] ) && $voc_ref['vocabularyReferenceItem']['formattedValue'] === 'Entry' ) {
								$entry_count ++;
							}
						}
					}
				}
			}

			return $entry_count;
		}

		private function log_message( $message ) {
			$logDir = __DIR__ . '/logs';
			if ( ! is_dir( $logDir ) ) {
				mkdir( $logDir, 0777, true );
			}
			$logFile          = $logDir . "/birdlife_reserved_tn_log.txt";
			$currentDateTime  = date( 'Y-m-d H:i:s' );
			$formattedMessage = $currentDateTime . " - " . $message . "\n";
			file_put_contents( $logFile, $formattedMessage, FILE_APPEND );
		}
	}
}
