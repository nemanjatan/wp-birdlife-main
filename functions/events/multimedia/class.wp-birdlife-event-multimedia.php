<?php

if ( ! class_exists( 'WP_Birdlife_Event_Multimedia' ) ) {
	class WP_Birdlife_Event_Multimedia {
		private function get_manage_plus_api_attachment_args(): array {
			return array(
				'headers' => array(
					'Authorization' => 'Basic ' . base64_encode( MANAGE_PLUS_USERNAME . ':' . MANAGE_PLUS_PASSWORD ),
					'Accept'        => 'application/octet-stream'
				),
				'timeout' => 50
			);
		}

		public function handle_multimedia_for_event( $module_reference, $helper ): array {
			$wp_birdlife_event_featured_image = '';
			$wp_birdlife_event_featured_image_photocredit_txt = '';
			if ( is_array( $module_reference['moduleReferenceItem'] ) ) {
				if ( is_array( $module_reference['moduleReferenceItem']['@attributes'] ) ) {
					if ( $module_reference['moduleReferenceItem']['@attributes']['moduleItemId'] !== null ) {
						$module_item_arr['wp_birdlife_evt_multimedia_ref'] = $module_reference['moduleReferenceItem']['@attributes']['moduleItemId'];

						// fetch the image
						$image_id                                              = $module_item_arr['wp_birdlife_evt_multimedia_ref'];
						$thumbnail_url                                         = 'https://maBirdlife.zetcom.app/ria-ws/application/module/Multimedia/' . $image_id . '/attachment';
						$args                                                  = $this->get_manage_plus_api_attachment_args();
						$resp                                                  = wp_remote_get( $thumbnail_url, $args );
						$module_item_arr['wp_birdlife_event_multimedia_image'] = $resp['body'];

						$multimedia_xml  = file_get_contents( WP_BIRDLIFE_PATH . 'xml/multimedia-search/event-multimedia-search.xml' );
						$multimedia_xml  = str_replace( "{{multimedia_id}}", $image_id, $multimedia_xml );
						$multimedia_args = $helper->get_manage_plus_api_args( $multimedia_xml );
						$multimedia_resp = wp_remote_post( 'https://maBirdlife.zetcom.app/ria-ws/application/module/Multimedia/search', $multimedia_args );
						$multimedia_body = $multimedia_resp['body'];

						$parsed_multimedia_xml  = simplexml_load_string( $multimedia_body );
						$multimedia_json        = json_encode( $parsed_multimedia_xml );
						$parsed_multimedia_json = json_decode( $multimedia_json, true );

						$module_multimedia_items = $parsed_multimedia_json['modules']['module']['moduleItem'];

						if ( is_array( $module_multimedia_items['dataField'] ) ) {
							foreach ( $module_multimedia_items['dataField'] as $module_item ) {
								if ( is_array( $module_item['@attributes'] ) ) {
									if ( $module_item['@attributes']['name'] === 'MulPhotocreditTxt' ) {
										$wp_birdlife_event_featured_image_photocredit_txt = $module_item['value'];
									}
								}
							}
						}

						if ( $module_item_arr['wp_birdlife_event_multimedia_image'] !== null && $module_item_arr['wp_birdlife_event_multimedia_image'] !== "" ) {
							$encoded                          = base64_encode( $module_item_arr['wp_birdlife_event_multimedia_image'] );
							$wp_birdlife_event_featured_image = $encoded;
						}
					}
				}
			}

			return array( $wp_birdlife_event_featured_image, $wp_birdlife_event_featured_image_photocredit_txt );
		}

		public function get_image_for_event( $module_item, $helper ): array {
			$wp_birdlife_event_featured_image                 = '';
			$wp_birdlife_event_featured_image_photocredit_txt = '';

			if ( is_array( $module_item['moduleReference']['moduleReferenceItem'] ) ) {
				if ( is_array( $module_item['moduleReference']['moduleReferenceItem']['@attributes'] ) ) {
					if ( $module_item['moduleReference']['moduleReferenceItem']['@attributes']['moduleItemId'] !== null ) {
						$module_item_arr['wp_birdlife_evt_multimedia_ref'] = $module_item['moduleReference']['moduleReferenceItem']['@attributes']['moduleItemId'];

						// fetch the image
						$image_id                                              = $module_item_arr['wp_birdlife_evt_multimedia_ref'];
						$thumbnail_url                                         = 'https://maBirdlife.zetcom.app/ria-ws/application/module/Multimedia/' . $image_id . '/attachment';
						$args                                                  = $this->get_manage_plus_api_attachment_args();
						$resp                                                  = wp_remote_get( $thumbnail_url, $args );
						$module_item_arr['wp_birdlife_event_multimedia_image'] = $resp['body'];

						$multimedia_xml  = file_get_contents( WP_BIRDLIFE_PATH . 'xml/multimedia-search/event-multimedia-search.xml' );
						$multimedia_xml  = str_replace( "{{multimedia_id}}", $image_id, $multimedia_xml );
						$multimedia_args = $helper->get_manage_plus_api_args( $multimedia_xml );
						$multimedia_resp = wp_remote_post( 'https://maBirdlife.zetcom.app/ria-ws/application/module/Multimedia/search', $multimedia_args );
						$multimedia_body = $multimedia_resp['body'];

						$parsed_multimedia_xml  = simplexml_load_string( $multimedia_body );
						$multimedia_json        = json_encode( $parsed_multimedia_xml );
						$parsed_multimedia_json = json_decode( $multimedia_json, true );

						$module_multimedia_items = $parsed_multimedia_json['modules']['module']['moduleItem'];

						if ( is_array( $module_multimedia_items['dataField'] ) ) {
							foreach ( $module_multimedia_items['dataField'] as $module_item ) {
								if ( is_array( $module_item['@attributes'] ) ) {
									if ( $module_item['@attributes']['name'] === 'MulPhotocreditTxt' ) {
										$wp_birdlife_event_featured_image_photocredit_txt = $module_item['value'];
									}
								}
							}
						}

						if ( $module_item_arr['wp_birdlife_event_multimedia_image'] !== null && $module_item_arr['wp_birdlife_event_multimedia_image'] !== "" ) {
							$encoded                          = base64_encode( $module_item_arr['wp_birdlife_event_multimedia_image'] );
							$wp_birdlife_event_featured_image = $encoded;
						}
					}
				} else {
					if ( is_array( $module_item['moduleReference']['moduleReferenceItem'] ) ) {
						$item = $module_item['moduleReference']['moduleReferenceItem'][0];
						// foreach ( $module_item['moduleReference']['moduleReferenceItem'] as $item ) {
						if ( $item['@attributes']['moduleItemId'] !== null ) {
							$module_item_arr['wp_birdlife_evt_multimedia_ref'] = $item['@attributes']['moduleItemId'];

							// fetch the image
							$image_id                                              = $module_item_arr['wp_birdlife_evt_multimedia_ref'];
							$thumbnail_url                                         = 'https://maBirdlife.zetcom.app/ria-ws/application/module/Multimedia/' . $image_id . '/attachment';
							$args                                                  = $this->get_manage_plus_api_attachment_args();
							$resp                                                  = wp_remote_get( $thumbnail_url, $args );
							$module_item_arr['wp_birdlife_event_multimedia_image'] = $resp['body'];

							$multimedia_xml  = file_get_contents( WP_BIRDLIFE_PATH . 'xml/multimedia-search/event-multimedia-search.xml' );
							$multimedia_xml  = str_replace( "{{multimedia_id}}", $image_id, $multimedia_xml );
							$multimedia_args = $helper->get_manage_plus_api_args( $multimedia_xml );
							$multimedia_resp = wp_remote_post( 'https://maBirdlife.zetcom.app/ria-ws/application/module/Multimedia/search', $multimedia_args );
							$multimedia_body = $multimedia_resp['body'];

							$parsed_multimedia_xml  = simplexml_load_string( $multimedia_body );
							$multimedia_json        = json_encode( $parsed_multimedia_xml );
							$parsed_multimedia_json = json_decode( $multimedia_json, true );

							$module_multimedia_items = $parsed_multimedia_json['modules']['module']['moduleItem'];

							if ( is_array( $module_multimedia_items['dataField'] ) ) {
								foreach ( $module_multimedia_items['dataField'] as $module_item ) {
									if ( is_array( $module_item['@attributes'] ) ) {
										if ( $module_item['@attributes']['name'] === 'MulPhotocreditTxt' ) {
											$wp_birdlife_event_featured_image_photocredit_txt = $module_item['value'];
										}
									}
								}
							}

							if ( $module_item_arr['wp_birdlife_event_multimedia_image'] !== null && $module_item_arr['wp_birdlife_event_multimedia_image'] !== "" ) {
								$encoded                          = base64_encode( $module_item_arr['wp_birdlife_event_multimedia_image'] );
								$wp_birdlife_event_featured_image = $encoded;
							}
						}
					}
				}
			}

			return array( $wp_birdlife_event_featured_image, $wp_birdlife_event_featured_image_photocredit_txt );
		}
	}
}