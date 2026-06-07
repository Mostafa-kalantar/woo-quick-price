jQuery(function ($) {

    $('.wqp-save').on('click', function () {

        let row = $(this).closest('tr');

        let data = {
            action: 'wqp_save_product',
            nonce: wqp.nonce,
            id: row.data('id'),
            regular: row.find('.regular_price').val(),
            sale: row.find('.sale_price').val(),
            stock: row.find('.stock').val()
        };

        $.post(ajaxurl, data, function (res) {
            if (res.success) {
                alert('Saved');
            } else {
                alert('Error');
            }
        });

    });

    jQuery(function ($) {

        $('.wqp-category-select').select2({
            width: '300px'
        });

    });

});