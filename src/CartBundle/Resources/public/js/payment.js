var Payment = {

    init : function() {
        Payment.validateCreditCard();
        Payment.applyMasks();
        // concatenate expiration month and year to hidden input
        $('#cc_month, #cc_year').on('change', function(e) {
            var expirationDate = $('#cc_month').val() + $('#cc_year').val();
            $('#cardExpirationDate').val(expirationDate);
        });

       // remove spaces from card number (space separators are made with the mask)
        $('#cc_number').on('change', function(e) {
            var ccNumber = $('#cc_number').val().replace(/\s+/g, '');
            $('#cardNumber').val(ccNumber);
        });
    },


    validateCreditCard: function() {
        if ($('#cc_number').length > 0) {
            $('#cc_number').validateCreditCard(function(result) {
                if(result.card_type) {
                    $(".cards li").addClass("off");
                    $(".cards ." + result.card_type.name).removeClass("off");
                    if(result.length_valid && result.luhn_valid)
                        $('#cc_number').addClass('validcc');
                    else
                        $('#cc_number').removeClass('validcc');
                } else {
                    $('#cc_number').removeClass('validcc');
                    $(".cards li").removeClass("off");
                }
            }, {
                accept: ['visa', 'mastercard', 'amex', 'maestro', 'visa_electron' ]
            });
        }
    },

    applyMasks: function() {
        if ($.fn.mask) { // if plugin is loaded
            $('#cc_number').mask("9999 9999 9999 9999");
            $('#cc_month').mask("99");
            $('#cc_year').mask("99");
            $('#cardCvx').mask("999");
        }
    },

};