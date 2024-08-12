<?php
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
            'id'          => '_custom_start_datetime',
            'label'       => __('Event Start Date and Time', 'woocommerce'),
            'placeholder' => 'YYYY-MM-DD HH:MM',
            'desc_tip'    => 'true',
            'description' => __('Enter the event start date and time.', 'woocommerce'),
            'class'       => 'short', // Needed for datetimepicker styling
        )
    );

    woocommerce_wp_text_input(
        array(
            'id'          => '_custom_end_datetime',
            'label'       => __('Event End Date and Time', 'woocommerce'),
            'placeholder' => 'YYYY-MM-DD HH:MM',
            'desc_tip'    => 'true',
            'description' => __('Enter the event end date and time.', 'woocommerce'),
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
    $custom_start_datetime = isset($_POST['_custom_start_datetime']) ? sanitize_text_field($_POST['_custom_start_datetime']) : '';
    $custom_end_datetime = isset($_POST['_custom_end_datetime']) ? sanitize_text_field($_POST['_custom_end_datetime']) : '';
    $custom_city = isset($_POST['_custom_city']) ? sanitize_text_field($_POST['_custom_city']) : '';
    $custom_venue = isset($_POST['_custom_venue']) ? sanitize_text_field($_POST['_custom_venue']) : '';
    $custom_promoter_name = isset($_POST['_custom_promoter_name']) ? sanitize_text_field($_POST['_custom_promoter_name']) : '';
    $custom_artiste_name = isset($_POST['_custom_artiste_name']) ? sanitize_text_field($_POST['_custom_artiste_name']) : '';

    update_post_meta($post_id, '_custom_event_title', $custom_event_title);
    update_post_meta($post_id, '_custom_start_datetime', $custom_start_datetime);
    update_post_meta($post_id, '_custom_end_datetime', $custom_end_datetime);
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
    // Split the promoter names by commas
    $promoter_names = array_map('trim', explode(',', $custom_promoter_name));

    // Only create a page if there is exactly 1 promoter name
    if (count($promoter_names) === 1) {
        $promoter_slug = sanitize_title($custom_promoter_name);
        $promoter_page_id = pe_create_or_get_event_page('events/promoter', $promoter_slug, 'Events by ' . $custom_promoter_name);
        update_post_meta($post_id, '_promoter_page_id', $promoter_page_id);
    }
}


 // Check if artiste page exists, create if not
if (!empty($custom_artiste_name)) {
    // Split the artist names by commas
    $artist_names = array_map('trim', explode(',', $custom_artiste_name));

    // Only create a page if there is exactly 1 artist name
    if (count($artist_names) === 1) {
        $artiste_slug = sanitize_title($custom_artiste_name);
        $artiste_page_id = pe_create_or_get_event_page('events/artiste', $artiste_slug, 'Events featuring ' . $custom_artiste_name);
        update_post_meta($post_id, '_artiste_page_id', $artiste_page_id);
    }
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

?>