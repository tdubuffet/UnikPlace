/**
 * Created by francois on 28/07/16.
 */

var Cart = {
    init: function () {
        Cart.cartAction();
    },

    cartAction: function () {
        $("td .btn-danger").click(function () {
            var row = $($(this).parents("tr")[0]);

            $.ajax({
                url: Routing.generate('product_cart'),
                type: 'POST',
                data: {product_id: $(this).data('id'), action: "remove"},
                success: function (result) {
                    row.remove();
                    var total = $('span.numb.main-numb');
                    console.log(result);
                    $('h1.title-h1').after("<div class='alert alert-success'>Le produit a bien été retiré du panier</div>");
                    $('span.product').html(result['prices']['product']);
                    $('span.delivery').html(result['prices']['delivery']);
                    $('span.totalProduct').html(result['prices']['total']);
                    total.html(total.html() - 1);

                },
                error: function (result) {
                    if (result.status == 401) {
                        window.location.href = Routing.generate('fos_user_security_login');
                    }
                }
            });
        });
    }
};