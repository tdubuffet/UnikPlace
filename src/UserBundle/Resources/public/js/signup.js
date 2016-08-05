var Signup = {

    init: function() {
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
                "fos_user_registration_form[company_address]": {
                    required: true,
                },
                "fos_user_registration_form[company_zipcode]": {
                    required: true,
                },
                "fos_user_registration_form[company_city]": {
                    required: true,
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