<?php
  
  if ( ! class_exists( 'WP_Birdlife_Free_Seats' ) ) {
    class WP_Birdlife_Free_Seats {
      public function get_free_seats( $module_item ) {
        $wp_birdlife_event_confirmed_tn = 0;
        if ( is_array( $module_item['repeatableGroup'] ) ) {
          if ( $module_item['repeatableGroup']['@attributes'] !== null ) {
            if ( $module_item['repeatableGroup']['@attributes']['name'] !== null ) {
              if ( $module_item['repeatableGroup']['@attributes']['name'] === 'EvtParticipantGrp' ) {
                if ( is_array( $module_item['repeatableGroup']['repeatableGroupItem'] ) ) {
                  foreach ( $module_item['repeatableGroup']['repeatableGroupItem'] as $repeatable_group_item ) {
                    if ( $repeatable_group_item['vocabularyReference'] !== null ) {
                      if ( is_array( $repeatable_group_item['vocabularyReference'] ) ) {
                        foreach ( $repeatable_group_item['vocabularyReference'] as $vocabulary_reference ) {
                          if ( $vocabulary_reference['@attributes']['name'] === 'StatusVoc' ) {
                            if ( $vocabulary_reference['vocabularyReferenceItem']['@attributes'] !== null ) {
                              if ( $vocabulary_reference['vocabularyReferenceItem']['@attributes']['name'] === 'TeilnehmerIn' ) {
                                $wp_birdlife_event_confirmed_tn = $wp_birdlife_event_confirmed_tn + 1;
                              }
                            } else if ( $vocabulary_reference['vocabularyReferenceItem']['formattedValue'] === 'Teilnehmer*in' ) {
                              $wp_birdlife_event_confirmed_tn = $wp_birdlife_event_confirmed_tn + 1;
                            }
                          }
                        }
                      }
                    }
                  }
                } else {
                  if ( is_array( $module_item['repeatableGroup']['repeatableGroupItem']['vocabularyReference'] ) ) {
                    foreach ( $module_item['repeatableGroup']['repeatableGroupItem']['vocabularyReference'] as $vocabulary_reference ) {
                      if ( $vocabulary_reference['@attributes']['name'] === 'StatusVoc' ) {
                        if ( $vocabulary_reference['vocabularyReferenceItem']['@attributes'] !== null ) {
                          if ( $vocabulary_reference['vocabularyReferenceItem']['@attributes']['name'] === 'TeilnehmerIn' ) {
                            $wp_birdlife_event_confirmed_tn = $wp_birdlife_event_confirmed_tn + 1;
                          }
                        } else if ( $vocabulary_reference['vocabularyReferenceItem']['formattedValue'] === 'Teilnehmer*in' ) {
                          $wp_birdlife_event_confirmed_tn = $wp_birdlife_event_confirmed_tn + 1;
                        }
                      }
                    }
                  }
                }
              }
            }
          } else {
            foreach ( $module_item['repeatableGroup'] as $repeatable_group ) {
              if ( $repeatable_group['@attributes'] !== null ) {
                if ( $repeatable_group['@attributes']['name'] !== null ) {
                  if ( $repeatable_group['@attributes']['name'] === 'EvtParticipantGrp' ) {
                    if ( is_array( $repeatable_group['repeatableGroupItem'] ) ) {
                      foreach ( $repeatable_group['repeatableGroupItem'] as $repeatable_group_item ) {
                        if ( $repeatable_group_item['vocabularyReference'] !== null ) {
                          if ( is_array( $repeatable_group_item['vocabularyReference'] ) ) {
                            if ( $repeatable_group_item['vocabularyReference']['@attributes'] !== null ) {
                              if ( $repeatable_group_item['vocabularyReference']['@attributes']['name'] === 'StatusVoc' ) {
                                if ( $repeatable_group_item['vocabularyReference']['vocabularyReferenceItem']['@attributes'] !== null ) {
                                  if ( $repeatable_group_item['vocabularyReference']['vocabularyReferenceItem']['@attributes']['name'] === 'TeilnehmerIn' ) {
                                    $wp_birdlife_event_confirmed_tn = $wp_birdlife_event_confirmed_tn + 1;
                                  }
                                } else if ( $repeatable_group_item['vocabularyReference']['vocabularyReferenceItem']['formattedValue'] === 'Teilnehmer*in' ) {
                                  $wp_birdlife_event_confirmed_tn = $wp_birdlife_event_confirmed_tn + 1;
                                }
                              }
                            } else {
                              foreach ( $repeatable_group_item['vocabularyReference'] as $vocabulary_reference ) {
                                if ( $vocabulary_reference['@attributes']['name'] === 'StatusVoc' ) {
                                  if ( $vocabulary_reference['vocabularyReferenceItem']['@attributes'] !== null ) {
                                    if ( $vocabulary_reference['vocabularyReferenceItem']['@attributes']['name'] === 'TeilnehmerIn' ) {
                                      $wp_birdlife_event_confirmed_tn = $wp_birdlife_event_confirmed_tn + 1;
                                    }
                                  } else if ( $vocabulary_reference['vocabularyReferenceItem']['formattedValue'] === 'Teilnehmer*in' ) {
                                    $wp_birdlife_event_confirmed_tn = $wp_birdlife_event_confirmed_tn + 1;
                                  }
                                }
                              }
                            }
                          }
                        }
                      }
                    } else {
                      if ( is_array( $repeatable_group['repeatableGroupItem']['vocabularyReference'] ) ) {
                        foreach ( $repeatable_group['repeatableGroupItem']['vocabularyReference'] as $vocabulary_reference ) {
                          if ( $vocabulary_reference['@attributes']['name'] === 'StatusVoc' ) {
                            if ( $vocabulary_reference['vocabularyReferenceItem']['@attributes'] !== null ) {
                              if ( $vocabulary_reference['vocabularyReferenceItem']['@attributes']['name'] === 'TeilnehmerIn' ) {
                                $wp_birdlife_event_confirmed_tn = $wp_birdlife_event_confirmed_tn + 1;
                              }
                            } else if ( $vocabulary_reference['vocabularyReferenceItem']['formattedValue'] === 'Teilnehmer*in' ) {
                              $wp_birdlife_event_confirmed_tn = $wp_birdlife_event_confirmed_tn + 1;
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
        
        return $wp_birdlife_event_confirmed_tn;
      }
    }
  }