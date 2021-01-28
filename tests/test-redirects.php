<?php
/**
 * Class RedirectsTest
 *
 * @package WCZoom
 */

use SeattleWebCo\WCZoom\Helpers;
use SeattleWebCo\WCZoom\Admin\Modules\Redirects;
 
/**
 * Redirects test case.
 */
class RedirectsTest extends WP_UnitTestCase {

	public static function setUpBeforeclass() {
		global $wpdb;

		$wpdb->query( "TRUNCATE {$wpdb->prefix}redirection_items" );

		$wp_redirection_items = [
			[
				"url" => "/test",
				"match_url" => "/test",
				"match_data" => NULL,
				"regex" => 0,
				"position" => 0,
				"last_count" => 0,
				"last_access" => "0000-00-00 00:00:00",
				"group_id" => 1,
				"status" => "enabled",
				"action_type" => "url",
				"action_code" => 301,
				"action_data" => "/target",
				"match_type" => "url",
				"title" => "",
			],
			[
				"url" => "/test2",
				"match_url" => "/test2",
				"match_data" => NULL,
				"regex" => 0,
				"position" => 0,
				"last_count" => 0,
				"last_access" => "0000-00-00 00:00:00",
				"group_id" => 1,
				"status" => "enabled",
				"action_type" => "url",
				"action_code" => 301,
				"action_data" => "/target2/",
				"match_type" => "url",
				"title" => "",
			],
			[
				"url" => "/blah",
				"match_url" => "regex",
				"match_data" => "{\"source\":{\"flag_case\":true,\"flag_trailing\":true,\"flag_regex\":true}}",
				"regex" => 1,
				"position" => 1,
				"last_count" => 0,
				"last_access" => "0000-00-00 00:00:00",
				"group_id" => 1,
				"status" => "enabled",
				"action_type" => "url",
				"action_code" => 301,
				"action_data" => "/hebada",
				"match_type" => "url",
				"title" => "",
			],
			[
				"url" => "^/post?123",
				"match_url" => "regex",
				"match_data" => "{\"source\":{\"flag_case\":true,\"flag_trailing\":true,\"flag_regex\":true}}",
				"regex" => 1,
				"position" => 2,
				"last_count" => 0,
				"last_access" => "0000-00-00 00:00:00",
				"group_id" => 1,
				"status" => "enabled",
				"action_type" => "url",
				"action_code" => 301,
				"action_data" => "/post-2",
				"match_type" => "url",
				"title" => "",
			],
			[
				"url" => "/one-post",
				"match_url" => "/one-post",
				"match_data" => "{\"source\":{\"flag_query\":\"ignore\",\"flag_case\":true,\"flag_trailing\":true}}",
				"regex" => 0,
				"position" => 3,
				"last_count" => 0,
				"last_access" => "0000-00-00 00:00:00",
				"group_id" => 1,
				"status" => "enabled",
				"action_type" => "url",
				"action_code" => 301,
				"action_data" => "/123",
				"match_type" => "url",
				"title" => "",
			],
			[
				"url" => "/33",
				"match_url" => "/33",
				"match_data" => "{\"source\":{\"flag_query\":\"ignore\",\"flag_case\":true,\"flag_trailing\":true}}",
				"regex" => 0,
				"position" => 3,
				"last_count" => 0,
				"last_access" => "0000-00-00 00:00:00",
				"group_id" => 1,
				"status" => "enabled",
				"action_type" => "url",
				"action_code" => 301,
				"action_data" => "https://google.com",
				"match_type" => "url",
				"title" => "",
			],
			[
				"url" => "/john",
				"match_url" => "/john",
				"match_data" => "{\"source\":{\"flag_query\":\"ignore\",\"flag_case\":true,\"flag_trailing\":true}}",
				"regex" => 0,
				"position" => 3,
				"last_count" => 0,
				"last_access" => "0000-00-00 00:00:00",
				"group_id" => 1,
				"status" => "enabled",
				"action_type" => "url",
				"action_code" => 301,
				"action_data" => "/bob",
				"match_type" => "url",
				"title" => "",
			],
		];
		
		foreach ( $wp_redirection_items as $item ) {
			$wpdb->insert( $wpdb->prefix . 'redirection_items', $item );
		}
	}

	public function test_sanitize_source_url() {
		$this->assertSame( Helpers::sanitize_source_url( 'test' ), '/test' );

		$this->assertSame( Helpers::sanitize_source_url( '/test' ), '/test' );

		$this->assertSame( Helpers::sanitize_source_url( 'https://example.com/test' ), '/test' );
	}

	public function test_sanitize_target_url() {
		$this->assertSame( Helpers::sanitize_target_url( 'test' ), '/test' );

		$this->assertSame( Helpers::sanitize_target_url( '/test' ), '/test' );

		$this->assertSame( Helpers::sanitize_target_url( 'http://example.org/test' ), '/test' );

		$this->assertSame( Helpers::sanitize_target_url( 'https://example.org/test' ), '/test' );

		$this->assertSame( Helpers::sanitize_target_url( 'http://www.example.org/test' ), '/test' );

		$this->assertSame( Helpers::sanitize_target_url( 'https://www.example.org/test' ), '/test' );

		$this->assertSame( Helpers::sanitize_target_url( 'http://www.example.org/test/' ), '/test' );

		$this->assertSame( Helpers::sanitize_target_url( 'https://www.example.org/test/' ), '/test' );

		$this->assertSame( Helpers::sanitize_target_url( 'https://example.com/test' ), 'https://example.com/test' );
	}

	public function test_get_redirect() {
		global $wpdb;

		$redirects = new Redirects;

		$this->assertSame( $redirects->get_target( '/test' ), home_url( '/target' ) );

		$this->assertSame( $redirects->get_target( '/test2' ), home_url( '/target2/' ) );

		$this->assertSame( $redirects->get_target( '/test2/' ), home_url( '/target2/' ) );

		$this->assertSame( $redirects->get_target( '/post?123' ), home_url( '/post-2' ) );

		$this->assertSame( $redirects->get_target( '/blah' ), home_url( '/hebada' ) );

		$this->assertSame( $redirects->get_target( '/33' ), 'https://google.com' );

		$this->assertSame( $redirects->get_target( 'john' ), home_url( '/bob' ) );

		$wpdb->insert( $wpdb->prefix . 'redirection_items', [
			"url" => "/",
			"match_url" => "/",
			"match_data" => "{\"source\":{\"flag_query\":\"ignore\",\"flag_case\":true,\"flag_trailing\":true}}",
			"regex" => 0,
			"position" => 3,
			"last_count" => 0,
			"last_access" => "0000-00-00 00:00:00",
			"group_id" => 1,
			"status" => "enabled",
			"action_type" => "url",
			"action_code" => 301,
			"action_data" => "https://google.com",
			"match_type" => "url",
			"title" => "",
		] );

		$this->assertSame( $redirects->get_target( '/' ), 'https://google.com' );

		$wpdb->insert( $wpdb->prefix . 'redirection_items', [
			"url" => "/",
			"match_url" => "/",
			"match_data" => "{\"source\":{\"flag_query\":\"ignore\",\"flag_case\":true,\"flag_trailing\":true}}",
			"regex" => 1,
			"position" => 0,
			"last_count" => 0,
			"last_access" => "0000-00-00 00:00:00",
			"group_id" => 1,
			"status" => "enabled",
			"action_type" => "url",
			"action_code" => 301,
			"action_data" => "https://bada.com",
			"match_type" => "url",
			"title" => "",
		] );

		$this->assertSame( $redirects->get_target( '/' ), 'https://google.com' );

	}

}
