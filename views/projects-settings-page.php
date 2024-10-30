<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
  <?php
    $active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'automatic_sync_settings';
  ?>
    <h2 class="nav-tab-wrapper">
        <a href="?page=wp_birdlife_projects_admin&tab=automatic_sync_settings"
           class="nav-tab <?php echo $active_tab == 'automatic_sync_settings' ? 'nav-tab-active' : ''; ?>">Automatic
            sync</a>
        <a href="?page=wp_birdlife_projects_admin&tab=hard_refresh_settings"
           class="nav-tab <?php echo $active_tab == 'hard_refresh_settings' ? 'nav-tab-active' : ''; ?>">Manual sync</a>
    </h2>
  <?php
    if ( $active_tab == 'automatic_sync_settings' ) {
      echo '<form action="options.php" method="post">';
      settings_fields( 'wp_birdlife_group' );
      do_settings_sections( 'wp_birdlife_page3' );
      submit_button( 'Save Settings' );
      echo '</form>';
    } else {
      $wp_birdlife_projects_loading_time = get_option( 'wp_birdlife_projects_loading_time' );
      if ( $wp_birdlife_projects_loading_time !== null ) {
        echo '<input type="hidden" id="wp_birdlife_projects_loading_time" value="' . $wp_birdlife_projects_loading_time . '" />';
      } else {
        $wp_birdlife_projects_loading_time = 190;
      }
      echo '<div id="myProgress" style="width: 100%; background-color: #f0f0f1; border: 1px solid #c3c4c7; margin: 5px; display: none;"> <div id="myBar" style="width: 0%; height: 30px; background-color: #59981A;"> </div> </div>';
      $html = '<div class="wrap">';
      $html .= '<button id="projects-hard-refresh-wp-ajax-button" class="button button-primary">Sync</button>';
      $html .= '</div>';
      echo $html;
    }
  ?>
</div>
