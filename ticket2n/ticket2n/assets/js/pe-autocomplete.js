jQuery(document).ready(function($) {
    $('#artist').on('keyup', function() {
        var query = $(this).val();
        
        if (query.length >= 3) {
            $.ajax({
                url: pe_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'pe_artist_autocomplete',
                    query: query
                },
                success: function(data) {
                    $('#artist-suggestions').fadeIn();
                    $('#artist-suggestions').html(data);
                }
            });
        } else {
            $('#artist-suggestions').fadeOut();
        }
    });

    // Populate the input field and submit the form when clicking on a suggestion
    $(document).on('click', '.suggestion-item', function() {
        $('#artist').val($(this).text());
        $('#artist-suggestions').fadeOut();

        // Automatically submit the form after selecting an artist
        $('form').submit();
    });
});



        // Automatically submit the form after period of time
        
        document.getElementById('start_date').addEventListener('change', function() {
            document.getElementById('filter-form').submit();
        });

        document.getElementById('end_date').addEventListener('change', function() {
            document.getElementById('filter-form').submit();
        });


            // Show filter

    document.getElementById('filter-toggle').addEventListener('click', function() {
        var filterForm = document.getElementById('filter-form');
        if (filterForm.style.display === 'none') {
            filterForm.style.display = 'block';
            this.textContent = 'Hide Filter';
        } else {
            filterForm.style.display = 'none';
            this.textContent = 'Show Filter';
        }
    });

        