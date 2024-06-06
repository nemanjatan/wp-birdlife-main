<?php
$post_id = $post->ID;

$fields = [
	'wp_birdlife_manage_plus_event_id'           => '__id',
	'wp_birdlife_event_registration_until_date'  => 'EvtRegistrationUntilDat',
	'wp_birdlife_event_external_link'            => 'EvtExternalLinkTxt',
	'wp_birdlife_event_place'                    => 'EvtPlaceTxt',
	'wp_birdlife_event_phone'                    => 'EvtPhoneTxt',
	'wp_birdlife_event_last_modified'            => '__lastModified',
	'wp_birdlife_event_information_registration' => 'EvtInformationRegistrationClb',
	'wp_birdlife_event_email'                    => 'EvtEmailTxt',
	'wp_birdlife_event_credits'                  => 'EvtCreditsNum',
	'wp_birdlife_event_online_date'              => 'EvtOnlineDat',
	'wp_birdlife_event_num_min_lnu'              => 'EvtNumMinLnu',
	'wp_birdlife_event_num_max_lnu'              => 'EvtNumMaxLnu',
	'wp_birdlife_event_course_description_short' => 'EvtCourseDescriptionShortClb',
	'wp_birdlife_event_cost'                     => 'EvtCostTxt',
	'wp_birdlife_event_course_multiple_events'   => 'EvtCourseMultipleEventsClb',
	'wp_birdlife_event_program'                  => 'EvtProgramClb',
	'wp_birdlife_event_time_to_tim'              => 'EvtTimeToTim',
	'wp_birdlife_event_overnight_place'          => 'EvtOvernightPlaceClb',
	'wp_birdlife_event_time_from_tim'            => 'EvtTimeFromTim',
	'wp_birdlife_event_materials'                => 'EvtMaterialsClb',
	'wp_birdlife_event_boking_template_id_lnu'   => 'EvtBokingTemplateIdLnu',
	'wp_birdlife_event_approved_notes'           => 'EvtApprovedNotesClb',
	'wp_birdlife_event_approved_text'            => 'EvtApprovedTxt',
	'wp_birdlife_event_approved_decision_date'   => 'EvtApprovedDecisionDateDat',
	'wp_birdlife_event_approved_date'            => 'EvtApprovedDateDat',
	'wp_birdlife_event_notes'                    => 'EvtNotesClb',
	'wp_birdlife_event_description'              => 'EvtDescriptionClb',
	'wp_birdlife_event_date_to_dat'              => 'EvtDateToDat',
	'wp_birdlife_event_date_from_dat'            => 'EvtDateFromDat',
	'wp_birdlife_event_registration_start_dat'   => 'EvtRegistrationStartDat',
	'wp_birdlife_event_leader'                   => 'EvtLeaderClb',
	'wp_birdlife_event_information'              => 'EvtInformation',
	'wp_birdlife_event_dating'                   => 'EvtDatingClb',
	'wp_birdlife_event_offer'                    => 'EvtOfferClb',
	'wp_birdlife_event_number_participants'      => 'EvtNumberParticipantsLnu',
	'wp_birdlife_event_id_num'                   => 'EvtIDNum',
	'wp_birdlife_event_number_groups'            => 'EvtNumberGroupsLnu',
	'wp_birdlife_event_region'                   => 'EvtRegionTxt',
	'wp_birdlife_event_course_description'       => 'EvtCourseDescriptionClb',
	'wp_birdlife_event_organizer'                => 'EvtOrganizerTxt',
	'wp_birdlife_event_course_costs'             => 'EvtCourseCostsTxt',
	'wp_birdlife_event_equipment'                => 'EvtEquipmentClb',
	'wp_birdlife_event_course_additional'        => 'EvtCourseAdditionalClb',
	'wp_birdlife_event_neues_feld'               => 'EvtNeuesFeldTxt',
	'wp_birdlife_event_status'                   => 'EvtCurrentStatusVrt',
	'wp_birdlife_event_currency_voc'             => 'EvtCurrencyVoc',
	'wp_birdlife_event_project_ref'              => 'EvtProjectRef',
	'wp_birdlife_event_type_voc'                 => 'EvtTypeVoc',
	'wp_birdlife_event_category_voc'             => 'EvtCategoryVoc',
	'wp_birdlife_event_category_voc_parent'      => 'EvtCategoryVoc (Parent)',
];

function display_value( $value ) {
	if ( is_array( $value ) ) {
		return implode( ', ', array_map( 'esc_attr', $value ) );
	}

	return esc_attr( $value );
}

?>

<table class="form-table wp-birdlife-metabox" style="border: 1px solid">
    <input type="hidden" name="wp_birdlife_nonce" value="<?php echo wp_create_nonce( 'wp_birdlife_nonce' ); ?>">
    <tr>
        <th style="padding-left: 10px; border: 1px solid">Field</th>
        <th style="padding-left: 10px; border: 1px solid">Value</th>
    </tr>
	<?php foreach ( $fields as $meta_key => $label ) :
		$value = get_post_meta( $post_id, $meta_key, true );
		?>
      <tr>
          <td style="border: 1px solid">
              <p>
                  <b><?php echo esc_html( $label ); ?></b>
              </p>
          </td>
          <td style="border: 1px solid">
              <p id="<?php echo esc_attr( $meta_key ); ?>">
								<?php echo isset( $value ) ? display_value( $value ) : ''; ?>
              </p>
          </td>
      </tr>
	<?php endforeach; ?>
</table>
