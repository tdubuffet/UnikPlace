/**
 * Created by francois on 28/07/16.
 */

var Cart = {
    init: function () {
        Cart.cartAction();
        Cart.calculateDeliveryFeeBindEvent();
        Cart.calculateDeliveryFee();
    },

    calculateDeliveryFeeBindEvent: function () {
        $('.cartDeliverySelection').change(function() {
            Cart.calculateDeliveryFee();
        });
    },

    calculateDeliveryFee: function(fee) {},

    cartBtnAdd: function(productId, action) {

        $.ajax({
            url: Routing.generate('product_cart_quantity', {id: productId, action: action}),
            type: 'GET',
            success: function (result) {

                if (typeof result.status != 'undefined' && result.status == 'OK') {
                    $('#' + productId).html(result.quantity);

                    var total = result.quantity * $('#' + productId).data('price');

                    $('.total-row-price-' + productId).html('€' + total);

                    var product = $('.totalProduct');
                    product.html(result['prices']['formated']);
                    product.data('total', result['prices']['price']);
                }
            }
        });

    },

    cartRefresh: function() {

    },

    cartAction: function () {
        $("td .btn-remove").click(function () {
            var row = $($(this).parents("tr")[0]);

            $.ajax({
                url: Routing.generate('product_cart'),
                type: 'POST',
                data: {product_id: $(this).data('id'), action: "remove"},
                success: function (result) {
                    row.remove();
                    var h1 = $('h1.title-h1');
                    h1.after("<div class='alert alert-success'>Le produit a bien été retiré du panier</div>");

                    if (result['prices']['price'] == 0) {
                        h1.after("<p>Votre panier est vide</p>");
                        $('#form-carts').remove();
                    }else {
                        var product = $('.totalProduct');
                        product.html(result['prices']['formated']);
                        product.data('total', result['prices']['price']);
                    }
                },
                error: function (result) {
                    if (result.status == 401) {
                        var redirectTo =  encodeURIComponent(window.location.href);
                        window.location.href = Routing.generate('fos_user_security_login') + '?redirect_to=' + redirectTo ;
                    }
                }
            });
        });
    }
};