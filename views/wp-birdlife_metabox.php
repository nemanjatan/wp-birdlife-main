<?php
  $post_id                        = $post->ID;
  $manage_plus_event_id           = get_post_meta( $post_id, 'wp_birdlife_manage_plus_event_id', true );
  $event_registration_until_date  = get_post_meta( $post_id, 'wp_birdlife_event_registration_until_date', true );
  $event_external_link            = get_post_meta( $post_id, 'wp_birdlife_event_external_link', true );
  $event_place                    = get_post_meta( $post_id, 'wp_birdlife_event_place', true );
  $event_phone                    = get_post_meta( $post_id, 'wp_birdlife_event_phone', true );
  $event_last_modified            = get_post_meta( $post_id, 'wp_birdlife_event_last_modified', true );
  $event_information_registration = get_post_meta( $post_id, 'wp_birdlife_event_information_registration', true );
  $event_email                    = get_post_meta( $post_id, 'wp_birdlife_event_email', true );
  $event_credits                  = get_post_meta( $post_id, 'wp_birdlife_event_credits', true );
  $event_online_date              = get_post_meta( $post_id, 'wp_birdlife_event_online_date', true );
  $event_num_min_lnu              = get_post_meta( $post_id, 'wp_birdlife_event_num_min_lnu', true );
  $event_num_max_lnu              = get_post_meta( $post_id, 'wp_birdlife_event_num_max_lnu', true );
  $event_course_description_short = get_post_meta( $post_id, 'wp_birdlife_event_course_description_short', true );
  $event_cost                     = get_post_meta( $post_id, 'wp_birdlife_event_cost', true );
  $event_course_multiple_events   = get_post_meta( $post_id, 'wp_birdlife_event_course_multiple_events', true );
  $event_program                  = get_post_meta( $post_id, 'wp_birdlife_event_program', true );
  $event_time_to_tim              = get_post_meta( $post_id, 'wp_birdlife_event_time_to_tim', true );
  $event_overnight_place          = get_post_meta( $post_id, 'wp_birdlife_event_overnight_place', true );
  $event_time_from_tim            = get_post_meta( $post_id, 'wp_birdlife_event_time_from_tim', true );
  $event_materials                = get_post_meta( $post_id, 'wp_birdlife_event_materials', true );
  $event_boking_template_id_lnu   = get_post_meta( $post_id, 'wp_birdlife_event_boking_template_id_lnu', true );
  $event_approved_notes           = get_post_meta( $post_id, 'wp_birdlife_event_approved_notes', true );
  $event_approved_text            = get_post_meta( $post_id, 'wp_birdlife_event_approved_text', true );
  $event_approved_decision_date   = get_post_meta( $post_id, 'wp_birdlife_event_approved_decision_date', true );
  $event_approved_date            = get_post_meta( $post_id, 'wp_birdlife_event_approved_date', true );
  $event_notes                    = get_post_meta( $post_id, 'wp_birdlife_event_notes', true );
  $event_description              = get_post_meta( $post_id, 'wp_birdlife_event_description', true );
  $event_date_to_dat              = get_post_meta( $post_id, 'wp_birdlife_event_date_to_dat', true );
  $event_date_from_dat            = get_post_meta( $post_id, 'wp_birdlife_event_date_from_dat', true );
  $event_registration_start_dat   = get_post_meta( $post_id, 'wp_birdlife_event_registration_start_dat', true );
  $event_leader                   = get_post_meta( $post_id, 'wp_birdlife_event_leader', true );
  $event_information              = get_post_meta( $post_id, 'wp_birdlife_event_information', true );
  $event_dating                   = get_post_meta( $post_id, 'wp_birdlife_event_dating', true );
  $event_offer                    = get_post_meta( $post_id, 'wp_birdlife_event_offer', true );
  $event_number_participants      = get_post_meta( $post_id, 'wp_birdlife_event_number_participants', true );
  $event_id_num                   = get_post_meta( $post_id, 'wp_birdlife_event_id_num', true );
  $event_number_groups            = get_post_meta( $post_id, 'wp_birdlife_event_number_groups', true );
  $event_region                   = get_post_meta( $post_id, 'wp_birdlife_event_region', true );
  $event_course_description       = get_post_meta( $post_id, 'wp_birdlife_event_course_description', true );
  $event_organizer                = get_post_meta( $post_id, 'wp_birdlife_event_organizer', true );
  $event_course_costs             = get_post_meta( $post_id, 'wp_birdlife_event_course_costs', true );
  $event_equipment                = get_post_meta( $post_id, 'wp_birdlife_event_equipment', true );
  $event_course_additional        = get_post_meta( $post_id, 'wp_birdlife_event_course_additional', true );
  $event_neues_feld               = get_post_meta( $post_id, 'wp_birdlife_event_neues_feld', true );
  $event_status                   = get_post_meta( $post_id, 'wp_birdlife_event_status', true );
  $event_currency_voc             = get_post_meta( $post_id, 'wp_birdlife_event_currency_voc', true );
  $event_project_ref              = get_post_meta( $post_id, 'wp_birdlife_event_project_ref', true );
  $event_type_voc                 = get_post_meta( $post_id, 'wp_birdlife_event_type_voc', true );
  $event_category_voc             = get_post_meta( $post_id, 'wp_birdlife_event_category_voc', true );
  $event_category_voc_parent      = get_post_meta( $post_id, 'wp_birdlife_event_category_voc_parent', true );
?>

<table class="form-table wp-birdlife-metabox" style="border: 1px solid">
    <input type="hidden" name="wp_birdlife_nonce" value="<?php echo wp_create_nonce( "wp_birdlife_nonce" ); ?>">
    <tr>
        <th style="padding-left: 10px; border: 1px solid">Field</th>
        <th style="padding-left: 10px; border: 1px solid">Value</th>
    </tr>
    <tr>
        <td style="border: 1px solid">
            <p>
                <b>__id</b>
            </p>
        </td style="border: 1px solid">
        <td>
            <p
                    id="manage_plus_event_id"><?php echo ( isset ( $manage_plus_event_id ) ) ? esc_textarea( $manage_plus_event_id ) : ''; ?></p>
        </td>
    </tr>
    </hr>
    <tr>
        <td style="border: 1px solid">
            <p>
                <b>EvtRegistrationUntilDat</b>
            </p>
        </td>
        <td style="border: 1px solid">
            <p id="wp_birdlife_event_registration_until_date">
              <?php echo ( isset ( $event_registration_until_date ) ) ? esc_attr( $event_registration_until_date ) : ''; ?>
            </p>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid">
            <p>
                <b>EvtExternalLinkTxt</b>
            </p>
        </td>
        <td style="border: 1px solid">
            <p id="wp_birdlife_event_external_link">
              <?php echo ( isset ( $event_external_link ) ) ? esc_attr( $event_external_link ) : ''; ?>
            </p>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid">
            <p>
                <b>EvtPlaceTxt</b>
            </p>
        </td>
        <td style="border: 1px solid">
            <p id="wp_birdlife_event_place">
              <?php echo ( isset ( $event_place ) ) ? esc_attr( $event_place ) : ''; ?>
            </p>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid">
            <p>
                <b>EvtPhoneTxt</b>
            </p>
        </td>
        <td style="border: 1px solid">
            <p id="wp_birdlife_event_phone">
              <?php echo ( isset ( $event_phone ) ) ? esc_attr( $event_phone ) : ''; ?>
            </p>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid">
            <p>
                <b>__lastModified</b>
            </p>
        </td>
        <td style="border: 1px solid">
            <p id="manage_plus_event_last_modified">
              <?php echo ( isset ( $event_last_modified ) ) ? esc_textarea( $event_last_modified ) : ''; ?>
            </p>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid">
            <p>
                <b>EvtInformationRegistrationClb</b>
            </p>
        </td>
        <td style="border: 1px solid">
            <p id="wp_birdlife_event_information_registration">
              <?php echo ( isset ( $event_information_registration ) ) ? esc_attr( $event_information_registration ) : ''; ?>
            </p>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid">
            <p>
                <b>EvtEmailTxt</b>
            </p>
        </td>
        <td style="border: 1px solid">
            <p id="wp_birdlife_event_email">
              <?php echo ( isset ( $event_email ) ) ? esc_attr( $event_email ) : ''; ?>
            </p>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid">
            <p>
                <b>EvtCreditsNum</b>
            </p>
        </td>
        <td style="border: 1px solid">
            <p
                    id="wp_birdlife_event_credits"><?php echo ( isset ( $event_credits ) ) ? esc_attr( $event_credits ) : ''; ?></p>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid">
            <p>
                <b>EvtOnlineDat</b>
            </p>
        </td>
        <td style="border: 1px solid">
            <p
                    id="wp_birdlife_event_online_date"><?php echo ( isset ( $event_online_date ) ) ? esc_attr( $event_online_date ) : ''; ?></p>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid">
            <p>
                <b>EvtNumMinLnu</b>
            </p>
        </td>
        <td style="border: 1px solid">
            <p
                    id="wp_birdlife_event_num_min_lnu"><?php echo ( isset ( $event_num_min_lnu ) ) ? esc_attr( $event_num_min_lnu ) : ''; ?></p>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid">
            <p>
                <b>EvtNumMaxLnu</b>
            </p>
        </td>
        <td style="border: 1px solid">
            <p
                    id="wp_birdlife_event_num_max_lnu"><?php echo ( isset ( $event_num_max_lnu ) ) ? esc_attr( $event_num_max_lnu ) : ''; ?></p>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid">
            <p>
                <b>EvtCourseDescriptionShortClb</b>
            </p>
        </td>
        <td style="border: 1px solid">
            <p
                    id="wp_birdlife_event_course_description_short"><?php echo ( isset ( $event_course_description_short ) ) ? esc_attr( $event_course_description_short ) : ''; ?></p>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid">
            <p>
                <b>EvtCostTxt</b>
            </p>
        </td>
        <td style="border: 1px solid">
            <p id="wp_birdlife_event_cost"><?php echo ( isset ( $event_cost ) ) ? esc_attr( $event_cost ) : ''; ?></p>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid">
            <p>
                <b>EvtCourseMultipleEventsClb</b>
            </p>
        </td>
        <td style="border: 1px solid">
            <p
                    id="wp_birdlife_event_course_multiple_events"><?php echo ( isset ( $event_course_multiple_events ) ) ? esc_attr( $event_course_multiple_events ) : ''; ?></p>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid">
            <p>
                <b>EvtProgramClb</b>
            </p>
        </td>
        <td style="border: 1px solid">
            <p
                    id="wp_birdlife_event_program"><?php echo ( isset ( $event_program ) ) ? esc_attr( $event_program ) : ''; ?></p>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid">
            <p>
                <b>EvtTimeToTim</b>
            </p>
        </td>
        <td style="border: 1px solid">
            <p
                    id="wp_birdlife_event_time_to_tim"><?php echo ( isset ( $event_time_to_tim ) ) ? esc_attr( $event_time_to_tim ) : ''; ?></p>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid">
            <p>
                <b>EvtOvernightPlaceClb</b>
            </p>
        </td>
        <td style="border: 1px solid">
            <p
                    id="wp_birdlife_event_overnight_place"><?php echo ( isset ( $event_overnight_place ) ) ? esc_attr( $event_overnight_place ) : ''; ?></p>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid">
            <p>
                <b>EvtTimeFromTim</b>
            </p>
        </td>
        <td style="border: 1px solid">
            <p
                    id="wp_birdlife_event_time_from_tim"><?php echo ( isset ( $event_time_from_tim ) ) ? esc_attr( $event_time_from_tim ) : ''; ?></p>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid">
            <p>
                <b>EvtMaterialsClb</b>
            </p>
        </td>
        <td style="border: 1px solid">
            <p
                    id="wp_birdlife_event_materials"><?php echo ( isset ( $event_materials ) ) ? esc_attr( $event_materials ) : ''; ?></p>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid">
            <p>
                <b>EvtBokingTemplateIdLnu</b>
            </p>
        </td>
        <td style="border: 1px solid">
            <p
                    id="wp_birdlife_event_boking_template_id_lnu"><?php echo ( isset ( $event_boking_template_id_lnu ) ) ? esc_textarea( $event_boking_template_id_lnu ) : ''; ?></p>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid">
            <p>
                <b>EvtApprovedNotesClb</b>
            </p>
        </td>
        <td style="border: 1px solid">
            <p
                    id="wp_birdlife_event_approved_notes"><?php echo ( isset ( $event_approved_notes ) ) ? esc_attr( $event_approved_notes ) : ''; ?></p>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid">
            <p>
                <b>EvtApprovedTxt</b>
            </p>
        </td>
        <td style="border: 1px solid">
            <p
                    id="wp_birdlife_event_approved_text"><?php echo ( isset ( $event_approved_text ) ) ? esc_attr( $event_approved_text ) : ''; ?></p>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid">
            <p>
                <b>EvtApprovedDecisionDateDat</b>
            </p>
        </td>
        <td style="border: 1px solid">
            <p
                    id="wp_birdlife_event_approved_decision_date"><?php echo ( isset ( $event_approved_decision_date ) ) ? esc_attr( $event_approved_decision_date ) : ''; ?></p>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid">
            <p>
                <b>EvtApprovedDateDat</b>
            </p>
        </td>
        <td style="border: 1px solid">
            <p
                    id="wp_birdlife_event_approved_date"><?php echo ( isset ( $event_approved_date ) ) ? esc_attr( $event_approved_date ) : ''; ?></p>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid">
            <p>
                <b>EvtNotesClb</b>
            </p>
        </td>
        <td style="border: 1px solid">
            <p id="wp_birdlife_event_notes"><?php echo ( isset ( $event_notes ) ) ? esc_attr( $event_notes ) : ''; ?></p>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid">
            <p>
                <b>EvtDescriptionClb</b>
            </p>
        </td>
        <td style="border: 1px solid">
            <p
                    id="wp_birdlife_event_description"><?php echo ( isset ( $event_description ) ) ? esc_attr( $event_description ) : ''; ?></p>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid">
            <p>
                <b>EvtDateToDat</b>
            </p>
        </td>
        <td style="border: 1px solid">
            <p
                    id="wp_birdlife_event_date_to_dat"><?php echo ( isset ( $event_date_to_dat ) ) ? esc_attr( $event_date_to_dat ) : ''; ?></p>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid">
            <p>
                <b>EvtDateFromDat</b>
            </p>
        </td>
        <td style="border: 1px solid">
            <p
                    id="wp_birdlife_event_date_from_dat"><?php echo ( isset ( $event_date_from_dat ) ) ? esc_attr( $event_date_from_dat ) : ''; ?></p>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid">
            <p>
                <b>EvtRegistrationStartDat</b>
            </p>
        </td>
        <td style="border: 1px solid">
            <p
                    id="wp_birdlife_event_registration_start_dat"><?php echo ( isset ( $event_registration_start_dat ) ) ? esc_attr( $event_registration_start_dat ) : ''; ?></p>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid">
            <p>
                <b>EvtLeaderClb</b>
            </p>
        </td>
        <td style="border: 1px solid">
            <p id="wp_birdlife_event_leader"><?php echo ( isset ( $event_leader ) ) ? esc_attr( $event_leader ) : ''; ?></p>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid">
            <p>
                <b>EvtDatingClb</b>
            </p>
        </td>
        <td style="border: 1px solid">
            <p id="wp_birdlife_event_dating"><?php echo ( isset ( $event_dating ) ) ? esc_attr( $event_dating ) : ''; ?></p>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid">
            <p>
                <b>EvtOfferClb</b>
            </p>
        </td>
        <td style="border: 1px solid">
            <p id="wp_birdlife_event_offer"><?php echo ( isset ( $event_offer ) ) ? esc_attr( $event_offer ) : ''; ?></p>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid">
            <p>
                <b>EvtNumberParticipantsLnu</b>
            </p>
        </td>
        <td style="border: 1px solid">
            <p
                    id="wp_birdlife_event_number_participants"><?php echo ( isset ( $event_number_participants ) ) ? esc_attr( $event_number_participants ) : ''; ?></p>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid">
            <p>
                <b>EvtNumberGroupsLnu</b>
            </p>
        </td>
        <td style="border: 1px solid">
            <p
                    id="wp_birdlife_event_number_groups"><?php echo ( isset ( $event_number_groups ) ) ? esc_attr( $event_number_groups ) : ''; ?></p>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid">
            <p>
                <b>EvtRegionTxt</b>
            </p>
        </td>
        <td style="border: 1px solid">
            <p id="wp_birdlife_event_region"><?php echo ( isset ( $event_region ) ) ? esc_attr( $event_region ) : ''; ?></p>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid">
            <p>
                <b>EvtCourseDescriptionClb</b>
            </p>
        </td>
        <td style="border: 1px solid">
            <p
                    id="wp_birdlife_event_course_description"><?php echo ( isset ( $event_course_description ) ) ? esc_attr( $event_course_description ) : ''; ?></p>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid">
            <p>
                <b>EvtOrganizerTxt</b>
            </p>
        </td>
        <td style="border: 1px solid">
            <p
                    id="wp_birdlife_event_organizer"><?php echo ( isset ( $event_organizer ) ) ? esc_attr( $event_organizer ) : ''; ?></p>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid">
            <p>
                <b>EvtCourseCostsTxt</b>
            </p>
        </td>
        <td style="border: 1px solid">
            <p
                    id="wp_birdlife_event_course_costs"><?php echo ( isset ( $event_course_costs ) ) ? esc_attr( $event_course_costs ) : ''; ?></p>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid">
            <p>
                <b>EvtEquipmentClb</b>
            </p>
        </td>
        <td style="border: 1px solid">
            <p
                    id="wp_birdlife_event_equipment"><?php echo ( isset ( $event_equipment ) ) ? esc_attr( $event_equipment ) : ''; ?></p>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid">
            <p>
                <b>EvtCourseAdditionalClb</b>
            </p>
        </td>
        <td style="border: 1px solid">
            <p
                    id="wp_birdlife_event_course_additional"><?php echo ( isset ( $event_course_additional ) ) ? esc_attr( $event_course_additional ) : ''; ?></p>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid">
            <p>
                <b>EvtNeuesFeldTxt</b>
            </p>
        </td>
        <td style="border: 1px solid">
            <p
                    id="wp_birdlife_event_neues_feld"><?php echo ( isset ( $event_neues_feld ) ) ? esc_attr( $event_neues_feld ) : ''; ?></p>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid">
            <p>
                <b>EvtIDNum</b>
            </p>
        </td>
        <td style="border: 1px solid">
            <p
                    id="wp_birdlife_event_id_num"><?php echo ( isset ( $event_id_num ) ) ? esc_textarea( $event_id_num ) : ''; ?></p>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid">
            <p>
                <b>EvtCurrentStatusVrt</b>
            </p>
        </td>
        <td style="border: 1px solid">
            <p
                    id="wp_birdlife_event_status"><?php echo ( isset ( $event_status ) ) ? esc_textarea( $event_status ) : ''; ?></p>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid">
            <p>
                <b>EvtCurrencyVoc</b>
            </p>
        </td>
        <td style="border: 1px solid">
            <p
                    id="wp_birdlife_event_status"><?php echo ( isset ( $event_currency_voc ) ) ? esc_textarea( $event_currency_voc ) : ''; ?></p>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid">
            <p>
                <b>EvtProjectRef</b>
            </p>
        </td>
        <td style="border: 1px solid">
            <p
                    id="wp_birdlife_event_status"><?php echo ( isset ( $event_project_ref ) ) ? esc_textarea( $event_project_ref ) : ''; ?></p>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid">
            <p>
                <b>EvtTypeVoc</b>
            </p>
        </td>
        <td style="border: 1px solid">
            <p
                    id="wp_birdlife_event_status"><?php echo ( isset ( $event_type_voc ) ) ? esc_textarea( $event_type_voc ) : ''; ?></p>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid">
            <p>
                <b>EvtCategoryVoc</b>
            </p>
        </td>
        <td style="border: 1px solid">
            <p
                    id="wp_birdlife_event_status"><?php echo ( isset ( $event_category_voc ) ) ? esc_textarea( $event_category_voc ) : ''; ?></p>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid">
            <p>
                <b>EvtCategoryVoc (Parent)</b>
            </p>
        </td>
        <td style="border: 1px solid">
            <p
                    id="wp_birdlife_event_status"><?php echo ( isset ( $event_category_voc_parent ) ) ? esc_textarea( $event_category_voc_parent ) : ''; ?></p>
        </td>
    </tr>
</table>
