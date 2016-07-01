var Profile = {

    init: function() {
        $("#profile-form").validate({
            rules: {
                "fos_user_profile_form[email]": {
                    required: true,
                    email: true,
                    minlength: 3,
                    maxlength: 100,
                },
                "fos_user_profile_form[current_password]": {
                    required: true,
                    minlength: 3,
                    maxlength: 50,
                },
                "fos_user_profile_form[firstname]": {
                    required: true,
                    minlength: 3,
                    maxlength: 75,
                },
                "fos_user_profile_form[lastname]": {
                    required: true,
                    minlength: 3,
                    maxlength: 75,
                },
                "fos_user_profile_form[company_code]": {
                    required: true,
                },
                "fos_user_profile_form[company_name]": {
                    required: true,
                },
                "fos_user_profile_form[company_address]": {
                    required: true,
                },
                "fos_user_profile_form[company_zipcode]": {
                    required: true,
                },
                "fos_user_profile_form[company_city]": {
                    required: true,
                },
            }
        });
    }

};