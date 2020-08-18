<?php

namespace WpAdroit\Wac_Coupon\Frontend;

use WpAdroit\Wac_Coupon\Frontend\Helpers\Apply;
use WpAdroit\Wac_Coupon\Frontend\Helpers\Validator;

/**
 * Class Wac_auto
 * control auto coupon system
 */
class Wac_auto
{

	public function __construct()
	{
		if (is_admin()) {
			return;
		}
		add_action('wp_loaded', [$this, "wac_auto_coupon_on_cart"]);
		add_action('woocommerce_cart_calculate_fees', [$this, "wac_auto_coupon_on_cart"]);
		add_filter("woocommerce_product_get_price", [$this, "wac_change_price"], 10, 2);
		add_filter("woocommerce_product_variation_get_price", [$this, "wac_variable_change_price"], 10, 2);
	}

	/**
	 * woocommerce auto coupon function
	 **/
	public function wac_auto_coupon_on_cart()
	{
		do_action('wac_before_wp_loaded');
		$this->wac_first_order();
		$this->wac_auto_coupon();
		do_action('wac_after_wp_loaded');
	}

	/**
	 * Wac Auto Coupon
	 **/
	public function wac_auto_coupon()
	{
		$args       = [
			"post_type"   => "woocoupon",
			'post_status' => 'publish'
		];
		$posts      = get_posts($args);
		$woocoupons = $this->wac_filter_woocoupn($posts);
		$first_coupon          = get_option("wac_first_time_purchase_coupon");
		if (count($woocoupons) == 0) {
			if ($first_coupon == 0) {
				WC()->session->__unset("wac_product_coupon");
			}
		}
		foreach ($woocoupons as $woocoupon) {
			$wac_main = get_post_meta($woocoupon->ID, "wac_coupon_main", true);
			if ($wac_main["type"] != "product") {
				if ($first_coupon == 0) {
					WC()->session->__unset("wac_product_coupon");
				}
			}
			$validate = Validator::check(null, null, $woocoupon->ID);
			if ($validate) {
				$apply                 = new Apply;
				$res_data              = $apply->apply_discount($woocoupon->ID);
				$wac_woo_setting_multi = get_option("wac_woo_setting_multi");
				if ($res_data) {
					$cart = WC()->cart;
					if ($wac_woo_setting_multi == "yes") {
						$cart->add_fee($res_data["label"], -$res_data["amount"]);
					} else {
						$cart->add_fee($res_data["label"], -$res_data["amount"]);
						break;
					}
				}
			}
		}
	}

	/**
	 * filter woocoupon
	 * @return filter_posts
	 **/
	public function wac_filter_woocoupn($posts)
	{
		$filter_posts = [];

		$wac_coupons = [];
		$args        = array(
			'posts_per_page' => -1,
			'order'          => 'asc',
			'post_type'      => 'shop_coupon',
			'post_status'    => 'publish',
		);
		$coupons     = get_posts($args);
		$first_coupon = get_option("wac_first_time_purchase_coupon");

		if (count($coupons) != 0) {
			foreach ($coupons as $coupon) {
				$post_meta = get_post_meta($coupon->ID, "wac_coupon_panel", true);
				if (!empty($post_meta["list_id"]) || $post_meta["list_id"] != '') {
					array_push($wac_coupons, $post_meta["list_id"]);
				}
			}
			foreach ($posts as $post) {
				if (!in_array($post->ID, $wac_coupons) && $first_coupon != $post->ID) {
					array_push($filter_posts, $post);
				}
			}
		} else {
			foreach ($posts as $post) {
				if ($first_coupon != $post->ID) {
					array_push($filter_posts, $post);
				}
			}
		}

		return $filter_posts;
	}

	/**
	 * control first purchase features
	 **/
	public function wac_first_order()
	{
		if (!is_user_logged_in()) {
			return;
		}

		$user    = wp_get_current_user();
		$user_id = $user->ID;
		$coupon  = get_option("wac_first_time_purchase_coupon");
		if ($coupon == 0) {
			$session_data = WC()->session->get("wac_product_coupon");
			if ($session_data && $session_data["first_coupon"] == "yes") {
				WC()->session->__unset("wac_product_coupon");
				return;
			}
			return;
		}
		$args   = array(
			'customer_id' => $user_id
		);
		$orders = wc_get_orders($args);
		if (count($orders) == 0) {
			$validate = Validator::check(null, null, $coupon);
			if ($validate) {
				$apply    = new Apply;
				$res_data = $apply->apply_discount($coupon);
				if ($res_data) {
					$cart = WC()->cart;
					$cart->add_fee($res_data["label"], -$res_data["amount"]);
				}
			}
		}
	}

	/**
	 * woocommerce product change price
	 **/
	public function wac_change_price($price, $product)
	{
		$data = WC()->session->get("wac_product_coupon");
		if ($data) {
			if ($data["first_coupon"] === "yes") {
				$coupon          = get_option("wac_first_time_purchase_coupon");
				if ($coupon == 0) {
					return $price;
				}
				$wac_main        = get_post_meta($coupon, "wac_coupon_main", true);
				if (!$wac_main) {
					return $price;
				}
				$wac_coupon_type = $wac_main["type"];
				$wac_discounts   = get_post_meta($coupon, "wac_coupon_discounts", true);
				$wac_filters     = get_post_meta($coupon, "wac_filters", true);
				if ($wac_coupon_type == "product") {
					foreach ($wac_filters as $wac_filter) {
						if ($wac_filter["type"] == "products") {
							foreach ($wac_filter["items"] as $wacproducts) {
								if ($wacproducts["value"] == $product->get_id()) {
									switch ($wac_discounts["type"]) {
										case 'percentage':
											$discount = ($wac_discounts["value"] / 100) * (float)$product->get_regular_price();
											break;
										case 'fixed':
											$discount = $wac_discounts["value"];
											break;
									}
									$amount = ((float)$product->get_regular_price() - $discount);
									$product->set_sale_price($amount);
									return $amount;
								}
							}
						} elseif ($wac_filter["type"] == "all_products") {
							$discount = 0;
							switch ($wac_discounts["type"]) {
								case 'percentage':
									$discount = ($wac_discounts["value"] / 100) * (float)$product->get_regular_price();
									break;
								case 'fixed':
									$discount = $wac_discounts["value"];
									break;
							}
							$amount = ((float)$product->get_regular_price() - $discount);
							$product->set_sale_price($amount);
							return $amount;
						}
					}
				}
			} else {
				return $this->wac_auto_product_coupon($price, $product);
			}
		}
		return $price;
	}

	/*
	 * automatically change product price
	 */
	public function wac_auto_product_coupon($price, $product)
	{
		$data = WC()->session->get("wac_product_coupon");
		foreach ($data["items"] as $woocoupon) {
			$validate = Validator::check(null, null, $woocoupon);
			if (!$validate) {
				return $price;
			}
			$wac_main        = get_post_meta($woocoupon, "wac_coupon_main", true);
			$wac_coupon_type = $wac_main["type"];
			$wac_discounts = get_post_meta($woocoupon, "wac_coupon_discounts", true);
			$wac_filters     = get_post_meta($woocoupon, "wac_filters", true);

			if ($wac_coupon_type == "product") {
				foreach ($wac_filters as $wac_filter) {
					if ($wac_filter["type"] == "products") {
						foreach ($wac_filter["items"] as $wacproducts) {
							if ($wacproducts["value"] == $product->get_id()) {
								switch ($wac_discounts["type"]) {
									case 'percentage':
										$discount = ($wac_discounts["value"] / 100) * (float)$product->get_regular_price();
										break;
									case 'fixed':
										$discount = $wac_discounts["value"];
										break;
								}
								$amount = ((float)$product->get_regular_price() - $discount);
								$product->set_sale_price($amount);
								return $amount;
							}
						}
						return $price;
					} elseif ($wac_filter["type"] == "all_products") {
						$discount = 0;
						switch ($wac_discounts["type"]) {
							case 'percentage':
								$discount = ($wac_discounts["value"] / 100) * (float)$product->get_regular_price();
								break;
							case 'fixed':
								$discount = $wac_discounts["value"];
								break;
						}
						$amount = ((float)$product->get_regular_price() - $discount);
						$product->set_sale_price($amount);
						return $amount;
					}
				}
			} else {
				return $price;
			}
		}
		return $price;
	}

	public function wac_variable_change_price($price, $product)
	{
		$data = WC()->session->get("wac_product_coupon");
		foreach ($data["items"] as $woocoupon) {
			$validate = Validator::check(null, null, $woocoupon);
			if (!$validate) {
				return $price;
			}
			$wac_main        = get_post_meta($woocoupon, "wac_coupon_main", true);
			$wac_coupon_type = $wac_main["type"];
			$wac_discounts = get_post_meta($woocoupon, "wac_coupon_discounts", true);
			$wac_filters     = get_post_meta($woocoupon, "wac_filters", true);

			if ($wac_coupon_type == "product") {
				foreach ($wac_filters as $wac_filter) {
					if ($wac_filter["type"] == "products") {
						foreach ($wac_filter["items"] as $wacproducts) {
							if ($wacproducts["value"] == $product->get_parent_id()) {
								switch ($wac_discounts["type"]) {
									case 'percentage':
										$discount = ($wac_discounts["value"] / 100) * (float)$product->get_regular_price();
										break;
									case 'fixed':
										$discount = $wac_discounts["value"];
										break;
								}
								$amount = ((float)$product->get_regular_price() - $discount);
								$product->set_sale_price($amount);
								return $amount;
							}
						}
						return $price;
					} elseif ($wac_filter["type"] == "all_products") {
						$discount = 0;
						switch ($wac_discounts["type"]) {
							case 'percentage':
								$discount = ($wac_discounts["value"] / 100) * (float)$product->get_regular_price();
								break;
							case 'fixed':
								$discount = $wac_discounts["value"];
								break;
						}
						$amount = ((float)$product->get_regular_price() - $discount);
						$product->set_sale_price($amount);
						return $amount;
					}
				}
			} else {
				return $price;
			}
		}
		return $price;
	}
}
