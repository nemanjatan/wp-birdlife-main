<?php

if ( ! class_exists( 'WP_Birdlife_New_Event' ) ) {
	class WP_Birdlife_New_Event {
		public function save_new_events(
			$module_item,
			$helper
		) {
			$event_multimedia_helper = new WP_Birdlife_Event_Multimedia();
			$event_reference         = new WP_Birdlife_Event_Reference();
			$birdlife_reserved_tn    = new WP_Birdlife_Reserved_Tn();
			$free_seats_helper       = new WP_Birdlife_Free_Seats();

			$module_item_arr                                    = array( 'id' => $module_item['systemField'][0]['value'] );
			$module_item_arr['wp_birdlife_event_last_modified'] = $module_item['systemField'][3]['value'];

			if ( ! empty( $module_item['dataField'] ) ) {
				$data_fields = $module_item['dataField'];

				if ( is_array( $data_fields ) && $data_fields['value'] === null ) {
					foreach ( $data_fields as $data_field ) {
						$module_item_arr = $helper->set_metabox_values_from_api( $data_field, $module_item_arr );
					}
				} else {
					$module_item_arr = $helper->set_metabox_values_from_api( $data_fields, $module_item_arr );
				}
			}

			$module_item_arr['wp_birdlife_event_reserved_tn'] = $birdlife_reserved_tn->fetch_reserved_tn( $module_item['systemField'][0]['value'] );

			if ( is_array( $module_item['virtualField'] ) ) {
				foreach ( $module_item['virtualField'] as $virtual_field ) {
					if ( $virtual_field['@attributes']['name'] === 'EvtStatusVrt' ) {
						$module_item_arr['wp_birdlife_event_status'] = $virtual_field['value'];
					}
				}
			} else {
				$module_item_arr['wp_birdlife_event_status'] = $module_item['virtualField']['value'];
			}
			$module_item_arr['wp_birdlife_event_currency_voc'] = $module_item['vocabularyReference']['vocabularyReferenceItem']['formattedValue'];

			if ( is_array( $module_item['vocabularyReference'] ) ) {
				if ( is_array( $module_item['vocabularyReference']['@attributes'] ) ) {
					$reference_name = $module_item['vocabularyReference']['@attributes']['name'];
					$reference_id   = $module_item['vocabularyReference']['@attributes']['id'];

					if ( $reference_name === 'EvtTypeVoc' && (
							$reference_id === '100183576' || $reference_id === 100183576 ||
							$reference_id === '100183577' || $reference_id === 100183577 ||
							$reference_id === '100183580' || $reference_id === 100183580 ||
							$reference_id === '100183581' || $reference_id === 100183581
						)
					) {
						$module_item_arr['wp_birdlife_event_type_voc'] = $module_item['vocabularyReference']['vocabularyReferenceItem']['formattedValue'];
					} else if ( $reference_name === 'EvtCategoryVoc' ) {
						$module_item_arr['wp_birdlife_event_type_voc'] = $module_item['vocabularyReference']['vocabularyReferenceItem']['@attributes']['id'];
						$wp_birdlife_event_category_voc                = $module_item_arr['wp_birdlife_event_type_voc'];

						if ( $wp_birdlife_event_category_voc !== null ) {
							$vocabulary_args = $helper->get_manage_plus_api_args_no_body();
							$vocabulary_resp = wp_remote_get( 'https://de1.zetcom-group.de/MpWeb-maZurichBirdlife/ria-ws/application/vocabulary/instances/EvtCategoryVgr/nodes/' . $wp_birdlife_event_category_voc . '/parents', $vocabulary_args );

							$xml       = simplexml_load_string( $vocabulary_resp['body'] );
							$namespace = $xml->getNamespaces( true );
							$parents   = $xml->children( $namespace[''] );

							$nodeId                                = (string) $parents->parent->attributes()->nodeId;
							$wp_birdlife_event_category_voc_parent = $nodeId;
							if ( $wp_birdlife_event_category_voc_parent == '100150582' ) {
								$wp_birdlife_event_category_voc_parent                    = $wp_birdlife_event_category_voc;
								$module_item_arr['wp_birdlife_event_category_voc_parent'] = $wp_birdlife_event_category_voc_parent;
							}
						}

//						if ( $wp_birdlife_event_category_voc !== null ) {
//							if ( $wp_birdlife_event_category_voc === '100120423' || $wp_birdlife_event_category_voc === 100120423 ||
//							     $wp_birdlife_event_category_voc === '100120427' || $wp_birdlife_event_category_voc === 100120427 ||
//							     $wp_birdlife_event_category_voc === '100120432' || $wp_birdlife_event_category_voc === 100120432 ||
//							     $wp_birdlife_event_category_voc === '100183571' || $wp_birdlife_event_category_voc === 100183571
//							) {
//								$wp_birdlife_event_category_voc = '100183571';
//							} else if (
//								$wp_birdlife_event_category_voc === '100183572' || $wp_birdlife_event_category_voc === 100183572 ||
//								$wp_birdlife_event_category_voc === '100120428' || $wp_birdlife_event_category_voc === 100120428 ||
//								$wp_birdlife_event_category_voc === '100120430' || $wp_birdlife_event_category_voc === 100120430 ||
//								$wp_birdlife_event_category_voc === '100120433' || $wp_birdlife_event_category_voc === 100120433 ||
//								$wp_birdlife_event_category_voc === '100120435' || $wp_birdlife_event_category_voc === 100120435
//							) {
//								$wp_birdlife_event_category_voc = '100183572';
//							} else if (
//								$wp_birdlife_event_category_voc === '100183575' || $wp_birdlife_event_category_voc === 100183575 ||
//								$wp_birdlife_event_category_voc === '100120434' || $wp_birdlife_event_category_voc === 100120434
//							) {
//								$wp_birdlife_event_category_voc = '100183575';
//							} else if (
//								$wp_birdlife_event_category_voc === '100183574' || $wp_birdlife_event_category_voc === 100183574 ||
//								$wp_birdlife_event_category_voc === '100127568' || $wp_birdlife_event_category_voc === 100127568
//							) {
//								$wp_birdlife_event_category_voc = '100183574';
//							} else if (
//								$wp_birdlife_event_category_voc === '100183573' || $wp_birdlife_event_category_voc === 100183573 ||
//								$wp_birdlife_event_category_voc === '100120422' || $wp_birdlife_event_category_voc === 100120422 ||
//								$wp_birdlife_event_category_voc === '100120424' || $wp_birdlife_event_category_voc === 100120424 ||
//								$wp_birdlife_event_category_voc === '100183582' || $wp_birdlife_event_category_voc === 100183582 ||
//								$wp_birdlife_event_category_voc === '100120426' || $wp_birdlife_event_category_voc === 100120426 ||
//								$wp_birdlife_event_category_voc === '100120429' || $wp_birdlife_event_category_voc === 100120429 ||
//								$wp_birdlife_event_category_voc === '100120431' || $wp_birdlife_event_category_voc === 100120431
//							) {
//								$wp_birdlife_event_category_voc = '100183573';
//							}
//						}

						$module_item_arr['wp_birdlife_event_type_voc'] = $wp_birdlife_event_category_voc;
					}
				} else {
					foreach ( $module_item['vocabularyReference'] as $module_reference ) {
						if ( is_array( $module_reference['@attributes'] ) ) {
							$reference_name = $module_reference['@attributes']['name'];
							$reference_id   = $module_reference['@attributes']['id'];

							if ( $reference_name === 'EvtTypeVoc' && (
									$reference_id === '100183576' || $reference_id === 100183576 ||
									$reference_id === '100183577' || $reference_id === 100183577 ||
									$reference_id === '100183580' || $reference_id === 100183580 ||
									$reference_id === '100183581' || $reference_id === 100183581
								)
							) {
								$module_item_arr['wp_birdlife_event_type_voc'] = $module_reference['vocabularyReferenceItem']['formattedValue'];
							} else if ( $reference_name === 'EvtCategoryVoc' ) {
								$module_item_arr['wp_birdlife_event_type_voc'] = $module_reference['vocabularyReferenceItem']['@attributes']['id'];
								$wp_birdlife_event_category_voc                = $module_item_arr['wp_birdlife_event_type_voc'];

								if ( $wp_birdlife_event_category_voc !== null ) {
									$vocabulary_args = $helper->get_manage_plus_api_args_no_body();
									$vocabulary_resp = wp_remote_get( 'https://de1.zetcom-group.de/MpWeb-maZurichBirdlife/ria-ws/application/vocabulary/instances/EvtCategoryVgr/nodes/' . $wp_birdlife_event_category_voc . '/parents', $vocabulary_args );

									$xml       = simplexml_load_string( $vocabulary_resp['body'] );
									$namespace = $xml->getNamespaces( true );
									$parents   = $xml->children( $namespace[''] );

									$nodeId                                = (string) $parents->parent->attributes()->nodeId;
									$wp_birdlife_event_category_voc_parent = $nodeId;
									if ( $wp_birdlife_event_category_voc_parent == '100150582' ) {
										$wp_birdlife_event_category_voc_parent                    = $wp_birdlife_event_category_voc;
										$module_item_arr['wp_birdlife_event_category_voc_parent'] = $wp_birdlife_event_category_voc_parent;
									}
								}

//								if ( $wp_birdlife_event_category_voc !== null ) {
//									if ( $wp_birdlife_event_category_voc === '100120423' || $wp_birdlife_event_category_voc === 100120423 ||
//									     $wp_birdlife_event_category_voc === '100120427' || $wp_birdlife_event_category_voc === 100120427 ||
//									     $wp_birdlife_event_category_voc === '100120432' || $wp_birdlife_event_category_voc === 100120432 ||
//									     $wp_birdlife_event_category_voc === '100183571' || $wp_birdlife_event_category_voc === 100183571
//									) {
//										$wp_birdlife_event_category_voc = '100183571';
//									} else if (
//										$wp_birdlife_event_category_voc === '100183572' || $wp_birdlife_event_category_voc === 100183572 ||
//										$wp_birdlife_event_category_voc === '100120428' || $wp_birdlife_event_category_voc === 100120428 ||
//										$wp_birdlife_event_category_voc === '100120430' || $wp_birdlife_event_category_voc === 100120430 ||
//										$wp_birdlife_event_category_voc === '100120433' || $wp_birdlife_event_category_voc === 100120433 ||
//										$wp_birdlife_event_category_voc === '100120435' || $wp_birdlife_event_category_voc === 100120435
//									) {
//										$wp_birdlife_event_category_voc = '100183572';
//									} else if (
//										$wp_birdlife_event_category_voc === '100183575' || $wp_birdlife_event_category_voc === 100183575 ||
//										$wp_birdlife_event_category_voc === '100120434' || $wp_birdlife_event_category_voc === 100120434
//									) {
//										$wp_birdlife_event_category_voc = '100183575';
//									} else if (
//										$wp_birdlife_event_category_voc === '100183574' || $wp_birdlife_event_category_voc === 100183574 ||
//										$wp_birdlife_event_category_voc === '100127568' || $wp_birdlife_event_category_voc === 100127568
//									) {
//										$wp_birdlife_event_category_voc = '100183574';
//									} else if (
//										$wp_birdlife_event_category_voc === '100183573' || $wp_birdlife_event_category_voc === 100183573 ||
//										$wp_birdlife_event_category_voc === '100120422' || $wp_birdlife_event_category_voc === 100120422 ||
//										$wp_birdlife_event_category_voc === '100120424' || $wp_birdlife_event_category_voc === 100120424 ||
//										$wp_birdlife_event_category_voc === '100183582' || $wp_birdlife_event_category_voc === 100183582 ||
//										$wp_birdlife_event_category_voc === '100120426' || $wp_birdlife_event_category_voc === 100120426 ||
//										$wp_birdlife_event_category_voc === '100120429' || $wp_birdlife_event_category_voc === 100120429 ||
//										$wp_birdlife_event_category_voc === '100120431' || $wp_birdlife_event_category_voc === 100120431
//									) {
//										$wp_birdlife_event_category_voc = '100183573';
//									}
//								}

								$module_item_arr['wp_birdlife_event_type_voc'] = $wp_birdlife_event_category_voc;
							}
						}
					}
				}
			}

			if ( is_array( $module_item['moduleReference'] ) ) {
				if ( is_array( $module_item['moduleReference']['@attributes'] ) ) {
					$reference_name = $module_item['moduleReference']['@attributes']['name'];

					if ( $reference_name === 'EvtMultimediaRef' ) {
						$multimedia = $event_multimedia_helper->get_image_for_event( $module_item, $helper );

						if ( $multimedia[0] !== '' ) {
							$module_item_arr['wp_birdlife_event_featured_image'] = $multimedia[0];
						}

						if ( $multimedia[1] !== '' ) {
							$module_item_arr['wp_birdlife_event_featured_image_photocredit_txt'] = $multimedia[1];
						}
					} else if ( $reference_name === 'EvtInvolvedRef' ) {
						$management = $event_reference->handle_event_involved_ref( $module_item, $helper );

						if ( $management !== '' ) {
							$module_item_arr['leitung'] = $management;
						}
					} else if ( $reference_name === 'EvtProjectRef' ) {
						$module_item_arr['wp_birdlife_event_project_ref'] = $module_item['moduleReference']['moduleReferenceItem']['formattedValue'];

						$wp_birdlife_event_project_ref = $module_item['moduleReference']['moduleReferenceItem']['formattedValue'];

						$module_item_id = $module_item['moduleReference']['moduleReferenceItem']['@attributes']['moduleItemId'];
						$xml            = file_get_contents( WP_BIRDLIFE_PATH . 'xml/project-search/project-search-all-fields-by-id.xml' );
						$xml            = str_replace( "{{project_id}}", $module_item_id, $xml );
						$args           = $helper->get_manage_plus_api_args( $xml );

						$resp      = wp_remote_post( 'https://de1.zetcom-group.de/MpWeb-maZurichBirdlife/ria-ws/application/module/Project/search/', $args );
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
									$ProSpeciesGrpId = $repeatableItem['repeatableGroupItem']['vocabularyReference']['vocabularyReferenceItem']['@attributes']['id'];

									if ( $repeatableItem['repeatableGroupItem']['vocabularyReference']['vocabularyReferenceItem']['@attributes']['name'] !== null ) {
										$ProSpeciesGrpName = $repeatableItem['repeatableGroupItem']['vocabularyReference']['vocabularyReferenceItem']['@attributes']['name'];
									} else {
										$ProSpeciesGrpName = $repeatableItem['repeatableGroupItem']['vocabularyReference']['vocabularyReferenceItem']['formattedValue'];
									}

									$module_item_arr['wp_birdlife_event_pro_species_grp_id']   = $ProSpeciesGrpId;
									$module_item_arr['wp_birdlife_event_pro_species_grp_name'] = $ProSpeciesGrpName;
								}
							}
						}
						// end of WORKING

						if ( is_array( $vocabulary_reference ) ) {
							foreach ( $vocabulary_reference as $v_r ) {
								if ( $v_r['@attributes']['name'] === 'ProRecordTypeVoc' ) {
									$module_item_arr['wp_birdlife_event_pro_record_type_voc_name'] = $v_r['vocabularyReferenceItem']['@attributes']['name'];
									$module_item_arr['wp_birdlife_event_pro_record_type_voc']      = $v_r['vocabularyReferenceItem']['@attributes']['id'];
								}
							}
						}
					}
				} else {
					foreach ( $module_item['moduleReference'] as $module_reference ) {
						if ( is_array( $module_reference['@attributes'] ) ) {
							$reference_name = $module_reference['@attributes']['name'];

							if ( $reference_name === 'EvtMultimediaRef' ) {
								list( $wp_birdlife_event_featured_image, $wp_birdlife_event_featured_image_photocredit_txt ) = $event_multimedia_helper->handle_multimedia_for_event( $module_reference, $helper );
								$module_item_arr['wp_birdlife_event_featured_image_photocredit_txt'] = $wp_birdlife_event_featured_image_photocredit_txt;
								if ( $wp_birdlife_event_featured_image !== '' ) {
									$module_item_arr['wp_birdlife_event_featured_image'] = $wp_birdlife_event_featured_image;
								}
							} else if ( $reference_name === 'EvtInvolvedRef' ) {
								$management                 = $event_reference->handle_event_involved( $module_reference, $helper );
								$module_item_arr['leitung'] = $management;
							} else if ( $reference_name === 'EvtProjectRef' ) {
								$module_item_arr['wp_birdlife_event_project_ref'] = $module_reference['moduleReferenceItem']['formattedValue'];

								$wp_birdlife_event_project_ref = $module_reference['moduleReferenceItem']['formattedValue'];
								$module_item_id                = $module_reference['moduleReferenceItem']['@attributes']['moduleItemId'];
								// todo get project and then get ProRecordTypeVoc

								$xml  = file_get_contents( WP_BIRDLIFE_PATH . 'xml/project-search/project-search-all-fields-by-id.xml' );
								$xml  = str_replace( "{{project_id}}", $module_item_id, $xml );
								$args = $helper->get_manage_plus_api_args( $xml );

								$resp      = wp_remote_post( 'https://de1.zetcom-group.de/MpWeb-maZurichBirdlife/ria-ws/application/module/Project/search/', $args );
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
											$ProSpeciesGrpId = $repeatableItem['repeatableGroupItem']['vocabularyReference']['vocabularyReferenceItem']['@attributes']['id'];

											if ( $repeatableItem['repeatableGroupItem']['vocabularyReference']['vocabularyReferenceItem']['@attributes']['name'] !== null ) {
												$ProSpeciesGrpName = $repeatableItem['repeatableGroupItem']['vocabularyReference']['vocabularyReferenceItem']['@attributes']['name'];
											} else {
												$ProSpeciesGrpName = $repeatableItem['repeatableGroupItem']['vocabularyReference']['vocabularyReferenceItem']['formattedValue'];
											}

											$module_item_arr['wp_birdlife_event_pro_species_grp_id']   = $ProSpeciesGrpId;
											$module_item_arr['wp_birdlife_event_pro_species_grp_name'] = $ProSpeciesGrpName;
										}
									}
								}
								// end of WORKING

								if ( is_array( $vocabulary_reference ) ) {
									foreach ( $vocabulary_reference as $v_r ) {
										if ( $v_r['@attributes']['name'] === 'ProRecordTypeVoc' ) {
											$module_item_arr['wp_birdlife_event_pro_record_type_voc']      = $v_r['vocabularyReferenceItem']['@attributes']['id'];
											$module_item_arr['wp_birdlife_event_pro_record_type_voc_name'] = $v_r['vocabularyReferenceItem']['@attributes']['name'];
										}
									}
								}
							}
						}
					}
				}
			}

			$wp_birdlife_event_confirmed_tn = 0;
			if ( $module_item['repeatableGroup'] !== null ) {
				$wp_birdlife_event_confirmed_tn = $free_seats_helper->get_free_seats( $module_item );
			}

			$module_item_arr['wp_birdlife_event_confirmed_tn'] = $wp_birdlife_event_confirmed_tn;

			return $module_item_arr;
		}
	}
}