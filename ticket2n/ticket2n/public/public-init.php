<?php
// Initialize public functions
function pe_public_init() {
    
// Modify short description to include custom event info
add_filter('woocommerce_short_description', 'pe_display_custom_fields_in_short_description', 10, 1);
function pe_display_custom_fields_in_short_description($short_description) {
    global $post;

    $custom_event_title = get_post_meta($post->ID, '_custom_event_title', true);
    $custom_start_datetime = get_post_meta($post->ID, '_custom_start_datetime', true);
    $custom_end_datetime = get_post_meta($post->ID, '_custom_end_datetime', true);
    $custom_city = get_post_meta($post->ID, '_custom_city', true);
    $custom_venue = get_post_meta($post->ID, '_custom_venue', true);
    $custom_promoter_name = get_post_meta($post->ID, '_custom_promoter_name', true);
    $custom_artiste_name = get_post_meta($post->ID, '_custom_artiste_name', true);

    if (!empty($custom_event_title) || !empty($custom_start_datetime) || !empty($custom_end_datetime) || !empty($custom_city) || !empty($custom_venue) || !empty($custom_promoter_name) || !empty($custom_artiste_name)) {
        $extra_info = '';

        if (!empty($custom_event_title)) {
            $extra_info .= '<h2>' . esc_html($custom_event_title) . '</h2>';
        }
        if (!empty($custom_start_datetime)) {
            $start_datetime = strtotime($custom_start_datetime);
            $formatted_start_datetime = date_i18n('l, d/m/Y | H:i', $start_datetime); // Formats the start date and time
            $extra_info .= '<p><i class="fas fa-calendar-alt"></i> ' . esc_html($formatted_start_datetime) . '</p>';
        }
        if (!empty($custom_end_datetime)) {
            $end_datetime = strtotime($custom_end_datetime);
            $formatted_end_datetime = date_i18n('l, d/m/Y | H:i', $end_datetime); // Formats the end date and time
            $extra_info .= '<p><i class="fas fa-calendar-alt"></i> ' . esc_html($formatted_end_datetime) . '</p>';
        }
        if (!empty($custom_city)) {
            $city_page_id = get_post_meta($post->ID, '_city_page_id', true);
            $city_link = $city_page_id ? get_permalink($city_page_id) : '#';
            $extra_info .= '<p><i class="fas fa-map-marker-alt"></i> <a href="' . esc_url($city_link) . '">' . strtoupper(esc_html($custom_city)) . '</a>';
        }
        if (!empty($custom_venue)) {
            $venue_page_id = get_post_meta($post->ID, '_venue_page_id', true);
            $venue_link = $venue_page_id ? get_permalink($venue_page_id) : '#';
            $extra_info .= ' | <a href="' . esc_url($venue_link) . '">' . esc_html($custom_venue) . '</a></p>';
            
        }
        if (!empty($custom_promoter_name)) {
            $promoter_page_id = get_post_meta($post->ID, '_promoter_page_id', true);
            $promoter_link = $promoter_page_id ? get_permalink($promoter_page_id) : '#';
            $extra_info .= '<p><i class="fas fa-users"></i> Promoter: <a href="' . esc_url($promoter_link) . '">' . esc_html($custom_promoter_name) . '</a></p>';
        }
        if (!empty($custom_artiste_name)) {
            $artiste_page_id = get_post_meta($post->ID, '_artiste_page_id', true);
            $artiste_link = $artiste_page_id ? get_permalink($artiste_page_id) : '#';
            $extra_info .= '<p><i class="fas fa-music"></i> Artiste: <a href="' . esc_url($artiste_link) . '">' . esc_html($custom_artiste_name) . '</a></p>';
        }

        $short_description .= $extra_info;
    }

    return $short_description;
}


}
add_action('wp', 'pe_public_init');
