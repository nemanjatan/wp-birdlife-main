<?php
  
  if ( ! class_exists( 'WP_Birdlife_Event' ) ) {
    class WP_Birdlife_Event {
      public function fetch_all_events( $counter ): void {
        $helper                = new WP_Birdlife_Helper();
        $birdlife_new_event    = new WP_Birdlife_New_Event();
        $birdlife_update_event = new WP_Birdlife_Update_Event();
        
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
          error_log( "No module items found. Exiting method." );
          
          return;
        }
        
        if ( $module_items['systemField'][0]['value'] === null ) {
          foreach ( $module_items as $module_item ) {
            $post        = $this->get_naturkurs_post_by_event_id( $module_item['systemField'][0]['value'] );
            $event_ids[] = $module_item['systemField'][0]['value'];
            
            if ( is_array( $post ) ) {
              // Update existing event
              if ( count( $post ) == 1 ) {
                $birdlife_update_event->update_events(
                  $module_item,
                  $helper,
                  $post
                );
                error_log( "Updated existing event with ID: " . $module_item['systemField'][0]['value'] );
              } else {
                // Save new event data
                $module_item_arr = $birdlife_new_event->save_new_events(
                  $module_item,
                  $helper
                );
                $formatted_arr[] = $module_item_arr;
                error_log( "Saved new event data for event ID: " . $module_item['systemField'][0]['value'] );
              }
            }
          }
        }
        
        foreach ( $formatted_arr as $item ) {
          if ( ! empty( $item['id'] ) ) {
            // Log the start of processing for this event
            error_log( "Processing event ID: " . $item['id'] );
            
            list( $meta_inputs, $post_title ) = $helper->set_meta_keys( $item, $helper );
            
            // Log the event title
            error_log( "Event title: " . $post_title );
            
            // Determine the post status
            $post_status     = 'publish';
            $today           = current_time( 'Y-m-d' );
            $today_timestamp = strtotime( $today );
            
            $event_date_to           = isset( $meta_inputs['wp_birdlife_event_date_to_dat'] ) ? $meta_inputs['wp_birdlife_event_date_to_dat'] : '';
            $event_date_to_timestamp = strtotime( $event_date_to );
            
            if ( ! empty( $event_date_to ) && $event_date_to_timestamp < $today_timestamp ) {
              $post_status = 'draft';
              error_log( "Event date_to is in the past. Setting post_status to 'draft'." );
            }
            
            if ( isset( $meta_inputs['wp_birdlife_event_status'] ) && ( $meta_inputs['wp_birdlife_event_status'] === 'in Durchführung' || $meta_inputs['wp_birdlife_event_status'] === 'abgesagt' ) ) {
              $post_status = 'draft';
              error_log( "Event status is '{$meta_inputs['wp_birdlife_event_status']}'. Setting post_status to 'draft'." );
            }
            
            // Log the determined post status
            error_log( "Post status determined: " . $post_status );
            
            // Create a unique slug
            $event_date_from = isset( $meta_inputs['wp_birdlife_event_date_from_dat'] ) ? date( 'Y-m-d', strtotime( $meta_inputs['wp_birdlife_event_date_from_dat'] ) ) : '';
            $slug            = $this->slugify( $post_title . '-' . $event_date_from );
            error_log( "Generated slug: " . $slug );
            
            // Check for existing events with the same title
            $args = array(
              'post_type'      => 'naturkurs',
              'post_status'    => array( 'publish', 'pending', 'future', 'private' ),
              'title'          => $post_title,
              'posts_per_page' => - 1,
            );
            
            $existing_posts = get_posts( $args );
            
            if ( ! empty( $existing_posts ) ) {
              error_log( "Found " . count( $existing_posts ) . " existing post(s) with the same title." );
              foreach ( $existing_posts as $existing_post ) {
                $existing_event_date_to           = get_post_meta( $existing_post->ID, 'wp_birdlife_event_date_to_dat', true );
                $existing_event_date_to_timestamp = strtotime( $existing_event_date_to );
                
                if ( $existing_event_date_to_timestamp < $today_timestamp ) {
                  wp_update_post(
                    array(
                      'ID'          => $existing_post->ID,
                      'post_status' => 'draft',
                    )
                  );
                  error_log( "Updated existing post ID {$existing_post->ID} status to 'draft' because date_to is in the past." );
                }
              }
            } else {
              error_log( "No existing posts found with the same title." );
            }
            
            // Insert or update the event
            $existing_event = $this->get_naturkurs_post_by_event_id( $meta_inputs['wp_birdlife_manage_plus_event_id'] );
            
            if ( empty( $existing_event ) ) {
              // Insert new event
              $new_post_id = wp_insert_post(
                array(
                  'post_title'   => $post_title,
                  'post_type'    => 'naturkurs',
                  'post_name'    => $slug,
                  'post_status'  => $post_status,
                  'post_content' => $meta_inputs['wp_birdlife_event_course_description'],
                  'meta_input'   => $meta_inputs
                )
              );
              
              if ( $new_post_id && ! is_wp_error( $new_post_id ) ) {
                error_log( "Inserted new event with ID: " . $new_post_id );
              } else {
                error_log( "Failed to insert new event. Error: " . print_r( $new_post_id, true ) );
              }
            } else {
              // Update existing event
              foreach ( $existing_event as $post ) {
                $updated_post_id = wp_update_post(
                  array(
                    'ID'           => $post->ID,
                    'post_title'   => $post_title,
                    'post_type'    => 'naturkurs',
                    'post_status'  => $post_status,
                    'post_content' => $meta_inputs['wp_birdlife_event_course_description'],
                    'meta_input'   => $meta_inputs
                  )
                );
                
                if ( $updated_post_id && ! is_wp_error( $updated_post_id ) ) {
                  error_log( "Updated existing event with ID: " . $post->ID );
                } else {
                  error_log( "Failed to update event with ID: " . $post->ID . ". Error: " . print_r( $updated_post_id, true ) );
                }
              }
            }
          } else {
            error_log( "Skipped item because 'id' is empty." );
          }
        }
        
        // Additional code as needed...
        
        // Update the last sync time
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
        // Update the last manual sync time
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
    }
  }
?>
