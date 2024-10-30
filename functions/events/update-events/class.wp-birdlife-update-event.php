<?php
  
  if ( ! class_exists( 'WP_Birdlife_Update_Event' ) ) {
    class WP_Birdlife_Update_Event {
      public function update_events(
        $module_item,
        $helper,
        $post
      ) {
        $event_multimedia_helper = new WP_Birdlife_Event_Multimedia();
        $event_reference         = new WP_Birdlife_Event_Reference();
        $birdlife_reserved_tn    = new WP_Birdlife_Reserved_Tn();
        $free_seats_helper       = new WP_Birdlife_Free_Seats();
        
        $existing_post = $post[0];
        $post_metas    = get_post_meta( $existing_post->ID );
        
        if ( ! empty( $module_item['dataField'] ) ) {
          $data_fields = $module_item['dataField'];
          
          if ( is_array( $data_fields ) && $data_fields['value'] === null ) {
            foreach ( $data_fields as $data_field ) {
              $this->update_event( $existing_post, $data_field, $post_metas, $helper );
            }
          } else {
            $this->update_event( $existing_post, $data_fields, $post_metas, $helper );
          }
        }
        
        // Updating reserved tn
        $wp_birdlife_event_reserved_tn = $birdlife_reserved_tn->fetch_reserved_tn( $module_item['systemField'][0]['value'] );
        update_post_meta(
          $existing_post->ID,
          'wp_birdlife_event_reserved_tn',
          $wp_birdlife_event_reserved_tn
        );
        
        // Updating wp_birdlife_event_status and wp_birdlife_event_currency_voc
        $this->update_event_status_and_currency(
          $existing_post,
          $module_item,
          $post_metas
        );
        
        // Updating image and leitung
        $leitung                                          = '';
        $wp_birdlife_event_featured_image_photocredit_txt = '';
        $wp_birdlife_event_featured_image                 = '';
        
        if ( is_array( $module_item['moduleReference'] ) ) {
          if ( is_array( $module_item['moduleReference']['@attributes'] ) ) {
            $reference_name = $module_item['moduleReference']['@attributes']['name'];
            
            if ( $reference_name === 'EvtMultimediaRef' ) {
              $multimedia = $event_multimedia_helper->get_image_for_event( $module_item, $helper );
              
              if ( $multimedia[0] !== '' ) {
                $wp_birdlife_event_featured_image = $multimedia[0];
              }
              
              if ( $multimedia[1] !== '' ) {
                $wp_birdlife_event_featured_image_photocredit_txt = $multimedia[1];
              }
            } else if ( $reference_name === 'EvtInvolvedRef' ) {
              $management = $event_reference->handle_event_involved_ref( $module_item, $helper );
              
              if ( $management !== '' ) {
                $leitung = $management;
              }
            }
          } else {
            foreach ( $module_item['moduleReference'] as $module_reference ) {
              if ( is_array( $module_reference['@attributes'] ) ) {
                $reference_name = $module_reference['@attributes']['name'];
                
                if ( $reference_name === 'EvtMultimediaRef' ) {
                  list ( $wp_birdlife_event_featured_image, $wp_birdlife_event_featured_image_photocredit_txt ) = $event_multimedia_helper->handle_multimedia_for_event( $module_reference, $helper );
                } else if ( $reference_name === 'EvtInvolvedRef' ) {
                  $leitung = $event_reference->handle_event_involved( $module_reference, $helper );
                }
              }
            }
          }
        }
        
        if ( $wp_birdlife_event_featured_image !== ''
             && $wp_birdlife_event_featured_image !== false ) {
          update_post_meta(
            $existing_post->ID,
            'wp_birdlife_event_featured_image',
            $wp_birdlife_event_featured_image
          );
          
          update_post_meta(
            $existing_post->ID,
            'wp_birdlife_event_featured_image_exists',
            'yes'
          );
        } else {
          update_post_meta(
            $existing_post->ID,
            'wp_birdlife_event_featured_image',
            'https://birdlife-zuerich.ch/wp-content/uploads/2022/10/default-img.png'
          );
          
          update_post_meta(
            $existing_post->ID,
            'wp_birdlife_event_featured_image_exists',
            'no'
          );
        }
        
        if ( $wp_birdlife_event_featured_image_photocredit_txt !== ''
             && $wp_birdlife_event_featured_image_photocredit_txt !== false ) {
          update_post_meta(
            $existing_post->ID,
            'wp_birdlife_event_featured_image_photocredit_txt',
            $wp_birdlife_event_featured_image_photocredit_txt
          );
        } else {
          delete_post_meta( $existing_post->ID,
            'wp_birdlife_event_featured_image_photocredit_txt'
          );
        }
        
        if ( $leitung !== '' && $leitung !== false ) {
          
          
          $wp_birdlife_event_leader = get_post_meta(
            $existing_post->ID,
            'wp_birdlife_event_leader',
            true
          );
          $leitung                  = $wp_birdlife_event_leader;
          
          
          update_post_meta(
            $existing_post->ID,
            'wp_birdlife_leitung',
            $leitung
          );
        } else {
          delete_post_meta( $existing_post->ID,
            'wp_birdlife_leitung'
          );
        }
        // End of updating the image and leitung
        
        // Updating confirmed seats
        $wp_birdlife_event_confirmed_tn = 0;
        if ( $module_item['repeatableGroup'] !== null ) {
          $wp_birdlife_event_confirmed_tn = $free_seats_helper->get_free_seats( $module_item );
        }
        
        update_post_meta(
          $existing_post->ID,
          'wp_birdlife_event_confirmed_tn',
          $wp_birdlife_event_confirmed_tn
        );
        // End of updating confirmed seats
        
        // UPDATE FREE SEATS
        $wp_birdlife_event_num_max_lnu = get_post_meta(
          $existing_post->ID,
          'wp_birdlife_event_num_max_lnu',
          true
        );
        
        if ( ! empty( $wp_birdlife_event_num_max_lnu ) ) {
          $free_seats = $wp_birdlife_event_num_max_lnu;
          
          if ( $free_seats > 0 ) {
            if ( ! empty( $wp_birdlife_event_confirmed_tn ) ) {
              $free_seats = $free_seats - $wp_birdlife_event_confirmed_tn;
            }
            
            if ( $free_seats > 0 ) {
              if ( ! empty( $wp_birdlife_event_reserved_tn ) ) {
                $free_seats = $free_seats - $wp_birdlife_event_reserved_tn;
              }
            }
          }
          
          if ( $free_seats < 0 ) {
            $free_seats = 0;
          }
          
          if ( $free_seats > 3 ) {
            $free_seats = 'freie Plätze';
          } else if ( $free_seats > 0 && $free_seats <= 3 ) {
            $free_seats = 'Letzte Plätze frei';
          } else if ( $free_seats == 0 ) {
            $free_seats = 'ausgebucht';
          }
          
          update_post_meta(
            $existing_post->ID,
            'wp_birdlife_event_free_seats',
            $free_seats
          );
        }
        // END OF UPDATING FREE SEATS
        
        // Update post status
        $post_status              = 'publish';
        $wp_birdlife_event_status = get_post_meta(
          $existing_post->ID,
          'wp_birdlife_event_status',
          true
        );
        
        if ( $wp_birdlife_event_status === 'in Durchführung'
             || str_contains( $wp_birdlife_event_status, 'in Planung' )
             || str_contains( $wp_birdlife_event_status, 'in planung' ) ) {
          $post_status = 'draft';
        }
        
        if ( $wp_birdlife_event_status === 'ausgeschrieben'
             || str_contains( $wp_birdlife_event_status, 'ausgeschrieben' )
             || str_contains( $wp_birdlife_event_status, 'ausgeschrieben' ) ) {
          $post_status = 'publish';
        }
        
        wp_update_post( array(
          'ID'          => $existing_post->ID,
          'post_status' => $post_status
        ) );
        // Update post status
        
        update_post_meta(
          $existing_post->ID,
          'wp_birdlife_event_confirmed_tn',
          $wp_birdlife_event_confirmed_tn
        );
      }
      
      public function update_event_status_and_currency( $existing_post, $module_item, $post_metas ) {
        $wp_birdlife_event_status = '';
        
        if ( is_array( $module_item['virtualField'] ) ) {
          foreach ( $module_item['virtualField'] as $virtual_field ) {
            if ( $virtual_field['@attributes']['name'] === 'EvtStatusVrt' ) {
              $wp_birdlife_event_status = $virtual_field['value'];
            }
          }
        } else {
          $wp_birdlife_event_status = $module_item['virtualField']['value'];
        }
        
        $wp_birdlife_event_currency_voc = $module_item['vocabularyReference']['vocabularyReferenceItem']['formattedValue'];
        
        // Updating wp_birdlife_event_currency_voc meta key
        if ( $wp_birdlife_event_currency_voc !== null && $wp_birdlife_event_currency_voc !== false ) {
          $meta_key_exists   = false;
          $post_needs_update = false;
          
          foreach ( $post_metas as $meta_key => $meta_value ) {
            if ( $meta_key === 'wp_birdlife_event_currency_voc' ) {
              $meta_key_exists = true;
              if ( is_array( $meta_value ) ) {
                if ( $meta_value[0] !== $wp_birdlife_event_currency_voc ) {
                  $post_needs_update = true;
                }
              } else {
                if ( $meta_value !== $wp_birdlife_event_currency_voc ) {
                  $post_needs_update = true;
                }
              }
            }
          }
          
          if ( ! $meta_key_exists || $post_needs_update ) {
            update_post_meta(
              $existing_post->ID,
              'wp_birdlife_event_currency_voc',
              $wp_birdlife_event_currency_voc
            );
          }
        }
        
        // Updating wp_birdlife_event_status meta key
        if ( $wp_birdlife_event_status !== '' ) {
          $meta_key_exists   = false;
          $post_needs_update = false;
          
          foreach ( $post_metas as $meta_key => $meta_value ) {
            if ( $meta_key === 'wp_birdlife_event_status' ) {
              $meta_key_exists = true;
              if ( is_array( $meta_value ) ) {
                if ( $meta_value[0] !== $wp_birdlife_event_status ) {
                  $post_needs_update = true;
                }
              } else {
                if ( $meta_value !== $wp_birdlife_event_status ) {
                  $post_needs_update = true;
                }
              }
            }
          }
          
          if ( ! $meta_key_exists || $post_needs_update ) {
            update_post_meta(
              $existing_post->ID,
              'wp_birdlife_event_status',
              $wp_birdlife_event_status
            );
          }
        }
      }
      
      public function update_event( $existing_post, $data_field, $post_metas, $helper ): void {
        if ( ! empty( $data_field['@attributes']['name'] ) ) {
          $data_field_name = $data_field['@attributes']['name'];
          if ( $data_field_name === 'EvtRegistrationUntilDat' ) {
            $this->check_and_update(
              $post_metas,
              $data_field['value'],
              $existing_post,
              'wp_birdlife_event_registration_until_date'
            );
          } else if ( $data_field_name === 'EvtCourseLabelTxt' ) {
            $this->check_and_update(
              $post_metas,
              $data_field['value'],
              $existing_post,
              'wp_birdlife_event_title'
            );
            $this->check_and_update_post_title( $existing_post, $data_field['value'] );
          } else if ( $data_field_name === 'EvtExternalLinkTxt' ) {
            $this->check_and_update(
              $post_metas,
              $data_field['value'],
              $existing_post,
              'wp_birdlife_event_external_link'
            );
          } else if ( $data_field_name === 'EvtPlaceTxt' ) {
            $this->check_and_update(
              $post_metas,
              $data_field['value'],
              $existing_post,
              'wp_birdlife_event_place'
            );
          } else if ( $data_field_name === 'EvtPhoneTxt' ) {
            $this->check_and_update(
              $post_metas,
              $data_field['value'],
              $existing_post,
              'wp_birdlife_event_phone'
            );
          } else if ( $data_field_name === 'EvtInformationRegistrationClb' ) {
            $this->check_and_update(
              $post_metas,
              $data_field['value'],
              $existing_post,
              'wp_birdlife_event_information_registration'
            );
          } else if ( $data_field_name === 'EvtEmailTxt' ) {
            $this->check_and_update(
              $post_metas,
              $data_field['value'],
              $existing_post,
              'wp_birdlife_event_email'
            );
          } else if ( $data_field_name === 'EvtCreditsNum' ) {
            $this->check_and_update(
              $post_metas,
              $data_field['value'],
              $existing_post,
              'wp_birdlife_event_credits'
            );
          } else if ( $data_field_name === 'EvtOnlineDat' ) {
            $this->check_and_update(
              $post_metas,
              $data_field['value'],
              $existing_post,
              'wp_birdlife_event_online_date'
            );
          } else if ( $data_field_name === 'EvtNumMinLnu' ) {
            $this->check_and_update(
              $post_metas,
              $data_field['value'],
              $existing_post,
              'wp_birdlife_event_num_min_lnu'
            );
          } else if ( $data_field_name === 'EvtNumMaxLnu' ) {
            $this->check_and_update(
              $post_metas,
              $data_field['value'],
              $existing_post,
              'wp_birdlife_event_num_max_lnu'
            );
          } else if ( $data_field_name === 'EvtCourseDescriptionShortClb' ) {
            $this->check_and_update(
              $post_metas,
              $data_field['value'],
              $existing_post,
              'wp_birdlife_event_course_description_short'
            );
          } else if ( $data_field_name === 'EvtCostTxt' ) {
            $this->check_and_update(
              $post_metas,
              $data_field['value'],
              $existing_post,
              'wp_birdlife_event_cost'
            );
          } else if ( $data_field_name === 'EvtCourseMultipleEventsClb' ) {
            $this->check_and_update(
              $post_metas,
              $data_field['value'],
              $existing_post,
              'wp_birdlife_event_course_multiple_events'
            );
          } else if ( $data_field_name === 'EvtProgramClb' ) {
            $this->check_and_update(
              $post_metas,
              $data_field['value'],
              $existing_post,
              'wp_birdlife_event_program'
            );
          } else if ( $data_field_name === 'EvtTimeToTim' ) {
            $this->check_and_update(
              $post_metas,
              $data_field['value'],
              $existing_post,
              'wp_birdlife_event_time_to_tim'
            );
          } else if ( $data_field_name === 'EvtOvernightPlaceClb' ) {
            $this->check_and_update(
              $post_metas,
              $data_field['value'],
              $existing_post,
              'wp_birdlife_event_overnight_place'
            );
          } else if ( $data_field_name === 'EvtTimeFromTim' ) {
            $this->check_and_update(
              $post_metas,
              $data_field['value'],
              $existing_post,
              'wp_birdlife_event_time_from_tim'
            );
          } else if ( $data_field_name === 'EvtMaterialsClb' ) {
            $this->check_and_update(
              $post_metas,
              $data_field['value'],
              $existing_post,
              'wp_birdlife_event_materials'
            );
          } else if ( $data_field_name === 'EvtBokingTemplateIdLnu' ) {
            $this->check_and_update(
              $post_metas,
              $data_field['value'],
              $existing_post,
              'wp_birdlife_event_boking_template_id_lnu'
            );
          } else if ( $data_field_name === 'EvtApprovedNotesClb' ) {
            $this->check_and_update(
              $post_metas,
              $data_field['value'],
              $existing_post,
              'wp_birdlife_event_approved_notes'
            );
          } else if ( $data_field_name === 'EvtApprovedTxt' ) {
            $this->check_and_update(
              $post_metas,
              $data_field['value'],
              $existing_post,
              'wp_birdlife_event_approved_text'
            );
          } else if ( $data_field_name === 'EvtApprovedDecisionDateDat' ) {
            $this->check_and_update(
              $post_metas,
              $data_field['value'],
              $existing_post,
              'wp_birdlife_event_approved_decision_date'
            );
          } else if ( $data_field_name === 'EvtApprovedDateDat' ) {
            $this->check_and_update(
              $post_metas,
              $data_field['value'],
              $existing_post,
              'wp_birdlife_event_approved_date'
            );
          } else if ( $data_field_name === 'EvtNotesClb' ) {
            $this->check_and_update(
              $post_metas,
              $data_field['value'],
              $existing_post,
              'wp_birdlife_event_notes'
            );
          } else if ( $data_field_name === 'EvtDescriptionClb' ) {
            $this->check_and_update(
              $post_metas,
              $data_field['value'],
              $existing_post,
              'wp_birdlife_event_description'
            );
          } else if ( $data_field_name === 'EvtDateToDat' ) {
            $this->check_and_update(
              $post_metas,
              $data_field['value'],
              $existing_post,
              'wp_birdlife_event_date_to_dat'
            );
          } else if ( $data_field_name === 'EvtDateFromDat' ) {
            list( $day, $day_from_date, $year_from_date, $german_month_name ) = $helper->generate_german_date( $data_field['value'] );
            $formatted_date = $day . ' ' . $day_from_date . '. ' . $german_month_name . ' ' . $year_from_date;
            
            $this->check_and_update(
              $post_metas,
              '<li>' . $formatted_date . '</li>',
              $existing_post,
              'wp_birdlife_event_date_from_dat'
            );
            
            $this->check_and_update(
              $post_metas,
              $formatted_date,
              $existing_post,
              'wp_birdlife_event_date_from_dat_naturkurse'
            );
            
            $this->check_and_update(
              $post_metas,
              strtotime( '+1 day', strtotime( $data_field['value'] ) ),
              $existing_post,
              'wp_birdlife_event_date_from_dat_timestamp'
            );
          } else if ( $data_field_name === 'EvtRegistrationStartDat' ) {
            $this->check_and_update(
              $post_metas,
              $data_field['value'],
              $existing_post,
              'wp_birdlife_event_registration_start_dat'
            );
          } else if ( $data_field_name === 'EvtLeaderClb' ) {
            $this->check_and_update(
              $post_metas,
              $data_field['value'],
              $existing_post,
              'wp_birdlife_event_leader'
            );
          } else if ( $data_field_name === 'EvtInformationClb' ) {
            $this->check_and_update(
              $post_metas,
              $data_field['value'],
              $existing_post,
              'wp_birdlife_event_information'
            );
          } else if ( $data_field_name === 'EvtDatingClb' ) {
            $this->check_and_update(
              $post_metas,
              $data_field['value'],
              $existing_post,
              'wp_birdlife_event_dating'
            );
          } else if ( $data_field_name === 'EvtOfferClb' ) {
            $this->check_and_update(
              $post_metas,
              $data_field['value'],
              $existing_post,
              'wp_birdlife_event_offer'
            );
          } else if ( $data_field_name === 'EvtNumberParticipantsLnu' ) {
            $this->check_and_update(
              $post_metas,
              $data_field['value'],
              $existing_post,
              'wp_birdlife_event_number_participants'
            );
          } else if ( $data_field_name === 'EvtIDNum' ) {
            $this->check_and_update(
              $post_metas,
              $data_field['value'],
              $existing_post,
              'wp_birdlife_event_id_num'
            );
          } else if ( $data_field_name === 'EvtNumberGroupsLnu' ) {
            $this->check_and_update(
              $post_metas,
              $data_field['value'],
              $existing_post,
              'wp_birdlife_event_number_groups'
            );
          } else if ( $data_field_name === 'EvtRegionTxt' ) {
            $this->check_and_update(
              $post_metas,
              $data_field['value'],
              $existing_post,
              'wp_birdlife_event_region'
            );
          } else if ( $data_field_name === 'EvtCourseDescriptionClb' ) {
            $this->check_and_update(
              $post_metas,
              $data_field['value'],
              $existing_post,
              'wp_birdlife_event_course_description'
            );
            $this->check_and_update_post_content( $existing_post, $data_field['value'] );
          } else if ( $data_field_name === 'EvtOrganizerTxt' ) {
            $this->check_and_update(
              $post_metas,
              $data_field['value'],
              $existing_post,
              'wp_birdlife_event_organizer'
            );
          } else if ( $data_field_name === 'EvtCourseCostsTxt' ) {
            $this->check_and_update(
              $post_metas,
              $data_field['value'],
              $existing_post,
              'wp_birdlife_event_course_costs'
            );
          } else if ( $data_field_name === 'EvtEquipmentClb' ) {
            $this->check_and_update(
              $post_metas,
              $data_field['value'],
              $existing_post,
              'wp_birdlife_event_equipment'
            );
          } else if ( $data_field_name === 'EvtCourseAdditionalClb' ) {
            $this->check_and_update(
              $post_metas,
              $data_field['value'],
              $existing_post,
              'wp_birdlife_event_course_additional'
            );
          } else if ( $data_field_name === 'EvtNeuesFeldTxt' ) {
            $this->check_and_update(
              $post_metas,
              $data_field['value'],
              $existing_post,
              'wp_birdlife_event_neues_feld'
            );
          }
        }
      }
      
      public function check_and_update( $post_metas, $value, $existing_post, $m_key ): void {
        $post_needs_update = false;
        $meta_key_exists   = false;
        foreach ( $post_metas as $meta_key => $meta_value ) {
          if ( $meta_key === $m_key ) {
            $meta_key_exists = true;
            if ( is_array( $meta_value ) ) {
              if ( $meta_value[0] !== $value ) {
                $post_needs_update = true;
              }
            } else {
              if ( $meta_value !== $value ) {
                $post_needs_update = true;
              }
            }
          }
        }
        
        if ( ! $meta_key_exists || $post_needs_update ) {
          update_post_meta(
            $existing_post->ID,
            $m_key,
            $value
          );
        }
      }
      
      private function check_and_update_post_title( $existing_post, $new_title ): void {
        if ( $existing_post->post_title !== $new_title ) {
          $my_post = array(
            'ID'         => $existing_post->ID,
            'post_title' => $new_title,
          );
          wp_update_post( $my_post );
        }
      }
      
      private function check_and_update_post_content( $existing_post, $new_content ): void {
        if ( $existing_post->post_content !== $new_content ) {
          $my_post = array(
            'ID'           => $existing_post->ID,
            'post_content' => $new_content,
          );
          wp_update_post( $my_post );
        }
      }
    }
  }