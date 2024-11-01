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
				$post_metas,
				$helper
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
					} else if ( $reference_name === 'EvtProjectRef' ) {
						$wp_birdlife_event_project_ref = $module_item['moduleReference']['moduleReferenceItem']['formattedValue'];

						$module_item_id = $module_item['moduleReference']['moduleReferenceItem']['@attributes']['moduleItemId'];
						$xml            = file_get_contents( WP_BIRDLIFE_PATH . 'xml/project-search/project-search-all-fields-by-id.xml' );
						$xml            = str_replace( "{{project_id}}", $module_item_id, $xml );
						$args           = $helper->get_manage_plus_api_args( $xml );

						$resp      = wp_remote_post( 'https://maBirdlife.zetcom.app/ria-ws/application/module/Project/search/', $args );
						$resp_body = $resp['body'];

						$parsed_xml  = simplexml_load_string( $resp_body );
						$json        = json_encode( $parsed_xml );
						$parsed_json = json_decode( $json, true );

						$module_items         = $parsed_json['modules']['module']['moduleItem'];
						$vocabulary_reference = $module_items['vocabularyReference'];

						// todo WORKING
						if ( is_array( $module_items['repeatableGroup'] ) ) {
							$repeatableGroup = $module_items['repeatableGroup'];
							foreach ( $repeatableGroup as $repeatableItem ) {
								if ( $repeatableItem['@attributes']['name'] === 'ProSpeciesGrp' ) {
									$existing_wp_birdlife_event_pro_species_grp_id = get_post_meta( $existing_post->ID, 'wp_birdlife_event_pro_species_grp_id', true );
									if ( ! is_array( $existing_wp_birdlife_event_pro_species_grp_id ) ) {
										$existing_wp_birdlife_event_pro_species_grp_id = array();
									}

									$existing_wp_birdlife_event_pro_species_grp_name = get_post_meta( $existing_post->ID, 'wp_birdlife_event_pro_species_grp_name', true );
									if ( ! is_array( $existing_wp_birdlife_event_pro_species_grp_name ) ) {
										$existing_wp_birdlife_event_pro_species_grp_name = array();
									}

									$ProSpeciesGrpId = $repeatableItem['repeatableGroupItem']['vocabularyReference']['vocabularyReferenceItem']['@attributes']['id'];

									$new_value                                       = $ProSpeciesGrpId;
									$existing_wp_birdlife_event_pro_species_grp_id[] = $new_value;

									$existing_wp_birdlife_event_pro_species_grp_id = array_values( array_unique( $existing_wp_birdlife_event_pro_species_grp_id ) );

									$meta_exists = metadata_exists( 'post', $existing_post->ID, 'wp_birdlife_event_pro_species_grp_id' );
									if ( ! $meta_exists ) {
										add_post_meta( $existing_post->ID, 'wp_birdlife_event_pro_species_grp_id', $existing_wp_birdlife_event_pro_species_grp_id );
									} else {
										update_post_meta( $existing_post->ID, 'wp_birdlife_event_pro_species_grp_id', $existing_wp_birdlife_event_pro_species_grp_id );
									}

									if ( $repeatableItem['repeatableGroupItem']['vocabularyReference']['vocabularyReferenceItem']['@attributes']['name'] !== null ) {
										$ProSpeciesGrpName = $repeatableItem['repeatableGroupItem']['vocabularyReference']['vocabularyReferenceItem']['@attributes']['name'];
									} else {
										$ProSpeciesGrpName = $repeatableItem['repeatableGroupItem']['vocabularyReference']['vocabularyReferenceItem']['formattedValue'];
									}

									$new_value                                         = $ProSpeciesGrpName;
									$existing_wp_birdlife_event_pro_species_grp_name[] = $new_value;

									$existing_wp_birdlife_event_pro_species_grp_name = array_values( array_unique( $existing_wp_birdlife_event_pro_species_grp_name ) );

									$meta_exists = metadata_exists( 'post', $existing_post->ID, 'wp_birdlife_event_pro_species_grp_name' );
									if ( ! $meta_exists ) {
										add_post_meta( $existing_post->ID, 'wp_birdlife_event_pro_species_grp_name', $existing_wp_birdlife_event_pro_species_grp_name );
									} else {
										update_post_meta( $existing_post->ID, 'wp_birdlife_event_pro_species_grp_name', $existing_wp_birdlife_event_pro_species_grp_name );
									}
								}
							}
						}
						// end of WORKING

						if ( is_array( $vocabulary_reference ) ) {
							foreach ( $vocabulary_reference as $v_r ) {
								if ( $v_r['@attributes']['name'] === 'ProRecordTypeVoc' ) {
									$wp_birdlife_event_pro_record_type_voc      = $v_r['vocabularyReferenceItem']['@attributes']['id'];
									$wp_birdlife_event_pro_record_type_voc_name = $v_r['vocabularyReferenceItem']['@attributes']['name'];
								}
							}
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
							} else if ( $reference_name === 'EvtProjectRef' ) {
								$wp_birdlife_event_project_ref = $module_reference['moduleReferenceItem']['formattedValue'];
								$module_item_id                = $module_reference['moduleReferenceItem']['@attributes']['moduleItemId'];
								// todo get project and then get ProRecordTypeVoc

								$xml  = file_get_contents( WP_BIRDLIFE_PATH . 'xml/project-search/project-search-all-fields-by-id.xml' );
								$xml  = str_replace( "{{project_id}}", $module_item_id, $xml );
								$args = $helper->get_manage_plus_api_args( $xml );

								$resp      = wp_remote_post( 'https://maBirdlife.zetcom.app/ria-ws/application/module/Project/search/', $args );
								$resp_body = $resp['body'];

								$parsed_xml  = simplexml_load_string( $resp_body );
								$json        = json_encode( $parsed_xml );
								$parsed_json = json_decode( $json, true );

								$module_items         = $parsed_json['modules']['module']['moduleItem'];
								$vocabulary_reference = $module_items['vocabularyReference'];

								// todo WORKING
								if ( is_array( $module_items['repeatableGroup'] ) ) {
									$repeatableGroup = $module_items['repeatableGroup'];
									foreach ( $repeatableGroup as $repeatableItem ) {
										if ( $repeatableItem['@attributes']['name'] === 'ProSpeciesGrp' ) {
											if ( is_array( $repeatableItem['repeatableGroupItem'] ) ) {
												$existing_wp_birdlife_event_pro_species_grp_id = get_post_meta( $existing_post->ID, 'wp_birdlife_event_pro_species_grp_id', true );
												if ( ! is_array( $existing_wp_birdlife_event_pro_species_grp_id ) ) {
													$existing_wp_birdlife_event_pro_species_grp_id = array();
												}

												$existing_wp_birdlife_event_pro_species_grp_name = get_post_meta( $existing_post->ID, 'wp_birdlife_event_pro_species_grp_name', true );
												if ( ! is_array( $existing_wp_birdlife_event_pro_species_grp_name ) ) {
													$existing_wp_birdlife_event_pro_species_grp_name = array();
												}

												$repeatableGroupItems = $repeatableItem['repeatableGroupItem'];

												foreach ( $repeatableGroupItems as $repeatableGroupItem ) {
													if ( $repeatableGroupItem['vocabularyReference'] === null ) {
														$ProSpeciesGrpName = $repeatableGroupItems['vocabularyReference']['vocabularyReferenceItem']['formattedValue'];
														$ProSpeciesGrpId   = $repeatableGroupItems['vocabularyReference']['vocabularyReferenceItem']['@attributes']['id'];
													} else {
														$ProSpeciesGrpId = $repeatableGroupItem['vocabularyReference']['vocabularyReferenceItem']['@attributes']['id'];

														if ( $repeatableGroupItem['vocabularyReference']['vocabularyReferenceItem']['@attributes']['name'] !== null ) {
															$ProSpeciesGrpName = $repeatableGroupItem['vocabularyReference']['vocabularyReferenceItem']['@attributes']['name'];
														} else {
															$ProSpeciesGrpName = $repeatableGroupItem['vocabularyReference']['vocabularyReferenceItem']['formattedValue'];
														}
													}

													$new_value                                       = $ProSpeciesGrpId;
													$existing_wp_birdlife_event_pro_species_grp_id[] = $new_value;

													$existing_wp_birdlife_event_pro_species_grp_id = array_values( array_unique( $existing_wp_birdlife_event_pro_species_grp_id ) );

													$existing_wp_birdlife_event_pro_species_grp_id = array_filter( $existing_wp_birdlife_event_pro_species_grp_id, function ( $value ) {
														return $value !== null;
													} );

													$existing_wp_birdlife_event_pro_species_grp_name = array_filter( $existing_wp_birdlife_event_pro_species_grp_name, function ( $value ) {
														return $value !== null;
													} );

													$existing_wp_birdlife_event_pro_species_grp_name = array_map( function ( $item ) {
														if ( $item === 'voegel' ) {
															return 'Vögel';
														}

														return ucfirst( $item );
													}, $existing_wp_birdlife_event_pro_species_grp_name );

													$existing_wp_birdlife_event_pro_species_grp_id = array_map( function ( $item ) {
														return ucfirst( $item );
													}, $existing_wp_birdlife_event_pro_species_grp_id );

													$meta_exists = metadata_exists( 'post', $existing_post->ID, 'wp_birdlife_event_pro_species_grp_id' );
													if ( ! $meta_exists ) {
														add_post_meta( $existing_post->ID, 'wp_birdlife_event_pro_species_grp_id', $existing_wp_birdlife_event_pro_species_grp_id );
													} else {
														update_post_meta( $existing_post->ID, 'wp_birdlife_event_pro_species_grp_id', $existing_wp_birdlife_event_pro_species_grp_id );
													}

													$new_value                                         = $ProSpeciesGrpName;
													$existing_wp_birdlife_event_pro_species_grp_name[] = $new_value;

													$existing_wp_birdlife_event_pro_species_grp_name = array_values( array_unique( $existing_wp_birdlife_event_pro_species_grp_name ) );

													$meta_exists = metadata_exists( 'post', $existing_post->ID, 'wp_birdlife_event_pro_species_grp_name' );
													if ( ! $meta_exists ) {
														add_post_meta( $existing_post->ID, 'wp_birdlife_event_pro_species_grp_name', $existing_wp_birdlife_event_pro_species_grp_name );
													} else {
														update_post_meta( $existing_post->ID, 'wp_birdlife_event_pro_species_grp_name', $existing_wp_birdlife_event_pro_species_grp_name );
													}
												}
											} else {
												$ProSpeciesGrpId = $repeatableItem['repeatableGroupItem']['vocabularyReference']['vocabularyReferenceItem']['@attributes']['id'];

												update_post_meta( $existing_post->ID, 'wp_birdlife_event_pro_species_grp_id', $ProSpeciesGrpId );

												if ( $repeatableItem['repeatableGroupItem']['vocabularyReference']['vocabularyReferenceItem']['@attributes']['name'] !== null ) {
													$ProSpeciesGrpName = $repeatableItem['repeatableGroupItem']['vocabularyReference']['vocabularyReferenceItem']['@attributes']['name'];
												} else {
													$ProSpeciesGrpName = $repeatableItem['repeatableGroupItem']['vocabularyReference']['vocabularyReferenceItem']['formattedValue'];
												}

												update_post_meta( $existing_post->ID, 'wp_birdlife_event_pro_species_grp_name', $ProSpeciesGrpName );
											}
										}
									}
								}
								// end of WORKING

								if ( is_array( $vocabulary_reference ) ) {
									foreach ( $vocabulary_reference as $v_r ) {
										if ( $v_r['@attributes']['name'] === 'ProRecordTypeVoc' ) {
											$wp_birdlife_event_pro_record_type_voc      = $v_r['vocabularyReferenceItem']['@attributes']['id'];
											$wp_birdlife_event_pro_record_type_voc_name = $v_r['vocabularyReferenceItem']['@attributes']['name'];
										}
									}
								}
							}
						}
					}
				}
			}

			if ( $wp_birdlife_event_project_ref !== null ) {
				update_post_meta(
					$existing_post->ID,
					'wp_birdlife_event_project_ref',
					$wp_birdlife_event_project_ref
				);
			}

//			if ( $ProSpeciesGrpId !== null ) {
//				update_post_meta(
//					$existing_post->ID,
//					'wp_birdlife_event_pro_species_grp_id',
//					$ProSpeciesGrpId
//				);
//			}
//
//			if ( $ProSpeciesGrpName !== null ) {
//				update_post_meta(
//					$existing_post->ID,
//					'wp_birdlife_event_pro_species_grp_name',
//					$ProSpeciesGrpName
//				);
//			}

			if ( $wp_birdlife_event_pro_record_type_voc !== null ) {
				update_post_meta(
					$existing_post->ID,
					'wp_birdlife_event_pro_record_type_voc',
					$wp_birdlife_event_pro_record_type_voc
				);
			}

			if ( $wp_birdlife_event_pro_record_type_voc_name !== null ) {
				update_post_meta(
					$existing_post->ID,
					'wp_birdlife_event_pro_record_type_voc_name',
					$wp_birdlife_event_pro_record_type_voc_name
				);
			}

			if ( $wp_birdlife_event_featured_image !== ''
			     && $wp_birdlife_event_featured_image !== false ) {

				// download and set featured image for post
				$this->update_featured_image(
					$existing_post,
					$wp_birdlife_event_featured_image,
					$post_metas,
					'wp_birdlife_event_featured_image'
				);

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
			     || str_contains( $wp_birdlife_event_status, 'in planung' )
			     || $wp_birdlife_event_status === 'abgesagt'
			     || str_contains( $wp_birdlife_event_status, 'abgesagt' ) ) {
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

			update_post_meta(
				$existing_post->ID,
				'wp_birdlife_event_updated_timestamp',
				date( 'Y-m-d H:i:s' )
			);
		}

		public function update_featured_image( $existing_post, $new_image, $post_metas, $m_key ): void {
			$post_needs_update = false;
			$meta_key_exists   = false;

			foreach ( $post_metas as $meta_key => $meta_value ) {
				if ( $meta_key === $m_key ) {
					$meta_key_exists = true;
					if ( is_array( $meta_value ) ) {
						if ( $meta_value[0] !== $new_image ) {
							$post_needs_update = true;
						}
					} else {
						if ( $meta_value !== $new_image ) {
							$post_needs_update = true;
						}
					}
				}
			}

			if ( ( ! $meta_key_exists || $post_needs_update ) || ! has_post_thumbnail( $existing_post->ID ) ) {
				$decode = base64_decode( $new_image );
				$size   = getImageSizeFromString( $decode );
				var_dump( $size );
				if ( empty( $size['mime'] ) || strpos( $size['mime'], 'image/' ) !== 0 ) {
					die( 'Base64 value is not a valid image' );
				}

				$ext      = substr( $size['mime'], 6 );
				$img_file = $existing_post->post_name . ".{$ext}";

				$upload_dir       = wp_upload_dir();
				$unique_file_name = wp_unique_filename( $upload_dir['path'], $img_file );
				$filename         = basename( $unique_file_name );

				if ( wp_mkdir_p( $upload_dir['path'] ) ) {
					$file = $upload_dir['path'] . '/' . $filename;
				} else {
					$file = $upload_dir['basedir'] . '/' . $filename;
				}

				file_put_contents( $file, $decode );
				$wp_filetype = wp_check_filetype( $filename, null );

				// Set attachment data
				$attachment = array(
					'post_mime_type' => $wp_filetype['type'],
					'post_title'     => sanitize_file_name( $filename ),
					'post_content'   => '',
					'post_status'    => 'inherit'
				);

				$attach_id = wp_insert_attachment( $attachment, $file, $existing_post->ID );

				require_once( ABSPATH . 'wp-admin/includes/image.php' );

				$attach_data = wp_generate_attachment_metadata( $attach_id, $file );
				wp_update_attachment_metadata( $attach_id, $attach_data );
				set_post_thumbnail( $existing_post->ID, $attach_id );
			}
		}

		public function update_event_status_and_currency(
			$existing_post,
			$module_item,
			$post_metas,
			$helper
		) {
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
			if ( is_array( $module_item['vocabularyReference'] ) ) {
				if ( is_array( $module_item['vocabularyReference']['@attributes'] ) ) {
					$reference_name = $module_item['vocabularyReference']['@attributes']['name'];
					$reference_id   = $module_item['vocabularyReference']['@attributes']['id'];

					if ( $reference_name === 'EvtTypeVoc' ) {
						$wp_birdlife_event_type_voc = $module_item['vocabularyReference']['vocabularyReferenceItem']['formattedValue'];
						$EvtTypeVocId               = $module_item['vocabularyReference']['vocabularyReferenceItem']['@attributes']['id'];

						if ( $wp_birdlife_event_type_voc !== null && (
								! str_contains( $wp_birdlife_event_type_voc, 'Anlass BirdLife Zürich' ) &&
								! str_contains( $wp_birdlife_event_type_voc, 'Delegiertenversammlung' ) &&
								! str_contains( $wp_birdlife_event_type_voc, 'Einführungs- und Auffrischungskurse' ) &&
								! str_contains( $wp_birdlife_event_type_voc, 'Grundkurse' ) &&
								! str_contains( $wp_birdlife_event_type_voc, 'Kantonale Exkursion' ) &&
								! str_contains( $wp_birdlife_event_type_voc, 'Kurse Bereich Naturschutz' ) &&
								! str_contains( $wp_birdlife_event_type_voc, 'Kurse für Fortgeschrittene' ) &&
								! str_contains( $wp_birdlife_event_type_voc, 'Lokale Veranstaltung' ) &&
								! str_contains( $wp_birdlife_event_type_voc, 'Pfingstexkursion' ) &&
								! str_contains( $wp_birdlife_event_type_voc, 'POKs' ) &&
								! str_contains( $wp_birdlife_event_type_voc, 'Veranstaltungen Bereich Politik' ) &&
								$EvtTypeVocId !== '100183579'
							)
						) {
							$wp_birdlife_event_type_voc      = $EvtTypeVocId;
							$wp_birdlife_event_type_voc_name = $wp_birdlife_event_type_voc;
						}
					} else if ( $reference_name === 'EvtCategoryVoc' ) {
						$wp_birdlife_event_category_voc = $module_item['vocabularyReference']['vocabularyReferenceItem']['@attributes']['id'];
						if ( $wp_birdlife_event_category_voc !== null ) {
							$vocabulary_args = $helper->get_manage_plus_api_args_no_body();
							$vocabulary_resp = wp_remote_get( 'https://maBirdlife.zetcom.app/ria-ws/application/vocabulary/instances/EvtCategoryVgr/nodes/' . $wp_birdlife_event_category_voc . '/parents', $vocabulary_args );

							$xml       = simplexml_load_string( $vocabulary_resp['body'] );
							$namespace = $xml->getNamespaces( true );
							$parents   = $xml->children( $namespace[''] );

							$nodeId                                = (string) $parents->parent->attributes()->nodeId;
							$wp_birdlife_event_category_voc_parent = $nodeId;
							if ( $wp_birdlife_event_category_voc_parent == '100150582' ) {
								$wp_birdlife_event_category_voc_parent = $wp_birdlife_event_category_voc;
							}
						}

//						if ( $wp_birdlife_event_category_voc !== null ) {
//							if ( $wp_birdlife_event_category_voc === '100120423' || $wp_birdlife_event_category_voc === 100120423 ||
//							     $wp_birdlife_event_category_voc === '100120427' || $wp_birdlife_event_category_voc === 100120427 ||
//							     $wp_birdlife_event_category_voc === '100120432' || $wp_birdlife_event_category_voc === 100120432 ||
//							     $wp_birdlife_event_category_voc === '100183571' || $wp_birdlife_event_category_voc === 100183571
//							) {
//								$wp_birdlife_event_category_voc = 100183571;
//							} else if (
//								$wp_birdlife_event_category_voc === '100183572' || $wp_birdlife_event_category_voc === 100183572 ||
//								$wp_birdlife_event_category_voc === '100120428' || $wp_birdlife_event_category_voc === 100120428 ||
//								$wp_birdlife_event_category_voc === '100120430' || $wp_birdlife_event_category_voc === 100120430 ||
//								$wp_birdlife_event_category_voc === '100120433' || $wp_birdlife_event_category_voc === 100120433 ||
//								$wp_birdlife_event_category_voc === '100120435' || $wp_birdlife_event_category_voc === 100120435
//							) {
//								$wp_birdlife_event_category_voc = 100183572;
//							} else if (
//								$wp_birdlife_event_category_voc === '100183575' || $wp_birdlife_event_category_voc === 100183575 ||
//								$wp_birdlife_event_category_voc === '100120434' || $wp_birdlife_event_category_voc === 100120434
//							) {
//								$wp_birdlife_event_category_voc = 100183575;
//							} else if (
//								$wp_birdlife_event_category_voc === '100183574' || $wp_birdlife_event_category_voc === 100183574 ||
//								$wp_birdlife_event_category_voc === '100127568' || $wp_birdlife_event_category_voc === 100127568
//							) {
//								$wp_birdlife_event_category_voc = 100183574;
//							} else if (
//								$wp_birdlife_event_category_voc === '100183573' || $wp_birdlife_event_category_voc === 100183573 ||
//								$wp_birdlife_event_category_voc === '100120422' || $wp_birdlife_event_category_voc === 100120422 ||
//								$wp_birdlife_event_category_voc === '100120424' || $wp_birdlife_event_category_voc === 100120424 ||
//								$wp_birdlife_event_category_voc === '100183582' || $wp_birdlife_event_category_voc === 100183582 ||
//								$wp_birdlife_event_category_voc === '100120426' || $wp_birdlife_event_category_voc === 100120426 ||
//								$wp_birdlife_event_category_voc === '100120429' || $wp_birdlife_event_category_voc === 100120429 ||
//								$wp_birdlife_event_category_voc === '100120431' || $wp_birdlife_event_category_voc === 100120431
//							) {
//								$wp_birdlife_event_category_voc = 100183573;
//							}
//						}
					}
				} else {
					foreach ( $module_item['vocabularyReference'] as $module_reference ) {
						if ( is_array( $module_reference['@attributes'] ) ) {
							$reference_name = $module_reference['@attributes']['name'];

							if ( $reference_name === 'EvtTypeVoc' ) {
								$EvtTypeVocId   = $module_reference['vocabularyReferenceItem']['@attributes']['id'];
								$EvtTypeVocName = $module_reference['vocabularyReferenceItem']['formattedValue'];

								if ( $EvtTypeVocName !== null && (
										! str_contains( $EvtTypeVocName, 'Anlass BirdLife Zürich' ) &&
										! str_contains( $EvtTypeVocName, 'Delegiertenversammlung' ) &&
										! str_contains( $EvtTypeVocName, 'Einführungs- und Auffrischungskurse' ) &&
										! str_contains( $EvtTypeVocName, 'Grundkurse' ) &&
										! str_contains( $EvtTypeVocName, 'Kantonale Exkursion' ) &&
										! str_contains( $EvtTypeVocName, 'Kurse Bereich Naturschutz' ) &&
										! str_contains( $EvtTypeVocName, 'Kurse für Fortgeschrittene' ) &&
										! str_contains( $EvtTypeVocName, 'Lokale Veranstaltung' ) &&
										! str_contains( $EvtTypeVocName, 'Pfingstexkursion' ) &&
										! str_contains( $EvtTypeVocName, 'POKs' ) &&
										! str_contains( $EvtTypeVocName, 'Veranstaltungen Bereich Politik' ) &&
										$EvtTypeVocId !== '100183579'
									)
								) {
									$wp_birdlife_event_type_voc      = $EvtTypeVocId;
									$wp_birdlife_event_type_voc_name = $EvtTypeVocName;
								}
							} else if ( $reference_name === 'EvtCategoryVoc' ) {
								$wp_birdlife_event_category_voc = $module_reference['vocabularyReferenceItem']['@attributes']['id'];

								if ( $wp_birdlife_event_category_voc !== null ) {
									$vocabulary_args = $helper->get_manage_plus_api_args_no_body();
									$vocabulary_resp = wp_remote_get( 'https://maBirdlife.zetcom.app/ria-ws/application/vocabulary/instances/EvtCategoryVgr/nodes/' . $wp_birdlife_event_category_voc . '/parents', $vocabulary_args );

									$xml       = simplexml_load_string( $vocabulary_resp['body'] );
									$namespace = $xml->getNamespaces( true );
									$parents   = $xml->children( $namespace[''] );

									$nodeId                                = (string) $parents->parent->attributes()->nodeId;
									$wp_birdlife_event_category_voc_parent = $nodeId;
									if ( $wp_birdlife_event_category_voc_parent == '100150582' ) {
										$wp_birdlife_event_category_voc_parent = $wp_birdlife_event_category_voc;
									}
								}

//								if ( $wp_birdlife_event_category_voc !== null ) {
//									if ( $wp_birdlife_event_category_voc === '100120423' || $wp_birdlife_event_category_voc === 100120423 ||
//									     $wp_birdlife_event_category_voc === '100120427' || $wp_birdlife_event_category_voc === 100120427 ||
//									     $wp_birdlife_event_category_voc === '100120432' || $wp_birdlife_event_category_voc === 100120432 ||
//									     $wp_birdlife_event_category_voc === '100183571' || $wp_birdlife_event_category_voc === 100183571
//									) {
//										$wp_birdlife_event_category_voc = 100183571;
//									} else if (
//										$wp_birdlife_event_category_voc === '100183572' || $wp_birdlife_event_category_voc === 100183572 ||
//										$wp_birdlife_event_category_voc === '100120428' || $wp_birdlife_event_category_voc === 100120428 ||
//										$wp_birdlife_event_category_voc === '100120430' || $wp_birdlife_event_category_voc === 100120430 ||
//										$wp_birdlife_event_category_voc === '100120433' || $wp_birdlife_event_category_voc === 100120433 ||
//										$wp_birdlife_event_category_voc === '100120435' || $wp_birdlife_event_category_voc === 100120435
//									) {
//										$wp_birdlife_event_category_voc = 100183572;
//									} else if (
//										$wp_birdlife_event_category_voc === '100183575' || $wp_birdlife_event_category_voc === 100183575 ||
//										$wp_birdlife_event_category_voc === '100120434' || $wp_birdlife_event_category_voc === 100120434
//									) {
//										$wp_birdlife_event_category_voc = 100183575;
//									} else if (
//										$wp_birdlife_event_category_voc === '100183574' || $wp_birdlife_event_category_voc === 100183574 ||
//										$wp_birdlife_event_category_voc === '100127568' || $wp_birdlife_event_category_voc === 100127568
//									) {
//										$wp_birdlife_event_category_voc = 100183574;
//									} else if (
//										$wp_birdlife_event_category_voc === '100183573' || $wp_birdlife_event_category_voc === 100183573 ||
//										$wp_birdlife_event_category_voc === '100120422' || $wp_birdlife_event_category_voc === 100120422 ||
//										$wp_birdlife_event_category_voc === '100120424' || $wp_birdlife_event_category_voc === 100120424 ||
//										$wp_birdlife_event_category_voc === '100183582' || $wp_birdlife_event_category_voc === 100183582 ||
//										$wp_birdlife_event_category_voc === '100120426' || $wp_birdlife_event_category_voc === 100120426 ||
//										$wp_birdlife_event_category_voc === '100120429' || $wp_birdlife_event_category_voc === 100120429 ||
//										$wp_birdlife_event_category_voc === '100120431' || $wp_birdlife_event_category_voc === 100120431
//									) {
//										$wp_birdlife_event_category_voc = 100183573;
//									}
//								}
							}
						}
					}
				}
			}

			if ( $wp_birdlife_event_category_voc !== null ) {
				update_post_meta(
					$existing_post->ID,
					'wp_birdlife_event_category_voc',
					$wp_birdlife_event_category_voc
				);
			}

			if ( $wp_birdlife_event_category_voc_parent !== null ) {
				update_post_meta(
					$existing_post->ID,
					'wp_birdlife_event_category_voc_parent',
					$wp_birdlife_event_category_voc_parent
				);
			}

			if ( $wp_birdlife_event_type_voc !== null ) {
				update_post_meta(
					$existing_post->ID,
					'wp_birdlife_event_type_voc',
					$wp_birdlife_event_type_voc
				);
			}

			if ( $wp_birdlife_event_type_voc_name !== null ) {
				update_post_meta(
					$existing_post->ID,
					'wp_birdlife_event_type_voc_name',
					$wp_birdlife_event_type_voc_name
				);
			}

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
			$post_metas = get_post_meta( $existing_post->ID );
			$flag       = false;
			foreach ( $post_metas as $meta_key => $meta_value ) {
				if ( $meta_key === 'wp_birdlife_event_place' ) {
					if ( is_array( $meta_value ) ) {
						$wp_birdlife_event_place = $meta_value[0];
						$my_post                 = array(
							'ID'           => $existing_post->ID,
							'post_content' => $new_content . '<p style="display:none">' . $wp_birdlife_event_place . '</p>'
						);
						wp_update_post( $my_post );
						$flag = true;
					}
				}
			}

			if ( ! $flag ) {
				if ( $existing_post->post_content !== $new_content ) {
					$my_post = array(
						'ID'           => $existing_post->ID,
						'post_content' => $new_content
					);
					wp_update_post( $my_post );
				}
			}
		}
	}
}