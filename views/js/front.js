'use strict';

(function ($) {
    $(document).ready(function () {
        $('.btn-cart-rounding').on('click', function () {
            let $that = $(this);

            $.post($(this).data('round'), {}, function (result) {
                result = JSON.parse(result);

                if (result.success === true) {
                    prestashop.on('updateCart', function (params) {
                        if (
                            typeof (params) !== 'undefined'
                            && typeof (prestashop.cart) !== 'undefined'
                        ) {
                            alert('update cart');
                        }
                    });

                    $that.text(result.new_text);
                    window.location.reload();
                }
            });
        })
    });
})(jQuery);

