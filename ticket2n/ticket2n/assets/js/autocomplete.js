// autocomplete.js

jQuery(document).ready(function($) {
    function enableAutocomplete(inputSelector, endpoint) {
        $(inputSelector).autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'pe_autocomplete',
                        term: request.term,
                        field: endpoint
                    },
                    success: function(data) {
                        response(data);
                    }
                });
            },
            minLength: 2
        });
    }

    // Enable autocomplete for each custom field
    enableAutocomplete('#custom_city', 'city');
    enableAutocomplete('#custom_venue', 'venue');
    enableAutocomplete('#custom_promoter_name', 'promoter');
    enableAutocomplete('#custom_artiste_name', 'artiste');
});
