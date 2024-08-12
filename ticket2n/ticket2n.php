<?php
/*
Plugin Name: Ticket2n
Description: Adds event title, date and time, city, venue, promoter name, and artiste name to WooCommerce product short description. Includes pages to list events by city, venue, promoter, and artiste.
Version: 1.0
Author: Peguy2n
*/

// Enqueue scripts and styles
add_action('admin_enqueue_scripts', 'pe_enqueue_assets');
function pe_enqueue_assets() {
    // Enqueue datetimepicker script and styles
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_script('jquery-ui-timepicker', 'https://cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js', array('jquery-ui-datepicker'), null, true);
    wp_enqueue_style('jquery-ui-css', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
    wp_enqueue_style('jquery-ui-timepicker-css', 'https://cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css');

    // Enqueue Autocomplete script and style
    wp_enqueue_script('jquery-ui-autocomplete');
    wp_enqueue_style('jquery-ui-autocomplete', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');

    // Enqueue Font Awesome for icons
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');

    // Custom script to initialize datetimepicker and autocomplete
    wp_add_inline_script('jquery-ui-timepicker', 'jQuery(document).ready(function($) {
        $("#_custom_datetime").datetimepicker({
            dateFormat: "yy-mm-dd",
            timeFormat: "HH:mm"
        });

        // Autocomplete for city, venue, promoter, and artiste fields
        $("#_custom_city, #_custom_venue, #_custom_promoter_name, #_custom_artiste_name").autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: ajaxurl,
                    dataType: "json",
                    data: {
                        action: "pe_autocomplete_city_venue_promoter_artiste",
                        term: request.term
                    },
                    success: function(data) {
                        response(data);
                    }
                });
            },
            minLength: 3 // Minimum length of input before autocomplete activates
        });
    });');
}

// Ajax handler for city, venue, promoter, and artiste autocomplete
add_action('wp_ajax_pe_autocomplete_city_venue_promoter_artiste', 'pe_autocomplete_city_venue_promoter_artiste');
function pe_autocomplete_city_venue_promoter_artiste() {
    global $wpdb;

    $term = sanitize_text_field($_GET['term']);

    // Query cities, venues, promoters, and artistes
    $results = $wpdb->get_results(
        $wpdb->prepare("
            SELECT DISTINCT meta_value
            FROM {$wpdb->postmeta}
            WHERE meta_key IN ('_custom_city', '_custom_venue', '_custom_promoter_name', '_custom_artiste_name')
            AND meta_value LIKE %s
            ORDER BY meta_value ASC
            LIMIT 10
        ", '%' . $wpdb->esc_like($term) . '%'),
        ARRAY_A
    );

    // Format results for autocomplete
    $suggestions = array();
    foreach ($results as $row) {
        $suggestions[] = $row['meta_value'];
    }

    // Return JSON response
    wp_send_json($suggestions);
}

// Add custom fields to product edit page
add_action('woocommerce_product_options_general_product_data', 'pe_add_custom_fields');
function pe_add_custom_fields() {
    woocommerce_wp_text_input(
        array(
            'id'          => '_custom_event_title',
            'label'       => __('Event Title', 'woocommerce'),
            'placeholder' => 'Event Title',
            'desc_tip'    => 'true',
            'description' => __('Enter the event title.', 'woocommerce')
        )
    );

    woocommerce_wp_text_input(
        array(
            'id'          => '_custom_datetime',
            'label'       => __('Event Date and Time', 'woocommerce'),
            'placeholder' => 'YYYY-MM-DD HH:MM',
            'desc_tip'    => 'true',
            'description' => __('Enter the event date and time.', 'woocommerce'),
            'class'       => 'short', // Needed for datetimepicker styling
        )
    );

    woocommerce_wp_text_input(
        array(
            'id'          => '_custom_city',
            'label'       => __('Event City', 'woocommerce'),
            'placeholder' => 'City',
            'desc_tip'    => 'true',
            'description' => __('Enter the event city.', 'woocommerce')
        )
    );

    woocommerce_wp_text_input(
        array(
            'id'          => '_custom_venue',
            'label'       => __('Event Venue', 'woocommerce'),
            'placeholder' => 'Venue',
            'desc_tip'    => 'true',
            'description' => __('Enter the event venue.', 'woocommerce')
        )
    );

    woocommerce_wp_text_input(
        array(
            'id'          => '_custom_promoter_name',
            'label'       => __('Promoter Name', 'woocommerce'),
            'placeholder' => 'Promoter Name',
            'desc_tip'    => 'true',
            'description' => __('Enter the promoter name.', 'woocommerce')
        )
    );

    woocommerce_wp_text_input(
        array(
            'id'          => '_custom_artiste_name',
            'label'       => __('Artiste Name', 'woocommerce'),
            'placeholder' => 'Artiste Name',
            'desc_tip'    => 'true',
            'description' => __('Enter the artiste name.', 'woocommerce')
        )
    );
}

// Save custom fields
add_action('woocommerce_process_product_meta', 'pe_save_custom_fields');
function pe_save_custom_fields($post_id) {
    $custom_event_title = isset($_POST['_custom_event_title']) ? sanitize_text_field($_POST['_custom_event_title']) : '';
    $custom_datetime = isset($_POST['_custom_datetime']) ? sanitize_text_field($_POST['_custom_datetime']) : '';
    $custom_city = isset($_POST['_custom_city']) ? sanitize_text_field($_POST['_custom_city']) : '';
    $custom_venue = isset($_POST['_custom_venue']) ? sanitize_text_field($_POST['_custom_venue']) : '';
    $custom_promoter_name = isset($_POST['_custom_promoter_name']) ? sanitize_text_field($_POST['_custom_promoter_name']) : '';
    $custom_artiste_name = isset($_POST['_custom_artiste_name']) ? sanitize_text_field($_POST['_custom_artiste_name']) : '';

    update_post_meta($post_id, '_custom_event_title', $custom_event_title);
    update_post_meta($post_id, '_custom_datetime', $custom_datetime);
    update_post_meta($post_id, '_custom_city', $custom_city);
    update_post_meta($post_id, '_custom_venue', $custom_venue);
    update_post_meta($post_id, '_custom_promoter_name', $custom_promoter_name);
    update_post_meta($post_id, '_custom_artiste_name', $custom_artiste_name);

    // Check if city page exists, create if not
    if (!empty($custom_city)) {
        $city_slug = sanitize_title($custom_city);
        $city_page_id = pe_create_or_get_event_page('events/city', $city_slug, 'Events in ' . $custom_city);
        update_post_meta($post_id, '_city_page_id', $city_page_id);
    }

    // Check if venue page exists, create if not
    if (!empty($custom_venue)) {
        $venue_slug = sanitize_title($custom_venue);
        $venue_page_id = pe_create_or_get_event_page('events/venue', $venue_slug, 'Events at ' . $custom_venue);
        update_post_meta($post_id, '_venue_page_id', $venue_page_id);
    }

    // Check if promoter page exists, create if not
    if (!empty($custom_promoter_name)) {
        $promoter_slug = sanitize_title($custom_promoter_name);
        $promoter_page_id = pe_create_or_get_event_page('events/promoter', $promoter_slug, 'Events by ' . $custom_promoter_name);
        update_post_meta($post_id, '_promoter_page_id', $promoter_page_id);
    }

    // Check if artiste page exists, create if not
    if (!empty($custom_artiste_name)) {
        $artiste_slug = sanitize_title($custom_artiste_name);
        $artiste_page_id = pe_create_or_get_event_page('events/artiste', $artiste_slug, 'Events featuring ' . $custom_artiste_name);
        update_post_meta($post_id, '_artiste_page_id', $artiste_page_id);
    }
}

// Create or get event page function
function pe_create_or_get_event_page($parent_slug, $page_slug, $page_title) {
    $parent_page_id = pe_get_parent_page_id($parent_slug);

    // Check if child page exists
    $child_page_id = pe_get_page_id_by_slug($page_slug);

    if (!$child_page_id) {
        // Create child page if it doesn't exist
        $child_page_id = wp_insert_post(array(
            'post_title' => $page_title,
            'post_name' => $page_slug,
            'post_parent' => $parent_page_id,
            'post_status' => 'publish',
            'post_type' => 'page',
        ));
        update_post_meta($child_page_id, '_wp_page_template', 'default');
    }

    return $child_page_id;
}

// Get parent page ID by slug
function pe_get_parent_page_id($parent_slug) {
    $parent_page = get_page_by_path($parent_slug);
    return $parent_page ? $parent_page->ID : 0;
}

// Get page ID by slug
function pe_get_page_id_by_slug($slug) {
    $page = get_page_by_path($slug);
    return $page ? $page->ID : 0;
}

// Modify short description to include custom event info
add_filter('woocommerce_short_description', 'pe_display_custom_fields_in_short_description', 10, 1);
function pe_display_custom_fields_in_short_description($short_description) {
    global $post;

    $custom_event_title = get_post_meta($post->ID, '_custom_event_title', true);
    $custom_datetime = get_post_meta($post->ID, '_custom_datetime', true);
    $custom_city = get_post_meta($post->ID, '_custom_city', true);
    $custom_venue = get_post_meta($post->ID, '_custom_venue', true);
    $custom_promoter_name = get_post_meta($post->ID, '_custom_promoter_name', true);
    $custom_artiste_name = get_post_meta($post->ID, '_custom_artiste_name', true);

    if (!empty($custom_event_title) || !empty($custom_datetime) || !empty($custom_city) || !empty($custom_venue) || !empty($custom_promoter_name) || !empty($custom_artiste_name)) {
        $extra_info = '';

        if (!empty($custom_event_title)) {
            $extra_info .= '<h2>' . esc_html($custom_event_title) . '</h2>';
        }
        if (!empty($custom_datetime)) {
            $datetime = strtotime($custom_datetime);
            $formatted_datetime = date_i18n('l, d/m/Y | H:i', $datetime); // Formats the date and time
            $extra_info .= '<p><i class="fas fa-calendar-alt"></i> ' . esc_html($formatted_datetime) . '</p>';
        }
        if (!empty($custom_city)) {
            $city_page_id = get_post_meta($post->ID, '_city_page_id', true);
            $city_link = $city_page_id ? get_permalink($city_page_id) : '#';
            $extra_info .= '<p><i class="fas fa-map-marker-alt"></i> <a href="' . esc_url($city_link) . '">' . strtoupper(esc_html($custom_city)) . ' | ' . esc_html($custom_venue) . '</a></p>';
        }
        if (!empty($custom_promoter_name)) {
            $promoter_page_id = get_post_meta($post->ID, '_promoter_page_id', true);
            $promoter_link = $promoter_page_id ? get_permalink($promoter_page_id) : '#';
            $extra_info .= '<p><i class="fas fa-user"></i> <a href="' . esc_url($promoter_link) . '">' . esc_html($custom_promoter_name) . '</a></p>';
        }
        if (!empty($custom_artiste_name)) {
            $artiste_page_id = get_post_meta($post->ID, '_artiste_page_id', true);
            $artiste_link = $artiste_page_id ? get_permalink($artiste_page_id) : '#';
            $extra_info .= '<p><i class="fas fa-microphone-alt"></i> <a href="' . esc_url($artiste_link) . '">' . esc_html($custom_artiste_name) . '</a></p>';
        }

        $short_description .= '<div class="custom-event-info">' . $extra_info . '</div>';
    }

    return $short_description;
}


// Shortcode to list events by city
add_shortcode('list_events_by_city', 'pe_list_events_by_city');
function pe_list_events_by_city($atts) {
    $atts = shortcode_atts(array(
        'city' => '', // Default city slug
    ), $atts);

    $city_page = get_page_by_path('events/city/' . sanitize_title($atts['city']));
    if (!$city_page) {
        return '<p>No events found for this city.</p>';
    }

    // Query events related to the city
    $events_query = new WP_Query(array(
        'post_type' => 'product',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => '_custom_city',
                'value' => get_the_title($city_page),
                'compare' => 'LIKE'
            ),
        ),
    ));

    // Start output buffer
    ob_start();

    if ($events_query->have_posts()) {
        echo '<div class="events-list">';
        while ($events_query->have_posts()) {
            $events_query->the_post();
            $custom_venue = get_post_meta(get_the_ID(), '_custom_venue', true);
            echo '<div class="event-item">';
            echo '<h2>' . get_the_title() . '</h2>';
            echo '<p>' . strtoupper(get_the_title($city_page)) . ' | ' . esc_html($custom_venue) . '</p>';
            echo '</div>';
        }
        echo '</div>';
        wp_reset_postdata();
    } else {
        echo '<p>No events found for this city.</p>';
    }

    // Return buffered content
    return ob_get_clean();
}

// Shortcode to list events by venue
add_shortcode('list_events_by_venue', 'pe_list_events_by_venue');
function pe_list_events_by_venue($atts) {
    $atts = shortcode_atts(array(
        'venue' => '', // Default venue slug
    ), $atts);

    $venue_page = get_page_by_path('events/venue/' . sanitize_title($atts['venue']));
    if (!$venue_page) {
        return '<p>No events found for this venue.</p>';
    }

    // Query events related to the venue
    $events_query = new WP_Query(array(
        'post_type' => 'product',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => '_custom_venue',
                'value' => get_the_title($venue_page),
                'compare' => 'LIKE'
            ),
        ),
    ));

    // Start output buffer
    ob_start();

    if ($events_query->have_posts()) {
        echo '<div class="events-list">';
        while ($events_query->have_posts()) {
            $events_query->the_post();
            $custom_city = get_post_meta(get_the_ID(), '_custom_city', true);
            echo '<div class="event-item">';
            echo '<h2>' . get_the_title() . '</h2>';
            echo '<p>' . strtoupper(get_the_title($venue_page)) . ' | ' . esc_html($custom_city) . '</p>';
            echo '</div>';
        }
        echo '</div>';
        wp_reset_postdata();
    } else {
        echo '<p>No events found for this venue.</p>';
    }

    // Return buffered content
    return ob_get_clean();
}

// Shortcode to list events by promoter
add_shortcode('list_events_by_promoter', 'pe_list_events_by_promoter');
function pe_list_events_by_promoter($atts) {
    $atts = shortcode_atts(array(
        'promoter' => '', // Default promoter slug
    ), $atts);

    $promoter_page = get_page_by_path('events/promoter/' . sanitize_title($atts['promoter']));
    if (!$promoter_page) {
        return '<p>No events found for this promoter.</p>';
    }

    // Query events related to the promoter
    $events_query = new WP_Query(array(
        'post_type' => 'product',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => '_custom_promoter_name',
                'value' => get_the_title($promoter_page),
                'compare' => 'LIKE'
            ),
        ),
    ));

    // Start output buffer
    ob_start();

    if ($events_query->have_posts()) {
        echo '<div class="events-list">';
        while ($events_query->have_posts()) {
            $events_query->the_post();
            $custom_city = get_post_meta(get_the_ID(), '_custom_city', true);
            echo '<div class="event-item">';
            echo '<h2>' . get_the_title() . '</h2>';
            echo '<p>' . strtoupper(get_the_title($promoter_page)) . ' | ' . esc_html($custom_city) . '</p>';
            echo '</div>';
        }
        echo '</div>';
        wp_reset_postdata();
    } else {
        echo '<p>No events found for this promoter.</p>';
    }

    // Return buffered content
    return ob_get_clean();
}

// Shortcode to list events by artiste
add_shortcode('list_events_by_artiste', 'pe_list_events_by_artiste');
function pe_list_events_by_artiste($atts) {
    $atts = shortcode_atts(array(
        'artiste' => '', // Default artiste slug
    ), $atts);

    $artiste_page = get_page_by_path('events/artiste/' . sanitize_title($atts['artiste']));
    if (!$artiste_page) {
        return '<p>No events found for this artiste.</p>';
    }

    // Query events related to the artiste
    $events_query = new WP_Query(array(
        'post_type' => 'product',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => '_custom_artiste_name',
                'value' => get_the_title($artiste_page),
                'compare' => 'LIKE'
            ),
        ),
    ));

    // Start output buffer
    ob_start();

    if ($events_query->have_posts()) {
        echo '<div class="events-list">';
        while ($events_query->have_posts()) {
            $events_query->the_post();
            $custom_city = get_post_meta(get_the_ID(), '_custom_city', true);
            echo '<div class="event-item">';
            echo '<h2>' . get_the_title() . '</h2>';
            echo '<p>' . strtoupper(get_the_title($artiste_page)) . ' | ' . esc_html($custom_city) . '</p>';
            echo '</div>';
        }
        echo '</div>';
        wp_reset_postdata();
    } else {
        echo '<p>No events found for this artiste.</p>';
    }

    // Return buffered content
    return ob_get_clean();
}
?>