jQuery(document).ready(function($) {

    $('.validation input[type="checkbox"]').change(function(e) {
        e.preventDefault(); // Empêche la case à cocher de changer d'état immédiatement
        var postId = $(this).data('id'); // Get the post ID from a data attribute
        var isChecked = $(this).is(':checked') ? 1 : 0; // Determine if checkbox is checke

        // Si la case est déjà cochée (et donc cliquée pour être décochée par l'agence)
        if (isChecked) {
            prompt("Impossible de valider de nouveau");
            // Ici, vous pouvez ajouter la logique pour afficher une popup de confirmation pour l'agence.
            // ...
        } else {
            // Popup pour le commentaire client
            var comment = prompt("Veuillez entrer votre commentaire pour la validation:");
            if (comment) {
                // Envoyer la requête AJAX avec le commentaire
                $.post(
                    ajaxurl,
                    {
                        action: 'update_media_validation',
                        post_id: postId,
                        is_checked: 1, // On suppose la validation ici
                        comment: comment, // Commentaire à envoyer
                        security: MyAjax.security // La nonce de sécurité
                    },
                    function(response) {
                        console.log('The server responded: ', response);
                        // Mettre à jour l'interface utilisateur ici, par exemple griser la case à cocher
                        checkbox.prop('disabled', true);
                    }
                );
            }
        }

        // // Make the AJAX request
        // $.post(
        //     ajaxurl, // This variable is automatically defined by WordPress and points to /wp-admin/admin-ajax.php
        //     {
        //         action: 'update_media_validation', // The action hook name
        //         post_id: postId, // The post ID
        //         is_checked: isChecked, // The checkbox state
        //         security: MyAjax.security // A nonce for security. Replace 'MyAjax.security' with the localized object and property where you've stored the nonce.
        //     },
        //     function(response) {
        //         console.log('The server responded: ', response);
        //     }
        // );
    });

});

jQuery(document).ready(function($) {
    $('#send_email_checkbox').change(function() {
        if(this.checked) {
            $('#email_field_container').show();
        } else {
            $('#email_field_container').hide();
        }
    });
});

