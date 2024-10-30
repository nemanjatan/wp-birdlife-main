<?php
  
  if ( ! class_exists( 'WP_Birdlife_Event_Reference' ) ) {
    class WP_Birdlife_Event_Reference {
      
      private const ADDRESS_URL = 'https://de1.zetcom-group.de/MpWeb-maZurichBirdlife/ria-ws/application/module/Address/search';
      private const ADDRESS_SEARCH_XML_PATH = WP_BIRDLIFE_PATH . 'xml/address-search/address-search-all-fields.xml';
      
      public function handle_event_involved( $module_reference, $helper ) {
        $this->log_message( "Handling event involved reference." );
        
        return $this->process_addresses( $module_reference['moduleReferenceItem'] ?? [], $helper );
      }
      
      public function handle_event_involved_ref( $module_item, $helper ) {
        $this->log_message( "Handling event involved ref." );
        
        return $this->process_addresses( $module_item['moduleReference']['moduleReferenceItem'] ?? [], $helper );
      }
      
      private function process_addresses( $addresses, $helper ) {
        $management = "";
        
        foreach ( $addresses as $address ) {
          if ( is_array( $address ) && ! empty( $address['@attributes']['moduleItemId'] ) ) {
            $address_id = $address['@attributes']['moduleItemId'];
            $this->log_message( "Processing address with ID: $address_id" );
            
            $xml = file_get_contents( self::ADDRESS_SEARCH_XML_PATH );
            $xml = str_replace( "{{address_id}}", $address_id, $xml );
            
            $args = $helper->get_manage_plus_api_args( $xml );
            $resp = wp_remote_post( self::ADDRESS_URL, $args );
            $body = $resp['body'] ?? '';
            
            $parsed_xml  = simplexml_load_string( $body );
            $json        = json_encode( $parsed_xml );
            $parsed_json = json_decode( $json, true );
            
            $data_fields = $parsed_json['modules']['module']['moduleItem']['dataField'] ?? [];
            $composite   = $parsed_json['modules']['module']['moduleItem']['composite'] ?? [];
            
            $management .= $this->process_data_fields( $data_fields );
            $management .= $this->process_composite( $composite );
          } else {
            $this->log_message( "Invalid address or missing moduleItemId." );
          }
        }
        
        return $management;
      }
      
      private function process_data_fields( $data_fields ) {
        $management = "";
        
        foreach ( $data_fields as $data_field ) {
          $data_field_name = $data_field['@attributes']['name'] ?? '';
          $value           = $data_field['value'] ?? '';
          
          if ( $data_field_name === 'AdrForeNameTxt' ) {
            $management .= "<br>" . $value;
          } elseif ( $data_field_name === 'AdrSurNameTxt' ) {
            $management .= " " . $value;
          }
        }
        
        return $management;
      }
      
      private function process_composite( $composite ) {
        $management = "";
        
        foreach ( $composite as $item ) {
          if ( $item['@attributes']['name'] === 'AdrReferencesEventCre' ) {
            $composite_items = $item['compositeItem'] ?? [];
            
            foreach ( $composite_items as $composite_item ) {
              $module_reference_items = $composite_item['moduleReference']['moduleReferenceItem'] ?? [];
              
              foreach ( $module_reference_items as $module_reference_item ) {
                $data_fields = $module_reference_item['dataField'] ?? [];
                
                foreach ( $data_fields as $data_field ) {
                  if ( $data_field['@attributes']['name'] === 'NotesClb' ) {
                    $management .= ", " . ( $data_field['value'] ?? '' );
                  }
                }
              }
            }
          }
        }
        
        return $management;
      }
      
      private function log_message( $message ) {
        $logDir = __DIR__ . '/logs';
        if ( ! is_dir( $logDir ) ) {
          mkdir( $logDir, 0777, true );
        }
        $logFile          = $logDir . "/birdlife_event_reference_log.txt";
        $currentDateTime  = date( 'Y-m-d H:i:s' );
        $formattedMessage = $currentDateTime . " - " . $message . "\n";
        file_put_contents( $logFile, $formattedMessage, FILE_APPEND );
      }
    }
  }
