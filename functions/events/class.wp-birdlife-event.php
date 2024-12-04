<?php

if ( ! class_exists( 'WP_Birdlife_Event' ) ) {
	class WP_Birdlife_Event {
      private function set_past_events_to_draft() {
        $args = array(
          'numberposts' => - 1,
          'post_type'   => 'naturkurs',
          'post_status' => array( 'publish', 'future' ) // Include any statuses where the event is active
        );
        
        $all_naturkurs = get_posts( $args );
        $total_posts   = count( $all_naturkurs );
        error_log( "set_past_events_to_draft: Starting to process $total_posts posts." );
        
        foreach ( $all_naturkurs as $naturkurs ) {
          $post_id    = $naturkurs->ID;
          $post_title = get_the_title( $post_id );
          $date_to    = get_post_meta( $post_id, 'wp_birdlife_event_date_to_dat', true );
          
          // Log the post being processed
          error_log( "Processing post ID: $post_id, Title: '$post_title'." );
          
          if ( ! empty( $date_to ) ) {
            $date_to_timestamp      = strtotime( $date_to );
            $current_time           = time();
            $formatted_date_to      = date( 'Y-m-d H:i:s', $date_to_timestamp );
            $formatted_current_time = date( 'Y-m-d H:i:s', $current_time );
            
            error_log( "Post ID $post_id has 'date_to': $formatted_date_to." );
            
            if ( $date_to_timestamp < $current_time ) {
              // Event is in the past, set post to 'draft'
              $update_result = wp_update_post( array(
                'ID'          => $post_id,
                'post_status' => 'draft'
              ), true );
              
              if ( is_wp_error( $update_result ) ) {
                error_log( "Failed to set post ID $post_id to 'draft'. Error: " . $update_result->get_error_message() );
              } else {
                error_log( "Post ID $post_id is in the past (Date To: $formatted_date_to). Set status to 'draft'." );
              }
            } else {
              error_log( "Post ID $post_id is not in the past (Date To: $formatted_date_to, Current Time: $formatted_current_time). No action taken." );
            }
          } else {
            error_log( "Post ID $post_id has no 'date_to' meta value. Skipping." );
          }
        }
        
        error_log( "set_past_events_to_draft: Finished processing posts." );
      }
      
      public function fetch_all_events( $counter ): void {
			$helper                = new WP_Birdlife_Helper();
			$birdlife_new_event    = new WP_Birdlife_New_Event();
			$birdlife_update_event = new WP_Birdlife_Update_Event();
        
            // Set past events to draft before syncing new events
            $this->set_past_events_to_draft();

			$url       = $this->get_event_search_url();
			$xml       = file_get_contents( WP_BIRDLIFE_PATH . 'xml/event-search/event-search-all-fields.xml' );
			$event_ids = array();

			$total_size = $this->get_number_of_events( $helper, $url, $xml );
			update_option( 'wp_birdlife_total_size_of_events', $total_size );

			$offset = $counter * 10;
			$xml    = file_get_contents( WP_BIRDLIFE_PATH . 'xml/event-search/event-search-specific-fields.xml' );

			$resp_body = $this->get_module_items( $helper, $xml, $offset, $url );

			$parsed_xml  = simplexml_load_string( $resp_body );
			$json        = json_encode( $parsed_xml );
			$parsed_json = json_decode( $json, true );

			$module_items = $parsed_json['modules']['module']['moduleItem'];

			$formatted_arr = array();

			if ( $module_items == null ) {
				return;
			}

			if ( $module_items['systemField'][0]['value'] === null ) {
				foreach ( $module_items as $module_item ) {
					$post        = $this->get_naturkurs_post_by_event_id( $module_item['systemField'][0]['value'] );
					$event_ids[] = $module_item['systemField'][0]['value'];

					if ( is_array( $post ) ) {
						// update existing one
						if ( count( $post ) == 1 ) {
							$birdlife_update_event->update_events(
								$module_item,
								$helper,
								$post
							);
						} else {
							$module_item_arr = $birdlife_new_event->save_new_events(
								$module_item,
								$helper
							);
							$formatted_arr[] = $module_item_arr;
						}
					}
				}
			}

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

					$posts = get_posts( $args );

					if ( empty( $posts ) ) {
						$post_status = 'publish';
						if ( $meta_inputs['wp_birdlife_event_status'] === 'in Durchführung'
						     || $meta_inputs['wp_birdlife_event_status'] === 'abgesagt' ) {
							$post_status = 'draft';
						}

						$slug = $this->slugify( $post_title );

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

						foreach ( $posts as $post ) {
							$post_status = 'publish';
							if ( $meta_inputs['wp_birdlife_event_status'] === 'in Durchführung'
							     || $meta_inputs['wp_birdlife_event_status'] === 'abgesagt' ) {
								$post_status = 'draft';
							}

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
				}
			}
			// }

			$args = array(
				'numberposts' => - 1,
				'post_type'   => 'naturkurs'
			);

			$all_naturkurs = get_posts( $args );

//			foreach ( $all_naturkurs as $naturkurs ) {
//				$post_should_be_deleted           = true;
//				$wp_birdlife_manage_plus_event_id = get_post_meta(
//					$naturkurs->ID,
//					'wp_birdlife_manage_plus_event_id',
//					true
//				);
//
//				foreach ( $event_ids as $id ) {
//					if ( $wp_birdlife_manage_plus_event_id === $id ) {
//						$post_should_be_deleted = false;
//					}
//				}
//
//				if ( $post_should_be_deleted ) {
//					wp_delete_post( $naturkurs->ID );
//				}
//			}

			// todo remove after
			update_option( 'wp_birdlife_last_sync', time() );
		}

		private function slugify( $text, string $divider = '-' ) {
			// replace non letter or digits by divider
			$text = preg_replace( '~[^\pL\d]+~u', $divider, $text );

			// transliterate
			$text = iconv( 'utf-8', 'us-ascii//TRANSLIT', $text );

			// remove unwanted characters
			$text = preg_replace( '~[^-\w]+~', '', $text );

			// trim
			$text = trim( $text, $divider );

			// remove duplicate divider
			$text = preg_replace( '~-+~', $divider, $text );

			// lowercase
			$text = strtolower( $text );

			if ( empty( $text ) ) {
				return 'n-a';
			}

			return $text;
		}

		public function hard_refresh_ajax_script() {
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
                            .fail(function () {
                                console.log('Failed callAjaxForHelp for counter: ' + counter + 1 + ';');
                            });
                    }

                    $('#hard-refresh-wp-ajax-button').click(function () {
                        var id = $('#hard-refresh-ajax-option-id').val();

                        var totalSizeOfEvents = document.getElementById("wp_birdlife_total_size_of_events").value;
                        createOrUpdateEvents(0, totalSizeOfEvents);

                        var loadingTime = document.getElementById("wp_birdlife_loading_time").value;
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
			// todo remove after
			// update_option( 'wp_birdlife_last_sync', time() );
			update_option( 'wp_birdlife_last_manual_sync', time() );

			$data = 'new loading time: ' . $new_loading_time;
			echo json_encode( $data );

			wp_die();
		}

		public function get_last_sync() {
			$wp_birdlife_cron_job_time = get_option( 'wp_birdlife_options' );
			$wp_birdlife_last_sync     = get_option( 'wp_birdlife_last_sync' );

			echo json_encode( array(
				'type'      => $wp_birdlife_cron_job_time['wp_birdlife_cron_job_time'],
				'last_sync' => $wp_birdlife_last_sync
			), JSON_PRETTY_PRINT );
			wp_die();
		}

		private function get_number_of_events( $helper, $url, $xml ) {
			$args = $helper->get_manage_plus_api_args( $xml );

			$resp      = wp_remote_post( $url, $args );
			$resp_body = $resp['body'];

			$parsed_xml  = simplexml_load_string( $resp_body );
			$json        = json_encode( $parsed_xml );
			$parsed_json = json_decode( $json, true );

			$total_size = $parsed_json['modules']['module']['@attributes']['totalSize'];

			return $total_size;
		}

		private function update_offset( $xml, $offset ) {
			return str_replace( "{{offset}}", $offset, $xml );
		}

		private function get_event_search_url() {
			return 'https://maBirdlife.zetcom.app/ria-ws/application/module/Event/search/';
		}

		private function get_naturkurs_post_by_event_id( $event_id ) {
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

			return get_posts( $args );
		}

		private function get_module_items( $helper, $xml, $offset, $url ) {
			$xml = $this->update_offset( $xml, $offset );

			$args = $helper->get_manage_plus_api_args( $xml );

			$resp = wp_remote_post( $url, $args );

			return $resp['body'];
		}
      
        public function process_all_events() {
          $helper                = new WP_Birdlife_Helper();
          $birdlife_new_event    = new WP_Birdlife_New_Event();
          $birdlife_update_event = new WP_Birdlife_Update_Event();
          
          // Set past events to draft before syncing new events
          $this->set_past_events_to_draft();
          
          $url       = $this->get_event_search_url();
          $xml       = file_get_contents( WP_BIRDLIFE_PATH . 'xml/event-search/event-search-all-fields.xml' );
          $event_ids = array();
          
          $total_size = $this->get_number_of_events( $helper, $url, $xml );
          update_option( 'wp_birdlife_total_size_of_events', $total_size );
          
          $batches = ceil( $total_size / 10 );
          
          for ( $counter = 0; $counter < $batches; $counter ++ ) {
            $offset = $counter * 10;
            $xml    = file_get_contents( WP_BIRDLIFE_PATH . 'xml/event-search/event-search-specific-fields.xml' );
            
            $resp_body = $this->get_module_items( $helper, $xml, $offset, $url );
            
            $parsed_xml  = simplexml_load_string( $resp_body );
            $json        = json_encode( $parsed_xml );
            $parsed_json = json_decode( $json, true );
            
            $module_items = $parsed_json['modules']['module']['moduleItem'];
            
            $formatted_arr = array();
            
            if ( $module_items == null ) {
              continue;
            }
            
            if ( $module_items['systemField'][0]['value'] === null ) {
              foreach ( $module_items as $module_item ) {
                $post        = $this->get_naturkurs_post_by_event_id( $module_item['systemField'][0]['value'] );
                $event_ids[] = $module_item['systemField'][0]['value'];
                
                if ( is_array( $post ) ) {
                  if ( count( $post ) == 1 ) {
                    $birdlife_update_event->update_events(
                      $module_item,
                      $helper,
                      $post
                    );
                  } else {
                    $module_item_arr = $birdlife_new_event->save_new_events(
                      $module_item,
                      $helper
                    );
                    $formatted_arr[] = $module_item_arr;
                  }
                }
              }
            }
          }
          
          // Update last sync time
          update_option( 'wp_birdlife_last_sync', time() );
        }
    }
}