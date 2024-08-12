<?php
// Enqueue scripts and styles for both admin and public
function pe_enqueue_assets() {
    // Enqueue jQuery UI Datepicker
    wp_enqueue_style('jquery-ui-datepicker');
    wp_enqueue_script('jquery-ui-datepicker');


    // Enqueue jQuery UI Timepicker Addon CSS and JS
    wp_enqueue_style('jquery-ui-timepicker-addon', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-ui-timepicker-addon/1.6.3/jquery-ui-timepicker-addon.min.css', array('jquery-ui-datepicker'));
    wp_enqueue_script('jquery-ui-timepicker-addon', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-ui-timepicker-addon/1.6.3/jquery-ui-timepicker-addon.min.js', array('jquery-ui-datepicker'), false, true);

    // Enqueue Font Awesome
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');

    // Enqueue jQuery UI Autocomplete
    wp_enqueue_script('jquery-ui-autocomplete');

    // Enqueue Custom Script
    wp_enqueue_script('pe-custom-script', plugins_url('../assets/js/pe-custom-script.js', __FILE__), array('jquery', 'jquery-ui-datepicker', 'jquery-ui-timepicker-addon', 'jquery-ui-autocomplete'), false, true);
    wp_enqueue_script('jquery');
    wp_enqueue_script('pe-autocomplete', plugin_dir_url(__FILE__) . '../assets/js/pe-autocomplete.js', array('jquery'), null, true);
    wp_localize_script('pe-autocomplete', 'pe_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
    ));
    // Enqueue Custom CSS
    wp_enqueue_style('ticket2n-style', plugins_url('../assets/css/style.css', __FILE__));

    // Initialize datepicker and timepicker
    wp_add_inline_script('pe-custom-script', '
        jQuery(document).ready(function($) {
            $("#_custom_start_datetime, #_custom_end_datetime").datetimepicker({
                dateFormat: "yy-mm-dd",
                timeFormat: "HH:mm"
            });
        });
    ');
}
add_action('admin_enqueue_scripts', 'pe_enqueue_assets');
add_action('wp_enqueue_scripts', 'pe_enqueue_assets');

?>