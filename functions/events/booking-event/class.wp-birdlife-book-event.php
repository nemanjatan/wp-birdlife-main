<?php
  
  if ( ! class_exists( 'WP_Birdlife_Book_Event' ) ) {
    class WP_Birdlife_Book_Event {
      
      private const DEFAULT_DISCOUNT_VALUE = 'I do not want to become a member';
      
      /**
       * This method creates XML body request for booking event.
       *
       * @return array
       */
      public function create_xml_body_request(): array {
        $this->log_message( "Creating XML body request for booking event." );
        
        $xml_template         = file_get_contents( WP_BIRDLIFE_PATH . 'xml/booking-event/booking.xml' );
        $second_xml_template  = file_get_contents( WP_BIRDLIFE_PATH . 'xml/booking-event/booking.xml' );
        $second_person_exists = false;
        
        $event_id     = $_POST['event_id'] ?? '';
        $first_name   = $_POST['first_name'] ?? '';
        $last_name    = $_POST['last_name'] ?? '';
        $street       = $_POST['street'] ?? '';
        $postal_code  = $_POST['postal_code'] ?? '';
        $city         = $_POST['city'] ?? '';
        $email        = $_POST['email'] ?? '';
        $phone_number = $_POST['phone_number'] ?? '';
        $agb          = $_POST['agb'] ?? '';
        $newsletter   = isset( $_POST['newsletter'] );
        
        $event_course_label_txt = $_POST['wp_birdlife_event_title'] ?? '';
        
        $xml_content = $this->create_data_field_xml( 'BokForeNameTxt', $first_name );
        $xml_content .= $this->create_data_field_xml( 'BokSurNameTxt', $last_name );
        $xml_content .= $this->create_data_field_xml( 'BokStreetTxt', $street );
        $xml_content .= $this->create_data_field_xml( 'BokPostcodeTxt', $postal_code );
        $xml_content .= $this->create_data_field_xml( 'BokCityTxt', $city );
        $xml_content .= $this->create_data_field_xml( 'BokEmailTxt', $email );
        $xml_content .= $this->create_data_field_xml( 'BokPhoneTxt', $phone_number );
        
        $discounts   = $_POST['discounts'] ?? self::DEFAULT_DISCOUNT_VALUE;
        $xml_content .= $this->create_discount_xml( $discounts );
        
        $member_section = $_POST['member_section'] ?? '';
        $xml_content    .= $this->create_data_field_xml( 'BokMemberSectionTxt', $member_section );
        
        $pupil       = isset( $_POST['pupil'] ) ? 'ja' : 'nein';
        $xml_content .= $this->create_vocabulary_reference_xml( 'BokPupilVoc', '100045389', 'BokPupilVgr', $pupil, '100118399', '100118400' );
        
        $second_person_first_name = $_POST['second_person_first_name'] ?? '';
        $second_person_last_name  = $_POST['second_person_last_name'] ?? '';
        
        $newsletter_text = $newsletter ? 'Newsletter yes.' : 'Newsletter no.';
        if ( $second_person_first_name && $second_person_last_name ) {
          $newsletter_text .= ' Registered another person: ' . $second_person_first_name . ' ' . $second_person_last_name;
        }
        $xml_content .= $this->create_data_field_xml( 'BokNotesClb', $newsletter_text, 'Clob' );
        
        $comment     = $_POST['comment'] ?? '';
        $xml_content .= $this->create_data_field_xml( 'BokCommentClb', $comment, 'Clob' );
        
        $xml_content .= $this->create_booking_status_xml();
        
        $xml_content .= $this->create_data_field_xml( 'BokReceiptDat', date( 'Y-m-d' ), 'Date' );
        $xml_content .= $this->create_data_field_xml( 'BokBookingTitleTxt', 'Registration ' . htmlspecialchars( $event_course_label_txt ), 'Varchar' );
        
        $second_person_email = $_POST['email-optional'] ?? '';
        if ( $second_person_first_name && $second_person_last_name ) {
          $second_xml_content = $this->create_data_field_xml( 'BokForeNameTxt', $second_person_first_name );
          $second_xml_content .= $this->create_data_field_xml( 'BokSurNameTxt', $second_person_last_name );
          $second_xml_content .= $this->create_data_field_xml( 'BokEmailTxt', $second_person_email );
          
          $same_address_active = isset( $_POST['same_address'] ) ? true : false;
          if ( $same_address_active ) {
            $second_xml_content .= $this->create_data_field_xml( 'BokStreetTxt', $street );
            $second_xml_content .= $this->create_data_field_xml( 'BokPostcodeTxt', $postal_code );
            $second_xml_content .= $this->create_data_field_xml( 'BokCityTxt', $city );
          }
          
          $second_xml_content .= $this->create_data_field_xml( 'BokNotesClb', 'Second registration from ' . $first_name . ' ' . $last_name, 'Clob' );
          $second_xml_content .= $this->create_event_reference_xml( $event_id );
          $second_xml_content .= $this->create_booking_status_xml();
          $second_xml_content .= $this->create_data_field_xml( 'BokReceiptDat', date( 'Y-m-d' ), 'Date' );
          $second_xml_content .= $this->create_data_field_xml( 'BokBookingTitleTxt', 'Registration ' . htmlspecialchars( $event_course_label_txt ), 'Varchar' );
          
          $second_xml_template  = str_replace( "{{content}}", $second_xml_content, $second_xml_template );
          $second_person_exists = true;
        }
        
        $xml_content  .= $this->create_event_reference_xml( $event_id );
        $xml_template = str_replace( "{{content}}", $xml_content, $xml_template );
        
        $this->log_message( "XML body request created successfully." );
        
        return array(
          $second_person_exists,
          $xml_template,
          $second_xml_template,
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
          $same_address_active,
          $newsletter
        );
      }
      
      private function create_data_field_xml( $name, $value, $data_type = 'Varchar' ): string {
        return '<dataField dataType="' . $data_type . '" name="' . $name . '"> <value>' . $value . '</value> </dataField>';
      }
      
      private function create_vocabulary_reference_xml( $name, $id, $instance_name, $value, $yes_id, $no_id ): string {
        $item_id = ( $value === 'ja' ) ? $yes_id : $no_id;
        
        return '<vocabularyReference name="' . $name . '" id="' . $id . '" instanceName="' . $instance_name . '"> <vocabularyReferenceItem id="' . $item_id . '" name="' . $value . '"> <formattedValue language="en">' . ucfirst( $value ) . '</formattedValue> </vocabularyReferenceItem> </vocabularyReference>';
      }
      
      private function create_discount_xml( $discounts ): string {
        $discount_values = [
          'Ich bin Mitglied'               => [ 'id' => '100176568', 'name' => 'bin_mitglied' ],
          'Ich will Mitglied werden'       => [ 'id' => '100176569', 'name' => 'will_mitglied_werden' ],
          'Ich will nicht Mitglied werden' => [ 'id' => '100176570', 'name' => 'will_nicht_mitglied_werden' ],
          'Ich weiss es nicht'             => [ 'id' => '100176571', 'name' => 'bin_mitglied' ]
        ];
        
        if ( ! isset( $discount_values[ $discounts ] ) ) {
          $discounts = self::DEFAULT_DISCOUNT_VALUE;
        }
        
        $discount = $discount_values[ $discounts ];
        
        return '<vocabularyReference name="BokMemberVoc" id="100045387" instanceName="BokMemberVgr"> <vocabularyReferenceItem id="' . $discount['id'] . '" name="' . $discount['name'] . '"> <formattedValue language="en">' . $discounts . '</formattedValue> </vocabularyReferenceItem> </vocabularyReference>';
      }
      
      private function create_event_reference_xml( $event_id ): string {
        return '<moduleReference name="BokEventRef" targetModule="Event" multiplicity="N:1"> <moduleReferenceItem moduleItemId="' . $event_id . '"/> </moduleReference>';
      }
      
      private function create_booking_status_xml(): string {
        $date = date( 'Y-m-d' );
        
        return '
				<vocabularyReference name="BokStatusVoc" id="100038387" instanceName="BokStatusVgr">
					<vocabularyReferenceItem id="100118404" name="booked">
						<formattedValue language="en">Eingang</formattedValue>
					</vocabularyReferenceItem>
				</vocabularyReference>
				<repeatableGroup name="BokStatusGrp">
					<repeatableGroupItem>
						<dataField dataType="Date" name="DateDat">
							<value>' . $date . '</value>
						</dataField>
						<vocabularyReference name="StatusVoc" id="100038387" instanceName="BokStatusVgr">
							<vocabularyReferenceItem id="100118404" name="booked">
								<formattedValue language="en">Eingang</formattedValue>
							</vocabularyReferenceItem>
						</vocabularyReference>
					</repeatableGroupItem>
				</repeatableGroup>
				<vocabularyReference name="BokTypeVoc" id="100038388" instanceName="BokTypeVgr">
					<vocabularyReferenceItem id="100176567" name="online_wp">
						<formattedValue language="en">Online via Wordpress</formattedValue>
					</vocabularyReferenceItem>
				</vocabularyReference>';
      }
      
      private function log_message( $message ) {
        $logDir = __DIR__ . '/logs';
        if ( ! is_dir( $logDir ) ) {
          mkdir( $logDir, 0777, true );
        }
        $logFile          = $logDir . "/birdlife_booking_event_log.txt";
        $currentDateTime  = date( 'Y-m-d H:i:s' );
        $formattedMessage = $currentDateTime . " - " . $message . "\n";
        file_put_contents( $logFile, $formattedMessage, FILE_APPEND );
      }
    }
  }
