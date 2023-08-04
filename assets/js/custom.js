jQuery(document).ready(function($) {
    $('.validation input[type="checkbox"]').change(function() {
        var postId = $(this).data('id'); // Get the post ID from a data attribute
        var isChecked = $(this).is(':checked') ? 1 : 0; // Determine if checkbox is checked

        // Make the AJAX request
        $.post(
            ajaxurl, // This variable is automatically defined by WordPress and points to /wp-admin/admin-ajax.php
            {
                action: 'update_media_validation', // The action hook name
                post_id: postId, // The post ID
                is_checked: isChecked, // The checkbox state
                security: MyAjax.security // A nonce for security. Replace 'MyAjax.security' with the localized object and property where you've stored the nonce.
            },
            function(response) {
                console.log('The server responded: ', response);
            }
        );
    });
});
