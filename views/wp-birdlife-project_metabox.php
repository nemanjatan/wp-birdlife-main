<?php
  $post_id                                    = $post->ID;
  $manage_plus_project_id                     = get_post_meta( $post_id, 'wp_birdlife_manage_plus_project_id', true );
  $wp_birdlife_project_pro_title_txt          = get_post_meta( $post_id, 'wp_birdlife_project_pro_title_txt', true );
  $wp_birdlife_project_pro_description_clb    = get_post_meta( $post_id, 'wp_birdlife_project_pro_description_clb', true );
  $wp_birdlife_project_pro_contact_detail_txt = get_post_meta( $post_id, 'wp_birdlife_project_pro_contact_detail_txt', true );
  $wp_birdlife_project_pro_place_txt          = get_post_meta( $post_id, 'wp_birdlife_project_pro_place_txt', true );
  $wp_birdlife_project_pro_date_from_dat      = get_post_meta( $post_id, 'wp_birdlife_project_pro_date_from_dat', true );
  $wp_birdlife_project_pro_date_to_dat        = get_post_meta( $post_id, 'wp_birdlife_project_pro_date_to_dat', true );
  $wp_birdlife_project_pro_key_words_grp      = get_post_meta( $post_id, 'wp_birdlife_project_pro_key_words_grp', true );
  $wp_birdlife_project_pro_status_current_voc = get_post_meta( $post_id, 'wp_birdlife_project_pro_status_current_voc', true );
  $wp_birdlife_project_fachthemen             = get_post_meta( $post_id, 'wp_birdlife_project_fachthemen', true );
?>

<table class="form-table wp-birdlife-project-metabox" style="border: 1px solid">
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
            <p id="manage_plus_event_id"><?php echo ( isset ( $manage_plus_project_id ) ) ? esc_textarea( $manage_plus_project_id ) : ''; ?></p>
        </td>
    </tr>
    </hr>
    <tr>
        <td style="border: 1px solid">
            <p>
                <b>ProTitleTxt</b>
            </p>
        </td>
        <td style="border: 1px solid">
            <p id="wp_birdlife_event_registration_until_date">
              <?php echo ( isset ( $wp_birdlife_project_pro_title_txt ) ) ? esc_attr( $wp_birdlife_project_pro_title_txt ) : ''; ?>
            </p>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid">
            <p>
                <b>ProDescriptionClb</b>
            </p>
        </td style="border: 1px solid">
        <td>
            <p id="manage_plus_event_id"><?php echo ( isset ( $wp_birdlife_project_pro_description_clb ) ) ? esc_textarea( $wp_birdlife_project_pro_description_clb ) : ''; ?></p>
        </td>
    </tr>
    </hr>
    <tr>
        <td style="border: 1px solid">
            <p>
                <b>ProContactDetailTxt</b>
            </p>
        </td>
        <td style="border: 1px solid">
            <p id="wp_birdlife_event_registration_until_date">
              <?php echo ( isset ( $wp_birdlife_project_pro_contact_detail_txt ) ) ? esc_attr( $wp_birdlife_project_pro_contact_detail_txt ) : ''; ?>
            </p>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid">
            <p>
                <b>ProPlaceTxt</b>
            </p>
        </td style="border: 1px solid">
        <td>
            <p id="manage_plus_event_id"><?php echo ( isset ( $wp_birdlife_project_pro_place_txt ) ) ? esc_textarea( $wp_birdlife_project_pro_place_txt ) : ''; ?></p>
        </td>
    </tr>
    </hr>
    <tr>
        <td style="border: 1px solid">
            <p>
                <b>ProDateFromDat</b>
            </p>
        </td>
        <td style="border: 1px solid">
            <p id="wp_birdlife_event_registration_until_date">
              <?php echo ( isset ( $wp_birdlife_project_pro_date_from_dat ) ) ? esc_attr( $wp_birdlife_project_pro_date_from_dat ) : ''; ?>
            </p>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid">
            <p>
                <b>ProDateToDat</b>
            </p>
        </td style="border: 1px solid">
        <td>
            <p id="manage_plus_event_id"><?php echo ( isset ( $wp_birdlife_project_pro_date_to_dat ) ) ? esc_textarea( $wp_birdlife_project_pro_date_to_dat ) : ''; ?></p>
        </td>
    </tr>
    </hr>
    <tr>
        <td style="border: 1px solid">
            <p>
                <b>ProKeyWordsGrp</b>
            </p>
        </td>
        <td style="border: 1px solid">
            <p id="wp_birdlife_event_registration_until_date">
              <?php echo ( isset ( $wp_birdlife_project_pro_key_words_grp ) ) ? esc_attr( $wp_birdlife_project_pro_key_words_grp ) : ''; ?>
            </p>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid">
            <p>
                <b>ProStatusCurrentVoc</b>
            </p>
        </td>
        <td style="border: 1px solid">
            <p id="wp_birdlife_project_pro_status_current_voc">
              <?php echo ( isset ( $wp_birdlife_project_pro_status_current_voc ) ) ? esc_attr( $wp_birdlife_project_pro_status_current_voc ) : ''; ?>
            </p>
        </td>
    </tr>
    <tr>
        <td style="border: 1px solid">
            <p>
                <b>Fachthemen</b>
            </p>
        </td>
        <td style="border: 1px solid">
            <p id="wp_birdlife_project_fachthemen">
              <?php echo ( isset ( $wp_birdlife_project_fachthemen ) ) ? esc_attr( $wp_birdlife_project_fachthemen ) : ''; ?>
            </p>
        </td>
    </tr>
</table>