<?php
  
  if ( ! class_exists( 'WP_Birdlife_Update_Event' ) ) {
    class WP_Birdlife_Update_Event {
      
      private const PROJECT_SEARCH_URL = 'https://de1.zetcom-group.de/MpWeb-maZurichBirdlife/ria-ws/application/module/Project/search/';
      private const PROJECT_SEARCH_XML_PATH = WP_BIRDLIFE_PATH . 'xml/project-search/project-search-all-fields-by-id.xml';
      
      public function update_events( $module_item, $helper, $post ) {
        $this->log_message( "Updating events for post ID: {$post[0]->ID}" );
        $event_multimedia_helper = new WP_Birdlife_Event_Multimedia();
        $event_reference         = new WP_Birdlife_Event_Reference();
        $birdlife_reserved_tn    = new WP_Birdlife_Reserved_Tn();
        $free_seats_helper       = new WP_Birdlife_Free_Seats();
        
        $existing_post = $post[0];
        $post_metas    = get_post_meta( $existing_post->ID );
        
        // Update data fields
        $this->update_data_fields( $module_item, $existing_post, $post_metas, $helper );
        
        // Update reserved tn
        $this->update_reserved_tn( $module_item, $existing_post, $birdlife_reserved_tn );
        
        // Update status and currency
        $this->update_event_status_and_currency( $existing_post, $module_item, $post_metas, $helper );
        
        // Update image and leitung
        $this->update_image_and_leitung( $module_item, $existing_post, $post_metas, $helper, $event_multimedia_helper, $event_reference );
        
        // Update confirmed seats
        $this->update_confirmed_seats( $module_item, $existing_post, $free_seats_helper );
        
        // Update free seats
        $this->update_free_seats( $existing_post );
        
        // Update post status
        $this->update_post_status( $existing_post );
        
        update_post_meta( $existing_post->ID, 'wp_birdlife_event_updated_timestamp', date( 'Y-m-d H:i:s' ) );
        $this->log_message( "Event updated for post ID: {$existing_post->ID}" );
      }
      
      private function update_data_fields( $module_item, $existing_post, $post_metas, $helper ) {
        $this->log_message( "Updating data fields for post ID: {$existing_post->ID}" );
        $data_fields = $module_item['dataField'] ?? [];
        foreach ( (array) $data_fields as $data_field ) {
          $this->update_event( $existing_post, $data_field, $post_metas, $helper );
        }
      }
      
      private function update_reserved_tn( $module_item, $existing_post, $birdlife_reserved_tn ) {
        $this->log_message( "Updating reserved tn for post ID: {$existing_post->ID}" );
        $wp_birdlife_event_reserved_tn = $birdlife_reserved_tn->fetch_reserved_tn( $module_item['systemField'][0]['value'] ?? null );
        update_post_meta( $existing_post->ID, 'wp_birdlife_event_reserved_tn', $wp_birdlife_event_reserved_tn );
      }
      
      private function update_image_and_leitung( $module_item, $existing_post, $post_metas, $helper, $event_multimedia_helper, $event_reference ) {
        $this->log_message( "Updating image and leitung for post ID: {$existing_post->ID}" );
        $leitung                                          = '';
        $wp_birdlife_event_featured_image_photocredit_txt = '';
        $wp_birdlife_event_featured_image                 = '';
        
        foreach ( (array) $module_item['moduleReference'] ?? [] as $module_reference ) {
          $reference_name = $module_reference['@attributes']['name'] ?? '';
          
          if ( $reference_name === 'EvtMultimediaRef' ) {
            list( $wp_birdlife_event_featured_image, $wp_birdlife_event_featured_image_photocredit_txt ) = $event_multimedia_helper->handle_multimedia_for_event( $module_reference, $helper );
          } elseif ( $reference_name === 'EvtInvolvedRef' ) {
            $leitung = $event_reference->handle_event_involved( $module_reference, $helper );
          } elseif ( $reference_name === 'EvtProjectRef' ) {
            $this->update_project_reference( $module_reference, $existing_post, $helper );
          }
        }
        
        $this->update_post_image( $existing_post, $post_metas, $wp_birdlife_event_featured_image, 'wp_birdlife_event_featured_image' );
        $this->update_post_meta( $existing_post, 'wp_birdlife_event_featured_image_photocredit_txt', $wp_birdlife_event_featured_image_photocredit_txt );
        $this->update_post_meta( $existing_post, 'wp_birdlife_leitung', $leitung );
      }
      
      private function update_project_reference( $module_reference, $existing_post, $helper ) {
        $this->log_message( "Updating project reference for post ID: {$existing_post->ID}" );
        $wp_birdlife_event_project_ref = $module_reference['moduleReferenceItem']['formattedValue'] ?? '';
        $module_item_id                = $module_reference['moduleReferenceItem']['@attributes']['moduleItemId'] ?? '';
        
        $xml  = file_get_contents( self::PROJECT_SEARCH_XML_PATH );
        $xml  = str_replace( "{{project_id}}", $module_item_id, $xml );
        $args = $helper->get_manage_plus_api_args( $xml );
        
        $resp      = wp_remote_post( self::PROJECT_SEARCH_URL, $args );
        $resp_body = $resp['body'];
        
        $parsed_xml  = simplexml_load_string( $resp_body );
        $json        = json_encode( $parsed_xml );
        $parsed_json = json_decode( $json, true );
        
        $module_items         = $parsed_json['modules']['module']['moduleItem'];
        $vocabulary_reference = $module_items['vocabularyReference'];
        
        $this->update_project_vocabulary_references( $vocabulary_reference, $existing_post );
      }
      
      private function update_project_vocabulary_references( $vocabulary_reference, $existing_post ) {
        $this->log_message( "Updating project vocabulary references for post ID: {$existing_post->ID}" );
        foreach ( (array) $vocabulary_reference as $v_r ) {
          if ( $v_r['@attributes']['name'] === 'ProRecordTypeVoc' ) {
            $wp_birdlife_event_pro_record_type_voc      = $v_r['vocabularyReferenceItem']['@attributes']['id'] ?? '';
            $wp_birdlife_event_pro_record_type_voc_name = $v_r['vocabularyReferenceItem']['@attributes']['name'] ?? '';
            $this->update_post_meta( $existing_post, 'wp_birdlife_event_pro_record_type_voc', $wp_birdlife_event_pro_record_type_voc );
            $this->update_post_meta( $existing_post, 'wp_birdlife_event_pro_record_type_voc_name', $wp_birdlife_event_pro_record_type_voc_name );
          }
        }
      }
      
      private function update_confirmed_seats( $module_item, $existing_post, $free_seats_helper ) {
        $this->log_message( "Updating confirmed seats for post ID: {$existing_post->ID}" );
        $wp_birdlife_event_confirmed_tn = $free_seats_helper->get_free_seats( $module_item );
        update_post_meta( $existing_post->ID, 'wp_birdlife_event_confirmed_tn', $wp_birdlife_event_confirmed_tn );
      }
      
      private function update_free_seats( $existing_post ) {
        $this->log_message( "Updating free seats for post ID: {$existing_post->ID}" );
        $wp_birdlife_event_num_max_lnu = get_post_meta( $existing_post->ID, 'wp_birdlife_event_num_max_lnu', true );
        
        if ( ! empty( $wp_birdlife_event_num_max_lnu ) ) {
          $free_seats                     = $wp_birdlife_event_num_max_lnu;
          $wp_birdlife_event_confirmed_tn = get_post_meta( $existing_post->ID, 'wp_birdlife_event_confirmed_tn', true );
          $wp_birdlife_event_reserved_tn  = get_post_meta( $existing_post->ID, 'wp_birdlife_event_reserved_tn', true );
          
          if ( $free_seats > 0 ) {
            $free_seats -= $wp_birdlife_event_confirmed_tn;
          }
          
          if ( $free_seats > 0 ) {
            $free_seats -= $wp_birdlife_event_reserved_tn;
          }
          
          $free_seats = max( $free_seats, 0 );
          
          $free_seats_text = $this->get_free_seats_text( $free_seats );
          
          update_post_meta( $existing_post->ID, 'wp_birdlife_event_free_seats', $free_seats_text );
        }
      }
      
      private function get_free_seats_text( $free_seats ) {
        if ( $free_seats > 3 ) {
          return 'freie Plätze';
        } elseif ( $free_seats > 0 ) {
          return 'Letzte Plätze frei';
        } else {
          return 'ausgebucht';
        }
      }
      
      private function update_post_status( $existing_post ) {
        $this->log_message( "Updating post status for post ID: {$existing_post->ID}" );
        $post_status              = 'publish';
        $wp_birdlife_event_status = get_post_meta( $existing_post->ID, 'wp_birdlife_event_status', true );
        
        if ( in_array( $wp_birdlife_event_status, [ 'in Durchführung', 'abgesagt' ] ) ||
             str_contains( $wp_birdlife_event_status, 'in Planung' ) ||
             str_contains( $wp_birdlife_event_status, 'abgesagt' ) ) {
          $post_status = 'draft';
        }
        
        if ( in_array( $wp_birdlife_event_status, [ 'ausgeschrieben' ] ) ||
             str_contains( $wp_birdlife_event_status, 'ausgeschrieben' ) ) {
          $post_status = 'publish';
        }
        
        wp_update_post( [ 'ID' => $existing_post->ID, 'post_status' => $post_status ] );
      }
      
      private function update_post_image( $existing_post, $post_metas, $new_image, $m_key ) {
        $this->log_message( "Updating post image for post ID: {$existing_post->ID}" );
        $post_needs_update = false;
        $meta_key_exists   = false;
        
        foreach ( $post_metas as $meta_key => $meta_value ) {
          if ( $meta_key === $m_key ) {
            $meta_key_exists = true;
            if ( ( is_array( $meta_value ) && $meta_value[0] !== $new_image ) || $meta_value !== $new_image ) {
              $post_needs_update = true;
            }
          }
        }
        
        if ( ! $meta_key_exists || $post_needs_update || ! has_post_thumbnail( $existing_post->ID ) ) {
          $this->upload_image( $existing_post, $new_image );
        }
      }
      
      private function upload_image( $existing_post, $new_image ) {
        $this->log_message( "Uploading image for post ID: {$existing_post->ID}" );
        $decode = base64_decode( $new_image );
        $size   = getImageSizeFromString( $decode );
        
        if ( empty( $size['mime'] ) || strpos( $size['mime'], 'image/' ) !== 0 ) {
          die( 'Base64 value is not a valid image' );
        }
        
        $ext      = substr( $size['mime'], 6 );
        $img_file = $existing_post->post_name . ".{$ext}";
        
        $upload_dir       = wp_upload_dir();
        $unique_file_name = wp_unique_filename( $upload_dir['path'], $img_file );
        $filename         = basename( $unique_file_name );
        
        $file = wp_mkdir_p( $upload_dir['path'] ) ? $upload_dir['path'] . '/' . $filename : $upload_dir['basedir'] . '/' . $filename;
        file_put_contents( $file, $decode );
        
        $wp_filetype = wp_check_filetype( $filename, null );
        
        $attachment = [
          'post_mime_type' => $wp_filetype['type'],
          'post_title'     => sanitize_file_name( $filename ),
          'post_content'   => '',
          'post_status'    => 'inherit'
        ];
        
        $attach_id = wp_insert_attachment( $attachment, $file, $existing_post->ID );
        require_once( ABSPATH . 'wp-admin/includes/image.php' );
        $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
        wp_update_attachment_metadata( $attach_id, $attach_data );
        set_post_thumbnail( $existing_post->ID, $attach_id );
      }
      
      private function update_event_status_and_currency( $existing_post, $module_item, $post_metas, $helper ) {
        $this->log_message( "Updating event status and currency for post ID: {$existing_post->ID}" );
        $wp_birdlife_event_status       = '';
        $wp_birdlife_event_currency_voc = $module_item['vocabularyReference']['vocabularyReferenceItem']['formattedValue'] ?? '';
        
        if ( is_array( $module_item['virtualField'] ?? null ) ) {
          foreach ( $module_item['virtualField'] as $virtual_field ) {
            if ( $virtual_field['@attributes']['name'] === 'EvtStatusVrt' ) {
              $wp_birdlife_event_status = $virtual_field['value'] ?? '';
            }
          }
        } else {
          $wp_birdlife_event_status = $module_item['virtualField']['value'] ?? '';
        }
        
        $this->update_post_meta( $existing_post, 'wp_birdlife_event_currency_voc', $wp_birdlife_event_currency_voc );
        $this->update_post_meta( $existing_post, 'wp_birdlife_event_status', $wp_birdlife_event_status );
      }
      
      private function update_event( $existing_post, $data_field, $post_metas, $helper ) {
        $this->log_message( "Updating event field: " . json_encode( $data_field ) . " for post ID: {$existing_post->ID}" );
        $data_field_name = $data_field['@attributes']['name'] ?? '';
        
        $field_map = [
          'EvtRegistrationUntilDat'       => 'wp_birdlife_event_registration_until_date',
          'EvtCourseLabelTxt'             => 'wp_birdlife_event_title',
          'EvtExternalLinkTxt'            => 'wp_birdlife_event_external_link',
          'EvtPlaceTxt'                   => 'wp_birdlife_event_place',
          'EvtPhoneTxt'                   => 'wp_birdlife_event_phone',
          'EvtInformationRegistrationClb' => 'wp_birdlife_event_information_registration',
          'EvtEmailTxt'                   => 'wp_birdlife_event_email',
          'EvtCreditsNum'                 => 'wp_birdlife_event_credits',
          'EvtOnlineDat'                  => 'wp_birdlife_event_online_date',
          'EvtNumMinLnu'                  => 'wp_birdlife_event_num_min_lnu',
          'EvtNumMaxLnu'                  => 'wp_birdlife_event_num_max_lnu',
          'EvtCourseDescriptionShortClb'  => 'wp_birdlife_event_course_description_short',
          'EvtCostTxt'                    => 'wp_birdlife_event_cost',
          'EvtCourseMultipleEventsClb'    => 'wp_birdlife_event_course_multiple_events',
          'EvtProgramClb'                 => 'wp_birdlife_event_program',
          'EvtTimeToTim'                  => 'wp_birdlife_event_time_to_tim',
          'EvtOvernightPlaceClb'          => 'wp_birdlife_event_overnight_place',
          'EvtTimeFromTim'                => 'wp_birdlife_event_time_from_tim',
          'EvtMaterialsClb'               => 'wp_birdlife_event_materials',
          'EvtBokingTemplateIdLnu'        => 'wp_birdlife_event_boking_template_id_lnu',
          'EvtApprovedNotesClb'           => 'wp_birdlife_event_approved_notes',
          'EvtApprovedTxt'                => 'wp_birdlife_event_approved_text',
          'EvtApprovedDecisionDateDat'    => 'wp_birdlife_event_approved_decision_date',
          'EvtApprovedDateDat'            => 'wp_birdlife_event_approved_date',
          'EvtNotesClb'                   => 'wp_birdlife_event_notes',
          'EvtDescriptionClb'             => 'wp_birdlife_event_description',
          'EvtDateToDat'                  => 'wp_birdlife_event_date_to_dat',
          'EvtRegistrationStartDat'       => 'wp_birdlife_event_registration_start_dat',
          'EvtLeaderClb'                  => 'wp_birdlife_event_leader',
          'EvtInformationClb'             => 'wp_birdlife_event_information',
          'EvtDatingClb'                  => 'wp_birdlife_event_dating',
          'EvtOfferClb'                   => 'wp_birdlife_event_offer',
          'EvtNumberParticipantsLnu'      => 'wp_birdlife_event_number_participants',
          'EvtIDNum'                      => 'wp_birdlife_event_id_num',
          'EvtNumberGroupsLnu'            => 'wp_birdlife_event_number_groups',
          'EvtRegionTxt'                  => 'wp_birdlife_event_region',
          'EvtOrganizerTxt'               => 'wp_birdlife_event_organizer',
          'EvtCourseCostsTxt'             => 'wp_birdlife_event_course_costs',
          'EvtEquipmentClb'               => 'wp_birdlife_event_equipment',
          'EvtCourseAdditionalClb'        => 'wp_birdlife_event_course_additional',
          'EvtNeuesFeldTxt'               => 'wp_birdlife_event_neues_feld',
        ];
        
        if ( isset( $field_map[ $data_field_name ] ) ) {
          $this->check_and_update( $post_metas, $data_field['value'], $existing_post, $field_map[ $data_field_name ] );
        }
        
        if ( $data_field_name === 'EvtCourseLabelTxt' ) {
          $this->check_and_update_post_title( $existing_post, $data_field['value'] );
        } elseif ( $data_field_name === 'EvtDescriptionClb' ) {
          $this->check_and_update_post_content( $existing_post, $data_field['value'] );
        } elseif ( $data_field_name === 'EvtDateFromDat' ) {
          $this->update_date_from( $data_field, $existing_post, $post_metas, $helper );
        }
      }
      
      private function update_date_from( $data_field, $existing_post, $post_metas, $helper ) {
        $this->log_message( "Updating date from for post ID: {$existing_post->ID}" );
        list( $day, $day_from_date, $year_from_date, $german_month_name ) = $helper->generate_german_date( $data_field['value'] );
        $formatted_date = $day . ' ' . $day_from_date . '. ' . $german_month_name . ' ' . $year_from_date;
        
        $this->check_and_update( $post_metas, '<li>' . $formatted_date . '</li>', $existing_post, 'wp_birdlife_event_date_from_dat' );
        $this->check_and_update( $post_metas, $formatted_date, $existing_post, 'wp_birdlife_event_date_from_dat_naturkurse' );
        $this->check_and_update( $post_metas, strtotime( '+1 day', strtotime( $data_field['value'] ) ), $existing_post, 'wp_birdlife_event_date_from_dat_timestamp' );
      }
      
      private function check_and_update( $post_metas, $value, $existing_post, $m_key ) {
        $this->log_message( "Checking and updating meta key: $m_key for post ID: {$existing_post->ID}" );
        $post_needs_update = false;
        $meta_key_exists   = false;
        foreach ( $post_metas as $meta_key => $meta_value ) {
          if ( $meta_key === $m_key ) {
            $meta_key_exists = true;
            if ( ( is_array( $meta_value ) && $meta_value[0] !== $value ) || $meta_value !== $value ) {
              $post_needs_update = true;
            }
          }
        }
        
        if ( ! $meta_key_exists || $post_needs_update ) {
          update_post_meta( $existing_post->ID, $m_key, $value );
        }
      }
      
      private function check_and_update_post_title( $existing_post, $new_title ) {
        $this->log_message( "Checking and updating post title for post ID: {$existing_post->ID}" );
        if ( $existing_post->post_title !== $new_title ) {
          wp_update_post( [ 'ID' => $existing_post->ID, 'post_title' => $new_title ] );
        }
      }
      
      private function check_and_update_post_content( $existing_post, $new_content ) {
        $this->log_message( "Checking and updating post content for post ID: {$existing_post->ID}" );
        $post_metas = get_post_meta( $existing_post->ID );
        $flag       = false;
        foreach ( $post_metas as $meta_key => $meta_value ) {
          if ( $meta_key === 'wp_birdlife_event_place' ) {
            if ( is_array( $meta_value ) ) {
              $wp_birdlife_event_place = $meta_value[0];
              $my_post                 = [
                'ID'           => $existing_post->ID,
                'post_content' => $new_content . '<p style="display:none">' . $wp_birdlife_event_place . '</p>'
              ];
              wp_update_post( $my_post );
              $flag = true;
            }
          }
        }
        
        if ( ! $flag && $existing_post->post_content !== $new_content ) {
          wp_update_post( [ 'ID' => $existing_post->ID, 'post_content' => $new_content ] );
        }
      }
      
      private function update_post_meta( $existing_post, $meta_key, $meta_value ) {
        $this->log_message( "Updating post meta key: $meta_key for post ID: {$existing_post->ID}" );
        if ( $meta_value !== '' && $meta_value !== null ) {
          update_post_meta( $existing_post->ID, $meta_key, $meta_value );
        } else {
          delete_post_meta( $existing_post->ID, $meta_key );
        }
      }
      
      private function log_message( $message ) {
        $logDir = __DIR__ . '/../logs';
        if ( ! is_dir( $logDir ) ) {
          mkdir( $logDir, 0777, true );
        }
        $logFile          = $logDir . "/birdlife_update_event_log.txt";
        $currentDateTime  = date( 'Y-m-d H:i:s' );
        $formattedMessage = $currentDateTime . " - " . $message . "\n";
        file_put_contents( $logFile, $formattedMessage, FILE_APPEND );
      }
    }
  }
