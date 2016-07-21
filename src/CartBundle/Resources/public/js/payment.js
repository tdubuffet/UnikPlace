var Payment = {

    init : function() {
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
    }
};