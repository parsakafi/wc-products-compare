jQuery(document).ready(function ($) {
    var wpAjax = false;

    $('.wcpc-button').on('click', function (e) {
        var $this = $(this);
        if (wpAjax) return;
        wpAjax = true;

        $.post(
            WCProductsCompare.ajaxurl,
            {
                nonce: WCProductsCompare.ajaxnonce,
                action: "wcpc_add_remove",
                product_id: $this.data('id')
            }
        )
            .done(function (data) {
                if (data.data.status === 'added')
                    $this.addClass('wcpc-button-remove');
                else if (data.data.status === 'removed')
                    $this.removeClass('wcpc-button-remove');
                else if (data.data.status === 'max_exceeded')
                    alert(WCProductsCompare.maxExceededMessage.replace('%number%', data.data.count));

                if ($this.data('action') === 'refresh')
                    window.location.reload(true);

                if (data.data?.redirect && data.data.redirect !== '')
                    window.location.href = data.data.redirect;
            })
            .fail(function (xhr, status, error) {
                if (xhr.responseJSON?.data?.refresh)
                    setTimeout(function () {
                        window.location.reload(true);
                    }, 3000);
            })
            .always(function () {
                wpAjax = false;
            });
    });
});