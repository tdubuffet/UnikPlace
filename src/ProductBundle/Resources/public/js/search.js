var Search = {

    params: {},

    init: function() {
        Search.initPriceQuery();
        Search.initPriceSearch();
    },

    initPriceQuery: function() {
        $('#search_mini_form').submit(function(e) {
            Search.search();
            e.preventDefault();
        });
    },

    initPriceSearch: function() {
        $('.search-price-submit').click(function() {
            Search.search();
        });
    },

    collectParams: function() {
        Search.params.q = $('#search').val();
        Search.params.price = $('.search-price-from').val()+'-'+$('.search-price-to').val();
    },

    search: function() {
        Search.collectParams();
        // Todo Update url
        $.ajax({
            url: Routing.generate('ajax_search'),
            type: 'POST',
            data: Search.params,
            success: function(result) {
                $('.category-products-container').html(result);
            },
            error: function(result) {
            }
        });

    }

};