<?php

if ( ! class_exists( 'WP_Birdlife_Book_Event' ) ) {
	class WP_Birdlife_Book_Event {

		/**
		 * This method creates XML body request for booking event.
		 *
		 * @return array
		 */
		public function create_xml_body_request(): array {
			static $DEFAULT_DISCOUNT_VALUE = 'Ich will nicht Mitglied werden';

			$xml                  = file_get_contents( WP_BIRDLIFE_PATH . 'xml/booking-event/booking.xml' );
			$second_xml           = file_get_contents( WP_BIRDLIFE_PATH . 'xml/booking-event/booking.xml' );
			$second_person_exists = false;

			$event_id     = $_POST['event_id'];     // event_id
			$first_name   = $_POST['first_name'];   // BokForeNameTxt
			$last_name    = $_POST['last_name'];    // BokSurNameTxt
			$street       = $_POST['street'];       // BokStreetTxt
			$postal_code  = $_POST['postal_code'];  // BokPostcodeTxt
			$city         = $_POST['city'];         // BokCityTxt
			$email        = $_POST['email'];        // BokEmailTxt
			$phone_number = $_POST['phone_number']; // BokPhoneTxt
			$agb          = $_POST['agb'];          // can NOT be empty
			$newsletter   = false;

			if ( isset( $_POST['newsletter'] ) ) {
				$newsletter = true;
			}

			$event_course_label_txt = $_POST['wp_birdlife_event_title'];

			$xml_content = '<dataField dataType="Varchar" name="BokForeNameTxt"> <value>' . $first_name . '</value> </dataField>';
			$xml_content = $xml_content . '<dataField dataType="Varchar" name="BokSurNameTxt"> <value>' . $last_name . '</value> </dataField>';
			$xml_content = $xml_content . '<dataField dataType="Varchar" name="BokStreetTxt"> <value>' . $street . '</value> </dataField>';
			$xml_content = $xml_content . '<dataField dataType="Varchar" name="BokPostcodeTxt"> <value>' . $postal_code . '</value> </dataField>';
			$xml_content = $xml_content . '<dataField dataType="Varchar" name="BokCityTxt"> <value>' . $city . '</value> </dataField>';
			$xml_content = $xml_content . '<dataField dataType="Varchar" name="BokEmailTxt"> <value>' . $email . '</value> </dataField>';
			$xml_content = $xml_content . '<dataField dataType="Varchar" name="BokPhoneTxt"> <value>' . $phone_number . '</value> </dataField>';

			// Ermässigungen
			$discounts = $DEFAULT_DISCOUNT_VALUE;
			if ( isset( $_POST['discounts'] ) ) {
				$discounts = $_POST['discounts'];

				if ( $discounts === 'Ich bin Mitglied' ) {
					$xml_content = $xml_content . '<vocabularyReference name="BokMemberVoc" id="100045387" instanceName="BokMemberVgr"> <vocabularyReferenceItem id="100176568" name="bin_mitglied"> <formattedValue language="en">Ich bin Mitglied</formattedValue> </vocabularyReferenceItem> </vocabularyReference>';
				} else if ( $discounts === 'Ich will Mitglied werden' ) {
					$xml_content = $xml_content . '<vocabularyReference name="BokMemberVoc" id="100045387" instanceName="BokMemberVgr"> <vocabularyReferenceItem id="100176569" name="will_mitglied_werden"> <formattedValue language="en">Ich will Mitglied werden</formattedValue> </vocabularyReferenceItem> </vocabularyReference>';
				} else if ( $discounts === 'Ich will nicht Mitglied werden' ) {
					$xml_content = $xml_content . '<vocabularyReference name="BokMemberVoc" id="100045387" instanceName="BokMemberVgr"> <vocabularyReferenceItem id="100176570" name="will_nicht_mitglied_werden"> <formattedValue language="en">Ich will nicht Mitglied werden</formattedValue> </vocabularyReferenceItem> </vocabularyReference>';
				} else if ( $discounts === 'Ich weiss es nicht' ) {
					$xml_content = $xml_content . '<vocabularyReference name="BokMemberVoc" id="100045387" instanceName="BokMemberVgr"> <vocabularyReferenceItem id="100176571" name="bin_mitglied"> <formattedValue language="en">Ich weiss es nicht</formattedValue> </vocabularyReferenceItem> </vocabularyReference>';
				}
			} else {
				$xml_content = $xml_content . '<vocabularyReference name="BokMemberVoc" id="100045387" instanceName="BokMemberVgr"> <vocabularyReferenceItem id="100176570" name="will_nicht_mitglied_werden"> <formattedValue language="en">Ich will nicht Mitglied werden</formattedValue> </vocabularyReferenceItem> </vocabularyReference>';
			}

			$member_section = '';
			if ( isset( $_POST['member_section'] ) ) {
				$member_section = $_POST['member_section'];
			}

			$xml_content = $xml_content . '<dataField dataType="Varchar" name="BokMemberSectionTxt"> <value>' . $member_section . '</value> </dataField>';

			// Ich bin in Ausbildung (checkbox)
			if ( isset( $_POST['pupil'] ) ) {
				$xml_content = $xml_content . '<vocabularyReference name="BokPupilVoc" id="100045389" instanceName="BokPupilVgr"> <vocabularyReferenceItem id="100118399" name="ja"> <formattedValue language="en">Ja</formattedValue> </vocabularyReferenceItem> </vocabularyReference>';
			} else {
				$xml_content = $xml_content . '<vocabularyReference name="BokPupilVoc" id="100045389" instanceName="BokPupilVgr"> <vocabularyReferenceItem id="100118400" name="nein"> <formattedValue language="en">Nein</formattedValue> </vocabularyReferenceItem> </vocabularyReference>';
			}

			$second_person_first_name = '';
			if ( isset( $_POST['second_person_first_name'] ) ) {
				$second_person_first_name = $_POST['second_person_first_name'];
			}

			$second_person_last_name = '';
			if ( isset( $_POST['second_person_last_name'] ) ) {
				$second_person_last_name = $_POST['second_person_last_name'];
			}

			// Ja, ich möchte über BirdLife Zürich per EMail informiert bleiben
			if ( isset( $_POST['newsletter'] ) ) {
				$bok_notes = 'Newsletter ja. ';

				if ( $second_person_first_name !== '' && $second_person_last_name !== '' ) {
					$bok_notes = $bok_notes . 'Hat weitere Person angemeldet: ' . $second_person_first_name . ' ' . $second_person_last_name;
				}

				$xml_content = $xml_content . '<dataField dataType="Clob" name="BokNotesClb"> <value>' . $bok_notes . '</value> </dataField>';
			} else {
				$bok_notes = 'Newsletter nein. ';

				if ( $second_person_first_name !== '' && $second_person_last_name !== '' ) {
					$bok_notes = $bok_notes . 'Hat weitere Person angemeldet: ' . $second_person_first_name . ' ' . $second_person_last_name;
				}

				$xml_content = $xml_content . '<dataField dataType="Clob" name="BokNotesClb"> <value>' . $bok_notes . '</value> </dataField>';
			}

			// Bemerkungen (textarea)
			if ( isset( $_POST['comment'] ) ) {
				$comment     = $_POST['comment'];
				$xml_content = $xml_content . '<dataField dataType="Clob" name="BokCommentClb"> <value>' . $comment . '</value> </dataField>';
			}

			// Status for booking (it's not teststatus anymore)
			$xml_content = $xml_content . '<vocabularyReference name="BokStatusVoc" id="100038387" instanceName="BokStatusVgr"> <vocabularyReferenceItem id="100118404" name="booked"> <formattedValue language="en">Eingang</formattedValue> </vocabularyReferenceItem> </vocabularyReference>';

			// BokStatusGrp.StatusVoc
			$xml_content = $xml_content . '<repeatableGroup name="BokStatusGrp"> <repeatableGroupItem> <dataField dataType="Date" name="DateDat"> <value>' . date( 'Y-m-d' ) . '</value> </dataField> <vocabularyReference name="StatusVoc" id="100038387" instanceName="BokStatusVgr"> <vocabularyReferenceItem id="100118404" name="booked"> <formattedValue language="en">Eingang</formattedValue> </vocabularyReferenceItem> </vocabularyReference> </repeatableGroupItem> </repeatableGroup>';

			// Online via Wordpress
			$xml_content = $xml_content . '<vocabularyReference name="BokTypeVoc" id="100038388" instanceName="BokTypeVgr"> <vocabularyReferenceItem id="100176567" name="online_wp"> <formattedValue language="en">Online via Wordpress</formattedValue> </vocabularyReferenceItem> </vocabularyReference>';

			// Eingangsdatum is written: Field Called „BokReceiptDat“
			$xml_content = $xml_content . '<dataField dataType="Date" name="BokReceiptDat"> <value>' . date( 'Y-m-d' ) . '</value> </dataField>';

			$xml_content = $xml_content . '<dataField dataType="Date" name="BokBookingTitleTxt"> <value>Anmeldung ' . htmlspecialchars( $event_course_label_txt ) . '</value> </dataField>';

			$second_person_email = '';

			if ( $second_person_first_name !== '' && $second_person_last_name !== '' ) {
				if ( isset( $_POST['email-optional'] ) ) {
					$second_person_email = $_POST['email-optional'];
				}

				$second_xml_content = '<dataField dataType="Varchar" name="BokForeNameTxt"> <value>' . $second_person_first_name . '</value> </dataField>';
				$second_xml_content = $second_xml_content . '<dataField dataType="Varchar" name="BokSurNameTxt"> <value>' . $second_person_last_name . '</value> </dataField>';
				$second_xml_content = $second_xml_content . '<dataField dataType="Varchar" name="BokEmailTxt"> <value>' . $second_person_email . '</value> </dataField>';

				$gleiche_adresse_active = false;
				if ( isset( $_POST['gleiche-adresse'] ) && $_POST['gleiche-adresse'] !== '' ) {
					$second_xml_content = $second_xml_content . '<dataField dataType="Varchar" name="BokStreetTxt"> <value>' . $street . '</value> </dataField>';
					$second_xml_content = $second_xml_content . '<dataField dataType="Varchar" name="BokPostcodeTxt"> <value>' . $postal_code . '</value> </dataField>';
					$second_xml_content = $second_xml_content . '<dataField dataType="Varchar" name="BokCityTxt"> <value>' . $city . '</value> </dataField>';

					$gleiche_adresse_active = true;
				}

				$second_xml_content = $second_xml_content . '<dataField dataType="Clob" name="BokNotesClb"> <value>Zweite Anmeldung von ' . $first_name . ' ' . $last_name . '</value> </dataField>';
				$second_xml_content = $second_xml_content . '<moduleReference name="BokEventRef" targetModule="Event" multiplicity="N:1"> <moduleReferenceItem moduleItemId="' . $event_id . '"/> </moduleReference>';
				$second_xml_content = $second_xml_content . '<vocabularyReference name="BokStatusVoc" id="100038387" instanceName="BokStatusVgr"> <vocabularyReferenceItem id="100118404" name="booked"> <formattedValue language="en">Eingang</formattedValue> </vocabularyReferenceItem> </vocabularyReference>';
				$second_xml_content = $second_xml_content . '<repeatableGroup name="BokStatusGrp"> <repeatableGroupItem> <dataField dataType="Date" name="DateDat"> <value>' . date( 'Y-m-d' ) . '</value> </dataField> <vocabularyReference name="StatusVoc" id="100038387" instanceName="BokStatusVgr"> <vocabularyReferenceItem id="100118404" name="booked"> <formattedValue language="en">Eingang</formattedValue> </vocabularyReferenceItem> </vocabularyReference> </repeatableGroupItem> </repeatableGroup>';

				$second_xml_content = $second_xml_content . '<vocabularyReference name="BokTypeVoc" id="100038388" instanceName="BokTypeVgr"> <vocabularyReferenceItem id="100176567" name="online_wp"> <formattedValue language="en">Online via Wordpress</formattedValue> </vocabularyReferenceItem> </vocabularyReference>';
				$second_xml_content = $second_xml_content . '<dataField dataType="Date" name="BokBookingTitleTxt"> <value>Anmeldung ' . htmlspecialchars( $event_course_label_txt ) . '</value> </dataField>';

				// Eingangsdatum is written: Field Called „BokReceiptDat“
				$second_xml_content = $second_xml_content . '<dataField dataType="Date" name="BokReceiptDat"> <value>' . date( 'Y-m-d' ) . '</value> </dataField>';

				$second_xml = str_replace( "{{content}}", $second_xml_content, $second_xml );

				$second_person_exists = true;
			}

			$xml_content = $xml_content . '<moduleReference name="BokEventRef" targetModule="Event" multiplicity="N:1"> <moduleReferenceItem moduleItemId="' . $event_id . '"/> </moduleReference>';
			$xml         = str_replace( "{{content}}", $xml_content, $xml );

			return array(
				$second_person_exists,
				$xml,
				$second_xml,
				$email,
				$second_person_email,
				$event_id,
				$first_name,
				$last_name,
				$street,
				$postal_code,
				$city,
				$second_person_first_name,
				$second_person_last_name,
				$gleiche_adresse_active,
				$newsletter
			);
		}

	}
}