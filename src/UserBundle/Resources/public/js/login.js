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
    }

};