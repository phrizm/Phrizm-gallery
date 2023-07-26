jQuery(function($){
    var mediaUploader;
    var imageContainer = $('#phrizm-images-preview');

    imageContainer.find('.phrizm-image').each(function() {
        var imgId = $(this).data('id');
        $('<input>', {
            type: 'hidden',
            name: 'gallery_img_ids[]',
            value: imgId
        }).appendTo(imageContainer);
    });

    $('#upload_image_button').click(function(e) {
        e.preventDefault();
    
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }
    
        mediaUploader = wp.media.frames.file_frame = wp.media({
            title: 'Scegli le Immagini',
            button: {
                text: 'Scegli le Immagini'
            },
            multiple: true
        });
    
        mediaUploader.on('select', function() {
            var selection = mediaUploader.state().get('selection');
            var imageIds = [];
            selection.map( function( attachment ) {
                attachment = attachment.toJSON();
                imageContainer.append('<div class="phrizm-image" data-id="' + attachment.id + '"><img src="' + attachment.url + '" width="200" height="200"/><span class="remove-image">x</span></div>');
                imageContainer.append('<input type="hidden" name="gallery_img_ids[]" value="' + attachment.id + '">');
                imageIds.push(attachment.id);
            });
            
            var data = {
                action: 'save_phrizm_images',
                post_id: $('#post_ID').val(),
                image_ids: imageIds,
                template: $('#phrizm-gallery-template').val(),
                security: my_plugin.security
            };
        
            $.post(ajaxurl, data, function(response) {
                // Handle the response here
            });
        });
        
        mediaUploader.open();
    });

    imageContainer.on('click', '.remove-image', function() {
        $(this).parent().next('input[type="hidden"]').remove();
        $(this).parent().remove();
    });
    
    imageContainer.sortable({
        update: function(event, ui) {
            var inputs = imageContainer.find('input[type="hidden"]');
            imageContainer.append(inputs.get().sort(function(a, b) {
                return $(a).prev('.phrizm-image').index() - $(b).prev('.phrizm-image').index();
            }));
        }
    });

    $('#publish').on('click', function(e) {
        e.preventDefault();
        
        var imageIds = [];
        imageContainer.find('input[type="hidden"]').each(function() {
            imageIds.push($(this).val());
        });

        var data = {
            action: 'save_phrizm_images',
            post_id: $('#post_ID').val(),
            image_ids: imageIds,
            template: $('#phrizm-gallery-template').val(),
            security: my_plugin.security
        };

        $.post(ajaxurl, data, function(response) {
            $('#publish').off('click');
            $('#publish').trigger('click');
        });
    });
});
