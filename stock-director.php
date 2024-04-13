<?php

/**
 * Plugin Name: Stock Director
 * Description: Changes the way stock statuses are displayed in WooCommerce.
 * Version: 1.0
 * Author: Sławomir Kasprzak
 * Author URI: https://wellmade.online
 * Text Domain: stock-director
 * Domain Path: /languages
 */

// Enqueue scripts and styles
add_action('admin_enqueue_scripts', 'mws_enqueue_scripts');

function mws_enqueue_scripts($hook)
{
  if ('toplevel_page_stock-director-settings' !== $hook) {
    return;
  }

  // Enqueue Vue.js from a CDN for production use
  wp_register_script('vuejs', plugins_url('/assets/js/vue.global.prod.min.js', __FILE__), array(), '3.4.21', true);
  wp_enqueue_script('vuejs');


  // Enqueue custom admin CSS
  wp_enqueue_style('mws-admin-css', plugins_url('admin/css/admin-style.css', __FILE__));

  // Get saved conditions and decode JSON if necessary.
  $saved_conditions = get_option('mws_stock_conditions', '[]');
  //check type of $saved_conditions and if it is string, decode it
  if (is_string($saved_conditions)) {
    $saved_conditions = json_decode($saved_conditions, true);
  }

  // Register Vue script.
  wp_enqueue_script('mws-admin-js', plugins_url('admin/js/admin-script.min.js', __FILE__), array('vuejs'), '1.0.0', true);

  // Prepare data to pass to the script.
  $script_data = array(
    'ajax_url' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('mws_nonce'),
    'conditions' => $saved_conditions, // Now it will be a proper PHP array.
    'reloadMessage' => __('Are you sure you want to reload the page? All unsaved changes will be lost.', 'stock-director'),
  );
  // Localize data (for AJAX settings and nonce).
  wp_localize_script('mws-admin-js', 'mwsData', $script_data);

  // Add conditions data as inline script.
  wp_add_inline_script('mws-admin-js', 'const initialConditions = ' . wp_json_encode($script_data['conditions']) . ';', 'before');
}

// Register settings page
add_action('admin_menu', 'mws_register_settings_page');

function mws_register_settings_page()
{
  add_menu_page(
    __('Stock Status Settings', 'stock-director'), // Page title
    __('Stock Status', 'stock-director'), // Menu title
    'manage_options', // Capability
    'stock-director-settings', // Menu slug
    'wp_stock_director_settings_page', // Function to display page content
    'dashicons-store', // Icon
    7 // Position in menu
  );
}

function wp_stock_director_settings_page()
{
  // First, we load the file that contains the is_plugin_active() function
  if (!function_exists('is_plugin_active')) {
    include_once(ABSPATH . 'wp-admin/includes/plugin.php');
  }

  // Check if WooCommerce is active
  if (!is_plugin_active('woocommerce/woocommerce.php')) {
    echo '<div class="wrap"><h1>' . esc_html__('Stock Status Settings', 'stock-director') . '</h1>';
    echo '<h2>' . esc_html__('This feature requires WooCommerce to be installed and active.', 'stock-director') . '</h2></div>';
    return; // Early return if WooCommerce is not active
  }

  // If WooCommerce is active, load the settings
  include plugin_dir_path(__FILE__) . 'admin/partials/settings-page.php';
}


// Load plugin text domain for translations
function wp_stock_director_load_textdomain()
{
  load_plugin_textdomain('stock-director', false, basename(dirname(__FILE__)) . '/languages/');
}
add_action('plugins_loaded', 'wp_stock_director_load_textdomain');



// AJAX handling for saving settings
add_action('wp_ajax_save_conditions', 'mws_save_conditions');
function mws_save_conditions()
{
  // Check nonce for security
  check_ajax_referer('mws_nonce', 'nonce');

  // Get data sent via AJAX
  $conditions = isset($_POST['conditions']) ? json_decode(stripslashes($_POST['conditions']), true) : array();

  //convert to array if it is string
  if (is_string($conditions)) {
    $conditions = json_decode($conditions, true);
  }

  // Validate each key and value in the $conditions array
  foreach ($conditions as $key => $value) {
    if (!is_numeric($key) || !is_array($value)) {
      wp_send_json_error('Invalid data provided.');
      return;
    }
  }


  if (update_option('mws_stock_conditions', $conditions)) {
    $saved_conditions = get_option('mws_stock_conditions');
    mws_register_strings_for_translation();
    // Return the response
    wp_send_json_success('Settings saved successfully.');
  } else {
    // Handle the error if failed to update the data
    wp_send_json_error('Failed to save settings.');
  }
}

//TODO: Look for a better way to implement this function
function mws_get_condition_message(
  $min,
  $max,
  $message
) {
  if (defined('ICL_SITEPRESS_VERSION')) {
    // For WPML
    return apply_filters('wpml_translate_single_string', $message, 'stock-director', $message);
  } elseif (function_exists('pll__')) {
    // For Polylang
    return pll__($message);
  }
  // Fallback for situations without translation plugin
  return $message;
}

add_filter('woocommerce_get_availability_text', 'mws_custom_availability_text', 10, 2);

function mws_custom_availability_text($availability, $product)
{
  // First, we check if stock management is enabled and if the product is in stock
  if (!$product->managing_stock() || !$product->is_in_stock()) {
    return $availability;
  }
  // Get the saved conditions
  $saved_conditions = get_option('mws_stock_conditions', '[]');
  // Decode JSON if necessary and check the type
  if (is_string($saved_conditions)) {
    $saved_conditions = json_decode($saved_conditions, true);
  }


  // If no conditions are defined, return the default availability
  if (empty($saved_conditions)) {
    return $availability;
  }

  // Get the current stock quantity of the product
  $stock_quantity = $product->get_stock_quantity();

  // Iterate through the conditions to find a matching range
  foreach ($saved_conditions as $condition) {
    if ($stock_quantity >= $condition['minQuantity'] && $stock_quantity <= $condition['maxQuantity']) {
      // Return the translated message if the range matches
      return mws_get_condition_message($condition['minQuantity'], $condition['maxQuantity'], $condition['message']);
    }
  }

  // If no condition matches, return the default availability
  return $availability;
}

function mws_register_strings_for_translation()
{

  if (function_exists('pll_register_string')) {
    $saved_conditions = get_option('mws_stock_conditions', '[]');
    // Decode JSON if necessary and check the type
    if (is_string($saved_conditions)) {
      $saved_conditions = json_decode($saved_conditions, true);
    }
    foreach ($saved_conditions as $condition) {
      if (!isset($condition['minQuantity']) || !isset($condition['maxQuantity']) || !isset($condition['message'])) {
        continue;
      }

      $string_name = 'Condition Message ' . $condition['minQuantity'] . '-' . $condition['maxQuantity'];
      $string_value = $condition['message'];

      // Check if the string is already registered
      if (pll__($string_name) !== $string_name) {
        continue; // Skip this iteration if the string is already registered
      }

      pll_register_string($string_name, $string_value, 'stock-director', true);
    }
  }

  if (defined('ICL_SITEPRESS_VERSION')) {
    $saved_conditions = get_option('mws_stock_conditions', '[]');
    // Decode JSON if necessary and check the type
    if (is_string($saved_conditions)) {
      $saved_conditions = json_decode($saved_conditions, true);
    }

    foreach ($saved_conditions as $condition) {
      if (!isset($condition['minQuantity']) || !isset($condition['maxQuantity']) || !isset($condition['message'])) {
        continue;
      }
      $string_name = 'Condition Message ' . $condition['minQuantity'] . '-' . $condition['maxQuantity'];
      $string_value = $condition['message'];

      do_action('wpml_register_single_string', 'stock-director', $string_name, $string_value);
    }
  }
}


/**
 * Add settings link to plugin actions
 *
 * @param  array  $plugin_actions
 * @param  string $plugin_file
 * @since  1.0
 * @return array
 */
function add_plugin_link($plugin_actions, $plugin_file)
{

  $new_actions = array();
  if (basename(plugin_dir_path(__FILE__)) . '/stock-director.php' === $plugin_file) {
    // translators: This translates the word "Settings" in the link.
    $settings_text = __('Settings', 'stock-director');
    $settings_url = esc_url(admin_url('options-general.php?page=stock-director-settings'));
    $new_actions['cl_settings'] = sprintf('<a href="%s">%s</a>', $settings_url, $settings_text);
  }


  return array_merge($new_actions, $plugin_actions);
}
add_filter('plugin_action_links', 'add_plugin_link', 10, 2);
