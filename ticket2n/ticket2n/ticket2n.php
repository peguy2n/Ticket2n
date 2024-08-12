<?php
/*
Plugin Name: Ticket2n
Description: Adds event title, date and time, city, venue, promoter name, and artiste name to WooCommerce product short description. Includes pages to list events by city, venue, promoter, and artiste.
Version: 1.0
Author: Peguy2n
*/

// Enqueue scripts and styles for admin and public
require_once plugin_dir_path(__FILE__) . 'includes/enqueue-scripts.php';

// Load admin functionalities
if (is_admin()) {
    require_once plugin_dir_path(__FILE__) . 'admin/admin-init.php';
}

// Load public functionalities
require_once plugin_dir_path(__FILE__) . 'public/public-init.php';

// Shortcodes and Ajax handlers
require_once plugin_dir_path(__FILE__) . 'includes/shortcodes.php';
require_once plugin_dir_path(__FILE__) . 'includes/ajax-handlers.php';

?>
