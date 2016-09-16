var Signup = {

    init: function() {
        console.log('init signup');
        Signup.initProFields();
        $("#signup-form").validate({
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
                "fos_user_registration_form[address][street]": {
                    required: true,
                },
                "city_code": {
                    required: true,
                },
                "fos_user_registration_form[phone]": {
                    matches: "[0-9]+",
                    minlength:10,
                    maxlength:10,
                    required: true
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

        Signup.initAutoCompleteCity();
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
    },

    initAutoCompleteCity : function() {
        $('#fos_user_registration_form_address_city').select2({
            ajax: {
                url: Routing.generate('ajax_search_city'),
                dataType: 'json',
                delay: 250,
                method: 'post',
                data: function (params) {
                    return {
                        q: params.term // search term
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: $.map(data.cities, function(obj) {
                            return { id: obj.id, text: obj.name+' ('+obj.zipcode+')' };
                        })
                    };
                },
                cache: true
            },
            dropdownAutoWidth: true,
            minimumInputLength: 3,
            width: "100%"
        });
    }
};