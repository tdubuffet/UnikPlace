var Search = {

    params: {},
    initialized: false,

    init: function() {
        Search.loadFilters();
        Search.initSortBy();
        Search.initSortDirection();
        Search.initPagination();
        Search.setCategory();
        Search.initialized = true;
    },

    loadFilters: function() {
        var categoryId = Search.getUrlParameter('cat');
        var data = {};
        if (!categoryId) {
            categoryId = $('#search-category').val();
        }
        categoryId = parseInt(categoryId);
        if (!isNaN(categoryId)) {
            data = {category_id: categoryId};
        }

        $.ajax({
            url: Routing.generate('ajax_search_attribute_filters'),
            type: 'POST',
            data: data,
            success: function(result) {
                // Update filter div
                $('.sidebar .block-layered-nav .block-content').html(result);
                Search.initPrice();
                // TODO reinject values
                // Bind events on these filters
                $('select.attribute-search-filter').change(function() {
                    Search.search();
                });
                $('#attribute-search-filter-color div').click(function() {
                    if ($(this).hasClass('active')) {
                        $(this).removeClass('active');
                    }
                    else {
                        $(this).addClass('active');
                    }
                    Search.search();
                });
                $('input.attribute-search-filter').keyup(function() {
                    Search.search();
                });
                $(".attribute-search-filter label input[type='checkbox']").change(function() {
                    Search.search();
                });
            },
            error: function(result) {
            }
        });
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
            if (!isNaN(page)) { // Numbered links
                Search.params.p = page;
                Search.search('pagination');
            }
            else if ($(this).parent().hasClass('prev')) { // Previous link
                var currentPage = parseInt($('.pagination li.active span').text());
                Search.params.p = currentPage - 1;
                Search.search('pagination');
            }
            else if ($(this).parent().hasClass('next')) { // Next link
                var currentPage = parseInt($('.pagination li.active span').text());
                Search.params.p = currentPage + 1;
                Search.search('pagination');
            }
            e.preventDefault();
        });
    },

    setCategory: function() {
        // Event on category select is handled in Common
        // Just inject the right category
        var categoryId = Search.getUrlParameter('cat');
        if (categoryId) {
            categoryId = parseInt(categoryId);
            if (!isNaN(categoryId)) {
                var categoryLabel = $("#navbar-search .search-cat-filters li a[data-id='"+categoryId+"']").text();
                if (categoryLabel != '') {
                    $('.category-filter .category-label').text(categoryLabel);
                }
            }
        }
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
        Search.params.cat = $('#search-category').val();
        Search.params.price = $('.search-price-from').val()+'-'+$('.search-price-to').val();

        // Product attributes filters
        // select
        $('select.attribute-search-filter').each(function( index ) {
            if ($(this).val() != '') {
                Search.params[$(this).data('key')] = $(this).val();
            }
        });
        // color
        if ($('#attribute-search-filter-color').data('key')) {
            delete Search.params[$('#attribute-search-filter-color').data('key')];
        }
        $('#attribute-search-filter-color div.active').each(function( index ) {
            if (!Search.params[$(this).parent().data('key')]) {
                Search.params[$(this).parent().data('key')] = $(this).data('color');
            }
            else {
                Search.params[$(this).parent().data('key')] += ','+$(this).data('color');
            }
        });
        // input text and number
        $('input.attribute-search-filter').each(function( index ) {
            Search.params[$(this).data('key')] = $(this).val();
        });
        // multiselect
        var multiselects = [];
        $('.attribute-search-filter-multiselect').each(function(index) {
            multiselects.push($(this).data('key'));
        });
        $(multiselects).each(function(index, key) {
            delete Search.params[key];
            $("#attribute-search-filter-"+key+" label input[type='checkbox']").each(function( index ) {
                if (($(this).is(':checked'))) {
                    if (!Search.params[key]) {
                        Search.params[key] = $(this).val();
                    }
                    else {
                        Search.params[key] += ','+$(this).val();
                    }
                }
            });
        });
        // End of product attributes filters

        Search.params.sort = $('.sort_by_value').val();
        Search.params.ord = $('.ord_value').val();
        window.history.pushState(Search.params, 'Recherche', Routing.generate('search')+'?'+$.param(Search.params));
    },

    search: function(event) {
        Search.collectParams(event);
        $('.category-products-container').hide();
        $('.category-products-loading').show();
        $.ajax({
            url: Routing.generate('ajax_search'),
            type: 'POST',
            data: Search.params,
            success: function(result) {
                $('.category-products-container').html(result);
                Search.initPagination();
                Search.updateBreadcrumb();
                $('.category-products-loading').hide();
                $('.category-products-container').show();
            },
            error: function(result) {
            }
        });
    },

    updateBreadcrumb: function() {
        $('h1').hide();
        $('.breadcrumbs').hide();
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