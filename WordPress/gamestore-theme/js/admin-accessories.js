(function ($) {
    var frame;

    $('#accessories-bg-upload').on('click', function (e) {
        e.preventDefault();

        if (frame) {
            frame.open();
            return;
        }

        frame = wp.media({
            title: 'Фоновое изображение раздела',
            button: { text: 'Использовать' },
            multiple: false
        });

        frame.on('select', function () {
            var attachment = frame.state().get('selection').first().toJSON();
            $('#accessories-bg-id').val(attachment.id);
            $('#accessories-bg-preview').html(
                '<img src="' + attachment.url + '" alt="" style="max-width:320px;height:auto;border-radius:4px;">'
            );
            $('#accessories-bg-remove').show();
        });

        frame.open();
    });

    $('#accessories-bg-remove').on('click', function (e) {
        e.preventDefault();
        $('#accessories-bg-id').val('');
        $('#accessories-bg-preview').empty();
        $(this).hide();
    });
})(jQuery);
