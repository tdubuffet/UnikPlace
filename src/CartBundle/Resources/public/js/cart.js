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

    calculateDeliveryFee: function(fee) {
        var totalDeliveryFee = 0;
        $('.cartDeliverySelection').each(function(index, elem) {
            totalDeliveryFee += parseFloat($(elem).find(':selected').data('fee'));
        });
        $('.totalDelivery').text("€"+totalDeliveryFee.toFixed(2));
        var totalProduct = parseFloat($('.totalProduct').data('total'));
        var total = totalDeliveryFee+totalProduct;
        $('.totalOrder').text("€"+total.toFixed(2));
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
                        Cart.calculateDeliveryFee();
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