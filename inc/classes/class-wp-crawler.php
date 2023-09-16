<?php
namespace WPClawler;

use DOMDocument;
use Exception;

defined( 'ABSPATH' ) || exit;
require_once ABSPATH . 'wp-admin/includes/upgrade.php';
/**
 * Manages crawl and retrieval.
 *
 * @since 3.0
 * @author Gogo Ayesiga
 */
class Wp_Crawler {



	/**
	 * Crawl Home page function.
	 *
	 * Picks home url from get_home_url function and crawls to get only internal urls.
	 * If results are found , it saves them in the crawl_results table.
	 * Using curl to get home page details.
	 */
	public function wpc_crawl_home_page() {
		// $fetchurl  = 'https://servicecops.com';.
		$fetchurl  = get_home_url();
		$urls_list = [];
		try {

			// Using curl to extract page contents.
			$ch      = curl_init();
			$timeout = 5;
			curl_setopt( $ch, CURLOPT_URL, $fetchurl );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, $timeout );
			$url_content = curl_exec( $ch );
			curl_close( $ch );

			$dom = new DOMDocument();
			libxml_use_internal_errors( true );

			$dom->loadHTML( $url_content );
			libxml_clear_errors();

			if ( false === $url_content ) {
				$urls_list = 'No URL details found';
			}

			$unique_links = [];

			// Get all links on this page.
			$hrefs = $dom->getElementsByTagName( 'a' );

			// Iterate over the extracted links and display their URLs.
			foreach ( $hrefs as $link ) {

				$url = $link->getAttribute( 'href' );

				$url = filter_var( $url, FILTER_SANITIZE_URL );

				// Fetch the anchor tag text in case if extracting href attribute.
				if ( ! filter_var( $url, FILTER_VALIDATE_URL ) === false ) {
					$host_url  = wp_parse_url( $url );
					$fetch_url = wp_parse_url( $fetchurl );

					if ( $host_url['host'] === $fetch_url['host'] ) {
						if ( ! array_key_exists( $url, $unique_links ) ) {
							$unique_links[ $url ] = true;

							array_push( $urls_list, $url );

						}
					}
				}
			}

			$this->wpc_process_results( $urls_list );
			// Uncomment for unit testing.
			// wp_die(1);.

		} catch ( Exception $e ) {

			$error = $e->getMessage();
		}
	}

	/**
	 * Save Crawl Results.
	 *
	 * If results are found from the crawl site function.
	 * Function checks if the crawl_results table is not empty, deletes contents and saves new results.
	 * Creates a sitemap.html file if it doesnt exist else deletes sitemap if exists and creates a new one.
	 * The results are also written the sitemap file in html.
	 *
	 * @param Array $url_lists list of crawled sites.
	 */
	public function wpc_process_results( $url_lists ) {

		global $wpdb;
		$wpdb->show_errors();

		// Sitemap definitions.
		$wpc_website_root = get_home_path();
		$wpc_sitemap_file = $wpc_website_root . 'sitemap.html';

		$table_name = $wpdb->prefix . 'wpc_crawl_results';
		$result     = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM %i where id > %d', $table_name, 1 ) );

		if ( $result > 0 ) {
			$wpdb->query( 'TRUNCATE TABLE ' . $table_name );

		}

		// Save results to database table.
		foreach ( $url_lists as $list ) {
			$wpdb->insert(
				$table_name,
				[
					'link' => $list,
				]
			);

		}

		// delete sitemap if exists.
		if ( file_exists( $wpc_sitemap_file ) ) {
			unlink( $wpc_sitemap_file );
		}

		$sitemap_list = $this->retrieve_results();

		// Check if file exists and is writable first.
		$fp = fopen( $wpc_sitemap_file, 'a' );
		if ( ! $fp ) {
			exit;
		}

		// Write crawl results to our sitemap file.
		if ( fwrite( $fp, $sitemap_list ) === false ) {
			exit;
		}

		fclose( $fp );

		if ( ! wp_next_scheduled( 'wpc_cron_hook' ) ) {
			wp_schedule_event( time(), 'hourly', 'wpc_cron_hook' );
		}
	}

	/**
	 * Html Page for Crawl site menu
	 */
	public function wpc_crawl_page_html() {

		?>
		<div id="wpbody" role="main">

			<div id="wpbody-content">
				<div class="row content_wp">
					<div class="col-md-12">
						<h1> WP MEDIA - HOME PAGE CRAWLER PLUGIN</h1>
						<p>Click button below to crawl your site</p>
						<form>
							<p>
								<input type="submit" id="wpc_crawler"/>
							</p>
						</form>
					</div>
					<div class="col-md-12" id="results_div">
						<div class="col-md-12">
							<div class="col-md-12 loader3" id="loader_wpc" ></div>
							<h2><br><br><br></h2>
							<h2>My Crawl Results</h2>
							<div class="col-md-12" id="crawl_results"></div>
							<div class="clear"></div>

						</div>
					</div>


				</div>
				<div class="clear"></div></div><!-- wpbody-content -->
			<div class="clear"></div></div>


		<?php
	}

	/**
	 *  Function to display sitemap page
	 */
	public function wpc_sitemap_page_html() {

		// Check user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div id="wpbody" role="main">

			<div id="wpbody-content">
				<div class="row content_wp">
					<div class="col-md-12">
						<div class="wrap">
							<h4>Sitemap Details</h4>
							<a href="<?php echo esc_html( get_home_url() ); ?>/sitemap.html" target="_blank"><input type="button" value="View Site Map" /></a>

						</div>
					</div>
				</div>
			</div>
		</div>

		<?php
	}

	/**
	 * Html Page for Crawl results.
	 */
	public function wpc_crawl_results_page_html() {
		// Check user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		global $wpdb;
		$wpdb->show_errors();

		$table_name = $wpdb->prefix . 'wpc_crawl_results';

		$result_count = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM %i where id > %d', $table_name, 1 ) );
		$result       = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %i where id > %d', $table_name, 1 ) );

		?>

		<div id="wpbody" role="main">

			<div id="wpbody-content">
				<div class="row content_wp">
					<div class="col-md-12">
						<h1>Crawl Results (Links on your home page)</h1>
						<?php if ( $result_count > 0 ) { ?>

						<table style="width:100%;">
							<tr class='wpc_table'>
								<th class='wpc_table' colspan="5">URL</th>
								<th class='wpc_table' >Last Crawled</th>
							</tr>
							<?php
							foreach ( $result as $single_result ) {
								?>
								<tr class='wpc_table'>
									<td class='wpc_table' colspan="5"><a href ='<?php echo esc_html( $single_result->link ); ?>' target='_blank'><?php echo esc_html( $single_result->link ); ?></a></td>
									<td class='wpc_table'><?php echo esc_html( $single_result->date_created ); ?></td>
								</tr>

								<?php
							}
						} else {
							?>
								<tr><td>No Home Links Found</td></tr>
							<?php } ?>
						</table>

					</div>
				</div>
				<div class="clear"></div></div><!-- wpbody-content -->
			<div class="clear"></div></div>

		<?php
	}



	/**
	 * Admin Dashboard Creation
	 * Function view crawl, called when a page admin clicks view results
	 */
	public function wpc_add_wp_media_crawl_menu() {
		add_menu_page(
			'Dashboard',
			'WPMedia Crawler',
			'manage_options',
			'wpccrawler',
			[ $this, 'wpc_crawl_page_html' ],
			'dashicons-list-view',
			40
		);
	}


	/**
	 * Creating Admin page Crawl Site submenu.
	 */
	public function wpc_crawl_page() {
		add_submenu_page(
			'wpccrawler',
			'Crawl Site',
			'Crawl Site',
			'manage_options',
			'wpccrawler',
			[ $this, 'wpc_crawl_page_html' ]
		);
	}


	/**
	 * Creating Admin page Crawl Results submenu.
	 */
	public function wpc_results_page() {
		add_submenu_page(
			'wpccrawler',
			'Crawl Results',
			'Crawl Results',
			'manage_options',
			'wpcresult',
			[ $this, 'wpc_crawl_results_page_html' ]
		);
	}

	/**
	 * Creating Admin page Sitemap submenu.
	 */
	public function wpc_sitemap_page() {
		add_submenu_page(
			'wpccrawler',
			'Sitemap ',
			'Sitemap',
			'manage_options',
			'wpc_sitemap',
			[ $this, 'wpc_sitemap_page_html' ]
		);
	}



	/**
	 * Activating plugin
	 */
	public function wpc_activate() {
		add_option( 'Activated_Plugin', 'Plugin-Slug' );

	}

	/**
	 * Adds CSS and JS files
	 */
	public function wpc_add_scripts() {

		wp_enqueue_style( 'wpc_css', '/wp-content/plugins/wp-crawler/app/assets/css/loader.css', '1.0.0', 'all' );
		wp_enqueue_script( 'wpc_script', '/wp-content/plugins/wp-crawler/app/assets/js/crawl_button.js', [ 'jquery' ], '1.0.0', 'all' );
		wp_localize_script(
			'wpc_script',
			'ajax_value',
			[
				'ajax_url' => admin_url( 'admin-ajax.php' ),

			]
		);
	}

	/**
	 * Returns result data saved in the crawl table
	 */
	public function retrieve_results() {
		global $wpdb;
		$wpdb->show_errors();

		$table_name = $wpdb->prefix . 'wpc_crawl_results';

		$result = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %i where id > %d', $table_name, 1 ) );

		$textlink = '';

		$header_test = "<div id='wpbody' role='main'>

			<div id='wpbody-content'>
				<div class='row content_wp'>
					<div class='col-md-12'>
						<h4>Home Page Sitemap</h4>

							<table style='width:100%;' class='wpc_table'>
							<thead>
							<tr class='wpc_table'>
								<th colspan='6' style='float: left'>URL</th>
								<th colspan='5' style='text-align:left'>Last Crawled</th>
							</tr>
							</thead>
							<tbody>";

		foreach ( $result as $single_result ) {
			$textlink .= "<tr class='wpc_table'><td class='wpc_table' colspan='5'><a href ='$single_result->link ' target='_blank'> $single_result->link </a></td>
<td class='wpc_table'> $single_result->date_created </td></tr>
";

		}
		$text_footer = "</tbody>
						</table>
					</div>
				</div>
				<div class='clear'></div></div><!-- wpbody-content -->
			<div class='clear'></div></div>";

		$final_text = $header_test . $textlink . $text_footer;
		return $final_text;
	}

}
