<?php
/**
 * TestAddWebinarRegistrant
 *
 * @package SeattleWebCo\WPZoom
 */

/**
 * Test adding registrant to webinar
 */
class TestAddWebinarRegistrant extends WP_UnitTestCase {

	public $type_5_webinar = '{"created_at":"2040-09-13T15:35:00Z","duration":60,"host_id":"Labcjskdfsjgfg","id":12345678,"join_url":"https://zoom.us/j/12345678","settings":{"allow_multiple_devices":true,"alternative_hosts":"","approval_type":2,"audio":"both","auto_recording":"local","close_registration":true,"contact_email":"wonderfulemail@someemail.dsgfdjf","contact_name":"Wonderful person","enforce_login":false,"enforce_login_domains":"","global_dial_in_countries":["US"],"global_dial_in_numbers":[{"city":"New York","country":"US","country_name":"US","number":"+1 00000","type":"toll"},{"city":"San Jose","country":"US","country_name":"US","number":"+1 111111111","type":"toll"},{"city":"San Jose","country":"US","country_name":"US","number":"+1 11111110","type":"toll"}],"hd_video":false,"host_video":false,"on_demand":false,"panelists_video":false,"practice_session":false,"question_answer":true,"registrants_confirmation_email":true,"registrants_restrict_number":0,"show_share_button":true,"registrants_email_notification":true},"start_time":"2040-08-30T22:00:00Z","start_url":"https://zoom.us/s/00000011110?zhghTlUT1Rjd2FXRgh0amxoejNQZ1EiLCJjaWQiOiIifQ.NJ0CXWQ-yhI8Xv01JvxityBtzp3Bt7odMOEG2L8DLmY","timezone":"America/New_York","topic":"Test Webinar","type":5,"uuid":"nWMHAAAAAAAAAAAAAUDP1A=="}';

	public $type_6_webinar = '{"uuid":"bipNGOJMRxqBnMg0FGKgPA==","id":12345117105,"host_id":"oDbOKoP0JMNPHTE-Et_pHQ","host_email":"test@example.com","topic":"Test 4","type":6,"duration":60,"timezone":"America/New_York","agenda":"","created_at":"2021-01-31T16:56:03Z","start_url":"https://us02web.zoom.us/s/12345117105?zak=eyJ6bV9za20iOiJ6bV9vMm0iLCJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJhdWQiOiJjbGllbnQiLCJ1aWQiOiJvRGJPS29GM1RQS1BIVEUtRXRfcEhRIiwiaXNzIjoid2ViIiwic3R5IjoxMDAsIndjZCI6InVzMDIiLCJjbHQiOjAsInN0ayI6IkJqLMsPLkVtV0JfRm9uUHNlRFI5NDJSaGxEMEhfNzREekY1REJ4Uno0Y1EuQmdZZ2JYSkxZMDExV2pka1RDOXFPRkpIZW5SUE9VOWhXVE00VEU1V1ZIbFlaMmxBWkRJeFlqQXpObUUyWW1WbU0yRTVOekF6WkRBeE1qWm1PVEV3TkdSaE9XRTVOVE16WVdSaE5UZGxaVFZsWTJGbE1XRTBOR1F5T0dVM09XTTNPVGczT1FBTU0wTkNRWFZ2YVZsVE0zTTlBQVIxY3pBeUFBQUJkMWw4UmhnQUVuVUFBQUEiLCJleHAiOjE2MTIxMjEyNTIsImlhdCI6MTYxMjExNDA1MiwiYWlkIjoiUWpTUnJ2MHNUYU9wNGloY2QwSXZOdyIsImNpZCI6IiJ9.psjPu2ZzyQ5WIxn011YX8Xh8wyWjczDXygV8VyrfbAc","join_url":"https://us02web.zoom.us/j/12345117105?pwd=NnRCQ2NKR3FLaFJFSHNBVm9qZFdUUT09","password":"123757","settings":{"host_video":false,"panelists_video":false,"approval_type":2,"audio":"both","auto_recording":"none","enforce_login":false,"enforce_login_domains":"","alternative_hosts":"","close_registration":true,"show_share_button":false,"allow_multiple_devices":true,"practice_session":false,"hd_video":false,"question_answer":true,"registrants_confirmation_email":true,"on_demand":false,"request_permission_to_unmute_participants":false,"global_dial_in_countries":["US"],"global_dial_in_numbers":[{"country_name":"US","number":"+1 1232158782","type":"toll","country":"US"},{"country_name":"US","number":"+1 1239006833","type":"toll","country":"US"},{"country_name":"US","number":"+1 1232487799","type":"toll","country":"US"},{"country_name":"US","number":"+1 1237158592","type":"toll","country":"US"},{"country_name":"US","number":"+1 4566266799","type":"toll","country":"US"},{"country_name":"US","number":"+1 1232056099","type":"toll","country":"US"}],"contact_name":"Test","contact_email":"test@example.com","registrants_restrict_number":0,"registrants_email_notification":true,"post_webinar_survey":false,"meeting_authentication":false,"question_and_answer":{"enable":true,"allow_anonymous_questions":true,"answer_questions":"only","attendees_can_upvote":false,"attendees_can_comment":false}}}';

	public $type_9_webinar = '{"uuid":"NrIWvG8eRDmMJCCgjcKYEA==","id":26733991893,"host_id":"oDbOKoF3LAPPHTE-Et_pHQ","host_email":"test@example.com","topic":"Sample Webinar","type":9,"duration":180,"timezone":"America/Chicago","agenda":"Lorem ipsum dolor sit amet","created_at":"2020-05-22T18:12:15Z","start_url":"https://us02web.zoom.us/s/26733991893?zak=eyJ6bV9za20iOiJ6bV9vMm0iLCJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJhdWQiOiJjbGllbnQiLCJ1aWQiOiJvRGJPS29GM1RQS1BIVEUtRXRfcEhRIiwiaXNzIjoid2ViIiwic3R5IjoxMDAsIndjZCI6InVzMDIiLCJjbHQiOjAsInN0ayI6IkxGbWlLAP9IX3pBSTZ6NjNtUEI5MHZzMnB6VGI5WjIyX3NZMkZYVFd2X1kuQmdZZ2JYSkxZMDExV2pka1RDOXFPRkpIZW5SUE9VOWhXVE00VEU1V1ZIbFlaMmxBWkRJeFlqQXpObUUyWW1WbU0yRTVOekF6WkRBeE1qWm1PVEV3TkdSaE9XRTVOVE16WVdSaE5UZGxaVFZsWTJGbE1XRTBOR1F5T0dVM09XTTNPVGczT1FBTU0wTkNRWFZ2YVZsVE0zTTlBQVIxY3pBeUFBQUJkMWx6THlBQUVuVUFBQUEiLCJleHAiOjE2MTIxMjA2NTYsImlhdCI6MTYxMjExMzQ1NiwiYWlkIjoiUWpTUnJ2MHNUYU9wNGloY2QwSXZOdyIsImNpZCI6IiJ9.LWdE27U6kNGw6PIHXPFVRTXXVbEebHF_RMRHcdDgT3M","join_url":"https://us02web.zoom.us/j/86733990891","registration_url":"https://us02web.zoom.us/webinar/register/WN_i-QYPWPdQfaBxaPx6vi5NA","occurrences":[{"occurrence_id":"1612364400000","start_time":"2040-02-03T15:00:00Z","duration":180,"status":"available"},{"occurrence_id":"1613660400000","start_time":"2040-02-18T15:00:00Z","duration":180,"status":"available"},{"occurrence_id":"1614265200000","start_time":"2040-02-25T15:00:00Z","duration":180,"status":"available"},{"occurrence_id":"1615302000000","start_time":"2040-03-09T15:00:00Z","duration":180,"status":"available"},{"occurrence_id":"1616076000000","start_time":"2040-03-18T14:00:00Z","duration":180,"status":"available"},{"occurrence_id":"1616680800000","start_time":"2040-03-25T14:00:00Z","duration":180,"status":"available"},{"occurrence_id":"1617026400000","start_time":"2040-03-29T14:00:00Z","duration":180,"status":"available"}],"settings":{"host_video":true,"panelists_video":true,"approval_type":0,"registration_type":2,"audio":"both","auto_recording":"none","enforce_login":false,"enforce_login_domains":"","alternative_hosts":"","close_registration":true,"show_share_button":false,"allow_multiple_devices":true,"practice_session":true,"hd_video":false,"question_answer":true,"registrants_confirmation_email":true,"on_demand":false,"request_permission_to_unmute_participants":false,"global_dial_in_countries":["US"],"global_dial_in_numbers":[{"country_name":"US","number":"+1 1232158782","type":"toll","country":"US"},{"country_name":"US","number":"+1 1239006833","type":"toll","country":"US"},{"country_name":"US","number":"+1 1232487799","type":"toll","country":"US"},{"country_name":"US","number":"+1 1237158592","type":"toll","country":"US"},{"country_name":"US","number":"+1 1236266799","type":"toll","country":"US"},{"country_name":"US","number":"+1 1232056099","type":"toll","country":"US"}],"contact_name":"Test","contact_email":"test@example.com","registrants_restrict_number":0,"registrants_email_notification":true,"post_webinar_survey":false,"meeting_authentication":false,"question_and_answer":{"enable":true,"allow_anonymous_questions":true,"answer_questions":"only","attendees_can_upvote":false,"attendees_can_comment":false}},"recurrence":{"type":2,"repeat_interval":1,"weekly_days":"4,5","end_date_time":"2040-06-29T04:59:00Z"}}';

	public static $customer;

	public static $simple_product;

	public static function setUpBeforeClass() {
		$simple_product = WC_Helper_Product::create_simple_product();
		$simple_product->set_regular_price( 10.00 );
		$simple_product->save();

		self::$simple_product = $simple_product;

		self::$customer = WC_Helper_Customer::create_customer();
	}

	public function setUp() {
		global $wpdb;

		$wpdb->query( "DELETE FROM {$wpdb->prefix}woocommerce_order_items" );
		$wpdb->query( "DELETE FROM {$wpdb->prefix}woocommerce_order_itemmeta" );

		$provider = $this->getMockBuilder('\League\OAuth2\Client\Provider\GenericProvider')
			->setConstructorArgs(array( array(
				'urlAuthorize' => 'https://example.com',
				'urlAccessToken' => 'https://example.com',
				'urlResourceOwnerDetails' => 'https://example.com'
			)))
			->getMock();
		
		
		$GLOBALS['wp_zoom'] = $this->getMockBuilder('\SeattleWebCo\WPZoom\API')
			->setConstructorArgs(array($provider))
			->getMock();
		
	}

	public function test_registration_success() {
		$GLOBALS['wp_zoom']->expects($this->once())
			->method('add_webinar_registrant')
			->will($this->returnValue(json_decode( '{"registrant_id":"culpa deserunt ea est commodo","id":"velit dolore minim Ut","topic":"et laboris Lorem in Ut","start_time":"1974-02-26T23:01:16.899Z","join_url":"pariatur"}', true)));

		WC()->cart->empty_cart();
		WC()->cart->add_to_cart( self::$simple_product->get_id(), 1, 0, array(), array( 
			'wp_zoom_webinars'	=> array(
				'test' => json_decode( $this->type_5_webinar, true )
			)
		) );
		$checkout = WC_Checkout::instance();

		update_option( 'timezone_string', 'America/New_York' );

		$order = wc_create_order();
		$order->set_customer_id( self::$customer->get_id() );
		$checkout->set_data_from_cart( $order );
		$order->set_status( 'pending' );
		$order->save();

		$complete = $order->payment_complete( '123' );

		$notes = wp_list_pluck( wc_get_order_notes( array( 'order_id' => $order->get_id() ) ), 'content' );

		$this->assertTrue( $complete );
		$this->assertContains( 'User successfully registered for Test Webinar (Thursday, August 30th, 2040 at 5:00pm EST)', $notes );
	}

	public function test_registration_type_9_success() {
		$GLOBALS['wp_zoom']->expects($this->once())
			->method('add_webinar_registrant')
			->will($this->returnValue(json_decode( '{"registrant_id":"culpa deserunt ea est commodo","id":"velit dolore minim Ut","topic":"et laboris Lorem in Ut","start_time":"1974-02-26T23:01:16.899Z","join_url":"pariatur"}', true)));

		WC()->cart->empty_cart();
		WC()->cart->add_to_cart( self::$simple_product->get_id(), 1, 0, array(), array( 
			'wp_zoom_webinars'	=> array(
				'test' => json_decode( $this->type_9_webinar, true ),
			),
			'wp_zoom_webinars_occurrences' => array( '26733991893' => json_decode( '{"occurrence_id":"1612364400000","start_time":"2040-02-03T15:00:00Z","duration":180,"status":"available"}', true ) )
		) );

		$checkout = WC_Checkout::instance();

		update_option( 'timezone_string', 'America/New_York' );

		$order = wc_create_order();
		$order->set_customer_id( self::$customer->get_id() );
		$checkout->set_data_from_cart( $order );
		$order->save();

		$complete = $order->payment_complete( '123' );

		$notes = wp_list_pluck( wc_get_order_notes( array( 'order_id' => $order->get_id() ) ), 'content' );

		$this->assertTrue( $complete );
		$this->assertContains( 'User successfully registered for Sample Webinar (Friday, February 3rd, 2040 at 10:00am EST)', $notes );
	}

	public function test_registration_multiple_success() {
		$GLOBALS['wp_zoom']->expects($this->exactly(2))
			->method('add_webinar_registrant')
			->will($this->returnValue(json_decode( '{"registrant_id":"culpa deserunt ea est commodo","id":"velit dolore minim Ut","topic":"et laboris Lorem in Ut","start_time":"1974-02-26T23:01:16.899Z","join_url":"pariatur"}', true)));

		WC()->cart->empty_cart();
		WC()->cart->add_to_cart( self::$simple_product->get_id(), 1, 0, array(), array( 
			'wp_zoom_webinars'	=> array(
				'test' => json_decode( $this->type_5_webinar, true )
			)
		) );

		$simple_product_2 = WC_Helper_Product::create_simple_product();
		$simple_product_2->set_regular_price( 10.00 );
		$simple_product_2->save();

		WC()->cart->add_to_cart( $simple_product_2->get_id(), 1, 0, array(), array( 
			'wp_zoom_webinars'	=> array(
				'test' => json_decode( $this->type_6_webinar, true )
			)
		) );

		$checkout = WC_Checkout::instance();

		update_option( 'timezone_string', 'America/New_York' );

		$order = wc_create_order();
		$order->set_customer_id( self::$customer->get_id() );
		$checkout->set_data_from_cart( $order );
		$order->set_status( 'pending' );
		$order->save();

		$complete = $order->payment_complete( '123' );

		$notes = wp_list_pluck( wc_get_order_notes( array( 'order_id' => $order->get_id() ) ), 'content' );

		$this->assertTrue( $complete );
		$this->assertContains( 'User successfully registered for Test Webinar (Thursday, August 30th, 2040 at 5:00pm EST)', $notes );
	}
	
	public function test_registration_fail() {
		$GLOBALS['wp_zoom']->expects($this->once())
			->method('add_webinar_registrant')
			->will($this->returnValue(json_decode( '{"code":1010,"message":"User does not belong to this account:{accountId}."}', true)));

		WC()->cart->empty_cart();
		WC()->cart->add_to_cart( self::$simple_product->get_id(), 1, 0, array(), array( 
			'wp_zoom_webinars'	=> array(
				'test' => json_decode( $this->type_5_webinar, true )
			)
		) );
		$checkout = WC_Checkout::instance();

		$order = wc_create_order();
		$order->set_customer_id( self::$customer->get_id() );
		$checkout->set_data_from_cart( $order );
		$order->set_status( 'pending' );
		$order->save();

		$complete = $order->payment_complete( '123' );

		$notes = wp_list_pluck( wc_get_order_notes( array( 'order_id' => $order->get_id() ) ), 'content' );

		$this->assertTrue( $complete );
		$this->assertContains( 'An error occurred while registering customer for Test Webinar', $notes );
	}
}
