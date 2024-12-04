<?php
  if ( ! defined( 'ABSPATH' ) ) {
    exit;
  }
  
  if ( ! function_exists( 'start_updating_events' ) ) {
    function start_updating_events() {
      error_log( "Cron job started at " . date( 'Y-m-d H:i:s' ) );
      
      require_once __DIR__ . '/functions/events/class.wp-birdlife-event.php';
      require_once __DIR__ . '/functions/events/class.wp-birdlife-helper.php';
      require_once __DIR__ . '/functions/events/new-events/class.wp-birdlife-new-event.php';
      require_once __DIR__ . '/functions/events/update-events/class.wp-birdlife-update-event.php';
      
      echo "Starting WP Birdlife Event Sync...\n";
      
      $birdlife_event = new WP_Birdlife_Event();
      $start_time     = microtime( true );
      $birdlife_event->process_all_events();
      $end_time = microtime( true );
      
      $execution_time = round( $end_time - $start_time, 2 );
      
      echo "WP Birdlife Event Sync Completed in {$execution_time} seconds.\n";
      
      error_log( "Cron job executed at " . date( 'Y-m-d H:i:s' ) );
      
      return array( 'message' => 'Cron task executed successfully at ' . date( 'Y-m-d H:i:s' ) );
    }
  }
  
  add_action( 'rest_api_init', function () {
    error_log( "Registering REST API route for cron task." );
    register_rest_route( 'wp-birdlife/v1', '/sync', array(
      'methods'  => 'GET',
      'callback' => 'start_updating_events',
    ) );
  } );
