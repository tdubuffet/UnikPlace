var Signup = {

    init: function() {

        Signup.initProFields();

        // Address jQuery Validator
        function AddressValidator(value, element, paras) {

            if (!$.trim($('#fos_user_registration_form_route').val()).length && !$.trim($('#fos_user_registration_form_sublocality_level_1').val()).length) {
                return false;
            }

            if (!$.trim($('#fos_user_registration_form_locality').val()).length) {
                return false;
            }

            if (!$.trim($('#fos_user_registration_form_administrative_area_level_1').val()).length) {
                return false;
            }

            if (!$.trim($('#fos_user_registration_form_country').val()).length) {
                return false;
            }

            if (!$.trim($('#fos_user_registration_form_postal_code').val()).length) {
                return false;
            }

            return true;
        }

        // Define a new jQuery Validator method
        $.validator.addMethod("fulladdress", AddressValidator);

        var placeSearch, autocomplete;
        var componentForm = {
            street_number: 'short_name',
            route: 'long_name',
            locality: 'long_name',
            administrative_area_level_1: 'short_name',
            country: 'long_name',
            postal_code: 'short_name'
        };

        autocomplete = new google.maps.places.Autocomplete(
            (document.getElementById('fos_user_registration_form_street')),
            {types: ['geocode']});

        autocomplete.addListener('place_changed', fillInAddress);

        function fillInAddress() {
            // Get the place details from the autocomplete object.
            var place = autocomplete.getPlace();
            for (var component in componentForm) {
                document.getElementById('fos_user_registration_form_' + component).value = '';
            }

            for (var i = 0; i < place.address_components.length; i++) {
                var addressType = place.address_components[i].types[0];
                if (componentForm[addressType]) {
                    var val = place.address_components[i][componentForm[addressType]];
                    document.getElementById('fos_user_registration_form_' + addressType).value = val;
                }
            }
        }

        $("#signup-form").validate({
            onkeyup: true,
            rules: {
                "fos_user_registration_form[email]": {
                    required: true,
                    email: true,
                    minlength: 3,
                    maxlength: 100,
                },
                "fos_user_registration_form[firstname]": {
                    required: true,
                    minlength: 3,
                    maxlength: 75,
                },
                "fos_user_registration_form[lastname]": {
                    required: true,
                    minlength: 3,
                    maxlength: 75,
                },
                "fos_user_registration_form[plainPassword][first]": {
                    required: true,
                    minlength: 3,
                    maxlength: 50,
                },
                "fos_user_registration_form[plainPassword][second]": {
                    minlength: 3,
                    maxlength: 50,
                    equalTo: "#fos_user_registration_form_plainPassword_first",
                },
                "fos_user_registration_form[company_code]": {
                    required: true,
                },
                "fos_user_registration_form[company_name]": {
                    required: true,
                },
                "fos_user_registration_form[street]": {
                    fulladdress: true,
                    required: true,
                },
                "fos_user_registration_form[phone]": {
                    required: true
                }

            },messages: {
                'fos_user_registration_form[street]': {
                    fulladdress: "Cette adresse n'est pas valide. Si le problème persiste, merci de nous contacter."
                }
            },
            errorPlacement: function(error, element) {
                if (element.attr("type") == "radio") {
                    error.appendTo($('.showErrorPro'));
                } else {
                    error.insertAfter(element);
                }
            }
        });

        $("input[name='fos_user_registration_form[phone]']").intlTelInput({
            initialCountry: "auto",
            geoIpLookup: function (callback) {
                $.get('http://ipinfo.io', function () {
                }, "jsonp").always(function (resp) {
                    var countryCode = (resp && resp.country) ? resp.country : "";
                    callback(countryCode);
                });
            },
            utilsScript: "/components/intl-tel-input/build/js/utils.js" // just for formatting/placeholders etc
        });

        $("form").submit(function () {
            $("#phone-full").val($("input[name='fos_user_registration_form[phone]']").intlTelInput("getNumber"));
        });
    },

    initProFields: function() {
        var input = $('input:radio[name="fos_user_registration_form[pro]"]');
        input.change(function(){
            Signup.toggleProFields($(this).val());
        });
        input.attr('checked', false);
        Signup.toggleProFields(input.val());
    },

    toggleProFields: function(val) {
        if (val == 1) {
            $('.pro-user-fields').show();
        }
        else {
            $('.pro-user-fields').hide();
        }
    }
};