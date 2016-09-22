var Product = {

    init: function() {
        $("[data-toggle=popover]").popover();
        $("[data-toggle=tooltip]").tooltip();

        Product.initFavoriteButton();
        Product.initCartButton();

        // Initialize inner zoom on main picture
        $('#image-main').elevateZoom(Product.getZoomConfig());

        Product.setMainPicture();
        Product.initCarousel();
        Product.initSimilarProductsCarousel();
    },

    getZoomConfig: function() {
        return {cursor: 'crosshair', zoomType: "inner"};
    },

    setMainPicture: function() {
        $('.thumb-link').click(function(e){
            e.preventDefault();

            var zoomImg = $('#image-main'),
                src = $(this).find('> img').data('src');

            $('.zoomContainer').remove();
            zoomImg.removeData('elevateZoom');

            // Update images sources
            zoomImg.prop('src', src).data('zoom-image', src);

            // Reinitialize elevateZoom
            var zoomConfig = Product.getZoomConfig();
            zoomImg.elevateZoom(zoomConfig);

            return false;
        });
    },

    initCarousel: function() {
        $("#img-products").owlCarousel({
            "autoPlay": false,
            "items": 4,
            "loop": true,
            responsiveClass:true,
            responsive:{
                0:{
                    items:1,
                },
                600:{
                    items:2,
                },
                1000:{
                    items:3,
                }
            }

        });
        $(".owl-nav-left.img-products-nav").click(function() {
            $("#img-products").trigger('prev.owl.carousel');
        });

        $(".owl-nav-right.img-products-nav").click(function() {
            $("#img-products").trigger('next.owl.carousel');
        });
    },

    initSimilarProductsCarousel: function() {
        $("#similar-products").owlCarousel({
            "autoPlay": false,
            "items": 4,
            "loop": true,
            responsiveClass:true,
            responsive:{
                0:{
                    items:1,
                },
                600:{
                    items:3,
                },
                1000:{
                    items:4,
                }
            }

        });
        $(".owl-nav-left").click(function() {
            $("#similar-products").trigger('prev.owl.carousel');
        });

        $(".owl-nav-right").click(function() {
            $("#similar-products").trigger('next.owl.carousel');
        });
    },

    initFavoriteButton: function() {
        $('.link-wishlist').click(function() {
            var button = this;
            $.ajax({
                url: Routing.generate('product_favorite'),
                type: 'POST',
                data: {product_id: $(button).data('product-id'), action: $(button).data('action')},
                success: function(result) {
                    if ($(button).data('action') === 'add') {
                        $(button).addClass('is-favorite');
                        $(button).find('.text').html('Retirer de ma Whishlist')
                    }
                    else {
                        $(button).removeClass('is-favorite');
                        $(button).find('.text').html('Ajouter à ma Whishlist')
                    }
                    $(button).data('action', $(button).data('action') === 'add' ? 'remove' : 'add');
                },
                error: function(result) {
                    if (result.status == 401) {
                        window.location.href = Routing.generate('fos_user_security_login');
                    }
                }
            });
        });
    },

    initCartButton: function() {
        $('.btn-cart').click(function() {
            if (!$(this).hasClass('is-added')) {
                var product_id = $(this).data('product-id');
                var loading = $('.popup-wrapper .loading');
                $.ajax({
                    url: Routing.generate('product_cart'),
                    type: 'POST',
                    data: {product_id: product_id, action: 'add'},
                    beforeSend: function() {
                        loading.show();
                    },
                    complete: function() {
                        loading.fadeOut('fast');
                    },
                    success: function(result) {
                        // Update add button
                        $('.btn-cart-txt').text("Dans votre panier");
                        $('.btn-cart').prop('title', "Ce produit est dans votre panier").addClass('is-added');

                        // Update header cart link
                        $('.icon-cart-header span.total strong').animate({fontSize: '20px'}, 'fast').animate({fontSize: '16px'}, 'fast');
                        var total = parseInt($('.header-maincart .total .numb:first').text()) + 1;
                        $('.header-maincart .total .numb').text(total);

                        $('.popup-wrapper').show();
                    },
                    error: function(result) {
                        if (result.status == 401) {
                            var redirectTo =  encodeURIComponent(window.location.href);
                            window.location.href = Routing.generate('fos_user_security_login') + '?redirect_to=' + redirectTo;
                        }
                    }
                });
            }
        });

        $('.btn-continue').click(function() {
            $(this).closest('.popup-wrapper').fadeOut();
        });
    }

};