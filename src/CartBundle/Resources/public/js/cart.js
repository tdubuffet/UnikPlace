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
        $('.totalDelivery').text("€"+totalDeliveryFee);
        var totalProduct = parseFloat($('.totalProduct').data('total'));
        var total = totalDeliveryFee+totalProduct;
        $('.totalOrder').text("€"+total);
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
                    var total = $('span.numb.main-numb');
                    $('h1.title-h1').after("<div class='alert alert-success'>Le produit a bien été retiré du panier</div>");
                    $('span.product').html(result['prices']['product']);
                    Cart.calculateDeliveryFee();
                    total.html(total.html() - 1);

                },
                error: function (result) {
                    if (result.status == 401) {
                        window.location.href = Routing.generate('fos_user_security_login') + '?redirect_to=' + window.location.href ;
                    }
                }
            });
        });
    }
};