<?php

if ( ! class_exists( 'WP_Birdlife_Reserved_Tn' ) ) {
	class WP_Birdlife_Reserved_Tn {
		private function get_booking_search_url() {
			return 'https://de1.zetcom-group.de/MpWeb-maZurichBirdlife/ria-ws/application/module/Booking/search/';
		}

		private function get_number_of_events( $helper, $url, $xml ) {
			$args = $helper->get_manage_plus_api_args( $xml );

			$resp      = wp_remote_post( $url, $args );
			$resp_body = $resp['body'];

			$parsed_xml  = simplexml_load_string( $resp_body );
			$json        = json_encode( $parsed_xml );
			$parsed_json = json_decode( $json, true );

			$total_size = $parsed_json['modules']['module']['@attributes']['totalSize'];

			return ( $total_size / 10 );
		}

		private function update_offset( $xml, $offset ) {
			return str_replace( "{{offset}}", $offset, $xml );
		}

		public function fetch_reserved_tn( $event_id ) {
			$helper = new WP_Birdlife_Helper();
			$url    = $this->get_booking_search_url();
			$xml    = file_get_contents( WP_BIRDLIFE_PATH . 'xml/booking-event/book-event-search-all-fields.xml' );
			$xml    = str_replace( "{{offset}}", "0", $xml );
			$xml    = str_replace( "{{event_id}}", $event_id, $xml );

			$number_of_iterations = $this->get_number_of_events( $helper, $url, $xml );

			$eingang = 0;

			if ( $number_of_iterations == 0 ) {
				return $eingang;
			}

			for ( $i = 0; $i <= $number_of_iterations; $i ++ ) {
				$offset = $i * 10;
				$xml    = file_get_contents( WP_BIRDLIFE_PATH . 'xml/booking-event/book-event-search-all-fields.xml' );
				$xml    = str_replace( "{{offset}}", $offset, $xml );
				$xml    = str_replace( "{{event_id}}", $event_id, $xml );

				$xml = $this->update_offset( $xml, $offset );

				$args = $helper->get_manage_plus_api_args( $xml );

				$resp      = wp_remote_post( $url, $args );
				$resp_body = $resp['body'];

				$parsed_xml  = simplexml_load_string( $resp_body );
				$json        = json_encode( $parsed_xml );
				$parsed_json = json_decode( $json, true );

				$module_items = $parsed_json['modules']['module']['moduleItem'];

				if ( is_array( $module_items ) ) {
					if ( $module_items['systemField'][0]['value'] !== null ) {
						$vocabulary_ref = $module_items['vocabularyReference'];
						if ( $vocabulary_ref['@attributes'] ) {
							if ( $vocabulary_ref['@attributes'] === 'BokStatusVoc' ) {
								if ( $vocabulary_ref['vocabularyReferenceItem']['@attributes']['name'] === 'registration' ) {
									if ( $vocabulary_ref['vocabularyReferenceItem']['formattedValue'] === 'Eingang' ) {
										$eingang = $eingang + 1;
									}
								}
							}
						} else {
							if ( is_array( $vocabulary_ref ) ) {
								foreach ( $vocabulary_ref as $voc_ref ) {
									if ( $voc_ref['@attributes']['name'] === 'BokStatusVoc' ) {
										if ( $voc_ref['vocabularyReferenceItem']['@attributes']['name'] === 'registration' ) {
											if ( $voc_ref['vocabularyReferenceItem']['formattedValue'] === 'Eingang' ) {
												$eingang = $eingang + 1;
											}
										}
									}
								}
							}
						}
					} else {
						foreach ( $module_items as $module_item ) {
							$vocabulary_ref = $module_item['vocabularyReference'];
							if ( $vocabulary_ref['@attributes'] ) {
								if ( $vocabulary_ref['@attributes'] === 'BokStatusVoc' ) {
									if ( $vocabulary_ref['vocabularyReferenceItem']['@attributes']['name'] === 'registration' ) {
										if ( $vocabulary_ref['vocabularyReferenceItem']['formattedValue'] === 'Eingang' ) {
											$eingang = $eingang + 1;
										}
									}
								}
							} else {
								if ( is_array( $vocabulary_ref ) ) {
									foreach ( $vocabulary_ref as $voc_ref ) {
										if ( $voc_ref['@attributes']['name'] === 'BokStatusVoc' ) {
											if ( $voc_ref['vocabularyReferenceItem']['@attributes']['name'] === 'booked' ) {
												if ( $voc_ref['vocabularyReferenceItem']['formattedValue'] === 'Eingang' ) {
													$eingang = $eingang + 1;
												}
											}
										}
									}
								}
							}
						}
					}
				}
			}

			return $eingang;
		}
	}
}