<?php
  
  if ( ! class_exists( 'WP_Birdlife_New_Event' ) ) {
    class WP_Birdlife_New_Event {
      public function save_new_events(
        $module_item,
        $helper
      ) {
        $event_multimedia_helper = new WP_Birdlife_Event_Multimedia();
        $event_reference         = new WP_Birdlife_Event_Reference();
        $birdlife_reserved_tn    = new WP_Birdlife_Reserved_Tn();
        $free_seats_helper       = new WP_Birdlife_Free_Seats();
        
        $module_item_arr                                    = array( 'id' => $module_item['systemField'][0]['value'] );
        $module_item_arr['wp_birdlife_event_last_modified'] = $module_item['systemField'][3]['value'];
        
        if ( ! empty( $module_item['dataField'] ) ) {
          $data_fields = $module_item['dataField'];
          
          if ( is_array( $data_fields ) && $data_fields['value'] === null ) {
            foreach ( $data_fields as $data_field ) {
              $module_item_arr = $helper->set_metabox_values_from_api( $data_field, $module_item_arr );
            }
          } else {
            $module_item_arr = $helper->set_metabox_values_from_api( $data_fields, $module_item_arr );
          }
        }
        
        $module_item_arr['wp_birdlife_event_reserved_tn'] = $birdlife_reserved_tn->fetch_reserved_tn( $module_item['systemField'][0]['value'] );
        
        if ( is_array( $module_item['virtualField'] ) ) {
          foreach ( $module_item['virtualField'] as $virtual_field ) {
            if ( $virtual_field['@attributes']['name'] === 'EvtStatusVrt' ) {
              $module_item_arr['wp_birdlife_event_status'] = $virtual_field['value'];
            }
          }
        } else {
          $module_item_arr['wp_birdlife_event_status'] = $module_item['virtualField']['value'];
        }
        $module_item_arr['wp_birdlife_event_currency_voc'] = $module_item['vocabularyReference']['vocabularyReferenceItem']['formattedValue'];
        
        if ( is_array( $module_item['moduleReference'] ) ) {
          if ( is_array( $module_item['moduleReference']['@attributes'] ) ) {
            $reference_name = $module_item['moduleReference']['@attributes']['name'];
            
            if ( $reference_name === 'EvtMultimediaRef' ) {
              $multimedia = $event_multimedia_helper->get_image_for_event( $module_item, $helper );
              
              if ( $multimedia[0] !== '' ) {
                $module_item_arr['wp_birdlife_event_featured_image'] = $multimedia[0];
              }
              
              if ( $multimedia[1] !== '' ) {
                $module_item_arr['wp_birdlife_event_featured_image_photocredit_txt'] = $multimedia[1];
              }
            } else if ( $reference_name === 'EvtInvolvedRef' ) {
              $management = $event_reference->handle_event_involved_ref( $module_item, $helper );
              
              if ( $management !== '' ) {
                $module_item_arr['leitung'] = $management;
              }
            }
          } else {
            foreach ( $module_item['moduleReference'] as $module_reference ) {
              if ( is_array( $module_reference['@attributes'] ) ) {
                $reference_name = $module_reference['@attributes']['name'];
                
                if ( $reference_name === 'EvtMultimediaRef' ) {
                  list( $wp_birdlife_event_featured_image, $wp_birdlife_event_featured_image_photocredit_txt ) = $event_multimedia_helper->handle_multimedia_for_event( $module_reference, $helper );
                  $module_item_arr['wp_birdlife_event_featured_image_photocredit_txt'] = $wp_birdlife_event_featured_image_photocredit_txt;
                  if ( $wp_birdlife_event_featured_image !== '' ) {
                    $module_item_arr['wp_birdlife_event_featured_image'] = $wp_birdlife_event_featured_image;
                  }
                } else if ( $reference_name === 'EvtInvolvedRef' ) {
                  $management                 = $event_reference->handle_event_involved( $module_reference, $helper );
                  $module_item_arr['leitung'] = $management;
                }
              }
            }
          }
        }
        
        $wp_birdlife_event_confirmed_tn = 0;
        if ( $module_item['repeatableGroup'] !== null ) {
          $wp_birdlife_event_confirmed_tn = $free_seats_helper->get_free_seats( $module_item );
        }
        
        $module_item_arr['wp_birdlife_event_confirmed_tn'] = $wp_birdlife_event_confirmed_tn;
        
        return $module_item_arr;
      }
    }
  }