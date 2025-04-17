'use strict';

(function ($) {
    $(document).ready(function () {
        $('.btn-cart-rounding').on('click', function () {
            let $that = $(this);

            $.post($(this).data('round'), {}, function (result) {
                result = JSON.parse(result);

                if (result.success === true) {
                    $that.text(result.new_text);
                    window.location.reload();
                }
            });
        })
    });
})(jQuery);

