<?php

if ( ! class_exists( 'WP_Birdlife_Event_Multimedia' ) ) {
	class WP_Birdlife_Event_Multimedia {
		private const ATTACHMENT_URL_TEMPLATE = 'https://de1.zetcom-group.de/MpWeb-maZurichBirdlife/ria-ws/application/module/Multimedia/%s/attachment';
		private const MULTIMEDIA_SEARCH_URL = 'https://de1.zetcom-group.de/MpWeb-maZurichBirdlife/ria-ws/application/module/Multimedia/search';

		private function get_api_attachment_args(): array {
			return array(
				'headers' => array(
					'Authorization' => 'Basic ' . base64_encode( MANAGE_PLUS_USERNAME . ':' . MANAGE_PLUS_PASSWORD ),
					'Accept'        => 'application/octet-stream'
				),
				'timeout' => 50
			);
		}

		private function fetch_image_data( $image_id, $helper ) {
			$this->log_message( "Fetching image data for image ID: $image_id" );
			$thumbnail_url = sprintf( self::ATTACHMENT_URL_TEMPLATE, $image_id );
			$args          = $this->get_api_attachment_args();
			$resp          = wp_remote_get( $thumbnail_url, $args );

			$multimedia_xml  = file_get_contents( WP_BIRDLIFE_PATH . 'xml/multimedia-search/event-multimedia-search.xml' );
			$multimedia_xml  = str_replace( "{{multimedia_id}}", $image_id, $multimedia_xml );
			$multimedia_args = $helper->get_manage_plus_api_args( $multimedia_xml );
			$multimedia_resp = wp_remote_post( self::MULTIMEDIA_SEARCH_URL, $multimedia_args );
			$multimedia_body = $multimedia_resp['body'];

			$parsed_multimedia_xml  = simplexml_load_string( $multimedia_body );
			$multimedia_json        = json_encode( $parsed_multimedia_xml );
			$parsed_multimedia_json = json_decode( $multimedia_json, true );

			return array(
				'image_data'       => $resp['body'],
				'multimedia_items' => $parsed_multimedia_json['modules']['module']['moduleItem']
			);
		}

		private function extract_photocredit( $multimedia_items ) {
			$this->log_message( "Extracting photocredit from multimedia items" );
			$photocredit = '';
			if ( is_array( $multimedia_items['dataField'] ) ) {
				foreach ( $multimedia_items['dataField'] as $data_field ) {
					if ( is_array( $data_field['@attributes'] ) && $data_field['@attributes']['name'] === 'MulPhotocreditTxt' ) {
						$photocredit = $data_field['value'];
						break;
					}
				}
			}

			return $photocredit;
		}

		public function handle_multimedia_for_event( $module_reference, $helper ): array {
			$this->log_message( "Handling multimedia for event" );
			$featured_image  = '';
			$photocredit_txt = '';

			if ( isset( $module_reference['moduleReferenceItem']['@attributes']['moduleItemId'] ) ) {
				$image_id   = $module_reference['moduleReferenceItem']['@attributes']['moduleItemId'];
				$image_data = $this->fetch_image_data( $image_id, $helper );

				$featured_image  = base64_encode( $image_data['image_data'] );
				$photocredit_txt = $this->extract_photocredit( $image_data['multimedia_items'] );
			}

			return array( $featured_image, $photocredit_txt );
		}

		public function get_image_for_event( $module_item, $helper ): array {
			$this->log_message( "Getting image for event" );
			$featured_image  = '';
			$photocredit_txt = '';

			if ( isset( $module_item['moduleReference']['moduleReferenceItem']['@attributes']['moduleItemId'] ) ) {
				$image_id   = $module_item['moduleReference']['moduleReferenceItem']['@attributes']['moduleItemId'];
				$image_data = $this->fetch_image_data( $image_id, $helper );

				$featured_image  = base64_encode( $image_data['image_data'] );
				$photocredit_txt = $this->extract_photocredit( $image_data['multimedia_items'] );
			} elseif ( isset( $module_item['moduleReference']['moduleReferenceItem'][0]['@attributes']['moduleItemId'] ) ) {
				$image_id   = $module_item['moduleReference']['moduleReferenceItem'][0]['@attributes']['moduleItemId'];
				$image_data = $this->fetch_image_data( $image_id, $helper );

				$featured_image  = base64_encode( $image_data['image_data'] );
				$photocredit_txt = $this->extract_photocredit( $image_data['multimedia_items'] );
			}

			return array( $featured_image, $photocredit_txt );
		}

		private function log_message( $message ) {
			$logDir = __DIR__ . '/logs';
			if ( ! is_dir( $logDir ) ) {
				mkdir( $logDir, 0777, true );
			}
			$logFile          = $logDir . "/birdlife_event_multimedia_log.txt";
			$currentDateTime  = date( 'Y-m-d H:i:s' );
			$formattedMessage = $currentDateTime . " - " . $message . "\n";
			file_put_contents( $logFile, $formattedMessage, FILE_APPEND );
		}
	}
}
