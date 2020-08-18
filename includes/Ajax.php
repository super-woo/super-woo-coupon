<?php

namespace WpAdroit\Wac_Coupon;

/**
 * Ajax Handler
 */
class Ajax
{

	function __construct()
	{
		add_action('wp_ajax_wac_product_search', [$this, 'wac_product_search']);
		add_action('wp_ajax_wac_get_filters', [$this, 'wac_get_filters']);
		add_action('wp_ajax_wac_save_filters', [$this, 'wac_save_filters']);
		add_action('wp_ajax_wac_get_main', [$this, 'wac_get_main']);
		add_action('wp_ajax_wac_get_discounts', [$this, 'wac_get_discounts']);
		add_action('wp_ajax_wac_get_rules', [$this, 'wac_get_rules']);
		add_action('wp_ajax_wac_get_woocoupons', [$this, 'wac_get_woocoupons']);
		add_action('wp_ajax_wac_get_wac_panel', [$this, 'wac_get_wac_panel']);
	}

	public function wac_get_wac_panel()
	{
		$post_id = $_POST["post_id"];
		$post_meta = get_post_meta($post_id, "wac_coupon_panel", true);
		wp_send_json($post_meta);
	}

	public function wac_get_woocoupons()
	{
		$args = [
			"post_type" => "woocoupon",
			'post_status' => 'publish'
		];
		$posts = get_posts($args);
		$filter_Posts = [];
		foreach ($posts as $post) {
			array_push($filter_Posts, [
				"label" => $post->post_title,
				"value" => $post->ID
			]);
		}
		wp_send_json($filter_Posts);
	}

	public function wac_get_rules()
	{
		$post_id = $_POST["post_id"];
		$post_meta = get_post_meta($post_id, "wac_coupon_rules", true);
		wp_send_json($post_meta);
	}

	public function wac_get_discounts()
	{
		$post_id = $_POST["post_id"];
		$post_meta = get_post_meta($post_id, "wac_coupon_discounts", true);
		wp_send_json($post_meta);
	}

	public function wac_get_main()
	{
		$post_id = $_POST["post_id"];
		$post_meta = get_post_meta($post_id, "wac_coupon_main", true);
		$discount_type = [
			"product" => [ "label" => "Product Adjustment" , "has_label" => false ],
			"cart" => [ "label" => "Cart Adjustment" , "has_label" => true ],
			"bulk" => [ "label" => "Bulk Discount" , "has_label" => true ]
		];
		$data = [
			"post_meta" => $post_meta,
			"discount_type" => apply_filters( "wac_discount_type", $discount_type )
		];
		wp_send_json($data);
	}

	public function wac_product_search()
	{
		$args = [
			"post_type" => "product",
			'post_status' => 'publish',
			"s" => $_POST["queryData"]
		];
		$posts = get_posts($args);
		$filter_Posts = [];
		foreach ($posts as $post) {
			array_push($filter_Posts, [
				"label" => $post->post_title,
				"value" => $post->ID
			]);
		}

		if (isset($_POST["option"])) {
			foreach ($_POST["option"] as $option) {
				$filter_Posts = array_filter($filter_Posts, function ($post) use ($option) {
					return ($post["value"] != $option["value"]);
				});
			}
		}

		wp_send_json($filter_Posts);
	}

	public function wac_get_filters()
	{
		$post_id = $_POST["post_id"];
		$post_meta = get_post_meta($post_id, "wac_filters", true);
		$filters_data = [
			[ "label" => "All Products", "value" => "all_products", "has_item" => false, "items" => null ],
			[ "label" => "Products", "value" => "products", "has_item" => true, 
				"items" => [ "action" => "wac_product_search", "label" => "Select Products" ] 
			]
		];
		$send_data = [
			"post_meta" => $post_meta,
			"filters_data" => apply_filters( "wac_filters", $filters_data )
		];
		wp_send_json($send_data);
	}

	public function wac_save_filters()
	{
		if (!wp_verify_nonce($_POST["wac_nonce"], "wac_with_ajax")) {
			wp_die(__('Sorry !! You cannot permit to access.', 'superwoo'));
		}
		$post_id = $_POST["post_id"];
		$wacfilters = [];
		foreach ($_POST["wacfilters"] as $wac_filter) {
			if (!isset($wac_filter["items"])) {
				$wac_filter["items"] = [];
			}
			array_push($wacfilters, $wac_filter);
		}
		update_post_meta($post_id, "wac_filters", $wacfilters);
		wp_send_json(["message" => "Updated SuccessFully", "status" => "success"]);
	}
}
