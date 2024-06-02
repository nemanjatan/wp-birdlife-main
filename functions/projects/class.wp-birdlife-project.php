<?php

if ( ! class_exists( 'WP_Birdlife_Project' ) ) {
	class WP_Birdlife_Project {
		private function get_project_search_url() {
			return 'https://de1.zetcom-group.de/MpWeb-maZurichBirdlife/ria-ws/application/module/Project/search/';
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

		private function update_offset( $xml, $offset ) {
			return str_replace( "{{offset}}", $offset, $xml );
		}

		private function get_all_naturforderung_project_posts() {
			// get all naturforderung projects
			$args = array(
				'numberposts' => - 1,
				'post_type'   => 'naturforderung',
				'post_status' => array(
					'publish',
					'pending',
					'draft',
					'auto-draft',
					'future',
					'private',
					'inherit',
					'trash'
				)
			);

			return get_posts( $args );
		}

		private function get_number_of_projects( $helper, $url, $xml ) {
			$args = $helper->get_manage_plus_api_args( $xml );

			$resp      = wp_remote_post( $url, $args );
			$resp_body = $resp['body'];

			$parsed_xml  = simplexml_load_string( $resp_body );
			$json        = json_encode( $parsed_xml );
			$parsed_json = json_decode( $json, true );

			$total_size = $parsed_json['modules']['module']['@attributes']['totalSize'];

			return ( $total_size / 10 );
		}

		private function get_module_items( $helper, $xml, $offset, $url ) {
			$xml = $this->update_offset( $xml, $offset );

			$args = $helper->get_manage_plus_api_args( $xml );

			$resp = wp_remote_post( $url, $args );

			return $resp['body'];
		}

		private function get_projects_post_by_event_id( $project_id ) {
			$args = array(
				'meta_key'       => 'wp_birdlife_manage_plus_project_id',
				'meta_value'     => $project_id,
				'post_type'      => 'naturforderung',
				'post_status'    => array(
					'publish',
					'pending',
					'draft',
					'auto-draft',
					'future',
					'private',
					'inherit',
					'trash'
				),
				'posts_per_page' => - 1
			);

			return get_posts( $args );
		}

		public function fetch_all_projects(): void {
			$birdlife_update_project = new WP_Birdlife_Update_Project();

			$helper = new WP_Birdlife_Helper();
			$url    = $this->get_project_search_url();
			$xml    = file_get_contents( WP_BIRDLIFE_PATH . 'xml/project-search/project-search-all-fields.xml' );

			$number_of_iterations = $this->get_number_of_projects( $helper, $url, $xml );
			for ( $i = 0; $i <= $number_of_iterations; $i ++ ) {
				$offset = $i * 10;
				$xml    = file_get_contents( WP_BIRDLIFE_PATH . 'xml/project-search/project-search-specific-fields.xml' );

				$resp_body = $this->get_module_items( $helper, $xml, $offset, $url );

				$parsed_xml = simplexml_load_string( $resp_body );

				$json        = json_encode( $parsed_xml );
				$parsed_json = json_decode( $json, true );

				$module_items = $parsed_json['modules']['module']['moduleItem'];

				$formatted_arr = array();

				if ( $module_items == null ) {
					return;
				}

				foreach ( $module_items as $module_item ) {
					$post = $this->get_projects_post_by_event_id( $module_item['systemField'][0]['value'] );

					if ( is_array( $post ) ) {
						// update existing one
						if ( count( $post ) == 1 ) {
							// update project
							$birdlife_update_project->update_projects(
								$module_item,
								$post
							);
						} else {
							// save new project
							$skip                                                 = false;
							$module_item_arr                                      = array( 'id' => $module_item['systemField'][0]['value'] );
							$module_item_arr['wp_birdlife_project_last_modified'] = $module_item['systemField'][3]['value'];

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
											$module_item_arr = $helper->set_project_metabox_values_from_api( $data_field, $module_item_arr );
										}
									} else {
										$module_item_arr = $helper->set_project_metabox_values_from_api( $data_fields, $module_item_arr );
									}
								}

								$module_item_arr['wp_birdlife_project_featured_image_exists'] = 'no';
								if ( is_array( $module_item['moduleReference'] ) ) {
									foreach ( $module_item['moduleReference'] as $module_reference ) {
										if ( is_array( $module_reference['@attributes'] ) ) {
											$reference_name = $module_reference['@attributes']['name'];

											if ( $reference_name === 'ProMultimediaRef' ) {
												if ( is_array( $module_reference['moduleReferenceItem'] ) ) {
													if ( is_array( $module_reference['moduleReferenceItem']['@attributes'] ) ) {
														if ( $module_reference['moduleReferenceItem']['@attributes']['moduleItemId'] !== null ) {
															$module_item_arr['wp_birdlife_project_multimedia_ref'] = $module_reference['moduleReferenceItem']['@attributes']['moduleItemId'];

															// fetch the image
															$image_id                                                = $module_item_arr['wp_birdlife_project_multimedia_ref'];
															$thumbnail_url                                           = 'https://de1.zetcom-group.de/MpWeb-maZurichBirdlife/ria-ws/application/module/Multimedia/' . $image_id . '/attachment';
															$args                                                    = $this->get_manage_plus_api_attachment_args();
															$resp                                                    = wp_remote_get( $thumbnail_url, $args );
															$module_item_arr['wp_birdlife_project_multimedia_image'] = $resp['body'];

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
																			$module_item_arr['wp_birdlife_project_featured_image_photocredit_txt'] = $module_item['value'];
																		}
																	}
																}
															}
															// end of photocredits

															if ( $module_item_arr['wp_birdlife_project_multimedia_image'] !== null && $module_item_arr['wp_birdlife_project_multimedia_image'] !== "" ) {
																$encoded                                                      = base64_encode( $module_item_arr['wp_birdlife_project_multimedia_image'] );
																$module_item_arr['wp_birdlife_project_featured_image']        = $encoded;
																$module_item_arr['wp_birdlife_project_featured_image_exists'] = 'yes';
															}
														}
													}
												}
											}
										}
									}
								}

								if ( $module_item_arr['wp_birdlife_project_featured_image_exists'] === 'no' ) {
									$module_item_arr['wp_birdlife_project_featured_image'] = 'https://birdlife-zuerich.ch/wp-content/uploads/2022/11/default-img-project.png';
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
									$module_item_arr['wp_birdlife_project_fachthemen'] = $fachthemen;
								}

								if ( ! empty( $module_item_arr_2['wp_birdlife_project_pro_status_current_voc'] ) ) {
									$module_item_arr['wp_birdlife_project_pro_status_current_voc'] = $module_item_arr_2['wp_birdlife_project_pro_status_current_voc'];
								}

								$formatted_arr[] = $module_item_arr;
							}
						}
					}
				}

				foreach ( $formatted_arr as $item ) {
					if ( ! empty( $item['id'] ) ) {
						list( $meta_inputs, $post_title ) = $this->set_meta_keys( $item );

						if ( $meta_inputs['wp_birdlife_project_pro_description_clb'] === null ) {
							$meta_inputs['wp_birdlife_project_pro_description_clb'] = "";
						}

						$slug = $this->slugify( $post_title );

						wp_insert_post(
							array(
								'post_title'   => $post_title,
								'post_name'    => $slug,
								'post_type'    => 'naturforderung',
								'post_status'  => 'publish',
								'post_content' => $meta_inputs['wp_birdlife_project_pro_description_clb'],
								'meta_input'   => $meta_inputs
							)
						);
					}
				}

				// get all project posts
				$posts = get_posts(
					array(
						'post_type'    => 'naturforderung',
						'number_posts' => - 1,
						'post_status'  => 'any'
					)
				);

				foreach ( $posts as $post ) {
					// iterate and check for fachthema
					$wp_birdlife_project_fachthemen = get_post_meta( $post->ID, 'wp_birdlife_project_fachthemen', true );

					if ( $wp_birdlife_project_fachthemen === 'Feldlerchen' ) {
						wp_set_object_terms( $post->ID, 44, 'fachthemen' );
					} else if ( $wp_birdlife_project_fachthemen === 'Hecken' ) {
						wp_set_object_terms( $post->ID, 45, 'fachthemen' );
					} else if ( $wp_birdlife_project_fachthemen === 'Kiebitz' ) {
						wp_set_object_terms( $post->ID, 385, 'fachthemen' );
					} else if ( $wp_birdlife_project_fachthemen === 'Kleingewässe' || $wp_birdlife_project_fachthemen === 'Kleingewässer' ) {
						wp_set_object_terms( $post->ID, 384, 'fachthemen' );
					} else if ( $wp_birdlife_project_fachthemen === 'Segler und Schwalben' ) {
						wp_set_object_terms( $post->ID, 50, 'fachthemen' );
					} else if ( $wp_birdlife_project_fachthemen === 'Amphibien' ) {
						wp_set_object_terms( $post->ID, 386, 'fachthemen' );
					} else if ( $wp_birdlife_project_fachthemen === 'Orchideen' ) {
						wp_set_object_terms( $post->ID, 383, 'fachthemen' );
					} else if ( $wp_birdlife_project_fachthemen === 'Rebberge' ) {
						wp_set_object_terms( $post->ID, 387, 'fachthemen' );
					} else if ( $wp_birdlife_project_fachthemen === 'Übergang Wald-Kulturland' ) {
						wp_set_object_terms( $post->ID, 388, 'fachthemen' );
					}
				}
			}
		}

		private function slugify( $text, string $divider = '-' ) {
			// replace non letter or digits by divider
			$text = preg_replace( '~[^\pL\d]+~u', $divider, $text );

			// transliterate
			$text = iconv( 'utf-8', 'us-ascii//TRANSLIT', $text );

			// remove unwanted characters
			$text = preg_replace( '~[^-\w]+~', '', $text );

			// trim
			$text = trim( $text, $divider );

			// remove duplicate divider
			$text = preg_replace( '~-+~', $divider, $text );

			// lowercase
			$text = strtolower( $text );

			if ( empty( $text ) ) {
				return 'n-a';
			}

			return $text;
		}

		private function set_meta_keys( $item ): array {
			$project_id                                 = $item['id'];
			$wp_birdlife_project_pro_title_txt          = $item['wp_birdlife_project_pro_title_txt'];
			$wp_birdlife_project_pro_description_clb    = $item['wp_birdlife_project_pro_description_clb'];
			$wp_birdlife_project_pro_contact_detail_txt = $item['wp_birdlife_project_pro_contact_detail_txt'];
			$wp_birdlife_project_pro_place_txt          = $item['wp_birdlife_project_pro_place_txt'];
			$wp_birdlife_project_pro_date_from_dat      = $item['wp_birdlife_project_pro_date_from_dat'];
			$wp_birdlife_project_pro_date_to_dat        = $item['wp_birdlife_project_pro_date_to_dat'];
			$wp_birdlife_project_pro_key_words_grp      = $item['wp_birdlife_project_pro_key_words_grp'];
			$wp_birdlife_project_featured_image         = $item['wp_birdlife_project_featured_image'];
			$wp_birdlife_project_featured_image_photocredit_txt         = $item['wp_birdlife_project_featured_image_photocredit_txt'];

			$wp_birdlife_project_pro_status_current_voc = $item['wp_birdlife_project_pro_status_current_voc'];
			$wp_birdlife_project_fachthemen             = $item['wp_birdlife_project_fachthemen'];

			$meta_inputs = array( 'wp_birdlife_manage_plus_project_id' => $project_id );

			if ( ! empty( $wp_birdlife_project_pro_title_txt ) ) {
				$meta_inputs['wp_birdlife_project_pro_title_txt'] = $wp_birdlife_project_pro_title_txt;
			}

			if ( ! empty( $wp_birdlife_project_featured_image ) ) {
				$meta_inputs['wp_birdlife_project_featured_image']        = $wp_birdlife_project_featured_image;
				$meta_inputs['wp_birdlife_project_featured_image_exists'] = 'yes';
			} else {
				$meta_inputs['wp_birdlife_project_featured_image']        = 'https://birdlife-zuerich.ch/wp-content/uploads/2022/11/default-img-project.png';
				$meta_inputs['wp_birdlife_project_featured_image_exists'] = 'no';
			}

            if ( ! empty( $wp_birdlife_project_featured_image_photocredit_txt ) ) {
                $meta_inputs['wp_birdlife_project_featured_image_photocredit_txt'] = $wp_birdlife_project_featured_image_photocredit_txt;
            }

			if ( ! empty( $wp_birdlife_project_pro_description_clb ) ) {
				$meta_inputs['wp_birdlife_project_pro_description_clb'] = $wp_birdlife_project_pro_description_clb;
			}

			if ( ! empty( $wp_birdlife_project_pro_contact_detail_txt ) ) {
				$meta_inputs['wp_birdlife_project_pro_contact_detail_txt'] = $wp_birdlife_project_pro_contact_detail_txt;
			}

			if ( ! empty( $wp_birdlife_project_pro_place_txt ) ) {
				$meta_inputs['wp_birdlife_project_pro_place_txt'] = $wp_birdlife_project_pro_place_txt;
			}

			if ( ! empty( $wp_birdlife_project_pro_date_from_dat ) ) {
				$meta_inputs['wp_birdlife_project_pro_date_from_dat'] = $wp_birdlife_project_pro_date_from_dat;
			}

			if ( ! empty( $wp_birdlife_project_pro_date_to_dat ) ) {
				$meta_inputs['wp_birdlife_project_pro_date_to_dat'] = $wp_birdlife_project_pro_date_to_dat;
			}

			if ( ! empty( $wp_birdlife_project_pro_key_words_grp ) ) {
				$meta_inputs['wp_birdlife_project_pro_key_words_grp'] = $wp_birdlife_project_pro_key_words_grp;
			}

			if ( ! empty( $wp_birdlife_project_pro_status_current_voc ) ) {
				$meta_inputs['wp_birdlife_project_pro_status_current_voc'] = $wp_birdlife_project_pro_status_current_voc;
			}

			if ( ! empty( $wp_birdlife_project_fachthemen ) ) {
				$meta_inputs['wp_birdlife_project_fachthemen'] = $wp_birdlife_project_fachthemen;
			}

			return array( $meta_inputs, $wp_birdlife_project_pro_title_txt );
		}

		public function get_last_sync() {
			$wp_birdlife_cron_job_time = get_option( 'wp_birdlife_options_for_projects' );
			$wp_birdlife_last_sync     = get_option( 'wp_birdlife_last_sync_for_projects' );

			echo json_encode( array(
				'type'      => $wp_birdlife_cron_job_time['wp_birdlife_cron_job_time_for_projects'],
				'last_sync' => $wp_birdlife_last_sync
			), JSON_PRETTY_PRINT );
			wp_die();
		}

		public function hard_refresh_ajax_script() {
			?>
            <script type="text/javascript">
                jQuery(document).ready(function ($) {

                    $('#projects-hard-refresh-wp-ajax-button').click(function () {
                        var id = $('#projects-hard-refresh-ajax-option-id').val();
                        $.ajax({
                            method: "POST",
                            url: ajaxurl,
                            data: {'action': 'projects_hard_refresh_action', 'id': id}
                        })
                            .done(function () {
                                console.log('Successful Projects AJAX Call! /// Return Data: success');
                                document.getElementById("myBar").style.display = 'none';
                                var tag = document.createElement("p");
                                tag.style.textAlign = 'center';

                                var text = document.createTextNode("Done!");
                                tag.appendChild(text);

                                var element = document.getElementById("myProgress");
                                element.appendChild(tag);
                            })
                            .fail(function (data) {
                                console.log('Failed AJAX Call :( /// Return Data: ' + data);
                            });

                        var loadingTime = document.getElementById("wp_birdlife_projects_loading_time").value;
                        var dividedByTen = loadingTime / 10;
                        document.getElementById("myProgress").style.display = "block";

                        for (var y = 0; y < dividedByTen - 1; y++) {
                            (function (x) {
                                setTimeout(function () {
                                    console.log(x);
                                    var elem = document.getElementById("myBar");
                                    var width = (100 * x) / dividedByTen;
                                    if (width >= 100) {
                                        clearInterval(id);
                                        i = 0;
                                    } else {
                                        width++;
                                        elem.style.width = width + "%";
                                    }
                                }, x * 10000);
                            })(y);
                        }
                    });

                });
            </script>
			<?php
		}

		public function refresh_ajax_handler() {
			$WP_Birdlife_Project = new WP_Birdlife_Project();
			$start_time          = microtime( true );

			$WP_Birdlife_Project->fetch_all_projects();

			$end_time       = microtime( true );
			$execution_time = ( $end_time - $start_time );

			update_option( 'wp_birdlife_projects_loading_time', floor( $execution_time ) );

			update_option( 'wp_birdlife_last_sync_for_projects', time() );

			$data = 'success';
			echo json_encode( $data );
			wp_die();
		}
	}
}