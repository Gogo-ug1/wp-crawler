<?php

use WPClawler\Wp_Crawler;

defined( 'ABSPATH' ) or die( 'Access denied !' );

/**
 * Plugin's ajax routines
 *
 * @package wpc_crawler
 * @since 1.0.0
 * @author Gogo Ayesiga
 */
class Wpc_Ajax {


	/**
	 * register ajax methods
	 *
	 * @ since 1.0.0
	 */
	public function setup() {
		// register ajax
		// add_action( 'wp_ajax_save-stats',
		// array(
		// $this,
		// 'wpc_crawl_home_page'
		// ) );
		add_action( 'wp_ajax_wpc_crawl_home_page', [ $this, 'wpc_crawl_home_page' ] );

	}

	/**
	 * save stat sent by client when shared by user
	 *
	 * @ since 1.0.0
	 */


	public function wpc_crawl_home_page() {
		 $crawlpage = new Wp_Crawler();
		$crawlpage->wpc_crawl_home_page();
	}
}
