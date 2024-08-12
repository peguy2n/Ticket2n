<?php

// AJAX handler for artist autocomplete
function pe_artist_autocomplete() {
    global $wpdb;

    $query = sanitize_text_field($_POST['query']);

    if (!empty($query)) {
        $artists = $wpdb->get_results($wpdb->prepare("
            SELECT DISTINCT meta_value 
            FROM {$wpdb->postmeta}
            WHERE meta_key = '_custom_artiste_name' 
            AND meta_value LIKE %s 
            LIMIT 10
        ", '%' . $wpdb->esc_like($query) . '%'));

        if ($artists) {
            foreach ($artists as $artist) {
                echo '<div class="suggestion-item">' . esc_html($artist->meta_value) . '</div>';
            }
        } else {
            echo '<div class="suggestion-item">No artists found</div>';
        }
    }
    wp_die();
}
add_action('wp_ajax_pe_artist_autocomplete', 'pe_artist_autocomplete');
add_action('wp_ajax_nopriv_pe_artist_autocomplete', 'pe_artist_autocomplete');





?>