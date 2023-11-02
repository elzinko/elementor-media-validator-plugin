jQuery(document).ready(function($) {

    $('.validation input[type="checkbox"]').click(function(event) {
        var checkbox = $(this); // L'élément case à cocher sur lequel l'utilisateur a cliqué
        var isChecked = checkbox.is(':checked'); // L'état de la case à cocher avant le clic

        // Nous devons empêcher le changement de l'état si la case est déjà cochée.
        if (isChecked) {
            // Demander un commentaire avant de cocher la case
            var comment = prompt("Veuillez entrer votre commentaire pour la validation:");
        }
        var postId = checkbox.data('id'); // Récupérer l'ID du post
        
        // Envoyer la requête AJAX avec le commentaire
        $.post(
            ajaxurl,
            {
                action: 'update_media_validation',
                post_id: postId,
                is_checked: isChecked, // On suppose la validation ici
                comment: comment, // Commentaire à envoyer
                security: MyAjax.security // La nonce de sécurité
            },
            function(response) {
                console.log('The server responded: ', response);
                // Si la réponse du serveur est positive, cocher la case et la désactiver
                if (response.success) {
                    checkbox.prop('checked', true).prop('disabled', true);
                } else {
                    // Gérer les erreurs ici
                    console.error('Error: ', response);
                }
            }
        );
    });

    $('#send_email_checkbox').change(function() {
        if(this.checked) {
            $('#email_field_container').show();
        } else {
            $('#email_field_container').hide();
        }
    });
});
