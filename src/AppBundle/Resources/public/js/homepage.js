var Homepage = {

    init: function() {
        $('#block-slide-home .owl-carousel').owlCarousel({
            "enable":true,
            "pagination":true,
            "autoPlay":false,
            "items":1,
            "singleItem":true,
            "lazyLoad":true,
            "lazyEffect":false,
            "addClassActive":true,
            "navigation":false,
            "navigationText":[null,null]});
    }

};