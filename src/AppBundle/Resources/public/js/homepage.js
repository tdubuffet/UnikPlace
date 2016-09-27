var Homepage = {

    init: function() {
        $('#block-slide-home .owl-carousel').owlCarousel({
            "enable":true,
            "pagination":true,
            "autoplay":true,
            "autoplayTimeout":8000,
            "autoplayHoverPause":false,
            "autoplaySpeed":1000,
            "loop":true,
            "items":1,
            "singleItem":true,
            "lazyLoad":true,
            "lazyEffect":false,
            "addClassActive":true,
            "navigation":false,
            "animateOut": 'fadeOut',
            "navigationText":[null,null],
            "mouseDrag": false,
            "touchDrag": false
        }
        )
    }


};