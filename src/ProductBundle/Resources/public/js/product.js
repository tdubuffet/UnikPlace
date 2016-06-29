var Product = {

    init: function() {

        // Initialize inner zoom on main picture
        var zoomConfig = {cursor: 'crosshair', zoomType: "inner"};
        $('#image-main').elevateZoom(zoomConfig);

        // Carousel behaviour
        var carCount = $('.product-img-box .verticl-carousel').find('a').length;
        if (carCount <= 3) {
            $('.product-img-box .more-views-nav').hide();
        }
        $(".product-img-box #carousel-up").on("click", function () {
            if (!$(".product-img-box .verticl-carousel").is(':animated')) {
                var bottom = $(".product-img-box .verticl-carousel > a:last-child");
                var clone = $(".product-img-box .verticl-carousel > a:last-child").clone();
                clone.prependTo(".product-img-box .verticl-carousel");
                $(".product-img-box .verticl-carousel").animate({
                    "top": "-=85"
                }, 0).stop().animate({
                    "top": '+=85'
                }, 250, function () {
                    bottom.remove();
                });
            }
        });
        $(".product-img-box #carousel-down").on("click", function () {
            if (!$(".product-img-box .verticl-carousel").is(':animated')) {
                var top = $(".product-img-box .verticl-carousel > a:first-child");
                var clone = $(".product-img-box .verticl-carousel > a:first-child").clone();
                clone.appendTo(".product-img-box .verticl-carousel");
                $(".product-img-box .verticl-carousel").animate({
                    "top": '-=85'
                }, 250, function () {
                    top.remove();
                    $(".product-img-box .verticl-carousel").animate({
                        "top": "+=85"
                    }, 0);
                });
            }
        });

        $('.thumb-link').click(function(e){
            e.preventDefault();

            var zoomImg = $('#image-main'),
                src = $(this).find('> img').prop('src');

            $('.zoomContainer').remove();
            zoomImg.removeData('elevateZoom');

            // Update images sources
            zoomImg.prop('src', src).data('zoom-image', src);

            // Reinitialize elevateZoom
            zoomImg.elevateZoom(zoomConfig);
        });

    }

}