<?php
  
  if ( ! class_exists( 'WP_Birdlife_Event_Reference' ) ) {
    class WP_Birdlife_Event_Reference {
      public function handle_event_involved( $module_reference, $helper ) {
        $management  = "";
        $address_url = 'https://maBirdlife.zetcom.app/ria-ws/application/module/Address/search';
        
        if ( is_array( $module_reference['moduleReferenceItem'] ) ) {
          // it has multiple addresses
          
          foreach ( $module_reference['moduleReferenceItem'] as $address ) {
            if ( is_array( $address ) && $address['@attributes'] !== null ) {
              if ( $address['@attributes']['moduleItemId'] !== null ) {
                $address_id = $address['@attributes']['moduleItemId'];
                
                $xml = file_get_contents( WP_BIRDLIFE_PATH . 'xml/address-search/address-search-all-fields.xml' );
                $xml = str_replace( "{{address_id}}", $address_id, $xml );
                
                $args = $helper->get_manage_plus_api_args( $xml );
                $resp = wp_remote_post( $address_url, $args );
                $body = $resp['body'];
                
                $parsed_xml  = simplexml_load_string( $body );
                $json        = json_encode( $parsed_xml );
                $parsed_json = json_decode( $json, true );
                
                $data_fields = $parsed_json['modules']['module']['moduleItem']['dataField'];
                $composite   = $parsed_json['modules']['module']['moduleItem']['composite'];
                
                foreach ( $data_fields as $data_field ) {
                  if ( ! empty( $data_field['@attributes']['name'] ) ) {
                    $data_field_name = $data_field['@attributes']['name'];
                    if ( $data_field_name === 'AdrForeNameTxt' ) {
                      $management = $management . "<br>" . $data_field['value'];
                    }
                    
                    if ( $data_field_name === 'AdrSurNameTxt' ) {
                      $management = $management . " " . $data_field['value'];
                    }
                  }
                }
                
                foreach ( $composite as $item ) {
                  if ( $item['@attributes']['name'] === 'AdrReferencesEventCre' ) {
                    $composite_items = $item['compositeItem'];
                    
                    foreach ( $composite_items as $composite_item ) {
                      if ( $composite_item['moduleReference'] !== null ) {
                        $module_reference_items = $composite_item['moduleReference']['moduleReferenceItem'];
                        
                        foreach ( $module_reference_items as $module_reference_item ) {
                          if ( is_array( $module_reference_item ) && $module_reference_item['dataField'] !== null ) {
                            $notes_clb = $module_reference_item['dataField'];
                            
                            if ( is_array( $notes_clb ) && $notes_clb['@attributes'] !== null && $notes_clb['@attributes']['name'] === 'NotesClb' ) {
                              $management = $management . ", " . $notes_clb['value'];
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
        }
        
        return $management;
      }
      
      public function handle_event_involved_ref( $module_item, $helper ) {
        $management  = '';
        $address_url = 'https://maBirdlife.zetcom.app/ria-ws/application/module/Address/search';
        
        if ( is_array( $module_item['moduleReference']['moduleReferenceItem'] ) ) {
          foreach ( $module_item['moduleReference']['moduleReferenceItem'] as $address ) {
            if ( is_array( $address ) && $address['@attributes'] !== null ) {
              if ( $address['@attributes']['moduleItemId'] !== null ) {
                $address_id = $address['@attributes']['moduleItemId'];
                
                $xml = file_get_contents( WP_BIRDLIFE_PATH . 'xml/address-search/address-search-all-fields.xml' );
                $xml = str_replace( "{{address_id}}", $address_id, $xml );
                
                $args = $helper->get_manage_plus_api_args( $xml );
                $resp = wp_remote_post( $address_url, $args );
                $body = $resp['body'];
                
                $parsed_xml  = simplexml_load_string( $body );
                $json        = json_encode( $parsed_xml );
                $parsed_json = json_decode( $json, true );
                
                $data_fields = $parsed_json['modules']['module']['moduleItem']['dataField'];
                $composite   = $parsed_json['modules']['module']['moduleItem']['composite'];
                
                foreach ( $data_fields as $data_field ) {
                  if ( ! empty( $data_field['@attributes']['name'] ) ) {
                    $data_field_name = $data_field['@attributes']['name'];
                    if ( $data_field_name === 'AdrForeNameTxt' ) {
                      $management = $management . "<br>" . $data_field['value'];
                    }
                    
                    if ( $data_field_name === 'AdrSurNameTxt' ) {
                      $management = $management . " " . $data_field['value'];
                    }
                  }
                }
                
                foreach ( $composite as $item ) {
                  if ( $item['@attributes']['name'] === 'AdrReferencesEventCre' ) {
                    $composite_items = $item['compositeItem'];
                    
                    foreach ( $composite_items as $composite_item ) {
                      if ( $composite_item['moduleReference'] !== null ) {
                        $module_reference_items = $composite_item['moduleReference']['moduleReferenceItem'];
                        
                        foreach ( $module_reference_items as $module_reference_item ) {
                          if ( is_array( $module_reference_item ) && $module_reference_item['dataField'] !== null ) {
                            $notes_clb = $module_reference_item['dataField'];
                            
                            if ( is_array( $notes_clb ) && $notes_clb['@attributes'] !== null && $notes_clb['@attributes']['name'] === 'NotesClb' ) {
                              $management = $management . ", " . $notes_clb['value'];
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
        }
        
        return $management;
      }
    }
  }