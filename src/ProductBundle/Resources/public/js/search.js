var Search = {

    params: {},

    init: function() {
        Search.initSortBy();
        Search.initSortDirection();
        Search.initPagination();
        Search.initPriceQuery();
        Search.initPriceSearch();
    },

    initSortBy: function() {
        $('.overwrite-sortby').text(sortModeLabels[$('.sort_by_value').val()]);
        $('#sort_by button').click(function() {
            $('.overwrite-sortby').text($(this).text());
            $('.sort_by_value').val($(this).data('sort'));
            $('#sort_by').collapse('hide');
            Search.search();
        });
    },

    initSortDirection: function() {
        Search.changeSortDirectionArrow($('.ord_value').val());
        $('.sort-direction').click(function() {
            var currentDirection = $('.ord_value').val();
            if (currentDirection == 'asc') {
                $('.ord_value').val('desc');
            }
            else if (currentDirection == 'desc') {
                $('.ord_value').val('asc');
            }
            Search.changeSortDirectionArrow($('.ord_value').val());
            Search.search();
        });
    },

    changeSortDirectionArrow: function(direction) {
        if (direction == 'desc') {
            $($('.sort-direction').find('i')[0]).removeClass('fa-arrow-up').addClass('fa-arrow-down');
        }
        else if (direction == 'asc') {
            $($('.sort-direction').find('i')[0]).removeClass('fa-arrow-down').addClass('fa-arrow-up');
        }
    },

    initPagination: function() {
        // TODO
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
        Search.params.sort = $('.sort_by_value').val();
        Search.params.ord = $('.ord_value').val();
        window.history.pushState(Search.params, 'Recherche', Routing.generate('search')+'?'+$.param(Search.params));
    },

    search: function() {
        Search.collectParams();
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