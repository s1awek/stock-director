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
  wp_enqueue_script('vuejs', 'https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.min.js');

  // Enqueue custom admin CSS
  wp_enqueue_style('mws-admin-css', plugins_url('admin/css/admin-style.css', __FILE__));

  // Enqueue custom admin JS
  wp_enqueue_script('mws-admin-js', plugins_url('admin/js/admin-script.min.js', __FILE__), array('vuejs'), '1.0.0', true);
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
  echo '<div class="wrap"><h1>' . esc_html_e('Stock Status Settings', 'wp-stock-director') . '</h1></div>';
}

// Load plugin text domain for translations
add_action('init', 'wp_stock_director_load_textdomain');

function wp_stock_director_load_textdomain()
{
  load_plugin_textdomain('wp-stock-director', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}
