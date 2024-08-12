Events By City Plugin
Description
The Events By City plugin is a WordPress tool designed to list and filter events by city. This plugin allows you to display events with options to filter by artist, start date, and end date. It also supports pagination, enabling users to browse through events in a city with ease.

Features
City-Based Filtering: Display events based on a specified city.
Artist Filtering: Narrow down events by artist name.
Date Filtering: Filter events by start and end date.
Pagination: Easily navigate through multiple pages of events.
Custom Fields: Leverages custom meta fields for event details, such as venue, event title, start date, and end date.
Shortcode Support: Easily display the events listing anywhere on your site using a shortcode.
Installation
Upload the Plugin Files:

Upload the plugin files to the /wp-content/plugins/events-by-city directory, or install the plugin through the WordPress plugins screen directly.
Activate the Plugin:

Activate the plugin through the 'Plugins' screen in WordPress.
Use the Shortcode:

Use the [list_events_by_city city="your-city-slug"] shortcode to display the events in a specific city.
Usage
Shortcode
Use the following shortcode to display events for a specific city:

php
Copy code
[list_events_by_city city="paris"]
Replace "paris" with the slug of the city you want to display events for.

Shortcode Attributes
city: (string) (required) The slug of the city for which you want to list events.
URL Parameters
artist: (string) (optional) Filter events by artist name.
start_date: (date) (optional) Filter events starting from a specific date.
end_date: (date) (optional) Filter events up to a specific date.
paged: (integer) (optional) Navigate through pages of events.
Example URL: http://yourwebsite.com/events/?city=paris&artist=John&start_date=2024-08-01&end_date=2024-08-31&paged=2

Custom Fields
Ensure that your event posts have the following custom fields to work seamlessly with this plugin:

_custom_city: The city where the event is taking place.
_custom_artiste_name: The name of the artist performing at the event.
_custom_start_datetime: The start date and time of the event.
_custom_end_datetime: The end date and time of the event.
_custom_venue: The venue where the event is being held.
_custom_event_title: The title of the event.
Pagination
The plugin automatically handles pagination, displaying 2 events per page by default. Use the paged URL parameter to navigate through pages.

Contributing
If you want to contribute to this plugin, please fork the repository and submit a pull request with your changes. Any contributions are welcome!

Changelog
1.0.0
Initial release of the plugin with city-based filtering, artist filtering, date filtering, and pagination features.
License
This plugin is licensed under the GPLv2 or later.