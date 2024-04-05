<?php

/**
 * Plugin Name: WP Stock Director
 * Description: Changes the way stock statuses are displayed in WooCommerce.
 * Version: 1.0
 * Author: Sławomir Kasprzak
 * Author URI: https://wellmade.online
 * Text Domain: wp-stock-director
 * Domain Path: /languages
 */

// Enqueue scripts and styles
add_action('admin_enqueue_scripts', 'mws_enqueue_scripts');

function mws_enqueue_scripts($hook)
{
  if ('toplevel_page_mws-settings' !== $hook) {
    return;
  }

  // Enqueue Vue.js from a CDN
  // Enqueue Vue.js from a CDN for production use
  wp_enqueue_script('vuejs', 'https://cdn.jsdelivr.net/npm/vue@3.4.21/dist/vue.global.prod.min.js', array(), '3.4.21', true);


  // Enqueue custom admin CSS
  wp_enqueue_style('mws-admin-css', plugins_url('admin/css/admin-style.css', __FILE__));

  // Pobierz zapisane warunki i zdekoduj JSON, jeśli to konieczne.
  $saved_conditions = get_option('mws_stock_conditions', '[]');
  //check type of $saved_conditions and if it is string, decode it
  if (is_string($saved_conditions)) {
    $saved_conditions = json_decode($saved_conditions, true);
  }

  // Rejestrowanie skryptu Vue.
  wp_enqueue_script('mws-admin-js', plugins_url('admin/js/admin-script.min.js', __FILE__), array('vuejs'), '1.0.0', true);

  // Przygotowanie danych do przekazania do skryptu.
  $script_data = array(
    'ajax_url' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('mws_nonce'),
    'conditions' => $saved_conditions, // Teraz to będzie właściwa tablica PHP.
  );
  // Lokalizowanie danych (dla ustawień AJAX i nonce).
  wp_localize_script('mws-admin-js', 'mwsData', $script_data);

  // Dodanie danych warunków jako skryptu inline.
  wp_add_inline_script('mws-admin-js', 'const initialConditions = ' . wp_json_encode($script_data['conditions']) . ';', 'before');
}

// Register settings page
add_action('admin_menu', 'mws_register_settings_page');

function mws_register_settings_page()
{
  add_menu_page(
    __('Stock Status Settings', 'wp-stock-director'), // Page title
    __('Stock Status', 'wp-stock-director'), // Menu title
    'manage_options', // Capability
    'mws-settings', // Menu slug
    'mws_settings_page', // Function to display page content
    null, // Icon
    6 // Position in menu
  );
}

function mws_settings_page()
{
  include plugin_dir_path(__FILE__) . 'admin/partials/settings-page.php';
}

// Load plugin text domain for translations
add_action('init', 'wp_stock_director_load_textdomain');

function wp_stock_director_load_textdomain()
{
  load_plugin_textdomain('wp-stock-director', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}


// Obsługa AJAX do zapisu ustawień
add_action('wp_ajax_save_conditions', 'mws_save_conditions');
function mws_save_conditions()
{
  // Sprawdzamy nonce dla bezpieczeństwa
  check_ajax_referer('mws_nonce', 'nonce');

  // Pobieramy dane przesłane przez AJAX
  $conditions = isset($_POST['conditions']) ? $_POST['conditions'] : array();
  //convert to array
  $conditions = json_decode(stripslashes($conditions), true);
  foreach ($conditions as $condition) {
    if (function_exists('icl_register_string')) {
      // Dla WPML
      icl_register_string('wp-stock-director', 'Condition Message ' . $condition['minQuantity'] . '-' . $condition['maxQuantity'], $condition['message']);
    } elseif (function_exists('pll_register_string')) {
      // Dla Polylang
      pll_register_string('Condition Message ' . $condition['minQuantity'] . '-' . $condition['maxQuantity'], $condition['message'], 'wp-stock-director');
    }
    // Nie dodajemy 'else', bo nie chcemy rejestrować ciągów, jeśli nie ma wtyczki do tłumaczeń.
  }

  // Tutaj dokonujemy zapisu do bazy danych, używając na przykład update_option()
  update_option('mws_stock_conditions', $conditions);
  $saved_conditions = get_option('mws_stock_conditions');


  // Zwracamy odpowiedź
  wp_send_json_success('Settings saved successfully.');
}

//TODO: Look for better way to implement this function
function mws_get_condition_message(
  $min,
  $max,
  $message
) {
  if (function_exists('icl_t')) {
    // Dla WPML
    return icl_t('wp-stock-director', 'Condition Message ' . $min . '-' . $max, $message);
  } elseif (function_exists('pll__')) {
    // Dla Polylang
    return pll__($message);
  }
  // Fallback dla sytuacji bez wtyczki do tłumaczeń
  return $message;
}
