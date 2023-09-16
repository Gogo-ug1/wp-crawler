<?php

namespace WpCrawler;

use WPClawler\Wp_Crawler;


require_once ABSPATH . 'wp-admin/includes/upgrade.php';
require_once ABSPATH . 'wp-content/plugins/wp-crawler/inc/classes/class-wp-crawler.php';


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * General hook registry
 */
class Hook_Registry {

	/**
	 * Class constructor
	 */
	public function __construct() {
		$this->add_hooks();
	}

	/**
	 * Add all hooks
	 */
	public function add_hooks() {

		add_action( 'admin_menu', [ $this, 'wpc_crawl_home_page' ] );
		add_action( 'admin_menu', [ $this, 'wpc_add_scripts' ] );
		add_action( 'admin_menu', [ $this, 'wpc_add_wp_media_crawl_menu' ] );
		add_action( 'admin_menu', [ $this, 'wpc_sitemap_page' ] );
		add_action( 'admin_menu', [ $this, 'wpc_results_page' ] );
		add_action( 'wp_ajax_wpc_crawl_home_page', [ $this, 'wpc_crawl_home_page' ] );
		add_filter( 'the_content', [ $this, 'wpc_crawl_home_page' ] );

	}

	/**
	 * Hook function for wpc_crawl_home_page
	 */
	public function wpc_crawl_home_page() {
		$crawlpage = new Wp_Crawler();
		$crawlpage->wpc_crawl_home_page();
	}

	/**
	 * Hook function for wpc_add_scripts
	 */
	public function wpc_add_scripts() {
		$crawlpage = new Wp_Crawler();
		$crawlpage->wpc_add_scripts();
	}

	/**
	 * Hook function for wpc_add_wp_media_crawl_menu
	 */
	public function wpc_add_wp_media_crawl_menu() {
		$crawlpage = new Wp_Crawler();
		$crawlpage->wpc_add_wp_media_crawl_menu();
	}

	/**
	 * Hook function for wpc_sitemap_page
	 */
	public function wpc_sitemap_page() {
		$crawlpage = new Wp_Crawler();
		$crawlpage->wpc_sitemap_page();
	}

	/**
	 * Hook function for wpc_results_page
	 */
	public function wpc_results_page() {
		$crawlpage = new Wp_Crawler();
		$crawlpage->wpc_results_page();
	}



}

new Hook_Registry();
