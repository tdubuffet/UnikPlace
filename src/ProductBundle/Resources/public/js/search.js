var Search = {

    params: {},
    initialized: false,

    init: function() {
        Search.filterCollapsing();
        Search.loadFilters();
        Search.initSortBy();
        Search.initSortDirection();
        Search.initLimiter();
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
                $('.sidebar .block-layered-nav .block-content-filters').html(result);
                Search.initPrice();
                Search.initRange();
                Search.initCounty();
                // Reinject values
                Search.reinjectValuesInFilters();
                // Bind events on these filters
                $('select.attribute-search-filter').change(function() {
                    Search.search();
                });

                $('.attribute-search-filter-multiselect2').change(function() {
                    Search.search();
                });

                $('.attribute-search-filter-color div').click(function() {
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

    initCounty : function () {
        $.ajax({
            url: Routing.generate('ajax_search_county'),
            type: 'GET',
            data: [],
            success: function(result) {
                var select = $(".search-county");
                result = result['counties'];
                var options = "<option value=''>Toute la France</option>";
                var county = Search.getUrlParameter('county');
                $.each(result, function (key, value) {
                    var selected = county == value['id'] ? "selected" : "";
                    options+= "<option "+selected+" value='"+value['id']+"'>"+value['name']+"</option>";
                });
                select.html(options);
                select = select.select2();

                select.change(function() {
                    Search.search();
                });

            },
            error: function(result) {
            }
        });



    },

    reinjectValuesInFilters: function() {
        $('.attribute-search-filter').each(function() {
            var key = $(this).data('key');
            var value = Search.getUrlParameter(key);
            if (key && value) {
                // Inputs and select
                if ($(this).prop('tagName').toLowerCase() == 'input' || 
                    $(this).prop('tagName').toLowerCase() == 'select') {
                    if ($(this).hasClass('attribute-search-filter-multiselect2')) {
                        var values = value.split(',');
                        var element = $(this);
                        $(values).each(function(index, value) {
                            element.find('option[value="' + value + '"]').prop('selected', true);
                            element.trigger('change.select2');
                        });
                    } else {
                        $(this).val(value);
                    }
                }
                // Multiselect and colors
                else if ($(this).prop('tagName').toLowerCase() == 'div') {

                    var values = value.split(',');
                    var element = $(this);

                    $(values).each(function(index, value) {
                        if ($(this).hasClass('attribute-search-filter-multiselect')) {
                            element.find('input[value="' + value + '"]').prop('checked', true);
                        }
                        else if ($(this).hasClass('attribute-search-filter-color')) {
                            element.find(".attribute-search-filter-color-block[data-color='"+value+"']").addClass('active');
                        }
                    });
                }
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

    initLimiter: function() {
        $('.overwrite-limiter-value').text($('.limiter_limit_value').val());
        $('#limiter button').click(function() {
            $('.overwrite-limiter-value').text($(this).text());
            $('.limiter_limit_value').val($(this).data('limit'));
            $('#limiter').collapse('hide');
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
            var priceElems = price.split('-');
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

    initRange: function () {
        $(".attribute-search-filter-range-from").each(function () {
            var key = $(this).data('key');
            var range = Search.getUrlParameter(key);
            if (range) {
                var rangeElems = range.split('-');
                if (rangeElems.length == 2) {
                    var from = parseInt(rangeElems[0]);
                    if (!isNaN(from)) {
                        $('#attribute-search-filter-from-'+key).val(from);
                    }
                    var to = parseInt(rangeElems[1]);
                    if (!isNaN(to)) {
                        $('#attribute-search-filter-to-'+key).val(to);
                    }
                }
            }
        });


        $('.search-range-submit').click(function() {
            Search.search();
        });
    },

    collectParams: function(event) {
        // Reset pagination if the trigger is not pagination
        if (event != 'pagination') {
            Search.params.p = 1;
        }

        // Reset search query bar
        $('#search').val('');

        Search.params.cat = $('#search-category').val();
        Search.params.price = $('.search-price-from').val()+'-'+$('.search-price-to').val();
        Search.params.county = $(".search-county").val();

        // Product attributes filters
        //range
        $(".attribute-search-filter-range-from").each(function () {
            var to = $('#attribute-search-filter-to-'+$(this).data('key'));
            Search.params[$(this).data('key')] = $(this).val()+ "-" + to.val();
        });

        // select
        $('select.attribute-search-filter').each(function( index ) {
            if ($(this).val() != '') {
                Search.params[$(this).data('key')] = $(this).val();
            }else {
                delete Search.params[$(this).data('key')];
            }
        });
        // color
        if ($('.attribute-search-filter-color').data('key')) {
            delete Search.params[$('.attribute-search-filter-color').data('key')];
        }
        $('.attribute-search-filter-color div.active').each(function( index ) {
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


        // multiselect2
        var multiselects2 = [];
        $('.attribute-search-filter-multiselect2').each(function(index) {
            multiselects2.push($(this).data('key'));
        });

        $(multiselects2).each(function(index, key) {
            delete Search.params[key];
            var vals = $("#attribute-search-filter-multiselect2-"+key).select2("val");


            $(vals).each(function( index, value) {
                if (!Search.params[key]) {
                    Search.params[key] = value;
                } else {
                    Search.params[key] += ','+ value;
                }
            });
        });

        // End of product attributes filters

        Search.params.sort = $('.sort_by_value').val();
        Search.params.ord = $('.ord_value').val();

        Search.params.limit = $('.limiter_limit_value').val();

        $.each(Search.params, function (key, value) {
            if ((value && value.length < 1) || (value && value.length == 1 && value == "-")) {
                delete Search.params[key];
            }
        });

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
                // Search.updateUrlParameters();
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
    },

    filterCollapsing: function () {
        var collapse = $('#filtersCollapse');
        collapse.on('shown.bs.collapse', function () {
            $('.collapseTitle').html("Cacher les filtres");
        });
        collapse.on('hide.bs.collapse', function () {
            $('.collapseTitle').html("Afficher les filtres");
        });
    }

};