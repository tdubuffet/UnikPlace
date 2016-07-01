var Wishlist = {

    init: function() {
        Wishlist.initFavoriteButtons();
    },

    initFavoriteButtons: function() {
        $('.link-wishlist').click(function() {
            var button = this;
            $.ajax({
                url: Routing.generate('product_favorite'),
                type: 'POST',
                data: {product_id: $(button).data('product-id'), action: 'remove'},
                beforeSend: function() {
                    $(button).prop('disabled', true);
                },
                success: function(result) {
                    $(button).closest('.item').fadeOut('slow', function() {
                        if (Wishlist.getTotalVisibleItems() <= 0) {
                            $('h1').after("<p>Vous n'avez aucun produit dans votre wishlist.</p>");
                        }
                    });
                },
                error: function(result) {
                    if (result.status == 401) {
                        window.location.href = Routing.generate('fos_user_security_login');
                    }
                }
            });
        });
    },

    getTotalVisibleItems: function() {
        return $('.products-grid .item:visible').length;
    }

};