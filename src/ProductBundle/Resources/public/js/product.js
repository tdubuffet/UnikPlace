var Product = {

    init: function() {
        $("[data-toggle=popover]").popover();
        $("[data-toggle=tooltip]").tooltip();

        Product.initFavoriteButton();
        Product.initCartButton();
        
        if ($(window).width() >= 750) {
            // Initialize inner zoom on main picture
            $('#image-main').elevateZoom(Product.getZoomConfig());
        }

        Product.mainOwlCarrousel();
        //Product.setMainPicture();
        Product.initSimilarProductsCarousel();
        Product.initUserProductsCarousel();
    },

    mainOwlCarrousel: function () {

        $(document).ready(function () {

            $("#main-images").owlCarousel({

                nav: true, // Show next and prev buttons
                singleItem: true,
                items: 1,
                lazyLoad: true,
                loop: true,
                navText: ['<i class="fa fa-chevron-left"></i>', '<i class="fa fa-chevron-right"></i>'],
                dots: false,
                nestedItemSelector: 'item'
                // "singleItem:true" is a shortcut for:
                // items : 1,
                // itemsDesktop : false,
                // itemsDesktopSmall : false,
                // itemsTablet: false,
                // itemsMobile : false

            });

        });


    },

    getZoomConfig: function() {
        return {
            cursor: 'crosshair',
            zoomWindowFadeIn: 500,
            zoomWindowFadeOut: 500,
            lensFadeIn: 500,
            lensFadeOut: 500,
            zoomWindowWidth: 500,
            zoomWindowHeight: 500,
            borderSize: 0
        };
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

    initSimilarProductsCarousel: function() {
        $("#similar-products").owlCarousel({
            "autoPlay": false,
            "items": 4,
            "loop": true,
            responsiveClass:true,
            nestedItemSelector: 'item',
            margin: 30,
            dots: false,
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
            },

        });
        $(".similar-products > .owl-nav-left").click(function () {
            $("#similar-products").trigger('prev.owl.carousel');
        });

        $(".similar-products > .owl-nav-right").click(function () {
            $("#similar-products").trigger('next.owl.carousel');
        });
    },

    initUserProductsCarousel: function() {
        $("#user-products").owlCarousel({
            "autoPlay": false,
            "items": 4,
            "loop": true,
            margin: 30,
            responsiveClass:true,
            nestedItemSelector: 'item',
            dots: false,
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
        $(".user-products > .owl-nav-left").click(function () {
            $("#user-products").trigger('prev.owl.carousel');
        });

        $(".user-products > .owl-nav-right").click(function () {
            $("#user-products").trigger('next.owl.carousel');
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
                        $(button).find('.text').html('<i class="fa fa-heart-o"></i>')
                    }
                    else {
                        $(button).removeClass('is-favorite');
                        $(button).find('.text').html('<i class="fa fa-heart"></i>')
                    }
                    $(button).data('action', $(button).data('action') === 'add' ? 'remove' : 'add');
                },
                error: function(result) {
                    if (result.status == 401) {
                        var redirectTo = encodeURIComponent(window.location.href);
                        window.location.href = Routing.generate('fos_user_security_login') + '?redirect_to=' + redirectTo;
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
                        $('.btn-cart-text').text("Dans votre panier");
                        $('.btn-cart').prop('title', "Ce produit est dans votre panier").addClass('is-added');

                        var total = parseInt($('.cart-container .text').text()) + 1;
                        $('.cart-container .text').text(total);

                        $('#modalAddToCart').modal('show') ;
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