var DetailOrder = {

    init: function() {
        DetailOrder.confirmLink();
        DetailOrder.initValidator();

        $('#confirm-sell-by-hand-form, #confirm-sell-form').submit(function(){
            var submitbutton = $(this).find('input[type=submit]');
            submitbutton.button('loading');
        });
    },

    confirmLink: function () {
        $('.btn-confirm-link').click(function(e) {
            e.preventDefault();
            var that = $(this);
            if(that.is('input') ) {
                var title = that.val();
                var form = that.closest('form');
            } else if (that.is('a')) {
                var title = that.html();
                var href = that.prop('href');
            }
            if (typeof that.data('button') !== 'undefined') {
                var buttonText = that.data('button');
            } else {
                var buttonText = "OK";
            }
            bootbox.dialog({
                message: that.data('message'),
                title: title,
                buttons: {
                    main: {
                        label: buttonText,
                        className: "btn-primary-outline",
                        callback: function() {
                            if(that.is('input') ) {
                                form.append('<input type="hidden" name="'+that.prop('name')+'" /> ');
                                form.submit();
                                return false;
                            } else if (that.is('a')) {
                                window.location.href = href;
                            }
                        }
                    }
                },
                onEscape: function() {  },
                backdrop: true
            });

            // Add data loading to cancel button
            var bootboxButton = $('.bootbox').find('button');
            if (bootboxButton.length > 0) {
                bootboxButton.attr('data-loading-text', "Confirmation en cours...");
                bootboxButton.click(function () {
                    $(this).button('loading');
                });
            }
        });
    },

    initValidator : function() {
        if ($.fn.validate) { // if plugin is loaded
            $.validator.setDefaults({
                highlight: function(element) {
                    $(element).closest('.form-group').addClass('has-error');
                },
                unhighlight: function(element) {
                    $(element).closest('.form-group').removeClass('has-error');
                },
                errorElement: 'span',
                errorClass: 'help-block',
                errorPlacement: function(error, element) {
                    if(element.parent('.radio-inline').length) {
                        element.parent().parent().append(error);
                    }
                    else if(element.parent('.input-group').length) {
                        error.insertAfter(element.parent());
                    } else {
                        error.insertAfter(element);
                    }
                },
                invalidHandler: function(form, validator) {
                    var errors = validator.numberOfInvalids();
                    if (errors) {
                        $('html, body').animate({
                            scrollTop: $(validator.errorList[0].element).offset().top - 90
                        }, 200);
                    }
                }
            });

            jQuery.validator.addMethod("validPostalCode", function(value, element) {
                return (/^[0-9]{5,5}$/.exec(value) !== null);
            }, "Vous devez saisir un code postal valide.");

            $("#confirm-sell-form").validate({
                rules : {
                    "billing_postal_code" : { validPostalCode: true },
                    "recipient_postal_code" : { validPostalCode: true },
                },
                ignore: ":hidden",
                focusInvalid: true
            });
        }
    }
}