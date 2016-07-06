var Search = {

    params: {},

    init: function() {
        Search.initSortBy();
        Search.initSortDirection();
        Search.initPagination();
        Search.initQuery();
        Search.initPrice();
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
        $('.pagination li a').click(function(e) {
            var page = $(this).text();
            page = parseInt(page);
            if (!isNaN(page)) {
                Search.params.p = page;
                Search.search('pagination');
                e.preventDefault();
            }
        });
    },


    initQuery: function() {
        $('#search_mini_form').submit(function(e) {
            Search.search();
            e.preventDefault();
        });
    },

    initPrice: function() {
        var price = Search.getUrlParameter('price');
        if (price) {
            // Reinject price values (from and to)
            priceElems = price.split('-');
            if (priceElems.length == 2) {
                var from = parseInt(priceElems[0]);
                if (!isNaN(from)) {
                    $('.search-price-from').val(from);
                }
                var to = parseInt(priceElems[1]);
                if (!isNaN(to)) {
                    $('.search-price-to').val(to);
                }
            }
        }
        $('.search-price-submit').click(function() {
            Search.search();
        });
    },

    collectParams: function(event) {
        // Reset pagination if the trigger is not pagination
        if (event != 'pagination') {
            Search.params.p = 1;
        }

        Search.params.q = $('#search').val();
        Search.params.price = $('.search-price-from').val()+'-'+$('.search-price-to').val();
        Search.params.sort = $('.sort_by_value').val();
        Search.params.ord = $('.ord_value').val();
        window.history.pushState(Search.params, 'Recherche', Routing.generate('search')+'?'+$.param(Search.params));
    },

    search: function(event) {
        Search.collectParams(event);
        $.ajax({
            url: Routing.generate('ajax_search'),
            type: 'POST',
            data: Search.params,
            success: function(result) {
                $('.category-products-container').html(result);
                Search.initPagination();
            },
            error: function(result) {
            }
        });

    },

    getUrlParameter: function(sParam) {
        var sPageURL = decodeURIComponent(window.location.search.substring(1)),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;

        for (i = 0; i < sURLVariables.length; i++) {
            sParameterName = sURLVariables[i].split('=');

            if (sParameterName[0] === sParam) {
                return sParameterName[1] === undefined ? true : sParameterName[1];
            }
        }
    }

};