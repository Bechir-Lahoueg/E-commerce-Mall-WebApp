$(document).ready(function() {
    $('#btnSearch').on('click', function() {
        // Get the search input value
        var keyword = $('#inputMobileSearch').val();

        // Send an AJAX request to the Symfony backend
        $.ajax({
            url: '/search', // Replace with the correct Symfony route
            method: 'GET',
            data: { keyword: keyword },
            success: function(response) {
                // Handle the response, e.g., update the DOM with the search results
                console.log(response);
            },
            error: function(error) {
                console.error(error);
            }
        });
    });
});