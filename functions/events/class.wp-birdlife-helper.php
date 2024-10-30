<?php
  
  if ( ! class_exists( 'WP_Birdlife_Helper' ) ) {
    class WP_Birdlife_Helper {
      public function set_meta_keys( $item, $helper ): array {
        $event_id                                         = $item['id'];
        $wp_birdlife_event_last_modified                  = $item['wp_birdlife_event_last_modified'];
        $wp_birdlife_event_registration_until_date        = $item['wp_birdlife_event_registration_until_date'];
        $wp_birdlife_event_title                          = $item['wp_birdlife_event_title'];
        $wp_birdlife_event_external_link                  = $item['wp_birdlife_event_external_link'];
        $wp_birdlife_event_place                          = $item['wp_birdlife_event_place'];
        $wp_birdlife_event_phone                          = $item['wp_birdlife_event_phone'];
        $wp_birdlife_event_information_registration       = $item['wp_birdlife_event_information_registration'];
        $wp_birdlife_event_email                          = $item['wp_birdlife_event_email'];
        $wp_birdlife_event_credits                        = $item['wp_birdlife_event_credits'];
        $wp_birdlife_event_online_date                    = $item['wp_birdlife_event_online_date'];
        $wp_birdlife_event_num_min_lnu                    = $item['wp_birdlife_event_num_min_lnu'];
        $wp_birdlife_event_num_max_lnu                    = $item['wp_birdlife_event_num_max_lnu'];
        $wp_birdlife_event_course_description_short       = $item['wp_birdlife_event_course_description_short'];
        $wp_birdlife_event_cost                           = $item['wp_birdlife_event_cost'];
        $wp_birdlife_event_course_multiple_events         = $item['wp_birdlife_event_course_multiple_events'];
        $wp_birdlife_event_program                        = $item['wp_birdlife_event_program'];
        $wp_birdlife_event_time_to_tim                    = $item['wp_birdlife_event_time_to_tim'];
        $wp_birdlife_event_overnight_place                = $item['wp_birdlife_event_overnight_place'];
        $wp_birdlife_event_time_from_tim                  = $item['wp_birdlife_event_time_from_tim'];
        $wp_birdlife_event_materials                      = $item['wp_birdlife_event_materials'];
        $wp_birdlife_event_boking_template_id_lnu         = $item['wp_birdlife_event_boking_template_id_lnu'];
        $wp_birdlife_event_approved_notes                 = $item['wp_birdlife_event_approved_notes'];
        $wp_birdlife_event_approved_text                  = $item['wp_birdlife_event_approved_text'];
        $wp_birdlife_event_approved_decision_date         = $item['wp_birdlife_event_approved_decision_date'];
        $wp_birdlife_event_approved_date                  = $item['wp_birdlife_event_approved_date'];
        $wp_birdlife_event_notes                          = $item['wp_birdlife_event_notes'];
        $wp_birdlife_event_description                    = $item['wp_birdlife_event_description'];
        $wp_birdlife_event_date_to_dat                    = $item['wp_birdlife_event_date_to_dat'];
        $wp_birdlife_event_date_from_dat                  = $item['wp_birdlife_event_date_from_dat'];
        $wp_birdlife_event_registration_start_dat         = $item['wp_birdlife_event_registration_start_dat'];
        $wp_birdlife_event_date_from_dat_timestamp        = $item['wp_birdlife_event_date_from_dat_timestamp'];
        $wp_birdlife_event_leader                         = $item['wp_birdlife_event_leader'];
        $wp_birdlife_event_information                    = $item['wp_birdlife_event_information'];
        $wp_birdlife_event_dating                         = $item['wp_birdlife_event_dating'];
        $wp_birdlife_event_offer                          = $item['wp_birdlife_event_offer'];
        $wp_birdlife_event_number_participants            = $item['wp_birdlife_event_number_participants'];
        $wp_birdlife_event_id_num                         = $item['wp_birdlife_event_id_num'];
        $wp_birdlife_event_number_groups                  = $item['wp_birdlife_event_number_groups'];
        $wp_birdlife_event_region                         = $item['wp_birdlife_event_region'];
        $wp_birdlife_event_course_description             = $item['wp_birdlife_event_course_description'];
        $wp_birdlife_event_organizer                      = $item['wp_birdlife_event_organizer'];
        $wp_birdlife_event_course_costs                   = $item['wp_birdlife_event_course_costs'];
        $wp_birdlife_event_equipment                      = $item['wp_birdlife_event_equipment'];
        $wp_birdlife_event_course_additional              = $item['wp_birdlife_event_course_additional'];
        $wp_birdlife_event_neues_feld                     = $item['wp_birdlife_event_neues_feld'];
        $wp_birdlife_event_status                         = $item['wp_birdlife_event_status'];
        $wp_birdlife_event_currency_voc                   = $item['wp_birdlife_event_currency_voc'];
        $wp_birdlife_event_featured_image                 = $item['wp_birdlife_event_featured_image'];
        $wp_birdlife_event_featured_image_photocredit_txt = $item['wp_birdlife_event_featured_image_photocredit_txt'];
        $wp_birdlife_leitung_plain                        = $item['leitung'];
        $wp_birdlife_number_of_involved                   = $item['number_of_involved'];
        $wp_birdlife_number_of_notes                      = $item['number_of_notes'];
        $wp_birdlife_leitung                              = $item['leitung'] . "<br>" . $wp_birdlife_event_leader;
        $wp_birdlife_event_confirmed_tn                   = $item['wp_birdlife_event_confirmed_tn'];
        $wp_birdlife_event_reserved_tn                    = $item['wp_birdlife_event_reserved_tn'];
        $meta_inputs                                      = array( 'wp_birdlife_manage_plus_event_id' => $event_id );
        $post_title                                       = '';
        
        if ( ! empty( $wp_birdlife_number_of_involved ) ) {
          $meta_inputs['wp_birdlife_number_of_involved'] = $wp_birdlife_number_of_involved;
        }
        
        if ( ! empty( $wp_birdlife_number_of_notes ) ) {
          $meta_inputs['wp_birdlife_number_of_notes'] = $wp_birdlife_number_of_notes;
        }
        
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
          
          $meta_inputs['wp_birdlife_event_free_seats'] = $free_seats;
        }
        
        if ( ! empty( $wp_birdlife_event_reserved_tn ) ) {
          $meta_inputs['wp_birdlife_event_reserved_tn'] = $wp_birdlife_event_reserved_tn;
        }
        
        if ( ! empty( $wp_birdlife_event_confirmed_tn ) ) {
          $meta_inputs['wp_birdlife_event_confirmed_tn'] = $wp_birdlife_event_confirmed_tn;
        }
        
        if ( ! empty( $wp_birdlife_leitung ) ) {
          $meta_inputs['wp_birdlife_leitung'] = $wp_birdlife_leitung;
        }
        
        if ( ! empty( $wp_birdlife_leitung_plain ) ) {
          $meta_inputs['wp_birdlife_leitung_plain'] = $wp_birdlife_leitung_plain;
        }
        
        if ( ! empty( $wp_birdlife_event_registration_until_date ) ) {
          $meta_inputs['wp_birdlife_event_registration_until_date'] = $wp_birdlife_event_registration_until_date;
        }
        
        if ( ! empty( $wp_birdlife_event_title ) ) {
          $post_title = $wp_birdlife_event_title;
        }
        
        if ( ! empty( $wp_birdlife_event_external_link ) ) {
          $meta_inputs['wp_birdlife_event_external_link'] = $wp_birdlife_event_external_link;
        }
        
        if ( ! empty( $wp_birdlife_event_place ) ) {
          $meta_inputs['wp_birdlife_event_place'] = $wp_birdlife_event_place;
        }
        
        if ( ! empty( $wp_birdlife_event_phone ) ) {
          $meta_inputs['wp_birdlife_event_phone'] = $wp_birdlife_event_phone;
        }
        
        if ( ! empty( $wp_birdlife_event_information_registration ) ) {
          $meta_inputs['wp_birdlife_event_information_registration'] = $wp_birdlife_event_information_registration;
        }
        
        if ( ! empty( $wp_birdlife_event_email ) ) {
          $meta_inputs['wp_birdlife_event_email'] = $wp_birdlife_event_email;
        }
        
        if ( ! empty( $wp_birdlife_event_credits ) ) {
          $meta_inputs['wp_birdlife_event_credits'] = $wp_birdlife_event_credits;
        }
        
        if ( ! empty( $wp_birdlife_event_online_date ) ) {
          $meta_inputs['wp_birdlife_event_online_date'] = $wp_birdlife_event_online_date;
        }
        
        if ( ! empty( $wp_birdlife_event_num_min_lnu ) ) {
          $meta_inputs['wp_birdlife_event_num_min_lnu'] = $wp_birdlife_event_num_min_lnu;
        }
        
        if ( ! empty( $wp_birdlife_event_num_max_lnu ) ) {
          $meta_inputs['wp_birdlife_event_num_max_lnu'] = $wp_birdlife_event_num_max_lnu;
        }
        
        if ( ! empty( $wp_birdlife_event_course_description_short ) ) {
          $meta_inputs['wp_birdlife_event_course_description_short'] = $wp_birdlife_event_course_description_short;
        }
        
        if ( ! empty( $wp_birdlife_event_cost ) ) {
          $meta_inputs['wp_birdlife_event_cost'] = $wp_birdlife_event_cost;
        }
        
        if ( ! empty( $wp_birdlife_event_course_multiple_events ) ) {
          $meta_inputs['wp_birdlife_event_course_multiple_events'] = $wp_birdlife_event_course_multiple_events;
        }
        
        if ( ! empty( $wp_birdlife_event_program ) ) {
          $meta_inputs['wp_birdlife_event_program'] = $wp_birdlife_event_program;
        }
        
        if ( ! empty( $wp_birdlife_event_time_to_tim ) ) {
          $meta_inputs['wp_birdlife_event_time_to_tim'] = $wp_birdlife_event_time_to_tim;
        }
        
        if ( ! empty( $wp_birdlife_event_overnight_place ) ) {
          $meta_inputs['wp_birdlife_event_overnight_place'] = $wp_birdlife_event_overnight_place;
        }
        
        if ( ! empty( $wp_birdlife_event_time_from_tim ) ) {
          $meta_inputs['wp_birdlife_event_time_from_tim'] = $wp_birdlife_event_time_from_tim;
        }
        
        if ( ! empty( $wp_birdlife_event_materials ) ) {
          $meta_inputs['wp_birdlife_event_materials'] = $wp_birdlife_event_materials;
        }
        
        if ( ! empty( $wp_birdlife_event_boking_template_id_lnu ) ) {
          $meta_inputs['wp_birdlife_event_boking_template_id_lnu'] = $wp_birdlife_event_boking_template_id_lnu;
        }
        
        if ( ! empty( $wp_birdlife_event_approved_notes ) ) {
          $meta_inputs['wp_birdlife_event_approved_notes'] = $wp_birdlife_event_approved_notes;
        }
        
        if ( ! empty( $wp_birdlife_event_approved_text ) ) {
          $meta_inputs['wp_birdlife_event_approved_text'] = $wp_birdlife_event_approved_text;
        }
        
        if ( ! empty( $wp_birdlife_event_approved_decision_date ) ) {
          $meta_inputs['wp_birdlife_event_approved_decision_date'] = $wp_birdlife_event_approved_decision_date;
        }
        
        if ( ! empty( $wp_birdlife_event_approved_date ) ) {
          $meta_inputs['wp_birdlife_event_approved_date'] = $wp_birdlife_event_approved_date;
        }
        
        if ( ! empty( $wp_birdlife_event_notes ) ) {
          $meta_inputs['wp_birdlife_event_notes'] = $wp_birdlife_event_notes;
        }
        
        if ( ! empty( $wp_birdlife_event_description ) ) {
          $meta_inputs['wp_birdlife_event_description'] = $wp_birdlife_event_description;
        }
        
        if ( ! empty( $wp_birdlife_event_date_to_dat ) ) {
          $meta_inputs['wp_birdlife_event_date_to_dat'] = $wp_birdlife_event_date_to_dat;
        }
        
        if ( ! empty( $wp_birdlife_event_date_from_dat ) ) {
          list( $day, $day_from_date, $year_from_date, $german_month_name ) = $helper->generate_german_date( $wp_birdlife_event_date_from_dat );
          
          $formatted_date = $day . ' ' . $day_from_date . '. ' . $german_month_name . ' ' . $year_from_date;
          
          $meta_inputs['wp_birdlife_event_date_from_dat']            = '<li>' . $formatted_date . '</li>';
          $meta_inputs['wp_birdlife_event_date_from_dat_naturkurse'] = $formatted_date;
          $meta_inputs['wp_birdlife_event_date_from_dat_timestamp']  = strtotime( '+1 day', strtotime( $wp_birdlife_event_date_from_dat ) );
        }
        
        if ( ! empty( $wp_birdlife_event_registration_start_dat ) ) {
          $meta_inputs['wp_birdlife_event_registration_start_dat'] = $wp_birdlife_event_registration_start_dat;
        }
        
        if ( ! empty( $wp_birdlife_event_date_from_dat_timestamp ) ) {
          $meta_inputs['wp_birdlife_event_date_from_dat_timestamp'] = strtotime( $wp_birdlife_event_date_from_dat_timestamp );
        }
        
        if ( ! empty( $wp_birdlife_event_leader ) ) {
          $meta_inputs['wp_birdlife_event_leader'] = $wp_birdlife_event_leader;
        }
        
        if ( ! empty( $wp_birdlife_event_information ) ) {
          $meta_inputs['wp_birdlife_event_information'] = $wp_birdlife_event_information;
        }
        
        if ( ! empty( $wp_birdlife_event_dating ) ) {
          $meta_inputs['wp_birdlife_event_dating'] = $wp_birdlife_event_dating;
        }
        
        if ( ! empty( $wp_birdlife_event_offer ) ) {
          $meta_inputs['wp_birdlife_event_offer'] = $wp_birdlife_event_offer;
        }
        
        if ( ! empty( $wp_birdlife_event_number_participants ) ) {
          $meta_inputs['wp_birdlife_event_number_participants'] = $wp_birdlife_event_number_participants;
        }
        
        if ( ! empty( $wp_birdlife_event_id_num ) ) {
          $meta_inputs['wp_birdlife_event_id_num'] = $wp_birdlife_event_id_num;
        }
        
        if ( ! empty( $wp_birdlife_event_number_groups ) ) {
          $meta_inputs['wp_birdlife_event_number_groups'] = $wp_birdlife_event_number_groups;
        }
        
        if ( ! empty( $wp_birdlife_event_region ) ) {
          $meta_inputs['wp_birdlife_event_region'] = $wp_birdlife_event_region;
        }
        
        if ( ! empty( $wp_birdlife_event_course_description ) ) {
          $meta_inputs['wp_birdlife_event_course_description'] = $wp_birdlife_event_course_description;
        }
        
        if ( ! empty( $wp_birdlife_event_organizer ) ) {
          $meta_inputs['wp_birdlife_event_organizer'] = $wp_birdlife_event_organizer;
        }
        
        if ( ! empty( $wp_birdlife_event_course_costs ) ) {
          $meta_inputs['wp_birdlife_event_course_costs'] = $wp_birdlife_event_course_costs;
        }
        
        if ( ! empty( $wp_birdlife_event_equipment ) ) {
          $meta_inputs['wp_birdlife_event_equipment'] = $wp_birdlife_event_equipment;
        }
        
        if ( ! empty( $wp_birdlife_event_course_additional ) ) {
          $meta_inputs['wp_birdlife_event_course_additional'] = $wp_birdlife_event_course_additional;
        }
        
        if ( ! empty( $wp_birdlife_event_neues_feld ) ) {
          $meta_inputs['wp_birdlife_event_neues_feld'] = $wp_birdlife_event_neues_feld;
        }
        
        if ( ! empty( $wp_birdlife_event_last_modified ) ) {
          $meta_inputs['wp_birdlife_event_last_modified'] = $wp_birdlife_event_last_modified;
        }
        
        if ( ! empty( $wp_birdlife_event_status ) ) {
          $meta_inputs['wp_birdlife_event_status'] = $wp_birdlife_event_status;
        }
        
        if ( ! empty( $wp_birdlife_event_currency_voc ) ) {
          $meta_inputs['wp_birdlife_event_currency_voc'] = $wp_birdlife_event_currency_voc;
        }
        
        if ( ! empty( $wp_birdlife_event_featured_image ) ) {
          $meta_inputs['wp_birdlife_event_featured_image']        = $wp_birdlife_event_featured_image;
          $meta_inputs['wp_birdlife_event_featured_image_exists'] = 'yes';
        } else {
          $meta_inputs['wp_birdlife_event_featured_image']        = 'https://birdlife-zuerich.ch/wp-content/uploads/2022/10/default-img.png';
          $meta_inputs['wp_birdlife_event_featured_image_exists'] = 'no';
        }
        
        if ( ! empty( $wp_birdlife_event_featured_image_photocredit_txt ) ) {
          $meta_inputs['wp_birdlife_event_featured_image_photocredit_txt'] = $wp_birdlife_event_featured_image_photocredit_txt;
        }
        
        return array( $meta_inputs, $post_title );
      }
      
      public function set_metabox_values_from_api( array $data_field, array $module_item_arr ): array {
        if ( ! empty( $data_field['@attributes']['name'] ) ) {
          $data_field_name = $data_field['@attributes']['name'];
          if ( $data_field_name === 'EvtRegistrationUntilDat' ) {
            $module_item_arr['wp_birdlife_event_registration_until_date'] = $data_field['value'];
          } else if ( $data_field_name === 'EvtCourseLabelTxt' ) {
            $module_item_arr['wp_birdlife_event_title'] = $data_field['value'];
          } else if ( $data_field_name === 'EvtExternalLinkTxt' ) {
            $module_item_arr['wp_birdlife_event_external_link'] = $data_field['value'];
          } else if ( $data_field_name === 'EvtPlaceTxt' ) {
            $module_item_arr['wp_birdlife_event_place'] = $data_field['value'];
          } else if ( $data_field_name === 'EvtPhoneTxt' ) {
            $module_item_arr['wp_birdlife_event_phone'] = $data_field['value'];
          } else if ( $data_field_name === 'EvtInformationRegistrationClb' ) {
            $module_item_arr['wp_birdlife_event_information_registration'] = $data_field['value'];
          } else if ( $data_field_name === 'EvtEmailTxt' ) {
            $module_item_arr['wp_birdlife_event_email'] = $data_field['value'];
          } else if ( $data_field_name === 'EvtCreditsNum' ) {
            $module_item_arr['wp_birdlife_event_credits'] = $data_field['value'];
          } else if ( $data_field_name === 'EvtOnlineDat' ) {
            $module_item_arr['wp_birdlife_event_online_date'] = $data_field['value'];
          } else if ( $data_field_name === 'EvtNumMinLnu' ) {
            $module_item_arr['wp_birdlife_event_num_min_lnu'] = $data_field['value'];
          } else if ( $data_field_name === 'EvtNumMaxLnu' ) {
            $module_item_arr['wp_birdlife_event_num_max_lnu'] = $data_field['value'];
          } else if ( $data_field_name === 'EvtCourseDescriptionShortClb' ) {
            $module_item_arr['wp_birdlife_event_course_description_short'] = $data_field['value'];
          } else if ( $data_field_name === 'EvtCostTxt' ) {
            $module_item_arr['wp_birdlife_event_cost'] = $data_field['value'];
          } else if ( $data_field_name === 'EvtCourseMultipleEventsClb' ) {
            $module_item_arr['wp_birdlife_event_course_multiple_events'] = $data_field['value'];
          } else if ( $data_field_name === 'EvtProgramClb' ) {
            $module_item_arr['wp_birdlife_event_program'] = $data_field['value'];
          } else if ( $data_field_name === 'EvtTimeToTim' ) {
            $module_item_arr['wp_birdlife_event_time_to_tim'] = $data_field['value'];
          } else if ( $data_field_name === 'EvtOvernightPlaceClb' ) {
            $module_item_arr['wp_birdlife_event_overnight_place'] = $data_field['value'];
          } else if ( $data_field_name === 'EvtTimeFromTim' ) {
            $module_item_arr['wp_birdlife_event_time_from_tim'] = $data_field['value'];
          } else if ( $data_field_name === 'EvtMaterialsClb' ) {
            $module_item_arr['wp_birdlife_event_materials'] = $data_field['value'];
          } else if ( $data_field_name === 'EvtBokingTemplateIdLnu' ) {
            $module_item_arr['wp_birdlife_event_boking_template_id_lnu'] = $data_field['value'];
          } else if ( $data_field_name === 'EvtApprovedNotesClb' ) {
            $module_item_arr['wp_birdlife_event_approved_notes'] = $data_field['value'];
          } else if ( $data_field_name === 'EvtApprovedTxt' ) {
            $module_item_arr['wp_birdlife_event_approved_text'] = $data_field['value'];
          } else if ( $data_field_name === 'EvtApprovedDecisionDateDat' ) {
            $module_item_arr['wp_birdlife_event_approved_decision_date'] = $data_field['value'];
          } else if ( $data_field_name === 'EvtApprovedDateDat' ) {
            $module_item_arr['wp_birdlife_event_approved_date'] = $data_field['value'];
          } else if ( $data_field_name === 'EvtNotesClb' ) {
            $module_item_arr['wp_birdlife_event_notes'] = $data_field['value'];
          } else if ( $data_field_name === 'EvtDescriptionClb' ) {
            $module_item_arr['wp_birdlife_event_description'] = $data_field['value'];
          } else if ( $data_field_name === 'EvtDateToDat' ) {
            $module_item_arr['wp_birdlife_event_date_to_dat'] = $data_field['value'];
          } else if ( $data_field_name === 'EvtDateFromDat' ) {
            $module_item_arr['wp_birdlife_event_date_from_dat']            = $data_field['value'];
            $module_item_arr['wp_birdlife_event_date_from_dat_naturkurse'] = $data_field['value'];
          } else if ( $data_field_name === 'EvtRegistrationStartDat' ) {
            $module_item_arr['wp_birdlife_event_registration_start_dat'] = $data_field['value'];
          } else if ( $data_field_name === 'EvtLeaderClb' ) {
            $module_item_arr['wp_birdlife_event_leader'] = $data_field['value'];
          } else if ( $data_field_name === 'EvtInformationClb' ) {
            $module_item_arr['wp_birdlife_event_information'] = $data_field['value'];
          } else if ( $data_field_name === 'EvtDatingClb' ) {
            $module_item_arr['wp_birdlife_event_dating'] = $data_field['value'];
          } else if ( $data_field_name === 'EvtOfferClb' ) {
            $module_item_arr['wp_birdlife_event_offer'] = $data_field['value'];
          } else if ( $data_field_name === 'EvtNumberParticipantsLnu' ) {
            $module_item_arr['wp_birdlife_event_number_participants'] = $data_field['value'];
          } else if ( $data_field_name === 'EvtIDNum' ) {
            $module_item_arr['wp_birdlife_event_id_num'] = $data_field['value'];
          } else if ( $data_field_name === 'EvtNumberGroupsLnu' ) {
            $module_item_arr['wp_birdlife_event_number_groups'] = $data_field['value'];
          } else if ( $data_field_name === 'EvtRegionTxt' ) {
            $module_item_arr['wp_birdlife_event_region'] = $data_field['value'];
          } else if ( $data_field_name === 'EvtCourseDescriptionClb' ) {
            $module_item_arr['wp_birdlife_event_course_description'] = $data_field['value'];
          } else if ( $data_field_name === 'EvtOrganizerTxt' ) {
            $module_item_arr['wp_birdlife_event_organizer'] = $data_field['value'];
          } else if ( $data_field_name === 'EvtCourseCostsTxt' ) {
            $module_item_arr['wp_birdlife_event_course_costs'] = $data_field['value'];
          } else if ( $data_field_name === 'EvtEquipmentClb' ) {
            $module_item_arr['wp_birdlife_event_equipment'] = $data_field['value'];
          } else if ( $data_field_name === 'EvtCourseAdditionalClb' ) {
            $module_item_arr['wp_birdlife_event_course_additional'] = $data_field['value'];
          } else if ( $data_field_name === 'EvtNeuesFeldTxt' ) {
            $module_item_arr['wp_birdlife_event_neues_feld'] = $data_field['value'];
          }
        }
        
        return $module_item_arr;
      }
      
      public function set_project_metabox_values_from_api( array $data_field, array $module_item_arr ): array {
        if ( ! empty( $data_field['@attributes']['name'] ) ) {
          $data_field_name = $data_field['@attributes']['name'];
          if ( $data_field_name === 'ProTitleTxt' ) {
            $module_item_arr['wp_birdlife_project_pro_title_txt'] = $data_field['value'];
          } else if ( $data_field_name === 'ProDescriptionClb' ) {
            $module_item_arr['wp_birdlife_project_pro_description_clb'] = $data_field['value'];
          } else if ( $data_field_name === 'ProContactDetailTxt' ) {
            $module_item_arr['wp_birdlife_project_pro_contact_detail_txt'] = $data_field['value'];
          } else if ( $data_field_name === 'ProPlaceTxt' ) {
            $module_item_arr['wp_birdlife_project_pro_place_txt'] = $data_field['value'];
          } else if ( $data_field_name === 'ProDateFromDat' ) {
            $module_item_arr['wp_birdlife_project_pro_date_from_dat'] = $data_field['value'];
          } else if ( $data_field_name === 'ProDateToDat' ) {
            $module_item_arr['wp_birdlife_project_pro_date_to_dat'] = $data_field['value'];
          } else if ( $data_field_name === 'ProKeyWordsGrp' ) {
            $module_item_arr['wp_birdlife_project_pro_key_words_grp'] = $data_field['value'];
          }
        }
        
        return $module_item_arr;
      }
      
      public function checkBookingError(): string {
        $error = '';
        if ( ! isset( $_POST['first_name'] ) || $_POST['first_name'] === '' ) {
          $error = $error . '[Vorname ungültig]';
        } else if ( ! isset( $_POST['last_name'] ) || $_POST['last_name'] === '' ) {
          $error = $error . '[Nachname ungültig]';
        } else if ( ! isset( $_POST['street'] ) || $_POST['street'] === '' ) {
          $error = $error . '[Strasse Und Nummer ungültig]';
        } else if ( ! isset( $_POST['postal_code'] ) || $_POST['postal_code'] === '' ) {
          $error = $error . '[PLZ ungültig]';
        } else if ( ! isset( $_POST['city'] ) || $_POST['city'] === '' ) {
          $error = $error . '[Ort ungültig]';
        } else if ( ! isset( $_POST['email'] ) || $_POST['email'] === '' ) {
          $error = $error . '[E-Mail ungültig]';
        } else if ( ! isset( $_POST['phone_number'] ) || $_POST['phone_number'] === '' ) {
          $error = $error . '[Telefon ungültig]';
        } else if ( ! isset( $_POST['agb'] ) || $_POST['agb'] === '' ) {
          $error = $error . 'Sie müssen den Nutzungsbedingungen zustimmen!';
        }
        
        return $error;
      }
      
      public function get_manage_plus_api_args( string $xml ): array {
        return array(
          'headers' => array(
            'Authorization' => 'Basic ' . base64_encode( MANAGE_PLUS_USERNAME . ':' . MANAGE_PLUS_PASSWORD ),
            'Content-Type'  => 'application/xml'
          ),
          'body'    => $xml,
          'timeout' => 50
        );
      }
      
      public function generate_german_date( string $wp_birdlife_event_date_from_dat ): array {
        $day_in_week = date( 'w', strtotime( $wp_birdlife_event_date_from_dat ) );
        $day         = 'Mo';
        
        switch ( $day_in_week ) {
          case "2":
            $day = 'Di';
            break;
          case "3":
            $day = 'Mi';
            break;
          case "4":
            $day = 'Do';
            break;
          case "5":
            $day = 'Fr';
            break;
          case "6":
            $day = 'Sa';
            break;
          case "0":
            $day = 'So';
            break;
        }
        
        $month_name     = date( "F", strtotime( $wp_birdlife_event_date_from_dat ) );
        $day_from_date  = date( 'd', strtotime( $wp_birdlife_event_date_from_dat ) );
        $year_from_date = date( 'Y', strtotime( $wp_birdlife_event_date_from_dat ) );
        
        $german_month_name = '';
        
        switch ( $month_name ) {
          case "January":
            $german_month_name = 'Januar';
            break;
          case "February":
            $german_month_name = 'Februar';
            break;
          case "March":
            $german_month_name = 'März';
            break;
          case "April":
            $german_month_name = 'April';
            break;
          case "May":
            $german_month_name = 'Mai';
            break;
          case "June":
            $german_month_name = 'Juni';
            break;
          case "July":
            $german_month_name = 'Juli';
            break;
          case "August":
            $german_month_name = 'August';
            break;
          case "September":
            $german_month_name = 'September';
            break;
          case "October":
            $german_month_name = 'Oktober';
            break;
          case "November":
            $german_month_name = 'November';
            break;
          case "December":
            $german_month_name = 'Dezember';
            break;
        }
        
        return array( $day, $day_from_date, $year_from_date, $german_month_name );
      }
      
    }
  }