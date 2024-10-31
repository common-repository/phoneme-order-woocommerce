<?php

/*
 * Plugin Name: PhoneMe Order for WooCommerce
 * Plugin URI:  http://wordpress.org/plugins/phoneme-order-woocommerce/
 * Description: Fast order creation with just a phone
 * Author:      Adrian Dimitrov <dimitrov.adrian@gmail.com>
 * Author URI:  http://e01.scifi.bg/
 * Version:     1.0
 * Text Domain: phoneme-order-woocommerce
 * Domain Path: /languages/
 */



/**
 * Class PhoneMeOrderWooCommerce
 */
class PhoneMeOrderWooCommerce {


  /**
   * Plugin constructor
   */
  function __construct() {
    add_action('plugins_loaded', array($this, 'plugins_loaded'));
  }


  /**
   * Attach some important actions
   */
  function plugins_loaded() {

    // L10N
    load_plugin_textdomain('phoneme-order-woocommerce', FALSE, dirname(plugin_basename(__FILE__)) . '/languages/');

    // AJAX handler
    add_action('wp_ajax_phoneme-order-woocommerce', array($this, 'phoneme_order_woocommerce_submit_ajax'));
    add_action('wp_ajax_nopriv_phoneme-order-woocommerce', array($this, 'phoneme_order_woocommerce_submit_ajax'));

    // Ouput form
    add_action('woocommerce_single_product_summary', array($this, 'woocommerce_single_product_summary_phoneme_order_form'), 100);

    // Global settings
    add_filter('woocommerce_get_settings_checkout', array($this, 'woocommerce_get_settings_checkout'));

    // Assets
    add_action('wp_enqueue_scripts', array($this, 'wp_enqueue_scripts'));

  }


  /**
   * Remove plugin specific settings.
   */
  function uninstall() {
    delete_option('phoneme_order_woocommerce_title_1');
    delete_option('phoneme_order_woocommerce_title_2');
    delete_option('phoneme_order_woocommerce_hint_text');
    delete_option('phoneme_order_woocommerce_outofstock');
  }


  /**
   * Init settings.
   */
  function install() {
    add_option('phoneme_order_woocommerce_title_1', __('Quick checkout', 'phoneme-order-woocommerce'));
    add_option('phoneme_order_woocommerce_title_2', __('No registrations and passwords', 'phoneme-order-woocommerce'));
    add_option('phoneme_order_woocommerce_hint_text', __('Just use your phone number to checkout and wait our assistant to contact your do clarify the order.', 'phoneme-order-woocommerce'));
    add_option('phoneme_order_woocommerce_outofstock', 'hide');
  }


  /**
   * Enqueue assets.
   */
  function wp_enqueue_scripts() {
    wp_register_style('phoneme-order-woocommerce', plugins_url('assets/phoneme-order-woocommerce.css', __FILE__));
    wp_register_script('phoneme-order-woocommerce', plugins_url('assets/phoneme-order-woocommerce.js', __FILE__), array('jquery'), NULL, TRUE);

    if (is_product()) {
      wp_enqueue_style('phoneme-order-woocommerce');
    }
  }


  /**
   * Phone Me Order settings
   */
  function woocommerce_get_settings_checkout($fields) {

    $fields[] = array(
      'title' => __('Phone Me Order', 'phoneme-order-woocommerce'),
      'type' => 'title',
      'id' => 'phoneme_order_woocommerce',
    );

    $fields[] = array(
      'title' => __('Heading', 'phoneme-order-woocommerce'),
      'id' => 'phoneme_order_woocommerce_title_1',
      'autoload' => 1,
      'type' => 'text',
    );

    $fields[] = array(
      'title' => __('Second heading', 'phoneme-order-woocommerce'),
      'id' => 'phoneme_order_woocommerce_title_2',
      'autoload' => 1,
      'type' => 'text',
    );

    $fields[] = array(
      'title' => __('Hint text', 'phoneme-order-woocommerce'),
      'id' => 'phoneme_order_woocommerce_hint_text',
      'autoload' => 1,
      'type' => 'textarea',
      'class' => 'widefat',
    );

    $fields[] = array(
      'title' => __('Out of stock', 'phoneme-order-woocommerce'),
      'id' => 'phoneme_order_woocommerce_outofstock',
      'autoload' => 1,
      'type' => 'select',
      'options' => array(
        'hide' => __('Hide', 'phoneme-order-woocommerce'),
        'inactive' => __('Inactive', 'phoneme-order-woocommerce'),
      ),
      'default' => 'hide',
    );

    $fields[] = array(
      'id' => 'phoneme_order_woocommerce',
      'type' => 'sectionend',
    );

    return $fields;
  }


  /**
   * Plugin product form
   */
  function woocommerce_single_product_summary_phoneme_order_form() {

    global $product;

    // Return aka skip if out of stock.
    if (!$product->is_in_stock() && 'hide' == get_option('phoneme_order_woocommerce_outofstock')) {
      return;
    }

    // Load the assets.
    wp_enqueue_script('phoneme-order-woocommerce');

    if (FALSE && !empty($_POST['phoneme-order-woocommerce'])) {
      $no_ajax_status = $this->phoneme_order_woocommerce_submit();
    }
    else {
      $no_ajax_status = array('status' => 0, 'message' => '');
    }

    ?>
    <div class="phoneme-order-woocommerce">
      <div id="phoneme-order-woocommerce-form" data-ajax-url="<?php echo admin_url('admin-ajax.php')?>">
        <div class="phoneme-order-woocommerce-header">
          <div class="first"><?php echo get_option('phoneme_order_woocommerce_title_1')?></div>
          <div class="secondary"><?php echo get_option('phoneme_order_woocommerce_title_2', __('No registrations and passwords', 'phoneme-order-woocommerce'))?></div>
        </div>
        <div class="hint-text">
          <?php echo get_option('phoneme_order_woocommerce_hint_text', __('Just use your phone number to checkout and wait our assistant to contact your do clarify the order.', 'phoneme-order-woocommerce'))?>
        </div>
        <?php if (!$no_ajax_status['status']):?>
        <div class="form-elements">
          <label>
            <?php _e('Phone', 'phoneme-order-wooocommerce') ?>
          </label>
          <input type="hidden" name="product_id" value="<?php the_ID() ?>" />
          <input type="hidden" name="variation_id" value="" />
          <input required="required" type="tel" name="phone" placeholder="<?php _e('Phone', 'phoneme-order-wooocommerce') ?>" maxlength="30" />
          <input type="hidden" name="action" value="phoneme-order-woocommerce" />
          <button type="submit" name="phoneme-order-woocommerce" value="true">
            <?php _e('Order', 'phoneme-order-wooocommerce') ?>
          </button>
        </div>
        <?php endif?>
        <div class="status-message"><?php echo $no_ajax_status['message']?></div>
      </div>
    </div>
    <?php
  }


  /**
   * AJAX submiter
   */
  function phoneme_order_woocommerce_submit_ajax() {
    $response = $this->phoneme_order_woocommerce_submit();
    wp_send_json($response);
    exit;
  }


  /**
   * Searching method
   */
  function phoneme_order_woocommerce_submit() {

    $phone = empty($_POST['phone']) ? '' : $_POST['phone'];
    if (strlen($phone) < 5 || strlen($phone) > 20 || !preg_match('#(\+|00)?(?:[\s\-\.]?\d+)+#', $phone)) {
      return array(
        'status' => 0,
        'message' => __('Invalid phone number', 'phoneme-order-woocommerce'),
      );
    }

    // Get product or variant.
    $variation = empty($_POST['variation_id']) ? 0 : wc_get_product($_POST['variation_id']);
    $product = empty($_POST['product_id']) ? 0 : wc_get_product($_POST['product_id']);
    $qty = !empty($_POST['quantity']) && is_numeric($_POST['quantity']) && $_POST['quantity'] > 0 ? $_POST['quantity'] : 1;

    if (!$product) {
      return array(
        'status'  => 0,
        'message' => __('Invalid product or variant', 'phoneme-order-woocommerce'),
      );
    }

    // Attempt to create order post.
    $order = wc_create_order();
    if (is_wp_error($order)) {
      return array(
        'status' => 0,
        'message' => __('Can\'t create order.', 'phoneme-order-woocommerce'),
      );
    }
    $order->add_order_note(__('Incoming order via PhoneMe method', 'phoneme-order-woocommerce'));

    $order_args = array();
    if ($variation) {
      $order_args['variation'] = $variation;
    }
    $order->add_product($product, $qty, $order_args);
    $order->set_total($order->calculate_totals());
    $order->set_address(array('phone' => $phone));
    // TODO integrate woocommerce google analytics

    return array(
      'status'  => 1,
      'id' => ($variation ? $variation->id : $product->id),
      'message' => __('Your order is placed, thank for ordering.', 'phoneme-order-woocommerce'),
    );

  }

}


$PhoneMeOrderWooCommerce = new PhoneMeOrderWooCommerce();

register_activation_hook(__FILE__, array($PhoneMeOrderWooCommerce, 'install'));
register_deactivation_hook(__FILE__, array($PhoneMeOrderWooCommerce, 'uninstall'));