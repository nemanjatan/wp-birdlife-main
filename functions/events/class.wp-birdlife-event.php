<?php

if ( ! class_exists( 'WP_Birdlife_Event' ) ) {
	class WP_Birdlife_Event {

		private const EVENT_SEARCH_URL = 'https://de1.zetcom-group.de/MpWeb-maZurichBirdlife/ria-ws/application/module/Event/search/';
		private const MODULE_ITEM_PATH = WP_BIRDLIFE_PATH . 'xml/event-search/event-search-specific-fields.xml';
		private const ALL_FIELDS_PATH = WP_BIRDLIFE_PATH . 'xml/event-search/event-search-all-fields.xml';

		public function fetch_all_events( $counter ): void {
			$this->log_message( "Fetching all events for counter: $counter" );

			$helper                = new WP_Birdlife_Helper();
			$birdlife_new_event    = new WP_Birdlife_New_Event();
			$birdlife_update_event = new WP_Birdlife_Update_Event();

			$total_size = $this->get_number_of_events( $helper, self::EVENT_SEARCH_URL );
			update_option( 'wp_birdlife_total_size_of_events', $total_size );
			$this->log_message( "Total size of events: $total_size" );

			$offset       = $counter * 10;
			$module_items = $this->fetch_module_items( $helper, self::EVENT_SEARCH_URL, $offset );

			if ( ! $module_items ) {
				$this->log_message( "No module items found." );

				return;
			}

			$formatted_arr = [];
			$event_ids     = [];
			$this->log_message( 'Number of module items: ' . count( $module_items ) );

			if ( isset( $module_items['systemField'] ) ) {
				// Single module item case
				$this->process_module_item( $module_items, $helper, $birdlife_update_event, $birdlife_new_event, $formatted_arr, $event_ids );
			} else {
				// Multiple module items case
				foreach ( (array) $module_items as $module_item ) {
					if ( isset( $module_item['systemField'] ) ) {
						$this->process_module_item( $module_item, $helper, $birdlife_update_event, $birdlife_new_event, $formatted_arr, $event_ids );
					}
				}
			}

			$this->process_formatted_arr( $formatted_arr, $helper );
		}


		private function fetch_module_items( $helper, $url, $offset ) {
			$this->log_message( "Fetching module items with offset: $offset" );

			$xml       = file_get_contents( self::MODULE_ITEM_PATH );
			$xml       = $this->update_offset( $xml, $offset );
			$resp_body = $this->get_module_items( $helper, $xml, $url );

			$parsed_xml = simplexml_load_string( $resp_body );
			$this->log_message( "Parsed XML: " . substr( $resp_body, 0, 5000 ) );
			$json        = json_encode( $parsed_xml );
			$parsed_json = json_decode( $json, true );

			$this->log_message( "Parsed JSON: " . json_encode( array_slice( $parsed_json, 0, 1 ) ) );

			return $parsed_json['modules']['module']['moduleItem'] ?? null;
		}

		private function process_module_item( $module_item, $helper, $birdlife_update_event, $birdlife_new_event, &$formatted_arr, &$event_ids ) {
			$this->log_message( "Processing module item: " . json_encode( array_slice( $module_item, 0, 1 ) ) );

			$event_id = $module_item['systemField'][0]['value'] ?? null;
			if ( ! $event_id ) {
				$this->log_message( "No event ID found for module item." );

				return;
			}

			$post        = $this->get_naturkurs_post_by_event_id( $event_id );
			$event_ids[] = $event_id;

			if ( is_array( $post ) && count( $post ) == 1 ) {
				$birdlife_update_event->update_events( $module_item, $helper, $post );
				$this->log_message( "Updated event for ID: $event_id" );
			} else {
				$this->log_message( "Saving new event where module_item is: " . json_encode( array_slice( $module_item, 0, 1 ) ) );
				$module_item_arr = $birdlife_new_event->save_new_events( $module_item, $helper );
				$formatted_arr[] = $module_item_arr;
				$this->log_message( "Saved new event for ID: $event_id" );
			}
		}


		private function process_formatted_arr( $formatted_arr, $helper ) {
			$this->log_message( "Processing formatted array." );

			foreach ( $formatted_arr as $item ) {
				if ( ! empty( $item['id'] ) ) {
					list( $meta_inputs, $post_title ) = $helper->set_meta_keys( $item, $helper );

					$args = array(
						'numberposts' => - 1,
						'post_type'   => 'naturkurs',
						'meta_query'  => array(
							array(
								'key'   => 'wp_birdlife_manage_plus_event_id',
								'value' => $meta_inputs['wp_birdlife_manage_plus_event_id'],
							)
						)
					);

					$posts       = get_posts( $args );
					$post_status = ( $meta_inputs['wp_birdlife_event_status'] === 'in Durchführung' || $meta_inputs['wp_birdlife_event_status'] === 'abgesagt' ) ? 'draft' : 'publish';
					$slug        = $this->slugify( $post_title );

					$meta_inputs['wp_birdlife_event_course_description'] = $meta_inputs['wp_birdlife_event_course_description'] ?? '';

					if ( empty( $posts ) ) {
						$this->insert_new_post( $post_title, $slug, $post_status, $meta_inputs );
					} else {
						$this->update_existing_posts( $posts, $post_title, $post_status, $meta_inputs );
					}
				}
			}
		}

		private function insert_new_post( $post_title, $slug, $post_status, $meta_inputs ) {
			$this->log_message( "Inserting new post with title: $post_title, slug: $slug, status: $post_status" );

			if ( ! empty( $meta_inputs['wp_birdlife_event_online_date'] ) ) {
				$date         = strtotime( $meta_inputs['wp_birdlife_event_online_date'] );
				$current_date = strtotime( "now" );

				if ( $current_date > $date ) {
					wp_insert_post(
						array(
							'post_title'   => $post_title,
							'post_type'    => 'naturkurs',
							'post_name'    => $slug,
							'post_status'  => $post_status,
							'post_content' => $meta_inputs['wp_birdlife_event_course_description'],
							'meta_input'   => $meta_inputs
						)
					);
				}
			} else {
				wp_insert_post(
					array(
						'post_title'   => $post_title,
						'post_type'    => 'naturkurs',
						'post_name'    => $slug,
						'post_status'  => $post_status,
						'post_content' => $meta_inputs['wp_birdlife_event_course_description'],
						'meta_input'   => $meta_inputs
					)
				);
			}
		}

		private function update_existing_posts( $posts, $post_title, $post_status, $meta_inputs ) {
			$this->log_message( "Updating existing posts with title: $post_title, status: $post_status" );

			foreach ( $posts as $post ) {
				wp_update_post(
					array(
						'ID'           => $post->ID,
						'post_title'   => $post_title,
						'post_type'    => 'naturkurs',
						'post_status'  => $post_status,
						'post_content' => $meta_inputs['wp_birdlife_event_course_description'],
						'meta_input'   => $meta_inputs
					)
				);
			}
		}

		private function slugify( $text, string $divider = '-' ) {
			$this->log_message( "Slugifying text: $text" );

			$text = preg_replace( '~[^\pL\d]+~u', $divider, $text );
			$text = iconv( 'utf-8', 'us-ascii//TRANSLIT', $text );
			$text = preg_replace( '~[^-\w]+~', '', $text );
			$text = trim( $text, $divider );
			$text = preg_replace( '~-+~', $divider, $text );
			$text = strtolower( $text );

			return empty( $text ) ? 'n-a' : $text;
		}

		public function hard_refresh_ajax_script() {
			$this->log_message( "Generating hard refresh AJAX script." );
			?>
        <script type="text/javascript">
            jQuery(document).ready(function ($) {
                function createOrUpdateEvents(counter, totalSizeOfEvents) {
                    $.ajax({
                        method: "POST",
                        url: ajaxurl,
                        data: {
                            'action': 'hard_refresh_action',
                            'counter': counter
                        }
                    })
                        .done(function (data) {
                            let totalSizeOfEventsDividedByTen = totalSizeOfEvents / 10;
                            console.log("totalSizeOfEventsDividedByTen: " + totalSizeOfEventsDividedByTen);
                            if (counter < totalSizeOfEventsDividedByTen) {
                                let newCounter = counter + 1;

                                console.log(data)
                                console.log('Executing callAjaxForHelp for counter: ' + newCounter + ';');

                                createOrUpdateEvents(newCounter, totalSizeOfEvents);
                            } else {
                                document.getElementById("myBar").style.display = 'none';
                                var tag = document.createElement("p");
                                tag.style.textAlign = 'center';

                                var text = document.createTextNode("Done!");
                                tag.appendChild(text);

                                var element = document.getElementById("myProgress");
                                element.appendChild(tag);
                            }

                        })
                        .fail(function (jqXHR, textStatus, errorThrown) {
                            console.log('Failed callAjaxForHelp for counter: ' + counter + 1 + ';');
                            console.log('Status: ' + textStatus);
                            console.log('Error: ' + errorThrown);
                            console.log('Response: ' + jqXHR.responseText);
                        });
                }

                $('#hard-refresh-wp-ajax-button').click(function () {
                    var totalSizeOfEvents = parseInt(document.getElementById("wp_birdlife_total_size_of_events").value, 10);
                    if (isNaN(totalSizeOfEvents) || totalSizeOfEvents <= 0) {
                        console.log("Invalid totalSizeOfEvents: " + totalSizeOfEvents);
                        return;
                    }
                    createOrUpdateEvents(0, totalSizeOfEvents);

                    var loadingTime = parseInt(document.getElementById("wp_birdlife_loading_time").value, 10);
                    if (isNaN(loadingTime) || loadingTime <= 0) {
                        console.log("Invalid loadingTime: " + loadingTime);
                        return;
                    }
                    var dividedByTen = loadingTime / 10;
                    document.getElementById("myProgress").style.display = "block";

                    for (var y = 0; y < dividedByTen - 1; y++) {
                        (function (x) {
                            setTimeout(function () {
                                console.log(x);
                                var elem = document.getElementById("myBar");
                                var width = (100 * x) / dividedByTen;
                                if (width >= 100) {
                                    clearInterval(id);
                                    i = 0;
                                } else {
                                    width++;
                                    elem.style.width = width + "%";
                                }
                            }, x * 10000);
                        })(y);
                    }
                });

            });
        </script>
			<?php
		}

		public function hard_refresh_ajax_handler() {
			$this->log_message( "Handling hard refresh AJAX request." );

			try {
				$counter           = $_POST['counter'];
				$WP_Birdlife_Event = new WP_Birdlife_Event();
				$start_time        = microtime( true );

				$WP_Birdlife_Event->fetch_all_events( floor( $counter ) );

				$end_time       = microtime( true );
				$execution_time = ( $end_time - $start_time );

				$wp_birdlife_loading_time = get_option( 'wp_birdlife_loading_time' );

				if ( $counter < 2 ) {
					$wp_birdlife_loading_time = 0;
				}

				$new_loading_time = $wp_birdlife_loading_time + floor( $execution_time );

				update_option( 'wp_birdlife_loading_time', $new_loading_time );
				update_option( 'wp_birdlife_last_manual_sync', time() );

				echo json_encode( 'new loading time: ' . $new_loading_time );

			} catch ( Exception $e ) {
				$this->log_message( "Error handling AJAX request: " . $e->getMessage() );
				echo json_encode( [ 'error' => $e->getMessage() ] );
			}

			wp_die();
		}


		public function get_last_sync() {
			$this->log_message( "Getting last sync information." );

			$wp_birdlife_cron_job_time = get_option( 'wp_birdlife_options' );
			$wp_birdlife_last_sync     = get_option( 'wp_birdlife_last_sync' );

			echo json_encode( array(
				'type'      => $wp_birdlife_cron_job_time['wp_birdlife_cron_job_time'],
				'last_sync' => $wp_birdlife_last_sync
			), JSON_PRETTY_PRINT );
			wp_die();
		}

		private function get_number_of_events( $helper, $url ) {
			$this->log_message( "Getting number of events from URL: $url" );

			$xml  = file_get_contents( self::ALL_FIELDS_PATH );
			$args = $helper->get_manage_plus_api_args( $xml );

			$resp      = wp_remote_post( $url, $args );
			$resp_body = $resp['body'];

			$parsed_xml  = simplexml_load_string( $resp_body );
			$json        = json_encode( $parsed_xml );
			$parsed_json = json_decode( $json, true );

			$total_size = $parsed_json['modules']['module']['@attributes']['totalSize'] ?? 0;
			$this->log_message( "Total size of events: $total_size" );

			return $total_size;
		}

		private function update_offset( $xml, $offset ) {
			$this->log_message( "Updating offset to: $offset" );

			return str_replace( "{{offset}}", $offset, $xml );
		}

		private function get_naturkurs_post_by_event_id( $event_id ) {
			$this->log_message( "Getting post by event ID: $event_id" );

			$args = array(
				'meta_key'       => 'wp_birdlife_manage_plus_event_id',
				'meta_value'     => $event_id,
				'post_type'      => 'naturkurs',
				'post_status'    => array(
					'publish',
					'pending',
					'draft',
					'auto-draft',
					'future',
					'private',
					'inherit',
					'trash'
				),
				'posts_per_page' => - 1
			);

			$posts = get_posts( $args );
			$this->log_message( "Found " . count( $posts ) . " posts for event ID: $event_id" );

			return $posts;
		}

		private function get_module_items( $helper, $xml, $url ) {
			$this->log_message( "Getting module items from URL: $url" );

			$args = $helper->get_manage_plus_api_args( $xml );

			$resp = wp_remote_post( $url, $args );

			return $resp['body'];
		}

		private function log_message( $message ) {
			$logDir = __DIR__ . '/logs';
			if ( ! is_dir( $logDir ) ) {
				mkdir( $logDir, 0777, true );
			}
			$logFile          = $logDir . "/birdlife_event_log.txt";
			$currentDateTime  = date( 'Y-m-d H:i:s' );
			$formattedMessage = $currentDateTime . " - " . $message . "\n";
			file_put_contents( $logFile, $formattedMessage, FILE_APPEND );
		}
	}
}
