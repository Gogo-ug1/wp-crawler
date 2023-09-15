<?php
/**
Plugin Name:  WP Crawler
Plugin URI:   https://www.none.com
Description:  Crawls Home Page and adds internal links to sitemap file.
Version:      1.0
Author:       Gorret Ayesiga
Author URI:   https://www.none.com
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  wpc
Domain Path:  /languages
 */

defined( 'ABSPATH' ) || die( 'Unauthorized access!' );


/**
 *
 * @var define constants
 */
define( 'WPSP_NAME', 'wp-media' );


define( 'WPSP_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );


require_once ABSPATH . 'wp-content/plugins/wp-crawler/inc/class-hook-registry.php';
// require_once plugin_dir_path( __FILE__ ) . '/inc/class-hook-registry.php';
// require_once WPSP_URL . '/inc/class-hook-registry.php';

require_once ABSPATH . 'wp-admin/includes/upgrade.php';


/**
 * After installation call.
 * Creates a new table  crawl_results that will be saving crawl results.
 * It also creates a cron task to be running hourly.
 */
function wpc_options_install() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'wpc_crawl_results';

	// Create the crawl_results database table.
	if ( $wpdb->get_var( "show tables like '%s'", $table_name ) !== $table_name ) {
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
  id mediumint(9) NOT NULL AUTO_INCREMENT,
  link text DEFAULT '' NOT NULL,
  date_created DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY  (id)
) $charset_collate;";

		dbDelta( $sql );
	}



}

function wpc_remove() {
	// Delete database table.
	global $wpdb;
	$table_name = $wpdb->prefix . 'wpc_crawl_results';

	$sql = "DROP TABLE IF EXISTS $table_name";
	$wpdb->query($sql);

	// Clear the custom events.
	wp_clear_scheduled_hook( 'wpc_cron_hook' );

	// Re-active WP sitemaps.
	//add_filter( 'wp_sitemaps_enabled', '__return_true' );
}




register_activation_hook( __FILE__, 'wpc_options_install' );
register_deactivation_hook( __FILE__, 'wpc_remove' );

//add_action( 'wpc_cron_hook', 'wpc_cron_exec' );
