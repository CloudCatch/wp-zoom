<?php
/**
 * Class SeoTest
 *
 * @package WCZoom
 */

/**
 * Seo test case.
 */
class SeoTest extends WP_UnitTestCase {

	public static $post_id;

	public static $page_id;

	public static $term_id;

	public static function setUpBeforeClass() {
		self::$post_id = wp_insert_post( [
			'post_type'		=> 'post',
			'post_status'	=> 'publish',
			'post_title'	=> 'A dog ran over a cat',
			'post_content'	=> 'Test',
			'post_date'		=> '2020-01-01 12:00:00',
		] );

		self::$page_id = wp_insert_post( [
			'post_type'		=> 'page',
			'post_status'	=> 'publish',
			'post_title'	=> 'Homepage',
			'post_content'	=> 'Test',
		] );
	 
		register_post_type( 'book', [
			'labels'             => [
				'name'                  => _x( 'Books', 'Post type general name' ),
				'singular_name'         => _x( 'Book', 'Post type singular name' ),
				'menu_name'             => _x( 'Books', 'Admin Menu text' ),
				'name_admin_bar'        => _x( 'Book', 'Add New on Toolbar' ),
				'add_new'               => __( 'Add New' ),
				'add_new_item'          => __( 'Add New Book' ),
				'new_item'              => __( 'New Book' ),
				'edit_item'             => __( 'Edit Book' ),
				'view_item'             => __( 'View Book' ),
				'all_items'             => __( 'All Books' ),
				'search_items'          => __( 'Search Books' ),
				'parent_item_colon'     => __( 'Parent Books:' ),
				'not_found'             => __( 'No books found.' ),
				'not_found_in_trash'    => __( 'No books found in Trash.' ),
				'featured_image'        => _x( 'Book Cover Image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3' ),
				'set_featured_image'    => _x( 'Set cover image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3' ),
				'remove_featured_image' => _x( 'Remove cover image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3' ),
				'use_featured_image'    => _x( 'Use as cover image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3' ),
				'archives'              => _x( 'Book archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4' ),
				'insert_into_item'      => _x( 'Insert into book', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4' ),
				'uploaded_to_this_item' => _x( 'Uploaded to this book', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4' ),
				'filter_items_list'     => _x( 'Filter books list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4' ),
				'items_list_navigation' => _x( 'Books list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4' ),
				'items_list'            => _x( 'Books list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4' ),
			],
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
		] );

		register_taxonomy( 'genre', 'book', [
			'labels'       => [
				'name'              => _x( 'Genres', 'taxonomy general name' ),
				'singular_name'     => _x( 'Genre', 'taxonomy singular name' ),
				'search_items'      => __( 'Search Genres' ),
				'all_items'         => __( 'All Genres' ),
				'parent_item'       => __( 'Parent Genre' ),
				'parent_item_colon' => __( 'Parent Genre:' ),
				'edit_item'         => __( 'Edit Genre' ),
				'update_item'       => __( 'Update Genre' ),
				'add_new_item'      => __( 'Add New Genre' ),
				'new_item_name'     => __( 'New Genre Name' ),
				'menu_name'         => __( 'Genre' ),
			],
			'hierarchical' => true
		] );

		$term = wp_insert_term( 'Fiction', 'genre', [
			'slug'		=> 'fiction',
		] );

		self::$term_id = $term['term_id'];

		update_option( 'blogname', 'Sandbox' );
		update_option( 'blogdescription', 'Description' );
	}

	public static function _setQueriedPost( $post_id ) {
		global $wp_query;

		$wp_query->parse_query( [ 
			'p'					=> $post_id,
			'page'				=> '',
			'posts_per_page' 	=> 10,
			'cache_results' 	=> 0,
		] );

		$wp_query->queried_object = get_post( $post_id );
		$wp_query->queried_object_id = $post_id;
		$GLOBALS['post'] = get_post( $post_id );
	}

	public static function _setQueriedPage( $page_id ) {
		global $wp_query;

		$page = get_post( $page_id );

		$wp_query->parse_query( [ 
			'pagename' 			=> $page->post_name,
			'page'				=> '',
			'posts_per_page' 	=> 10,
			'cache_results' 	=> 0,
		] );

		$wp_query->queried_object = get_post( $page_id );
		$wp_query->queried_object_id = $page_id;
		$GLOBALS['post'] = get_post( $page_id );
	}

	/**
	 * Test 404 title
	 *
	 * @return void
	 */
	public function test_404_title() {
		global $wp_query;

		$wp_query->set_404();

		$this->assertTrue( is_404() );

		$this->assertSame( '404 Not Found - Sandbox', wp_get_document_title() );

		update_option( 'sem_seo', [ '404' => '404' ] );

		$this->assertSame( '404', wp_get_document_title() );
	}

	/**
	 * Test search title
	 *
	 * @return void
	 */
	public function test_search_title() {
		global $wp_query;

		$wp_query->parse_query( [
			's'	=> 'Test'
		] );

		$this->assertTrue( is_search() );

		$this->assertSame( 'Search Results for Test - Sandbox', wp_get_document_title() );

		update_option( 'sem_seo', [ 'search' => 'Search' ] );

		$this->assertSame( 'Search', wp_get_document_title() );
	}

	/**
	 * Test front page title
	 *
	 * @return void
	 */
	public function test_front_page_title() {
		self::_setQueriedPage( self::$page_id );

		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', self::$page_id );

		$this->assertTrue( is_front_page() );

		$this->assertSame( 'Sandbox', wp_get_document_title() );

		update_option( 'sem_seo', [ 'front_page' => 'Home' ] );

		$this->assertSame( 'Home', wp_get_document_title() );

		update_post_meta( self::$page_id, 'seo_title', 'WCZoom' );

		$this->assertSame( 'WCZoom', wp_get_document_title() );
	}

	/**
	 * Test front page description
	 *
	 * @return void
	 */
	public function test_front_page_description() {
		self::_setQueriedPage( self::$page_id );

		$defaultSettings = new \SeattleWebCo\WCZoom\Admin\Modules\DefaultSettings;

		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', self::$page_id );

		$this->assertTrue( is_front_page() );

		$this->assertSame( '', $defaultSettings->get_description() );

		update_option( 'sem_seo', [ 'front_page_desc' => 'Description' ] );

		$this->assertSame( 'Description', $defaultSettings->get_description() );

		update_post_meta( self::$page_id, 'seo_description', 'New Description' );

		$this->assertSame( 'New Description', $defaultSettings->get_description() );
	}

	/**
	 * Test post type archive title
	 *
	 * @return void
	 */
	public function test_post_type_archive() {
		global $wp_query;

		$wp_query->parse_query( [ 
			'post_type'			=> 'book',
			'posts_per_page' 	=> 10,
			'cache_results' 	=> 0,
		] );

		$this->assertTrue( is_post_type_archive() );

		$this->assertSame( 'Books - Sandbox', wp_get_document_title() );

		update_option( 'sem_seo', [ 'post_type_archive_book' => 'Books' ] );

		$this->assertSame( 'Books', wp_get_document_title() );
	}

	/**
	 * Test tax title
	 *
	 * @return void
	 */
	public function test_tax() {
		global $wp_query;

		$wp_query->parse_query( [ 
			'genre'				=> 'fiction',
			'posts_per_page' 	=> 10,
			'cache_results' 	=> 0,
		] );

		$this->assertTrue( is_tax() );

		$this->assertSame( 'Fiction - Sandbox', wp_get_document_title() );

		update_option( 'sem_seo', [ 'tax_genre' => 'Fiction' ] );

		$this->assertSame( 'Fiction', wp_get_document_title() );

		update_term_meta( self::$term_id, '_su_title', '555' );

		$this->assertSame( '555', wp_get_document_title() );

		update_term_meta( self::$term_id, 'seo_title', 'WCZoom' );

		$this->assertSame( 'WCZoom', wp_get_document_title() );
	}

	/**
	 * Test posts page title
	 *
	 * @return void
	 */
	public function test_home() {
		global $wp_query;

		$wp_query->is_home = true;

		update_option( 'show_on_front', 'posts' );

		$this->assertTrue( is_home() );

		$this->assertSame( 'Sandbox', wp_get_document_title() );

		update_option( 'sem_seo', [ 'front_page' => 'Blog' ] );

		$this->assertSame( 'Blog', wp_get_document_title() );
	}

	/**
	 * Test posts page description
	 *
	 * @return void
	 */
	public function test_home_description() {
		global $wp_query;

		$wp_query->is_home = true;

		$defaultSettings = new \SeattleWebCo\WCZoom\Admin\Modules\DefaultSettings;

		update_option( 'show_on_front', 'posts' );

		$this->assertTrue( is_home() );

		$this->assertSame( '', $defaultSettings->get_description() );

		update_option( 'sem_seo', [ 'front_page_desc' => 'Description 2' ] );

		$this->assertSame( 'Description 2', $defaultSettings->get_description() );
	}

	/**
	 * Test posts page that is not the front page title
	 *
	 * @return void
	 */
	public function test_home_not_front() {
		global $wp_query;

		$posts_page_id = wp_insert_post( [
			'post_type'		=> 'page',
			'post_status'	=> 'publish',
			'post_title'	=> 'Blog',
			'post_content'	=> 'Test',
		] );

		self::_setQueriedPage( $posts_page_id );

		$wp_query->is_home = true;

		update_option( 'show_on_front', 'page' );
		update_option( 'page_for_posts', $posts_page_id );

		$this->assertTrue( is_home() && ! is_front_page() );

		$this->assertSame( 'Sandbox', wp_get_document_title() );

		update_option( 'sem_seo', [ 'posts_page' => 'Blog' ] );

		$this->assertSame( 'Blog', wp_get_document_title() );

		update_post_meta( $posts_page_id, 'seo_title', 'WCZoom Blog' );

		$this->assertSame( 'WCZoom Blog', wp_get_document_title() );
	}

	/**
	 * Test posts page that is not the front page description
	 *
	 * @return void
	 */
	public function test_home_not_front_description() {
		global $wp_query;

		$posts_page_id = wp_insert_post( [
			'post_type'		=> 'page',
			'post_status'	=> 'publish',
			'post_title'	=> 'Blog',
			'post_content'	=> 'Test',
		] );

		self::_setQueriedPage( $posts_page_id );

		$wp_query->is_home = true;

		$defaultSettings = new \SeattleWebCo\WCZoom\Admin\Modules\DefaultSettings;

		update_option( 'show_on_front', 'page' );
		update_option( 'page_for_posts', $posts_page_id );

		$this->assertTrue( is_home() && ! is_front_page() );

		$this->assertSame( '', $defaultSettings->get_description() );

		update_option( 'sem_seo', [ 'posts_page_desc' => 'Description 3' ] );

		$this->assertSame( 'Description 3', $defaultSettings->get_description() );

		update_post_meta( $posts_page_id, 'seo_description', '123' );

		$this->assertSame( '123', $defaultSettings->get_description() );
	}

	/**
	 * Test single title
	 *
	 * @return void
	 */
	public function test_singular_title() {
		self::_setQueriedPost( self::$post_id );

		$this->assertTrue( is_singular() );

		$this->assertSame( $GLOBALS['post']->post_title . ' - Sandbox', wp_get_document_title() );

		update_option( 'sem_seo', [ 'single_post' => 'Single' ] );

		$this->assertSame( 'Single', wp_get_document_title() );

		update_post_meta( self::$post_id, '_su_title', 'SEO Ultimate' );

		$this->assertSame( 'SEO Ultimate', wp_get_document_title() );

		delete_post_meta( self::$post_id, '_su_title' );

		update_post_meta( self::$post_id, '_yoast_wpseo_title', '%%title%% %%page%% %%sep%% %%sitename%%' );

		$this->assertSame( 'A dog ran over a cat - Sandbox', wp_get_document_title() );

		update_post_meta( self::$post_id, 'seo_title', 'WCZoom' );

		$this->assertSame( 'WCZoom', wp_get_document_title() );
	}

	/**
	 * Test author title
	 *
	 * @return void
	 */
	public function test_author_title() {
		global $wp_query;

		$wp_query->parse_query( [ 
			'author_name'		=> 'admin',
			'posts_per_page' 	=> 10,
			'cache_results' 	=> 0,
		] );

		set_query_var( 'author', '1' );

		$this->assertTrue( is_author() );

		$this->assertSame( 'admin - Sandbox', wp_get_document_title() );

		update_option( 'sem_seo', [ 'author' => 'Author' ] );

		$this->assertSame( 'Author', wp_get_document_title() );
	}

	/**
	 * Test year title
	 *
	 * @return void
	 */
	public function test_year_title() {
		global $wp_query;

		$wp_query->parse_query( [ 
			'year'				=> '2020',
			'posts_per_page' 	=> 10,
			'cache_results' 	=> 0,
		] );

		$this->assertTrue( is_year() );

		$this->assertSame( 'Archives for 2020 - Sandbox', wp_get_document_title() );

		update_option( 'sem_seo', [ 'year' => '2020' ] );

		$this->assertSame( '2020', wp_get_document_title() );
	}

	/**
	 * Test month title
	 *
	 * @return void
	 */
	public function test_month_title() {
		global $wp_query;

		$wp_query->parse_query( [ 
			'year'				=> '2020',
			'monthnum'			=> '01',
			'posts_per_page' 	=> 10,
			'cache_results' 	=> 0,
		] );

		$this->assertTrue( is_month() );

		$this->assertSame( 'Archives for January 2020 - Sandbox', wp_get_document_title() );

		update_option( 'sem_seo', [ 'month' => 'January' ] );

		$this->assertSame( 'January', wp_get_document_title() );
	}

	/**
	 * Test day title
	 *
	 * @return void
	 */
	public function test_day_title() {
		global $wp_query;

		$wp_query->parse_query( [ 
			'year'				=> '2020',
			'monthnum'			=> '01',
			'day'				=> '01',
			'posts_per_page' 	=> 10,
			'cache_results' 	=> 0,
		] );

		$this->assertTrue( is_day() );

		$this->assertSame( 'Archives for January 1, 2020 - Sandbox', wp_get_document_title() );

		update_option( 'sem_seo', [ 'day' => 'Day' ] );

		$this->assertSame( 'Day', wp_get_document_title() );
	}
}
