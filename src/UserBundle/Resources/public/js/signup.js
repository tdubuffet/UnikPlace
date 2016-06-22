var Signup = {

    init: function() {
        $( "#login-form" ).validate({
            rules: {
                _username: {
                    required: true,
                    minlength: 2
                }
            }
        });
    }

}