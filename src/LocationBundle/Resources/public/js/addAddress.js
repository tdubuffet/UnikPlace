var AddAddress = {

    init : function() {



        // Address jQuery Validator
        function AddressValidator(value, element, paras) {

            if (!$.trim($('#address_route').val()).length && !$.trim($('#address_sublocality_level_1').val()).length) {
                return false;
            }

            if (!$.trim($('#address_locality').val()).length) {
                return false;
            }

            if (!$.trim($('#address_administrative_area_level_1').val()).length) {
                return false;
            }

            if (!$.trim($('#address_country').val()).length) {
                return false;
            }

            if (!$.trim($('#address_postal_code').val()).length) {
                return false;
            }

            return true;
        }

        // Define a new jQuery Validator method
        $.validator.addMethod("fulladdress", AddressValidator);

        $(document).ready(function () {

            var placeSearch, autocomplete;
            var componentForm = {
                street_number: 'short_name',
                sublocality_level_1: 'long_name',
                route: 'long_name',
                locality: 'long_name',
                administrative_area_level_1: 'short_name',
                country: 'long_name',
                postal_code: 'short_name'
            };

            autocomplete = new google.maps.places.Autocomplete(
                (document.getElementById('address_street')),
                {types: ['geocode']});

            autocomplete.addListener('place_changed', fillInAddress);

            function fillInAddress() {
                // Get the place details from the autocomplete object.
                var place = autocomplete.getPlace();

                for (var component in componentForm) {
                    document.getElementById('address_' + component).value = '';
                }

                for (var i = 0; i < place.address_components.length; i++) {
                    var addressType = place.address_components[i].types[0];
                    if (componentForm[addressType]) {
                        var val = place.address_components[i][componentForm[addressType]];
                        document.getElementById('address_' + addressType).value = val;
                    }
                }
            }

            if ($('#add-address-form')) {

                // Enable jQuery Validation for the form
                $('#add-address-form').validate({
                    onkeyup: true
                });

                // Add validation rules to the Address field
                $("#address_street").rules("add", {
                    fulladdress: true,
                    required: true,
                    messages: {
                        fulladdress: "Cette adresse n'est pas valide. Si le problème persiste, merci de nous contacter."
                    }
                });
            }

        });
    },
};
