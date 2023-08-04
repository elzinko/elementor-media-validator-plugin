<?php

// MEDIA VALIDATION
function add_validation_checkbox($form_fields, $post)
{
    $checked = get_post_meta($post->ID, 'media_validation', true) ? 'checked' : '';
    $form_fields['media_validation'] = array(
        'label' => 'Validé',
        'input' => 'html',
        'html'  => "<input type='checkbox' name='attachments[{$post->ID}][media_validation]' id='attachments[{$post->ID}][media_validation]' value='1' $checked />",
        'value' => get_post_meta($post->ID, 'media_validation', true),
        'helps' => 'Cochez cette case si le média est validé par le client',
    );
    return $form_fields;
}
add_filter('attachment_fields_to_edit', 'add_validation_checkbox', 10, 2);


function save_validation_checkbox($post, $attachment)
{
    if (isset($attachment['media_validation'])) {
        update_post_meta($post['ID'], 'media_validation', $attachment['media_validation']);
    } else {
        delete_post_meta($post['ID'], 'media_validation');
    }
    return $post;
}
add_filter('attachment_fields_to_save', 'save_validation_checkbox', 10, 2);


function update_media_validation()
{
    // Check the nonce for security
    check_ajax_referer('my-special-string', 'security');

    $postId = $_POST['post_id'];
    $isChecked = $_POST['is_checked'];

    // Update the post meta
    update_post_meta($postId, 'media_validation', $isChecked);

    echo 'Success';
    wp_die(); // This is required to terminate immediately and return a proper response
}

add_action('wp_ajax_update_media_validation', 'update_media_validation');

// MEDIA VALIDATION - END