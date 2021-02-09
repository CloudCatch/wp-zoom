<?php
/**
 * TestHelperFunctions
 *
 * @package SeattleWebCo\WPZoom
 */

/**
 * Test helper functions
 */
class TestHelperFunctions extends WP_UnitTestCase {

    function test_wp_zoom_format_date_time() {
        $this->assertEquals( wp_zoom_format_date_time( '2019-09-13T15:35:00Z', 'CST' ), 'Friday, September 13th, 2019 at 9:35am CST' );
        $this->assertEquals( wp_zoom_format_date_time( '2019-09-13T15:35:00Z', 'EST' ), 'Friday, September 13th, 2019 at 10:35am EST' );
        $this->assertEquals( wp_zoom_format_date_time( '2019-09-13T15:35:00Z', 'America/Los_Angeles' ), 'Friday, September 13th, 2019 at 8:35am PDT' );

        add_filter( 'wp_zoom_datetime_format', function( $format ) {
            return 'Y-m-d';
        } );

        $this->assertEquals( wp_zoom_format_date_time( '2019-09-13T15:35:00Z', 'EST' ), '2019-09-13' );
    }

    function test_wp_zoom_get_webinars() {
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
            
        $GLOBALS['wp_zoom']->expects($this->exactly(4))
			->method('get_webinar')
            ->will($this->returnValue(json_decode( '{"uuid": "123"}', true ) ) );
            
        $post_id = wp_insert_post( array( 'post_title' => 'test' ) );
        $post = get_post( $post_id );

        $simple_product = WC_Helper_Product::create_simple_product();
		$simple_product->set_regular_price( 15.00 );
        $simple_product->save();

        update_post_meta( $simple_product->get_id(), '_wp_zoom_webinars', array( '123' ) );
        
        $post_product = get_post( $simple_product->get_id() );

        $this->assertSame( wp_zoom_get_webinars( 123 ), array() );
        $this->assertSame( wp_zoom_get_webinars(), array() );
        $this->assertSame( wp_zoom_get_webinars( $post_id ), array( array( 'uuid' => '123' ) ) );
        $this->assertSame( wp_zoom_get_webinars( $post ), array( array( 'uuid' => '123' ) ) );
        $this->assertSame( wp_zoom_get_webinars( $simple_product ), array( array( 'uuid' => '123' ) ) );
        $this->assertSame( wp_zoom_get_webinars( $post_product ), array( array( 'uuid' => '123' ) ) );
    }

}