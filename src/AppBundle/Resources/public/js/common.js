var Common = {

    init: function() {
        // this is for global javascripts functionnalities

        jQuery.extend(jQuery.validator.messages, {
            required: "Ce champ est obligatoire.",
            remote: "Veuillez corriger ce champ.",
            email: "Veuillez fournir une adresse électronique valide.",
            url: "Veuillez fournir une adresse URL valide.",
            date: "Veuillez fournir une date valide.",
            dateISO: "Veuillez fournir une date valide (ISO).",
            number: "Veuillez fournir un nombre valide.",
            digits: "Veuillez fournir seulement des chiffres.",
            creditcard: "Veuillez fournir un numéro de carte de crédit valide.",
            equalTo: "Veuillez fournir encore la meme valeur.",
            accept: "Veuillez fournir une valeur avec une extension valide.",
            maxlength: $.validator.format("Veuillez fournir au plus {0} caractères."),
            minlength: $.validator.format("Veuillez fournir au moins {0} caractères."),
            rangelength: $.validator.format("Veuillez fournir une valeur qui contient entre {0} et {1} caractères."),
            range: $.validator.format("Veuillez fournir une valeur entre {0} et {1}."),
            max: $.validator.format("Veuillez fournir une valeur inférieur ou égal à {0}."),
            min: $.validator.format("Veuillez fournir une valeur supérieur ou égal à {0}."),
        });

        $('#top-nav-wrapper').sticky({ topSpacing: 0 });
        $('#mobile-sticky').sticky({ topSpacing: 0 });

        $('.search-cat-filters > li').click(function(){
            var searchCatFilter = $(this).find('a').text();
            var searchCatId = $(this).find('a').data('id');

            $('.category-filter .category-label').text(searchCatFilter);
            $('.search-category').val(searchCatId);

            if (typeof Search !== 'undefined') {
                if (Search.initialized) {
                    Search.search();
                }
            }
        });

        Common.mobileSkipLink();
    },

    mobileSkipLink: function(e){
        var skipContents = jQuery('.skip-content');
        var skipLinks = jQuery('.skip-link');
        var self = jQuery(e);
        var target = self.attr('data-target-element');
        // Get target element
        var elem = jQuery(target);
        // Check if stub is open
        var isSkipContentOpen = elem.hasClass('skip-active') ? 1 : 0;
        // Hide all stubs
        skipLinks.removeClass('skip-active');
        skipContents.removeClass('skip-active');
        self.removeClass('skip-active');
        // Toggle stubs
        if (isSkipContentOpen) {
            self.removeClass('skip-active');
        } else {
            self.addClass('skip-active');
            elem.addClass('skip-active');
        }
    }

};