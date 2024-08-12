<?php
// Shortcode to list events by city
add_shortcode('list_events_by_city', 'pe_list_events_by_city');

function pe_list_events_by_city($atts) {
    $atts = shortcode_atts(array(
        'city' => '', // Default city slug
    ), $atts);

    // Sanitize the city attribute
    $city = sanitize_text_field($atts['city']);

    if (empty($city)) {
        return '<p>No city specified.</p>';
    }

    // Get filter values from the query parameters
    $artist = isset($_GET['artist']) ? sanitize_text_field($_GET['artist']) : '';
    $start_date = isset($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : '';
    $end_date = isset($_GET['end_date']) ? sanitize_text_field($_GET['end_date']) : '';
    $paged = isset($_GET['paged']) ? absint($_GET['paged']) : 1;

    // Build the base meta query for the city
    $meta_query = array(
        'relation' => 'AND',
        array(
            'key' => '_custom_city',
            'value' => $city,
            'compare' => 'LIKE'
        ),
    );

    // Update the meta query with filters if they exist
    if (!empty($artist)) {
        $meta_query[] = array(
            'key' => '_custom_artiste_name',
            'value' => $artist,
            'compare' => 'LIKE'
        );
    }

    if (!empty($start_date) && !empty($end_date)) {
        $meta_query[] = array(
            'key' => '_custom_start_datetime',
            'value' => array($start_date . ' 00:00:00', $end_date . ' 23:59:59'),
            'compare' => 'BETWEEN',
            'type' => 'DATETIME'
        );
    } elseif (!empty($start_date)) {
        $meta_query[] = array(
            'key' => '_custom_start_datetime',
            'value' => $start_date . ' 00:00:00',
            'compare' => '>=',
            'type' => 'DATETIME'
        );
    } elseif (!empty($end_date)) {
        $meta_query[] = array(
            'key' => '_custom_start_datetime',
            'value' => $end_date . ' 23:59:59',
            'compare' => '<=',
            'type' => 'DATETIME'
        );
    }

    // Filter to only include upcoming events
$today = current_time('Y-m-d H:i:s'); // Get current date and time in the correct format
$meta_query[] = array(
    'key'     => '_custom_start_datetime',
    'value'   => $today,
    'compare' => '>=',
    'type'    => 'DATETIME'
);

    // Get the current page number
    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

    // Query events related to the city with pagination
    $events_query = new WP_Query(array(
        'post_type'      => 'product', // Replace 'product' with your custom post type slug
        'posts_per_page' => 2, // Number of events per page
        'paged'          => $paged,
        'meta_query'     => $meta_query,
        'meta_key'       => '_custom_start_datetime',
        'orderby'        => 'meta_value',
        'order'          => 'ASC',
    ));

    // Start output buffer
    ob_start();

    // Display filter form
    ?>
    <button id="filter-toggle" style="margin-bottom: 10px; padding: 8px 12px; background-color: #5e17eb; color: white; border: none; cursor: pointer;">
        Filtre
    </button>

    <form method="GET" id="filter-form" action="" style="display: none; margin-bottom: 20px;">
        <input type="hidden" name="city" value="<?php echo esc_attr($city); ?>" />
        <div>
            <label for="artist"></label>
            <input type="text" id="artist" name="artist" placeholder="Artiste" value="<?php echo esc_attr($artist); ?>" autocomplete="off" data-autocomplete="<?php echo admin_url('admin-ajax.php'); ?>" />
            <div id="artist-suggestions" class="suggestions-box"></div>
        </div>
        <div class="dates">
        <div>
            <label for="start_date"></label>
            <input type="date" id="start_date" name="start_date" placeholder="Du" value="<?php echo esc_attr($start_date); ?>" />
        </div>
        <div>
            <label for="end_date"></label>
            <input type="date" id="end_date" name="end_date" placeholder="Au" value="<?php echo esc_attr($end_date); ?>" />
        </div>
        </div>
    </form>

    <?php

    // Display events list
    if ($events_query->have_posts()) {
        echo '<div class="events-list">';
        while ($events_query->have_posts()) {
            $events_query->the_post();
            $custom_venue = get_post_meta(get_the_ID(), '_custom_venue', true);
            $custom_artiste_name = get_post_meta(get_the_ID(), '_custom_artiste_name', true);
            $custom_event_title = get_post_meta(get_the_ID(), '_custom_event_title', true);
            $custom_start_datetime = get_post_meta(get_the_ID(), '_custom_start_datetime', true);
            $custom_end_datetime = get_post_meta(get_the_ID(), '_custom_end_datetime', true);
            $event_image = get_the_post_thumbnail(get_the_ID(), array(100, 100)); // Get the post's featured image
            
            // Third column: Button to post page
            $start_date_only = !empty($custom_start_datetime) ? (new DateTime($custom_start_datetime))->format('Y-m-d') : '';
            $end_date_only = !empty($custom_end_datetime) ? (new DateTime($custom_end_datetime))->format('Y-m-d') : '';

            echo '<a href="' . get_permalink() . '"><table class="event-item">';
            echo '<tr>';
            // First column: Event image
            echo '<td class="event-image" style="width: 100px; text-align: center;">';
            if ($event_image) {
                echo $event_image;
            } else {
                echo '<img src="' . esc_url(get_template_directory_uri() . '/path/to/default/image.jpg') . '" alt="Default Image" width="100" height="100">'; // Optional default image
            }
            echo '</td>';

             // Second column: Event details
            echo '<td class="event-details" style="padding: 8px;">';
            echo '<h3><a href="' . get_permalink() . '">' . esc_html($custom_artiste_name) . '</a></h3>';
            echo '<h4><a href="' . get_permalink() . '">' . esc_html($custom_event_title) . '</a></h4>';
            echo '<p><a href="' . get_permalink() . '">' . esc_html($custom_venue) . '</a></p>';
            echo '<p>' . esc_html($start_date_only) .' -  ' . esc_html($end_date_only) . '</p>';
            echo '</td>';

            // Third column: Button to post page
            echo '<td class="event-button">';
            echo '<a href="' . get_permalink() . '" class="button" style="color: #fff;"><i class="fas fa-calendar-alt"></i> »</a>';
            echo '</td>';
            echo '</tr>';
            echo '</table></a>';
        }
        
        // Display pagination
        echo '<div class="pagination">';
        echo paginate_links(array(
            'base'      => add_query_arg('paged', '%#%'), // Adding paged parameter to the current URL
            'format'    => '', // Since base already includes the pagination structure
            'current'   => max(1, $paged),
            'total'     => $events_query->max_num_pages,
            'prev_text' => __('«<i class="fas fa-calendar-alt"></i>'),
            'next_text' => __('<i class="fas fa-calendar-alt"></i> »'),
            'add_args'  => array_filter(array( // Remove empty parameters
                'city'       => $city,
                'artist'     => $artist,
                'start_date' => $start_date,
                'end_date'   => $end_date,
            )),
        ));
        echo '</div>';

        wp_reset_postdata();
    } else {
        echo '<p>No events found for this city.</p>';
    }

    // Return buffered content
    return ob_get_clean();
}




// Shortcode to list events by artist  [list_events_by_artiste artiste="Your artiste Name"]
add_shortcode('list_events_by_artist', 'pe_list_events_by_artist');

function pe_list_events_by_artist($atts) {
    $atts = shortcode_atts(array(
        'artist' => '', // Default artist name
    ), $atts);

    // Sanitize the artist attribute
    $artist = sanitize_text_field($atts['artist']);

    if (empty($artist)) {
        return '<p>No artist specified.</p>';
    }

    // Get filter values from the query parameters
    $city = isset($_GET['city']) ? sanitize_text_field($_GET['city']) : '';
    $start_date = isset($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : '';
    $end_date = isset($_GET['end_date']) ? sanitize_text_field($_GET['end_date']) : '';
    $paged = isset($_GET['paged']) ? absint($_GET['paged']) : 1;


    $meta_query = array(
        'relation' => 'AND',
        array(
            'key' => '_custom_artiste_name',
            'value' => $artist,
            'compare' => 'LIKE'
        ),
    );

    // Update the meta query with filters if they exist
    if (!empty($city)) {
        $meta_query[] = array(
            'key' => '_custom_city',
            'value' => $city,
            'compare' => 'LIKE'
        );
    }

    if (!empty($start_date) && !empty($end_date)) {
        $meta_query[] = array(
            'key' => '_custom_start_datetime',
            'value' => array($start_date . ' 00:00:00', $end_date . ' 23:59:59'),
            'compare' => 'BETWEEN',
            'type' => 'DATETIME'
        );
    } elseif (!empty($start_date)) {
        $meta_query[] = array(
            'key' => '_custom_start_datetime',
            'value' => $start_date . ' 00:00:00',
            'compare' => '>=',
            'type' => 'DATETIME'
        );
    } elseif (!empty($end_date)) {
        $meta_query[] = array(
            'key' => '_custom_start_datetime',
            'value' => $end_date . ' 23:59:59',
            'compare' => '<=',
            'type' => 'DATETIME'
        );
    }

       // Filter to only include upcoming events
$today = current_time('Y-m-d H:i:s'); // Get current date and time in the correct format
$meta_query[] = array(
    'key'     => '_custom_start_datetime',
    'value'   => $today,
    'compare' => '>=',
    'type'    => 'DATETIME'
);

    // Query events related to the artist with pagination
    $events_query = new WP_Query(array(
        'post_type'      => 'product', // Replace 'product' with your custom post type slug
        'posts_per_page' => -1, // Number of events per page
        'paged'          => $paged,
        'meta_query'     => $meta_query,
        'meta_key'       => '_custom_start_datetime',
        'orderby'        => 'meta_value',
        'order'          => 'ASC',
    ));

    // Start output buffer
    ob_start();

    // Display filter form
 
    ?>

 <button id="filter-toggle" style="margin-bottom: 10px; padding: 8px 12px; background-color: #5e17eb; color: white; border: none; cursor: pointer;">
    Filtre
    </button>

    <form method="GET" id="filter-form" action="" style="display: none; margin-bottom: 20px;">
        <input type="hidden" name="artist" value="<?php echo esc_attr($artist); ?>" />
        <div>
            <label for="city"></label>
            <input type="text" id="city" name="city" placeholder="City" value="<?php echo esc_attr($city); ?>" autocomplete="off" />
        </div>
        <div class="dates">
        <div>
            <label for="start_date"></label>
            <input type="date" id="start_date" name="start_date" placeholder="Du" value="<?php echo esc_attr($start_date); ?>" />
        </div>
        <div>
            <label for="end_date"></label>
            <input type="date" id="end_date" name="end_date" placeholder="Au" value="<?php echo esc_attr($end_date); ?>" />
        </div>
        </div>
    </form>

    <?php

    // Display events list
    if ($events_query->have_posts()) {
        echo '<div class="events-list">';
        while ($events_query->have_posts()) {
            $events_query->the_post();
            $custom_city = get_post_meta(get_the_ID(), '_custom_city', true);
            $custom_artiste_name = get_post_meta(get_the_ID(), '_custom_artiste_name', true);
            $custom_venue = get_post_meta(get_the_ID(), '_custom_venue', true);
            $custom_event_title = get_post_meta(get_the_ID(), '_custom_event_title', true);
            $custom_start_datetime = get_post_meta(get_the_ID(), '_custom_start_datetime', true);
            $custom_end_datetime = get_post_meta(get_the_ID(), '_custom_end_datetime', true);
            $event_image = get_the_post_thumbnail(get_the_ID(), array(100, 100)); // Get the post's featured image
            
            // Third column: Button to post page
            $start_date_only = !empty($custom_start_datetime) ? (new DateTime($custom_start_datetime))->format('d-m-Y') : '';
            $end_date_only = !empty($custom_end_datetime) ? (new DateTime($custom_end_datetime))->format('d-m-Y') : '';
            $start_time_only = !empty($custom_start_datetime) ? (new DateTime($custom_start_datetime))->format('H:i') : '';


            echo '<a href="' . get_permalink() . '"><table class="event-item">';
            echo '<tr>';
            // First column: Event image
            echo '<td class="event-image" style="width: 100px; text-align: center;">';
            echo '<p>' . esc_html($start_date_only) .'</p>';
            echo '<p>'. esc_html($end_date_only) . '</p>';
            echo '<p>' . esc_html($start_time_only) .'</p>';
            echo '</td>';

             // Second column: Event details
            echo '<td class="event-details" style="padding: 8px;">';
            echo '<h4><a href="' . get_permalink() . '">' . esc_html($custom_event_title) . '</a></h4>';
            echo '<h3>' . strtoupper(esc_html($custom_city)) . ' | <a href="' . get_permalink() . '">' . esc_html($custom_venue) . '</a></h3>';
            echo '</td>';

           // Third column: Button to post page
           echo '<td class="event-button">';
           echo '<a href="' . get_permalink() . '" class="button" style="color: #fff;"><i class="fas fa-calendar-alt"></i> »</a>';
           echo '</td>';
           echo '</tr>';
           echo '</table></a>';
        }
        
        // Display pagination
        /*echo '<div class="pagination">';
        echo paginate_links(array(
            'base'      => add_query_arg('paged', '%#%'), // Adding paged parameter to the current URL
            'format'    => '', // Since base already includes the pagination structure
            'current'   => max(1, $paged),
            'total'     => $events_query->max_num_pages,
            'prev_text' => __('« Precedent'),
            'next_text' => __('Suivant »'),
            'add_args'  => array_filter(array( // Remove empty parameters
                'artist'     => $artist,
                'city'       => $city,
                'start_date' => $start_date,
                'end_date'   => $end_date,
            )),
        ));
        echo '</div>'; */

        wp_reset_postdata();
    } else {
        echo '<p>No events found for this artist.</p>';
    }

    // Return buffered content
    return ob_get_clean();
}




// Shortcode to list events by venue [list_events_by_venue promoter="venue name"]
add_shortcode('list_events_by_venue', 'pe_list_events_by_venue');

function pe_list_events_by_venue($atts) {
    $atts = shortcode_atts(array(
        'venue' => '', // Default venue name
    ), $atts);

    // Sanitize the venue attribute
    $venue = sanitize_text_field($atts['venue']);

    if (empty($venue)) {
        return '<p>No venue specified.</p>';
    }

    // Get filter values from the query parameters
    $artist = isset($_GET['artist']) ? sanitize_text_field($_GET['artist']) : '';
    $start_date = isset($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : '';
    $end_date = isset($_GET['end_date']) ? sanitize_text_field($_GET['end_date']) : '';
    $paged = isset($_GET['paged']) ? absint($_GET['paged']) : 1;

    // Build the base meta query for the city
    $meta_query = array(
        'relation' => 'AND',
        array(
            'key' => '_custom_venue',
            'value' => $venue,
            'compare' => 'LIKE'
        ),
    );

    // Update the meta query with filters if they exist
    if (!empty($artist)) {
        $meta_query[] = array(
            'key' => '_custom_artiste_name',
            'value' => $artist,
            'compare' => 'LIKE'
        );
    }

    if (!empty($start_date) && !empty($end_date)) {
        $meta_query[] = array(
            'key' => '_custom_start_datetime',
            'value' => array($start_date . ' 00:00:00', $end_date . ' 23:59:59'),
            'compare' => 'BETWEEN',
            'type' => 'DATETIME'
        );
    } elseif (!empty($start_date)) {
        $meta_query[] = array(
            'key' => '_custom_start_datetime',
            'value' => $start_date . ' 00:00:00',
            'compare' => '>=',
            'type' => 'DATETIME'
        );
    } elseif (!empty($end_date)) {
        $meta_query[] = array(
            'key' => '_custom_start_datetime',
            'value' => $end_date . ' 23:59:59',
            'compare' => '<=',
            'type' => 'DATETIME'
        );
    }

        // Filter to only include upcoming events
$today = current_time('Y-m-d H:i:s'); // Get current date and time in the correct format
$meta_query[] = array(
    'key'     => '_custom_start_datetime',
    'value'   => $today,
    'compare' => '>=',
    'type'    => 'DATETIME'
);

    // Query events related to the venue with pagination
    $events_query = new WP_Query(array(
        'post_type'      => 'product', // Replace 'product' with your custom post type slug
        'posts_per_page' => -1, // Number of events per page
        'paged'          => $paged,
        'meta_query'     => $meta_query,
        'meta_key'       => '_custom_start_datetime',
        'orderby'        => 'meta_value',
        'order'          => 'ASC',
    ));

    // Start output buffer
    ob_start();

    // Display filter form
    ?>
    <button id="filter-toggle" style="margin-bottom: 10px; padding: 8px 12px; background-color: #5e17eb; color: white; border: none; cursor: pointer;">
        Filtre
    </button>

    <form method="GET" id="filter-form" action="" style="display: none; margin-bottom: 20px;">
        <input type="hidden" name="venue" value="<?php echo esc_attr($venue); ?>" />
        <div>
            <label for="artist"></label>
            <input type="text" id="artist" name="artist" placeholder="Artiste" value="<?php echo esc_attr($artist); ?>" autocomplete="off" data-autocomplete="<?php echo admin_url('admin-ajax.php'); ?>" />
            <div id="artist-suggestions" class="suggestions-box"></div>
        </div>
        <div class="dates">
        <div>
            <label for="start_date"></label>
            <input type="date" id="start_date" name="start_date" placeholder="From" value="<?php echo esc_attr($start_date); ?>" />
        </div>
        <div>
            <label for="end_date"></label>
            <input type="date" id="end_date" name="end_date" placeholder="To" value="<?php echo esc_attr($end_date); ?>" />
        </div>
        </div>
        <button type="submit" class="button">Filter</button>
    </form>

    <?php

    // Display events list
    if ($events_query->have_posts()) {
        echo '<div class="events-list">';
        while ($events_query->have_posts()) {
            $events_query->the_post();
            $custom_artiste_name = get_post_meta(get_the_ID(), '_custom_artiste_name', true);
            $custom_event_title = get_post_meta(get_the_ID(), '_custom_event_title', true);
            $custom_start_datetime = get_post_meta(get_the_ID(), '_custom_start_datetime', true);
            $custom_end_datetime = get_post_meta(get_the_ID(), '_custom_end_datetime', true);
            $event_image = get_the_post_thumbnail(get_the_ID(), array(100, 100)); // Get the post's featured image
            
            // Format dates
            $start_date_only = !empty($custom_start_datetime) ? (new DateTime($custom_start_datetime))->format('Y-m-d') : '';
            $end_date_only = !empty($custom_end_datetime) ? (new DateTime($custom_end_datetime))->format('Y-m-d') : '';

            echo '<a href="' . get_permalink() . '"><table class="event-item">';
            echo '<tr>';
            // First column: Event image
            echo '<td class="event-image" style="width: 100px; text-align: center;">';
            if ($event_image) {
                echo $event_image;
            } else {
                echo '<img src="' . esc_url(get_template_directory_uri() . '/path/to/default/image.jpg') . '" alt="Default Image" width="100" height="100">'; // Optional default image
            }
            echo '</td>';

             // Second column: Event details
             echo '<td class="event-details" style="padding: 8px;">';
             echo '<h3><a href="' . get_permalink() . '">' . esc_html($custom_artiste_name) . '</a></h3>';
             echo '<h4><a href="' . get_permalink() . '">' . esc_html($custom_event_title) . '</a></h4>';
             echo '<p>' . esc_html($start_date_only) .' -  ' . esc_html($end_date_only) . '</p>';
             echo '</td>';

            // Third column: Button to post page
            echo '<td class="event-button">';
            echo '<a href="' . get_permalink() . '" class="button" style="color: #fff;"><i class="fas fa-calendar-alt"></i> »</a>';
            echo '</td>';
            echo '</tr>';
            echo '</table></a>';
        }
        
        // Display pagination
        /*echo '<div class="pagination">';
        echo paginate_links(array(
            'base'      => add_query_arg('paged', '%#%'), // Adding paged parameter to the current URL
            'format'    => '', // Since base already includes the pagination structure
            'current'   => max(1, $paged),
            'total'     => $events_query->max_num_pages,
            'prev_text' => __('« Precedent'),
            'next_text' => __('Suivant »'),
            'add_args'  => array_filter(array( // Remove empty parameters
                'venue'       => $venue,
                'artist'     => $artist,
                'start_date' => $start_date,
                'end_date'   => $end_date,
            )),
        ));
        echo '</div>'; */

        wp_reset_postdata();
    } else {
        echo '<p>No events found for this venue.</p>';
    }

    // Return buffered content
    return ob_get_clean();
}




// Shortcode to list events by promoter [list_events_by_promoter promoter="Your Promoter Name"]
add_shortcode('list_events_by_promoter', 'pe_list_events_by_promoter');

function pe_list_events_by_promoter($atts) {
    $atts = shortcode_atts(array(
        'promoter' => '', // Default promoter name
    ), $atts);

    // Sanitize the promoter attribute
    $promoter = sanitize_text_field($atts['promoter']);

    if (empty($promoter)) {
        return '<p>No promoter specified.</p>';
    }

   // Get filter values from the query parameters
   $artist = isset($_GET['artist']) ? sanitize_text_field($_GET['artist']) : '';
   $start_date = isset($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : '';
   $end_date = isset($_GET['end_date']) ? sanitize_text_field($_GET['end_date']) : '';
   $paged = isset($_GET['paged']) ? absint($_GET['paged']) : 1;

   // Build the base meta query for the city
   $meta_query = array(
       'relation' => 'AND',
       array(
           'key' => '_custom_promoter_name',
           'value' => $promoter,
           'compare' => 'LIKE'
       ),
   );

   // Update the meta query with filters if they exist
   if (!empty($artist)) {
       $meta_query[] = array(
           'key' => '_custom_artiste_name',
           'value' => $artist,
           'compare' => 'LIKE'
       );
   }

   if (!empty($start_date) && !empty($end_date)) {
       $meta_query[] = array(
           'key' => '_custom_start_datetime',
           'value' => array($start_date . ' 00:00:00', $end_date . ' 23:59:59'),
           'compare' => 'BETWEEN',
           'type' => 'DATETIME'
       );
   } elseif (!empty($start_date)) {
       $meta_query[] = array(
           'key' => '_custom_start_datetime',
           'value' => $start_date . ' 00:00:00',
           'compare' => '>=',
           'type' => 'DATETIME'
       );
   } elseif (!empty($end_date)) {
       $meta_query[] = array(
           'key' => '_custom_start_datetime',
           'value' => $end_date . ' 23:59:59',
           'compare' => '<=',
           'type' => 'DATETIME'
       );
   }

       // Filter to only include upcoming events
$today = current_time('Y-m-d H:i:s'); // Get current date and time in the correct format
$meta_query[] = array(
    'key'     => '_custom_start_datetime',
    'value'   => $today,
    'compare' => '>=',
    'type'    => 'DATETIME'
);

    // Query events related to the promoter with pagination
    $events_query = new WP_Query(array(
        'post_type'      => 'product', // Replace 'product' with your custom post type slug
        'posts_per_page' => -1, // Number of events per page
        'paged'          => $paged,
        'meta_query'     => $meta_query,
        'meta_key'       => '_custom_start_datetime',
        'orderby'        => 'meta_value',
        'order'          => 'ASC',
    ));

    // Start output buffer
    ob_start();

    // Display filter form
    ?>
    <button id="filter-toggle" style="margin-bottom: 10px; padding: 8px 12px; background-color: #5e17eb; color: white; border: none; cursor: pointer;">
        Filtre
    </button>

    <form method="GET" id="filter-form" action="" style="display: none; margin-bottom: 20px;">
        <input type="hidden" name="promoter" value="<?php echo esc_attr($promoter); ?>" />
        <div>
            <label for="artist"></label>
            <input type="text" id="artist" name="artist" placeholder="Artiste" value="<?php echo esc_attr($artist); ?>" autocomplete="off" data-autocomplete="<?php echo admin_url('admin-ajax.php'); ?>" />
            <div id="artist-suggestions" class="suggestions-box"></div>
        </div>
        <div class="dates">
        <div>
            <label for="start_date"></label>
            <input type="date" id="start_date" name="start_date" placeholder="From" value="<?php echo esc_attr($start_date); ?>" />
        </div>
        <div>
            <label for="end_date"></label>
            <input type="date" id="end_date" name="end_date" placeholder="To" value="<?php echo esc_attr($end_date); ?>" />
        </div>
        </div>
    </form>

    <?php

    // Display events list
    if ($events_query->have_posts()) {
        echo '<div class="events-list">';
        while ($events_query->have_posts()) {
            $events_query->the_post();
            $custom_venue = get_post_meta(get_the_ID(), '_custom_venue', true);
            $custom_city = get_post_meta(get_the_ID(), '_custom_city', true);
            $custom_artiste_name = get_post_meta(get_the_ID(), '_custom_artiste_name', true);
            $custom_event_title = get_post_meta(get_the_ID(), '_custom_event_title', true);
            $custom_start_datetime = get_post_meta(get_the_ID(), '_custom_start_datetime', true);
            $custom_end_datetime = get_post_meta(get_the_ID(), '_custom_end_datetime', true);
            $event_image = get_the_post_thumbnail(get_the_ID(), array(100, 100)); // Get the post's featured image
            
            // Format dates
            $start_date_only = !empty($custom_start_datetime) ? (new DateTime($custom_start_datetime))->format('Y-m-d') : '';
            $end_date_only = !empty($custom_end_datetime) ? (new DateTime($custom_end_datetime))->format('Y-m-d') : '';

            echo '<a href="' . get_permalink() . '"><table class="event-item">';
            echo '<tr>';
            // First column: Event image
            echo '<td class="event-image" style="width: 100px; text-align: center;">';
            if ($event_image) {
                echo $event_image;
            } else {
                echo '<img src="' . esc_url(get_template_directory_uri() . '/path/to/default/image.jpg') . '" alt="Default Image" width="100" height="100">'; // Optional default image
            }
            echo '</td>';

             // Second column: Event details
             echo '<td class="event-details" style="padding: 8px;">';
             echo '<h3><a href="' . get_permalink() . '">' . esc_html($custom_artiste_name) . '</a></h3>';
             echo '<h4><a href="' . get_permalink() . '">' . esc_html($custom_event_title) . '</a></h4>';
             echo '<p>' . strtoupper(esc_html($custom_city)) . ' | <a href="' . get_permalink() . '">' . esc_html($custom_venue) . '</a></hp>';
             echo '<p>' . esc_html($start_date_only) .' -  ' . esc_html($end_date_only) . '</p>';
             echo '</td>';

            // Third column: Button to post page
            echo '<td class="event-button">';
            echo '<a href="' . get_permalink() . '" class="button" style="color: #fff;"><i class="fas fa-calendar-alt"></i> »</a>';
            echo '</td>';
            echo '</tr>';
            echo '</table></a>';
        }
        
        // Display pagination
       /* echo '<div class="pagination">';
        echo paginate_links(array(
            'base'      => add_query_arg('paged', '%#%'), // Adding paged parameter to the current URL
            'format'    => '', // Since base already includes the pagination structure
            'current'   => max(1, $paged),
            'total'     => $events_query->max_num_pages,
            'prev_text' => __('« Precedent'),
            'next_text' => __('Suivant »'),
            'add_args'  => array_filter(array( // Remove empty parameters
                'promoter'   => $promoter,
                'artist'     => $artist,
                'start_date' => $start_date,
                'end_date'   => $end_date,
            )),
        ));
        echo '</div>';*/

        wp_reset_postdata();
    } else {
        echo '<p>No events found for this promoter.</p>';
    }

    // Return buffered content
    return ob_get_clean();
}
?>




