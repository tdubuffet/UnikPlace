var Homepage = {

    init: function() {
        $('#block-slide-home .owl-carousel').owlCarousel({
            "enable":true,
            "pagination":true,
            "autoplay":true,
            "autoplayTimeout":8000,
            "autoplayHoverPause":false,
            "loop":true,
            "items":1,
            "singleItem":true,
            "lazyLoad":true,
            "lazyEffect":false,
            "addClassActive":true,
            "navigation":false,
            "navigationText":[null,null]});
    }

};