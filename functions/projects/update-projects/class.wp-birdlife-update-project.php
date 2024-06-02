<?php

if ( ! class_exists( 'WP_Birdlife_Update_Project' ) ) {
	class WP_Birdlife_Update_Project {
		public function update_projects(
			$module_item,
			$post
		) {
			$helper = new WP_Birdlife_Helper();

			$existing_post = $post[0];
			$post_metas    = get_post_meta( $existing_post->ID );

			$skip = false;

			// FILTER
			// We do not have to import any Project with ProRecordTypeVoc = „Veranstaltung“.
			// These projects are created to manage finances of events.
			// From the rest, we need to select those projects which have a current Status of
			// „abgeschlossen (publiziert auf Webseite)“
			// or „offen (publiziert auf Webseite)“
			// or "in Planung (publiziert auf Webseite)“
			// or "in Arbeit (publiziert auf Webseite)“.
			if ( ! empty( $module_item['vocabularyReference'] ) && is_array( $module_item['vocabularyReference'] ) ) {
				foreach ( $module_item['vocabularyReference'] as $vocabularyRef ) {
					if ( ! empty( $vocabularyRef ) && is_array( $vocabularyRef ) ) {
						if ( ! empty( $vocabularyRef['@attributes'] ) ) {
							$vocabularyRefName = $vocabularyRef['@attributes']['name'];

							if ( $vocabularyRefName === 'ProRecordTypeVoc' ) {
								if ( ! empty( $vocabularyRef['vocabularyReferenceItem'] ) && is_array( $vocabularyRef['vocabularyReferenceItem'] ) ) {
									$vocabularyRefItem = $vocabularyRef['vocabularyReferenceItem']['formattedValue'];

									if ( $vocabularyRefItem === 'Veranstaltung' ) {
										// do not save this project
										$skip = true;
									}
								}
							} else if ( $vocabularyRefName === 'ProStatusCurrentVoc' ) {
								if ( ! empty( $vocabularyRef['vocabularyReferenceItem'] ) && is_array( $vocabularyRef['vocabularyReferenceItem'] ) ) {
									$vocabularyRefItem = $vocabularyRef['vocabularyReferenceItem']['formattedValue'];

									if ( $vocabularyRefItem !== 'in Planung (publiziert auf Webseite)'
									     && $vocabularyRefItem !== 'abgeschlossen (publiziert auf Webseite)'
									     && $vocabularyRefItem !== 'offen (publiziert auf Webseite)'
									     && $vocabularyRefItem !== 'in Arbeit (publiziert auf Webseite)'
									) {
										$skip = true;
									} else {
										$module_item_arr_2['wp_birdlife_project_pro_status_current_voc'] = $vocabularyRefItem;
									}
								}
							}
						}
					}
				}
			}

			if ( ! str_contains( json_encode( $module_item ), 'ProStatusCurrentVoc' ) ) {
				$skip = true;
			}
			// end of filter

			if ( ! $skip ) {
				if ( ! empty( $module_item['dataField'] ) ) {
					$data_fields = $module_item['dataField'];

					if ( is_array( $data_fields ) && $data_fields['value'] === null ) {
						foreach ( $data_fields as $data_field ) {
							$this->update_project_metabox_values_from_api( $data_field, $existing_post, $post_metas );
						}
					} else {
						$this->update_project_metabox_values_from_api( $data_fields, $existing_post, $post_metas );
					}
				}

				if ( is_array( $module_item['moduleReference'] ) ) {
					foreach ( $module_item['moduleReference'] as $module_reference ) {
						if ( is_array( $module_reference['@attributes'] ) ) {
							$reference_name = $module_reference['@attributes']['name'];

							if ( $reference_name === 'ProMultimediaRef' ) {
								if ( is_array( $module_reference['moduleReferenceItem'] ) ) {
									if ( is_array( $module_reference['moduleReferenceItem']['@attributes'] ) ) {
										if ( $module_reference['moduleReferenceItem']['@attributes']['moduleItemId'] !== null ) {
											// fetch the image
											$image_id                             = $module_reference['moduleReferenceItem']['@attributes']['moduleItemId'];
											$thumbnail_url                        = 'https://de1.zetcom-group.de/MpWeb-maZurichBirdlife/ria-ws/application/module/Multimedia/' . $image_id . '/attachment';
											$args                                 = $this->get_manage_plus_api_attachment_args();
											$resp                                 = wp_remote_get( $thumbnail_url, $args );
											$wp_birdlife_project_multimedia_image = $resp['body'];

											// photocredits
											$multimedia_xml         = file_get_contents( WP_BIRDLIFE_PATH . 'xml/multimedia-search/event-multimedia-search.xml' );
											$multimedia_xml         = str_replace( "{{multimedia_id}}", $image_id, $multimedia_xml );
											$multimedia_args        = $helper->get_manage_plus_api_args( $multimedia_xml );
											$multimedia_resp        = wp_remote_post( 'https://de1.zetcom-group.de/MpWeb-maZurichBirdlife/ria-ws/application/module/Multimedia/search', $multimedia_args );
											$multimedia_body        = $multimedia_resp['body'];
											$parsed_multimedia_xml  = simplexml_load_string( $multimedia_body );
											$multimedia_json        = json_encode( $parsed_multimedia_xml );
											$parsed_multimedia_json = json_decode( $multimedia_json, true );

											$module_multimedia_items = $parsed_multimedia_json['modules']['module']['moduleItem'];

											if ( is_array( $module_multimedia_items['dataField'] ) ) {
												foreach ( $module_multimedia_items['dataField'] as $module_item ) {
													if ( is_array( $module_item['@attributes'] ) ) {
														if ( $module_item['@attributes']['name'] === 'MulPhotocreditTxt' ) {
															update_post_meta(
																$existing_post->ID,
																'wp_birdlife_project_featured_image_photocredit_txt',
																$module_item['value']
															);
														}
													}
												}
											}
											// end of photocredits

											if ( $wp_birdlife_project_multimedia_image !== null && $wp_birdlife_project_multimedia_image !== "" ) {
												update_post_meta(
													$existing_post->ID,
													'wp_birdlife_project_featured_image',
													$wp_birdlife_project_multimedia_image
												);

												update_post_meta(
													$existing_post->ID,
													'wp_birdlife_project_featured_image_exists',
													'yes'
												);
											} else {
												update_post_meta(
													$existing_post->ID,
													'wp_birdlife_project_featured_image',
													'https://birdlife-zuerich.ch/wp-content/uploads/2022/11/default-img-project.png'
												);

												update_post_meta(
													$existing_post->ID,
													'wp_birdlife_project_featured_image_exists',
													'no'
												);
											}
										}
									}
								}
							}
						}
					}
				}

				if ( $wp_birdlife_project_multimedia_image === null || $wp_birdlife_project_multimedia_image === "" ) {
					update_post_meta(
						$existing_post->ID,
						'wp_birdlife_project_featured_image',
						'https://birdlife-zuerich.ch/wp-content/uploads/2022/11/default-img-project.png'
					);

					update_post_meta(
						$existing_post->ID,
						'wp_birdlife_project_featured_image_exists',
						'no'
					);
				}

				$fachthemen = "";

				if ( is_array( $module_item['repeatableGroup'] ) ) {
					if ( is_array( $module_item['repeatableGroup']['@attributes'] ) ) {
						if ( $module_item['repeatableGroup']['@attributes']['name'] === 'ProKeyWordsGrp' ) {
							if ( is_array( $module_item['repeatableGroup']['vocabularyReference'] ) ) {
								if ( is_array( $module_item['repeatableGroup']['vocabularyReference']['@attributes'] ) ) {
									if ( $module_item['repeatableGroup']['vocabularyReference']['@attributes']['name'] === 'KeyWordVoc' ) {
										if ( is_array( $module_item['repeatableGroup']['vocabularyReference']['vocabularyReferenceItem'] ) ) {
											$fachthemen = $module_item['repeatableGroup']['vocabularyReference']['vocabularyReferenceItem']['formattedValue'];
										}
									}
								} else {
									foreach ( $module_item['repeatableGroup']['vocabularyReference'] as $vocabulary_reference ) {
										if ( is_array( $vocabulary_reference['@attributes'] ) ) {
											if ( $vocabulary_reference['@attributes']['name'] === 'KeyWordVoc' ) {
												if ( is_array( $vocabulary_reference['vocabularyReference']['vocabularyReferenceItem'] ) ) {
													$fachthemen = $vocabulary_reference['vocabularyReference']['vocabularyReferenceItem']['formattedValue'];
												}
											}
										}
									}
								}
							}
						}
					} else {
						foreach ( $module_item['repeatableGroup'] as $repeatable_group ) {
							if ( is_array( $repeatable_group['@attributes'] ) ) {
								if ( $repeatable_group['@attributes']['name'] === 'ProKeyWordsGrp' ) {
									if ( is_array( $repeatable_group['vocabularyReference'] ) ) {
										if ( is_array( $repeatable_group['vocabularyReference']['@attributes'] ) ) {
											if ( $repeatable_group['vocabularyReference']['@attributes']['name'] === 'KeyWordVoc' ) {
												if ( is_array( $repeatable_group['vocabularyReference']['vocabularyReferenceItem'] ) ) {
													$fachthemen = $repeatable_group['vocabularyReference']['vocabularyReferenceItem']['formattedValue'];
												}
											}
										} else {
											foreach ( $repeatable_group['vocabularyReference'] as $vocabulary_reference ) {
												if ( is_array( $vocabulary_reference['@attributes'] ) ) {
													if ( $vocabulary_reference['@attributes']['name'] === 'KeyWordVoc' ) {
														if ( is_array( $vocabulary_reference['vocabularyReference']['vocabularyReferenceItem'] ) ) {
															$fachthemen = $vocabulary_reference['vocabularyReference']['vocabularyReferenceItem']['formattedValue'];
														}
													}
												}
											}
										}
									} else {
										if ( is_array( $repeatable_group['repeatableGroupItem'] ) ) {
											if ( is_array( $repeatable_group['repeatableGroupItem']['@attributes'] ) ) {
												if ( is_array( $repeatable_group['repeatableGroupItem']['vocabularyReference']['@attributes'] ) ) {
													if ( $repeatable_group['repeatableGroupItem']['vocabularyReference']['@attributes']['name'] === 'KeyWordVoc' ) {
														$fachthemen = $repeatable_group['repeatableGroupItem']['vocabularyReference']['vocabularyReferenceItem']['formattedValue'];
													}
												}
											} else {
												foreach ( $repeatable_group['repeatableGroupItem'] as $repeatable_group_item ) {
													if ( is_array( $repeatable_group_item['vocabularyReference'] ) ) {
														if ( is_array( $repeatable_group_item['vocabularyReference']['vocabularyReferenceItem'] ) ) {
															$fachthemen = $repeatable_group_item['vocabularyReference']['vocabularyReferenceItem']['formattedValue'];
														}
													}
												}
											}
										}
									}
								}
							}
						}
					}
				}

				if ( $fachthemen !== '' ) {
					$this->check_and_update(
						$post_metas,
						$fachthemen,
						$existing_post,
						'wp_birdlife_project_fachthemen'
					);
				}

				if ( ! empty( $module_item_arr_2['wp_birdlife_project_pro_status_current_voc'] ) ) {
					$this->check_and_update(
						$post_metas,
						$module_item_arr_2['wp_birdlife_project_pro_status_current_voc'],
						$existing_post,
						'wp_birdlife_project_pro_status_current_voc'
					);
				}
			}
		}

		private function update_project_metabox_values_from_api( $data_field, $existing_post, $post_metas ) {
			if ( ! empty( $data_field['@attributes']['name'] ) ) {
				$data_field_name = $data_field['@attributes']['name'];

				if ( $data_field_name === 'ProTitleTxt' ) {
					$this->check_and_update(
						$post_metas,
						$data_field['value'],
						$existing_post,
						'wp_birdlife_project_pro_title_txt'
					);

					$my_post = array(
						'ID'         => $existing_post->ID,
						'post_title' => $data_field['value'],
					);
					wp_update_post( $my_post );
				} else if ( $data_field_name === 'ProDescriptionClb' ) {
					$this->check_and_update(
						$post_metas,
						$data_field['value'],
						$existing_post,
						'wp_birdlife_project_pro_description_clb'
					);

					$my_post = array(
						'ID'           => $existing_post->ID,
						'post_content' => $data_field['value'],
					);
					wp_update_post( $my_post );
				} else if ( $data_field_name === 'ProContactDetailTxt' ) {
					$this->check_and_update(
						$post_metas,
						$data_field['value'],
						$existing_post,
						'wp_birdlife_project_pro_contact_detail_txt'
					);
				} else if ( $data_field_name === 'ProPlaceTxt' ) {
					$this->check_and_update(
						$post_metas,
						$data_field['value'],
						$existing_post,
						'wp_birdlife_project_pro_place_txt'
					);
				} else if ( $data_field_name === 'ProDateFromDat' ) {
					$this->check_and_update(
						$post_metas,
						$data_field['value'],
						$existing_post,
						'wp_birdlife_project_pro_date_from_dat'
					);
				} else if ( $data_field_name === 'ProDateToDat' ) {
					$this->check_and_update(
						$post_metas,
						$data_field['value'],
						$existing_post,
						'wp_birdlife_project_pro_date_to_dat'
					);
				} else if ( $data_field_name === 'ProKeyWordsGrp' ) {
					$this->check_and_update(
						$post_metas,
						$data_field['value'],
						$existing_post,
						'wp_birdlife_project_pro_key_words_grp'
					);
				}

			}
		}

		private function check_and_update(
			$post_metas,
			$value,
			$existing_post,
			$m_key
		) {
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

		private function get_manage_plus_api_attachment_args(): array {
			return array(
				'headers' => array(
					'Authorization' => 'Basic ' . base64_encode( MANAGE_PLUS_USERNAME . ':' . MANAGE_PLUS_PASSWORD ),
					'Accept'        => 'application/octet-stream'
				),
				'timeout' => 50
			);
		}
	}
}