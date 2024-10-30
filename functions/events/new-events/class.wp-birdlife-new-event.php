<?php
  
  if ( ! class_exists( 'WP_Birdlife_New_Event' ) ) {
    class WP_Birdlife_New_Event {
      
      private const EVENT_CATEGORY_VOC_PARENT_ID = '100150582';
      private const EVT_CATEGORY_VOC_INSTANCES_URL = 'https://de1.zetcom-group.de/MpWeb-maZurichBirdlife/ria-ws/application/vocabulary/instances/EvtCategoryVgr/nodes/';
      private const EVT_PROJECT_SEARCH_URL = 'https://de1.zetcom-group.de/MpWeb-maZurichBirdlife/ria-ws/application/module/Project/search/';
      private const PROJECT_SEARCH_XML_PATH = WP_BIRDLIFE_PATH . 'xml/project-search/project-search-all-fields-by-id.xml';
      
      public function save_new_events( $module_item, $helper ) {
        $event_multimedia_helper = new WP_Birdlife_Event_Multimedia();
        $event_reference         = new WP_Birdlife_Event_Reference();
        $birdlife_reserved_tn    = new WP_Birdlife_Reserved_Tn();
        $free_seats_helper       = new WP_Birdlife_Free_Seats();
        
        $module_item_arr = [
          'id'                              => $module_item['systemField'][0]['value'] ?? null,
          'wp_birdlife_event_last_modified' => $module_item['systemField'][3]['value'] ?? null,
          'wp_birdlife_event_reserved_tn'   => $birdlife_reserved_tn->fetch_reserved_tn( $module_item['systemField'][0]['value'] ?? null ),
          'wp_birdlife_event_status'        => $this->get_virtual_field_value( $module_item, 'EvtStatusVrt' ),
          'wp_birdlife_event_currency_voc'  => $module_item['vocabularyReference']['vocabularyReferenceItem']['formattedValue'] ?? null,
          'wp_birdlife_event_confirmed_tn'  => $this->get_confirmed_tn( $module_item, $free_seats_helper ),
        ];
        
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
        
        $this->handle_vocabulary_references( $module_item, $helper, $module_item_arr );
        $this->handle_module_references( $module_item, $helper, $module_item_arr, $event_multimedia_helper, $event_reference );
        
        return $module_item_arr;
      }
      
      private function get_virtual_field_value( $module_item, $field_name ) {
        if ( is_array( $module_item['virtualField'] ) ) {
          foreach ( $module_item['virtualField'] as $virtual_field ) {
            if ( $virtual_field['@attributes']['name'] === $field_name ) {
              return $virtual_field['value'] ?? null;
            }
          }
        } else {
          return $module_item['virtualField']['value'] ?? null;
        }
        
        return null;
      }
      
      private function get_confirmed_tn( $module_item, $free_seats_helper ) {
        if ( ! empty( $module_item['repeatableGroup'] ) ) {
          return $free_seats_helper->get_free_seats( $module_item );
        }
        
        return 0;
      }
      
      private function handle_vocabulary_references( $module_item, $helper, &$module_item_arr ) {
        if ( ! empty( $module_item['vocabularyReference'] ) ) {
          $vocabulary_references = is_array( $module_item['vocabularyReference']['@attributes'] ) ? [ $module_item['vocabularyReference'] ] : $module_item['vocabularyReference'];
          foreach ( $vocabulary_references as $vocabulary_reference ) {
            $reference_name = $vocabulary_reference['@attributes']['name'] ?? '';
            $reference_id   = $vocabulary_reference['@attributes']['id'] ?? '';
            
            if ( $reference_name === 'EvtTypeVoc' && in_array( $reference_id, [
                100183576,
                100183577,
                100183580,
                100183581
              ] ) ) {
              $module_item_arr['wp_birdlife_event_type_voc'] = $vocabulary_reference['vocabularyReferenceItem']['formattedValue'] ?? '';
            } elseif ( $reference_name === 'EvtCategoryVoc' ) {
              $this->handle_evt_category_voc( $vocabulary_reference, $helper, $module_item_arr );
            }
          }
        }
      }
      
      private function handle_evt_category_voc( $vocabulary_reference, $helper, &$module_item_arr ) {
        $module_item_arr['wp_birdlife_event_type_voc'] = $vocabulary_reference['vocabularyReferenceItem']['@attributes']['id'] ?? '';
        $wp_birdlife_event_category_voc                = $module_item_arr['wp_birdlife_event_type_voc'];
        
        if ( ! empty( $wp_birdlife_event_category_voc ) ) {
          $vocabulary_args = $helper->get_manage_plus_api_args_no_body();
          $vocabulary_resp = wp_remote_get( self::EVT_CATEGORY_VOC_INSTANCES_URL . $wp_birdlife_event_category_voc . '/parents', $vocabulary_args );
          
          $xml       = simplexml_load_string( $vocabulary_resp['body'] );
          $namespace = $xml->getNamespaces( true );
          $parents   = $xml->children( $namespace[''] );
          
          $nodeId                                = (string) $parents->parent->attributes()->nodeId;
          $wp_birdlife_event_category_voc_parent = $nodeId;
          if ( $wp_birdlife_event_category_voc_parent == self::EVENT_CATEGORY_VOC_PARENT_ID ) {
            $wp_birdlife_event_category_voc_parent                    = $wp_birdlife_event_category_voc;
            $module_item_arr['wp_birdlife_event_category_voc_parent'] = $wp_birdlife_event_category_voc_parent;
          }
          $module_item_arr['wp_birdlife_event_type_voc'] = $wp_birdlife_event_category_voc;
        }
      }
      
      private function handle_module_references( $module_item, $helper, &$module_item_arr, $event_multimedia_helper, $event_reference ) {
        if ( ! empty( $module_item['moduleReference'] ) ) {
          $module_references = is_array( $module_item['moduleReference']['@attributes'] ) ? [ $module_item['moduleReference'] ] : $module_item['moduleReference'];
          foreach ( $module_references as $module_reference ) {
            $reference_name = $module_reference['@attributes']['name'] ?? '';
            
            if ( $reference_name === 'EvtMultimediaRef' ) {
              $this->handle_evt_multimedia_ref( $module_reference, $helper, $module_item_arr, $event_multimedia_helper );
            } elseif ( $reference_name === 'EvtInvolvedRef' ) {
              $this->handle_evt_involved_ref( $module_reference, $helper, $module_item_arr, $event_reference );
            } elseif ( $reference_name === 'EvtProjectRef' ) {
              $this->handle_evt_project_ref( $module_reference, $helper, $module_item_arr );
            }
          }
        }
      }
      
      private function handle_evt_multimedia_ref( $module_reference, $helper, &$module_item_arr, $event_multimedia_helper ) {
        list( $wp_birdlife_event_featured_image, $wp_birdlife_event_featured_image_photocredit_txt ) = $event_multimedia_helper->handle_multimedia_for_event( $module_reference, $helper );
        if ( ! empty( $wp_birdlife_event_featured_image ) ) {
          $module_item_arr['wp_birdlife_event_featured_image'] = $wp_birdlife_event_featured_image;
        }
        if ( ! empty( $wp_birdlife_event_featured_image_photocredit_txt ) ) {
          $module_item_arr['wp_birdlife_event_featured_image_photocredit_txt'] = $wp_birdlife_event_featured_image_photocredit_txt;
        }
      }
      
      private function handle_evt_involved_ref( $module_reference, $helper, &$module_item_arr, $event_reference ) {
        $management = $event_reference->handle_event_involved_ref( $module_reference, $helper );
        if ( ! empty( $management ) ) {
          $module_item_arr['leitung'] = $management;
        }
      }
      
      private function handle_evt_project_ref( $module_reference, $helper, &$module_item_arr ) {
        $module_item_arr['wp_birdlife_event_project_ref'] = $module_reference['moduleReferenceItem']['formattedValue'] ?? '';
        
        $module_item_id = $module_reference['moduleReferenceItem']['@attributes']['moduleItemId'] ?? '';
        $xml            = file_get_contents( self::PROJECT_SEARCH_XML_PATH );
        $xml            = str_replace( "{{project_id}}", $module_item_id, $xml );
        $args           = $helper->get_manage_plus_api_args( $xml );
        
        $resp      = wp_remote_post( self::EVT_PROJECT_SEARCH_URL, $args );
        $resp_body = $resp['body'];
        
        $parsed_xml  = simplexml_load_string( $resp_body );
        $json        = json_encode( $parsed_xml );
        $parsed_json = json_decode( $json, true );
        
        $module_items         = $parsed_json['modules']['module']['moduleItem'] ?? [];
        $vocabulary_reference = $module_items['vocabularyReference'] ?? [];
        
        if ( ! empty( $module_items['repeatableGroup'] ) ) {
          foreach ( $module_items['repeatableGroup'] as $repeatableItem ) {
            if ( $repeatableItem['@attributes']['name'] === 'ProSpeciesGrp' ) {
              $ProSpeciesGrpId   = $repeatableItem['repeatableGroupItem']['vocabularyReference']['vocabularyReferenceItem']['@attributes']['id'] ?? '';
              $ProSpeciesGrpName = $repeatableItem['repeatableGroupItem']['vocabularyReference']['vocabularyReferenceItem']['@attributes']['name'] ?? $repeatableItem['repeatableGroupItem']['vocabularyReference']['vocabularyReferenceItem']['formattedValue'] ?? '';
              
              $module_item_arr['wp_birdlife_event_pro_species_grp_id']   = $ProSpeciesGrpId;
              $module_item_arr['wp_birdlife_event_pro_species_grp_name'] = $ProSpeciesGrpName;
            }
          }
        }
        
        if ( is_array( $vocabulary_reference ) ) {
          foreach ( $vocabulary_reference as $v_r ) {
            if ( $v_r['@attributes']['name'] === 'ProRecordTypeVoc' ) {
              $module_item_arr['wp_birdlife_event_pro_record_type_voc']      = $v_r['vocabularyReferenceItem']['@attributes']['id'] ?? '';
              $module_item_arr['wp_birdlife_event_pro_record_type_voc_name'] = $v_r['vocabularyReferenceItem']['@attributes']['name'] ?? '';
            }
          }
        }
      }
    }
  }
