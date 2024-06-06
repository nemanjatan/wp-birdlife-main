<?php

if ( ! class_exists( 'WP_Birdlife_Free_Seats' ) ) {
	class WP_Birdlife_Free_Seats {
		public function get_free_seats( $module_item ) {
			$this->log_message( "Fetching free seats for module item" );
			$confirmed_participants = 0;

			$repeatable_groups = $module_item['repeatableGroup'] ?? [];

			foreach ( $repeatable_groups as $repeatable_group ) {
				if ( isset( $repeatable_group['@attributes']['name'] ) && $repeatable_group['@attributes']['name'] === 'EvtParticipantGrp' ) {
					$group_items = $repeatable_group['repeatableGroupItem'] ?? [];

					foreach ( $group_items as $group_item ) {
						$vocabulary_references = $group_item['vocabularyReference'] ?? [];

						foreach ( $vocabulary_references as $vocabulary_reference ) {
							if ( isset( $vocabulary_reference['@attributes']['name'] ) && $vocabulary_reference['@attributes']['name'] === 'StatusVoc' ) {
								$vocabulary_reference_item = $vocabulary_reference['vocabularyReferenceItem'] ?? [];

								if ( isset( $vocabulary_reference_item['@attributes']['name'] ) && $vocabulary_reference_item['@attributes']['name'] === 'Participant' ) {
									$confirmed_participants ++;
								} elseif ( isset( $vocabulary_reference_item['formattedValue'] ) && $vocabulary_reference_item['formattedValue'] === 'Participant' ) {
									$confirmed_participants ++;
								}
							}
						}
					}
				}
			}

			$this->log_message( "Confirmed participants: $confirmed_participants" );

			return $confirmed_participants;
		}

		private function log_message( $message ) {
			$logDir = __DIR__ . '/logs';
			if ( ! is_dir( $logDir ) ) {
				mkdir( $logDir, 0777, true );
			}
			$logFile          = $logDir . "/birdlife_free_seats_log.txt";
			$currentDateTime  = date( 'Y-m-d H:i:s' );
			$formattedMessage = $currentDateTime . " - " . $message . "\n";
			file_put_contents( $logFile, $formattedMessage, FILE_APPEND );
		}
	}
}
