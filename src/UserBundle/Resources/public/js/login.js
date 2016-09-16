var Login = {

    init: function() {
        $("#login-form").validate({
            rules: {
                "_username": {
                    required: true,
                    minlength: 3,
                    maxlength: 50,
                },
                "_password": {
                    required: true,
                    minlength: 3,
                    maxlength: 50,
                }
            }
        });



        $("#signup-form").validate({
            rules: {
                "email_registration": {
                    required: true,
                    email: true,
                    minlength: 3,
                    maxlength: 100,
                    remote: Routing.generate('check_email')
                }
            }
        });
    }

};