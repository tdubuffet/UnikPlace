var Deposit = {

    init: function() {
        $('#subcategories-select').select2({
            "language": {
                "noResults": function(){
                    return "Aucun résultat trouvé.";
                }
            },
            "placeholder": "Sélectionnez d'abord une catégorie ci-dessus."
        });

        // Initialize select as disabled
        $("#subcategories-select").prop("disabled", true);

        Deposit.loadSubCategories();

        Deposit.submitForm();
    },

    loadSubCategories: function () {
        $('.category-choice input').click(function() {
            selectedRadio = $(this);
            if (selectedRadio.length > 0) {
                $("#main-categ-error").hide();
                $('.category-choice').removeClass('active');
                selectedRadio.closest('.category-choice').addClass('active');

                // Loading subcategories
                $.ajax({
                    url: Routing.generate('deposit_subcategories'),
                    type: 'POST',
                    data: {category_id: selectedRadio.data('id')},
                    beforeSend: function () {
                        $("#subcategories-select").prop("disabled", true);
                    },
                    success: function(result) {
                        // Add options to select
                        if (result.subcategories != '') {
                            $('#subcategories-select').find('optgroup, option').remove();
                            var optionsHtml = '';
                            $.each(result.subcategories, function(k, subcateg) {
                                if (typeof subcateg.children !== 'undefined') {
                                    optionsHtml += '<optgroup label="'+subcateg.name+'">';
                                    $.each(subcateg.children, function (k, subchildcateg) {
                                        optionsHtml += '<option value="'+subchildcateg.id+'">'+subchildcateg.name+'</option>'
                                    });
                                    optionsHtml += '</optgroup>';
                                } else {
                                    optionsHtml += '<option value="'+subcateg.id+'">'+subcateg.name+'</option>';
                                }
                            });
                            $('#subcategories-select').append(optionsHtml);
                            $("#subcategories-select").prop("disabled", false);

                            // Focus on first option
                            var firstOption = $('#subcategories-select option:first');
                            $('#subcategories-select').val(firstOption.val()).trigger("change");
                        }
                    },
                    error: function(result) {
                        if (result.status == 401) {
                            window.location.href = Routing.generate('fos_user_security_login');
                        }
                    }
                });

            }
        });
    },

    submitForm: function () {
        $('#category-form').submit(function(e) {
            var result = true;

            if($(".category-choice input:checked").length <= 0) {
                result = false;
                $("#main-categ-error").show();
            }

            return result;
        });
    }

};