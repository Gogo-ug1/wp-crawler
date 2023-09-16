<?php

use WpCrawler\Hook_Registry;

/**
 *  !!!! ONLY FOR AJAX CALL !!!!
 *
 * Tests for the Ajax calls to save and get wpc stats.
 * - runTestsInSeparateProcesses
 *     For speed, non ajax calls of class-ajax.php are tested in test-ajax-others.php
 *     Ajax tests are not marked risky when run in separate processes and wp_debug
 *     disabled. But, this makes tests slow so non ajax calls are kept separate
 * - preserveGlobalState disabled
 *     if enabled, exception - Serialization of Closure is not allowed - is thrown
 *
 * @group ajax
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */

class Test_WP_Ajax extends WP_Ajax_UnitTestCase {

	var $wpc_ajax;

	public function setup (): void {
		parent::setup();


		require_once  ABSPATH . 'wp-content/plugins/wp-crawler/inc/classes/class-ajax.php';

		$this->wpc_ajax = new Wpc_Ajax();

		wp_set_current_user( 1 );
	}

	public function teardown (): void {
		parent::teardown();
		unload_textdomain( 'wpc' );
	}


	public function test_crawl_page_no_nonce () {
		global $_POST;

		try {
			$this->wpc_ajax->setup();
			$this->_handleAjax( 'wpc_crawl_home_page' );
		} catch (WPAjaxDieStopException $e) {

		}
//
		//Enable wp_die() in wp-crawler class
//		$this->assertTrue( isset( $e ) );
//		$this->assertEquals( '1', $e->getMessage() );
		$this->assertTrue(get_option('wpsp_test'));

	}



}
