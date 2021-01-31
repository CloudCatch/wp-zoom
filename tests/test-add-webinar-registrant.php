<?php
/**
 * TestAddWebinarRegistrant
 *
 * @package SeattleWebCo\WCZoom
 */

/**
 * Test adding registrant to webinar
 */
class TestAddWebinarRegistrant extends WP_UnitTestCase {

	public $product_id;

	public static function setUpBeforeClass() {
		//$product = 
	}

	public function test_payment_complete() {
		$simple_product = new WC_Product_Simple();
		$simple_product->save();

		$product_item = new WC_Order_Item_Product();
		$product_item->set_quantity( 1 );
		$product_item->set_product_id( $simple_product->get_id() );
		$product_item->set_total( '10.00' );

		$order = new WC_Order();
		$order->add_item( $product_item );
		$order->set_status( 'pending' );
		$order->save();
		
		$complete = $order->payment_complete( '123' );

		$this->assertTrue( $complete );
	}
	
}
