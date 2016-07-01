var Signup = {

    init: function() {
        $("#signup-form").validate({
            rules: {
                "fos_user_registration_form[email]": {
                    required: true,
                    email: true,
                    minlength: 3,
                    maxlength: 100,
                },
                "fos_user_registration_form[username]": {
                    required: true,
                    minlength: 3,
                    maxlength: 50,
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
            }
        });
    }

};