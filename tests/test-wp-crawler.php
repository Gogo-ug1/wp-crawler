<?php


use WpCrawler\Hook_Registry;

class Test_Wp_Crawler extends WP_UnitTestCase{

	 var $wpc_hook;
	 var $wpc;



	 public function setup ():void {
		 parent::setup();

		 require_once  ABSPATH . 'wp-content/plugins/wp-crawler/inc/class-hook-registry.php';
		 $this->wpc_hook = new Hook_Registry();

		 require_once WP_PLUGIN_DIR.'/wp-crawler/inc/classes/class-wp-crawler.php';
		 $this->wpc = new \WPClawler\Wp_Crawler();

		 wp_set_current_user( 1 );
		 $this->set_admin_role( true );
	 }


	 public function test_wpsp_option () {
		 $this->assertTrue(get_option('wpsp_test'));
	 }



	 public function teardown ():void {
		 parent::teardown();
		 unload_textdomain( 'wpc' );

		 global $submenu;
		 if ( isset( $submenu[ 'edit.php?post_type=wpc' ] ) ) {
			 unset( $submenu[ 'edit.php?post_type=wpc' ] );
		 }

		 global $wp_settings_fields;
		 if ( isset( $wp_settings_fields[ 'wpc_common_options' ] ) ) {
			 unset( $wp_settings_fields[ 'wpc_common_options' ] );
		 }
	 }

	 public function test_setup () {


//		 add_filter( 'the_content', [ $this, 'wpc_crawl_home_page' ]);
		 $this->assertFalse(
			 has_action( 'admin_menu', $this->wpc_hook,'wpc_crawl_home_page') );

		 $this->assertFalse(
			 has_action( 'admin_menu', $this->wpc_hook,'wpc_add_wp_media_crawl_menu') );

		 $this->assertFalse(
			 has_action( 'admin_menu', $this->wpc_hook,'wpc_sitemap_page') );

		 $this->assertFalse(
			 has_action( 'admin_menu', $this->wpc_hook,'wpc_results_page') );

		 $this->assertFalse(
			 has_action( 'the_content', $this->wpc_hook,'wpc_crawl_home_page') );

		 $this->wpc_hook->add_hooks();

		 $this->assertTrue(
			 $this->has_action( 'admin_menu', $this->wpc_hook, 'wpc_crawl_home_page') );

		 $this->assertTrue(
			 $this->has_action( 'admin_menu', $this->wpc_hook,'wpc_add_wp_media_crawl_menu') );

		 $this->assertTrue(
			 $this->has_action( 'admin_menu', $this->wpc_hook,'wpc_sitemap_page') );

		 $this->assertTrue(
			 $this->has_action( 'admin_menu', $this->wpc_hook,'wpc_results_page') );
		 $this->assertTrue(
			 $this->has_filter( 'the_content', $this->wpc_hook,'wpc_crawl_home_page') );

	 }

	static function set_admin_role ( $enable ) {
		global $current_user;
		if ( $enable ) {
			$current_user->add_role( 'administrator' );
			$current_user->get_role_caps();
		} else {
			$current_user->remove_role( 'administrator' );
			$current_user->get_role_caps();
		}
	}


	static public function has_action ( $action, $obj, $function ) {
		$registered = has_action( $action,
			array(
				$obj,
				$function
			) );
		if ( $registered ) {
			return true;
		} else {
			return false;
		}
	}

	// wrapper for wp has_filter()
	static public function has_filter ( $filter, $obj, $function ) {
		$registered = has_filter( $filter,
			array(
				$obj,
				$function
			) );
		if ( $registered ) {
			return true;
		} else {
			return false;
		}
	}


}

