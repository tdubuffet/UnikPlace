var Homepage = {

    init: function() {
        $('.alert-animation-close').click(function() {
            console.log('Disparition !');
            $(this).parent().animate({
                opacity: 0,
            });
        });
    }

}

$(document).ready(function() {
    Homepage.init();
});
