(function ($) {

    var $form = $('.variations_form').wc_variation_form();

    $form.on('found_variation reset_data', function (event, found_variation) {
        var webinars = typeof found_variation !== 'undefined' ? found_variation.webinars : [];

        $(this).find('.wp-zoom-variation-webinar').remove();

        webinars.map((webinar) => {
            console.log(event);
            $form.block({ message: null, overlayCSS: { background: '#fff', opacity: 0.6 } });

            $.ajax({
                url: wp_zoom.ajax_url,
                type: 'GET',
                data: {
                    action: 'wp_zoom_woocommerce_get_variation_webinars',
                    webinar: webinar
                },
                success: function (webinar) {
                    $form.find('.wp-zoom-variation-webinar').remove();
                    $form.find('.variations').after(webinar.html);
                },
                complete: function () {
                    $form.unblock();
                }
            });

        });
    });

})(jQuery);