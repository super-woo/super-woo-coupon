<?php

namespace WpAdroit\Wac_Coupon\Admin;

/**
 * Create WooCoupon Post Type
 */
class Wac_Coupon
{

	function __construct()
	{
		add_action('init', [$this, 'wac_post_type'], 0);
		add_action('add_meta_boxes', array($this, "wac_metaboxes"));
		add_action('admin_enqueue_scripts', [$this, 'wac_enqueue_scripts']);
		add_action('save_post', [$this, 'wac_save_meta_post']);
		add_filter('post_row_actions', [$this, 'wac_post_row_actions'], 10, 2);
		add_filter('manage_woocoupon_posts_columns', [$this, 'wac_custom_columns']);
		add_action('manage_woocoupon_posts_custom_column', [$this, 'wac_custom_columns_data'], 10, 2);
	}

	public function wac_custom_columns($columns)
	{
		$columns['wac_type'] = __('Type', 'superwoo');
		$new = array();
		$wac_type = $columns['wac_type'];
		unset($columns['wac_type']);

		foreach ($columns as $key => $value) {
			if ($key == 'date') {
				$new['wac_type'] = $wac_type;
			}
			$new[$key] = $value;
		}

		return $new;
	}

	public function wac_custom_columns_data($column, $post_id)
	{
		if ($column == "wac_type") {
			$wacMain = get_post_meta($post_id, "wac_coupon_main", true);
			switch ($wacMain["type"]) {
				case 'product':
					echo "<pre class='wac_pre_column'>Product Adjustment</pre>";
					break;
				case 'cart':
					echo "<pre class='wac_pre_column'>Cart Adjustment</pre>";
					break;
				case 'bulk':
					echo "<pre class='wac_pre_column'>Bulk Discount</pre>";
					break;

				default:
					break;
			}
		}
	}

	public function wac_post_row_actions($unset_actions, $post)
	{
		global $current_screen;
		if ($current_screen->post_type != 'woocoupon')
			return $unset_actions;
		unset($unset_actions['inline hide-if-no-js']);
		return $unset_actions;
	}

	public function wac_enqueue_scripts()
	{
		wp_enqueue_script("wac_app");
		wp_enqueue_style("wac_app_css");
		wp_localize_script(
			'wac_app',
			'wac_helper_obj',
			array('ajax_url' => admin_url('admin-ajax.php'))
		);
		wp_localize_script(
			'wac_app',
			'wac_post',
			array('id' => get_the_ID())
		);
	}

	/**
	 * Register Custom Post Type
	 *
	 * @uses register_post_type()
	 **/
	public function wac_post_type()
	{
		$labels = array(
			'name'                  => _x('Woo Coupon\'s', 'Post Type General Name', 'wac'),
			'singular_name'         => _x('Woo Coupon', 'Post Type Singular Name', 'wac'),
			'menu_name'             => __('Woo Coupon\'s', 'superwoo'),
			'name_admin_bar'        => __('Woo Coupon\'s', 'superwoo'),
			'archives'              => __('Item Archives', 'superwoo'),
			'attributes'            => __('Item Attributes', 'superwoo'),
			'parent_item_colon'     => __('Parent Coupon:', 'superwoo'),
			'all_items'             => __('Coupons', 'superwoo'),
			'add_new_item'          => __('Add New Coupon', 'superwoo'),
			'add_new'               => __('Add New', 'superwoo'),
			'new_item'              => __('New Coupon', 'superwoo'),
			'edit_item'             => __('Edit Coupon', 'superwoo'),
			'update_item'           => __('Update Coupon', 'superwoo'),
			'view_item'             => __('View Coupon', 'superwoo'),
			'view_items'            => __('View Coupons', 'superwoo'),
			'search_items'          => __('Search Coupon', 'superwoo'),
			'not_found'             => __('Not found', 'superwoo'),
			'not_found_in_trash'    => __('Not found in Trash', 'superwoo'),
			'featured_image'        => __('Featured Image', 'superwoo'),
			'set_featured_image'    => __('Set featured image', 'superwoo'),
			'remove_featured_image' => __('Remove featured image', 'superwoo'),
			'use_featured_image'    => __('Use as featured image', 'superwoo'),
			'insert_into_item'      => __('Insert into item', 'superwoo'),
			'uploaded_to_this_item' => __('Uploaded to this item', 'superwoo'),
			'items_list'            => __('Items list', 'superwoo'),
			'items_list_navigation' => __('Items list navigation', 'superwoo'),
			'filter_items_list'     => __('Filter items list', 'superwoo'),
		);
		$args = array(
			'label'                 => __('Woo Coupon', 'superwoo'),
			'description'           => __('Advanced Coupon Maker By WpAdroit', 'superwoo'),
			'labels'                => $labels,
			'supports'              => array('title'),
			'taxonomies'            => array('title'),
			'hierarchical'          => false,
			'public'                => false,
			'show_ui'               => true,
			'show_in_menu'          => "superwoo-addons",
			'menu_position'         => 50,
			'menu_icon'             => 'dashicons-nametag',
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => true,
			'can_export'            => true,
			'has_archive'           => true,
			'exclude_from_search'   => false,
			'publicly_queryable'    => false,
			'rewrite'               => false,
			'capability_type'       => 'page',
		);
		register_post_type('woocoupon', $args);
	}

	/**
	 * create Meta Box
	 *
	 * @uses add_meta_box
	 **/
	public function wac_metaboxes()
	{
		// Wac Discount Type
		add_meta_box(
			'wac_type_box',
			'Coupon Type',
			[$this, 'wac_type_screen'],
			'woocoupon',
			'normal',
			'default'
		);

		// Wac Filter
		add_meta_box(
			'wac_filter_box',
			'Coupon Filters',
			[$this, 'wac_filter_screen'],
			'woocoupon',
			'normal',
			'default'
		);

		// Wac Discount
		add_meta_box(
			'wac_discount_box',
			'Coupon Discounts',
			[$this, 'wac_discount_screen'],
			'woocoupon',
			'normal',
			'default'
		);

		// Wac Rules
		add_meta_box(
			'wac_rules_box',
			'Coupon Rules (optional)',
			[$this, 'wac_rules_screen'],
			'woocoupon',
			'normal',
			'default'
		);
	}

	/**
	 * Screen of Type Box
	 **/
	public function wac_type_screen()
	{
		$nonce = wp_create_nonce('wac_without_ajax');
?>
		<wactype :nonce='<?php echo json_encode($nonce); ?>' />
	<?php
	}

	/**
	 * Screen of Filter Box
	 **/
	public function wac_filter_screen()
	{
		$nonce = wp_create_nonce('wac_with_ajax');
	?>
		<wacfilter :nonce='<?php echo json_encode($nonce); ?>' />
	<?php
	}

	/**
	 * Screen of Discount Box
	 */
	public function wac_discount_screen()
	{
	?>
		<wacdiscount />
	<?php
	}

	/**
	 * Screen of Rules Box
	 */
	public function wac_rules_screen()
	{
	?>
		<wacrules />
<?php
	}

	/**
	 * save post meta
	 **/
	public function wac_save_meta_post($post_id)
	{
		if (!isset($_POST["wac_coupon_type"])) {
			return;
		}

		if (!wp_verify_nonce($_POST["wac_main_nonce"], "wac_without_ajax")) {
			wp_die(__('Sorry !! You cannot permit to access.', 'superwoo'));
		}

		$type = $_POST["wac_coupon_type"];

		if (isset($_POST["wac_discount_label"])) {
			$discount_label = $_POST["wac_discount_label"];
		} else {
			$discount_label = null;
		}

		$main = [
			"type" => $type,
			"label" => $discount_label
		];

		$discount_type = $_POST["wac_discount_type"];
		$discount_value = $_POST["wac_discount_value"] ? $_POST["wac_discount_value"] : 0;

		if (isset($_POST["discountLength"]) && $type == "bulk") {
			$discountLength = $_POST["discountLength"];
			$wac_discount = [];
			for ($i = 0; $i < $discountLength; $i++) {
				array_push($wac_discount, [
					"min" => $_POST["wac_discount_min_" . $i],
					"max" => $_POST["wac_discount_max_" . $i],
					"type" => $_POST["wac_discount_type_" . $i],
					"value" => $_POST["wac_discount_value_" . $i] ? $_POST["wac_discount_value_" . $i] : 0
				]);
			}
		} else {
			$wac_discount = [
				"type" => $discount_type,
				"value" => $discount_value
			];
		}

		$rulesLength = $_POST["rulesLength"];
		$wac_rules = [];
		if ($rulesLength == 0) {
			$wac_rules = null;
		} else {
			for ($i = 0; $i < $rulesLength; $i++) {
				array_push($wac_rules, [
					"type" => $_POST["wac_rule_type_" . $i],
					"operator" => $_POST["wac_rule_operator_" . $i],
					"item_count" => $_POST["wac_rule_item_" . $i],
					"calculate" => $_POST["wac_rule_calculate_" . $i]
				]);
			}
		}

		if ($type == "product") {
			$wac_rules = null;
		}

		$rules = [
			"relation" => $_POST["wac_rule_relation"] ? $_POST["wac_rule_relation"] : "match_all",
			"rules" => $wac_rules
		];

		$filters = get_post_meta($post_id, "wac_filters", true);

		if (!$filters) {
			$wac_filters = [[
				"type" => "all_products",
				"lists" => "inList",
				"items" => []
			]];
			update_post_meta($post_id, "wac_filters", $wac_filters);
		}

		update_post_meta($post_id, "wac_coupon_main", $main);
		update_post_meta($post_id, "wac_coupon_discounts", $wac_discount);
		update_post_meta($post_id, "wac_coupon_rules", $rules);
	}
}
