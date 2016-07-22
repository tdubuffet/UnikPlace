var AddAddress = {

    init : function() {
        $('.autocomplete-city').select2({
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
            dropdownParent: $("#addAddressModal"),
            minimumInputLength: 3
        });
    }
};
