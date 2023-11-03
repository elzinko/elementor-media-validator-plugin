jQuery(document).ready(function($) {

    $('.validation input[type="checkbox"]').click(function(event) {
        var checkbox = $(this); // L'élément case à cocher sur lequel l'utilisateur a cliqué
        var isChecked = checkbox.is(':checked'); // L'état de la case à cocher avant le clic
        var comment = prompt("Veuillez entrer votre commentaire pour la validation:");
        var postId = checkbox.data('id');
        
        if (isChecked && !comment) {
            // If the box is checked and no comment is provided, prevent the action
            event.preventDefault();
            alert("Un commentaire est nécessaire pour valider.");
            return;
        }
        
        // Prepare data for AJAX call
        var data = {
            action: 'media_action_validate', // This should be the name of your WP AJAX action
            media_id: postId,
            action_type: isChecked ? 'validate' : 'invalidate',
            comment: comment,
            security: MyAjax.security // Your nonce for security
        };
        
        // Envoyer la requête AJAX avec le commentaire
        $.post(ajaxurl, data, function(response) {
            if (response.success) {
                // Only disable checkbox if the user is not an admin or agency
                if (!MyAjax.isAdminOrAgency) {
                    checkbox.prop('disabled', true); // Disable checkbox if successful
                }
            } else {
                alert("Il y a eu une erreur lors de la validation.");
                checkbox.prop('checked', !isChecked); // Revert checkbox state if error
            }
        });
        // .always(function() {
        //     // Optionally reload the page to reflect any changes
        //     if (MyAjax.reloadOnSuccess) {
        //         window.location.reload(true);
        //     }
        // });
    });

    $('#send_email_checkbox').change(function() {
        if(this.checked) {
            $('#email_field_container').show();
        } else {
            $('#email_field_container').hide();
        }
    });
});
