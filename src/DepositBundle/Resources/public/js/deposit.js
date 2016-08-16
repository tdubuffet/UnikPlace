var Deposit = {

    init: function() {
        $("[data-toggle=popover]").popover();
        $('[data-toggle="tooltip"]').tooltip();

        $('#subcategories-select').select2({
            "language": {
                "noResults": function(){
                    return "Aucun résultat trouvé.";
                }
            },
            "placeholder": "Puis sélectionnez une sous-catégorie.",
            "width": "100%"
        });

        // Initialize select as disabled
        $("#subcategories-select").prop("disabled", true);

        Deposit.loadSubCategories();

        Deposit.submitCategoryForm();
        Deposit.submitPhotosForm();

        Deposit.fileUpload();

        Deposit.loadDescriptionValidation();

        Deposit.loadPriceStep();

        Deposit.loadShippingValidation();
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
                            var optionsHtml = '<option value=""></option>';
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

        $('#subcategories-select').on('change', function() {
            $("#sub-categ-error").hide();
        });
    },

    fileUpload: function () {
        $('#fileupload').fileupload({
            url: Routing.generate('upload_picture'),
            type: 'POST',
            dataType: 'json',
            previewMaxWidth: 550,
            previewMaxHeight: 413,
            previewCrop: true,
            singleFileUploads: true,
            done: function (e, data) {
                if (data.result.pic) {
                    var uploadPicPreview = $('.upload-pic[data-upload-id='+data.files[0].uploadId+']');
                    if (uploadPicPreview.length > 0) {
                        uploadPicPreview.find('.upload-pic-id').val(data.result.pic.id);
                    }
                    $('#minimum-photo-error').hide();
                }
            }
        })
        .on('fileuploadadd', function (e, data) {
            data.files[0].uploadId = Deposit.uniqid();
        })
        .on('fileuploaddone', function (e, data) {
            if (!data.result.pic.id) {
                $('#upload-alert-danger').show();
            }
        })
        .on('fileuploadprocessalways', function (e, data) {
            var file = data.files[0];
            var uploadErrors = 0;
            var acceptFileTypes = /^image\/(jpe?g|gif|png)$/i;

            if(typeof file['type'] !== "undefined" && !acceptFileTypes.test(file['type'])) {
                uploadErrors++;
            }
            if(typeof file['size'] !== "undefined" && file['size'] > 5242880) {
                uploadErrors++;
            }
            if (file.preview && uploadErrors <= 0) {
                var uploadPicPreview = $('.upload-pic-preview:not(:has(.upload-pic-img))').first();

                uploadPicPreview.siblings('.upload-pic-input').hide();
                uploadPicPreview.show();

                var jpegUrl = file.preview.toDataURL("image/jpeg");

                uploadPicPreview.append('<img class="upload-pic-img" src="'+jpegUrl+'" title="Glissez-déposez les photos pour en changer l\'ordre" width="100%" />');
                uploadPicPreview.parent('.upload-pic').attr('data-upload-id', data.files[0].uploadId);
                uploadPicPreview.find('.progress').show();
            }
        })
        .on('fileuploadprogress', function (e, data) {
            var uploadPic = $('.upload-pic[data-upload-id='+data.files[0].uploadId+']');
            var progressBar = uploadPic.find('.progress .progress-bar');
            progressBar.css('width', '0%');
            var progress = parseInt(data.loaded / data.total * 100, 10);
            progressBar.css('width', progress + '%');
            if(progress >= 100) {
                progressBar.parent('.progress').fadeOut().promise().done(function(){
                    progressBar.css('width', '0%');
                });
            }
        })
        .on('fileuploadfail', function (e, data) {
            $('#upload-alert-danger').show();
        })
        ;

        $('.upload-pic-input').on('click', function () {
            $('#upload-alert-danger').hide();
            $('#fileupload').trigger('click');
            return false;
        });

        $('.upload-pic-delete').on('click', function () {
            var uploadPic = $(this).parents('.upload-pic');
            uploadPic.removeAttr('data-upload-id');
            uploadPic.find('.upload-pic-img').remove();
            uploadPic.find('.upload-pic-id').val('');
            uploadPic.find('.upload-pic-preview').hide();
            uploadPic.find('.upload-pic-input').show();
        });

        if ($('.box-upload-pictures').length > 0) {
            sortable('.box-upload-pictures', {
                handle: '.upload-pic',
                placeholderClass: 'sortable-placeholder col-xs-6 col-md-3',
                forcePlaceholderSize: true,
                handle: '.upload-pic-img'
            })[0].addEventListener('sortupdate', function(e) {
                Deposit.reorderUploadInput();
            });
        }
    },

    uniqid: function () {
        var ts=String(new Date().getTime()), i = 0, out = '';
        for(i=0;i<ts.length;i+=2) {
           out+=Number(ts.substr(i, 2)).toString(36);
        }
        return ('d' + Math.round(Math.random()*10) + 'k' +out);
    },

    submitCategoryForm: function () {
        $('#category-form').submit(function(e) {
            var result = true;

            if($(".category-choice input:checked").length <= 0) {
                result = false;
                $("#main-categ-error").show();
            }

            if($("#subcategories-select").val() === '') {
                result = false;
                $("#sub-categ-error").show();
            }

            return result;
        });
    },

    submitPhotosForm: function () {
        $('#photos-form').submit(function(e) {
            var result = false;

            var filledInputs = $('.upload-pic-id').filter(function(){
                return $(this).val();
            }).length;

            if (filledInputs >= 1) {
                result = true;
            } else {
                $('#minimum-photo-error').show();
            }

            return result;
        });
    },

    reorderUploadInput: function () {
        $('.upload-pic').each(function(k, v) {
            var numpic = k + 1;
            $(this).find('.upload-pic-id').attr('name', 'image' + numpic);
        });
    },

    loadDescriptionValidation: function () {
        $('.select-select2').select2({
            "language": {
                "noResults": function(){
                    return "Aucun résultat trouvé.";
                }
            },
            "placeholder": "Sélectionnez une valeur.",
            "width": "100%"
        });

        $('.color-choice input').click(function() {
            $('.color-choice').removeClass('active');
            $(this).parent('.color-choice').addClass('active');
        });

        var descriptionRules = {
            "name": {
                required: true,
                minlength: 3,
                maxlength: 80,
            },
            "description": {
                required: true,
                minlength: 80,
                maxlength: 2000,
            },
        };

        $('.attribute-field').each(function(k, v) {
            var name = $(this).attr('name');
            var rules = {};

            if ($(this).data('required')) {
                rules.required = $(this).data('required');
            }
            if ($(this).data('minlength')) {
                rules.minlength = $(this).data('minlength');
            }
            if ($(this).data('maxlength')) {
                rules.maxlength = $(this).data('maxlength');
            }
            if ($(this).data('digits')) {
                rules.digits = $(this).data('digits');
            }

            if (!$.isEmptyObject(rules)) {
                descriptionRules[name] = rules;
            }
        });

        $("#description-form").validate({
            ignore: [],
            rules: descriptionRules,
            errorPlacement: function(error, element) {
                if(element.hasClass('select-select2')) {
                    error.insertAfter(".select2-container");
                } else if (element.hasClass('attribute-color')) {
                    error.insertAfter(".box-color-choice");
                } else {
                    error.insertAfter(element);
                }
            },
            highlight: function(element, errorClass) {
                if ($(element).hasClass('select-select2')) {
                    $('.select2').addClass(errorClass);
                } else {
                    $(element).addClass(errorClass);
                }
            },
            unhighlight: function(element, errorClass) {
                if ($(element).hasClass('select-select2')) {
                    $('.select2').removeClass(errorClass);
                } else {
                    $(element).removeClass(errorClass);
                }
            }
        });

        // hide select error if it's still displayed
        $("select").on("select2:close", function (e) {
            $(this).valid();
        });
    },

    loadPriceStep: function () {
        if ($('.valued-amount').length > 0) {
            Deposit.updateFinalAmount();
        }

        $('#product-price').blur(function() {
            Deposit.updateFinalAmount();
        });

        $("#price-form").validate({
            rules: {
                "price": {
                    required: true,
                    number: true,
                },
                "original_price": {
                    number: true
                },
            }
        });
    },

    getFeeRate: function() {
        var rates =  $('.valued-amount').data('rates');
        var price = parseFloat($('#product-price').val());
        var rate = 0;
        $(rates).each(function(idx, elem) {
            if (rates[idx+1] && price < rates[idx+1].min && rate == 0) {
                rate = elem.rate;
            }
            else if (!rates[idx+1] && rate == 0) {
                rate = elem.rate;
            }
        });
        return rate;
    },

    updateFinalAmount: function() {
        var amount = $('.valued-amount');
        var rate = Deposit.getFeeRate();
        var valueAmount = Math.round(($('#product-price').val() * (100 - rate) / 100) - amount.data('fee'));
        if (valueAmount <= 0) valueAmount = 0;
        valueAmount = valueAmount.toFixed(2);
        amount.text(valueAmount + ' €');
    },

    loadShippingValidation: function () {

        $("#shipping-form").validate({
            rules: {
                "weight": {
                    required: true,
                    number: true,
                    min: 0.01
                },
                "length": {
                    required: true,
                    number: true,
                },
                "width": {
                    required: true,
                    number: true,
                },
                "height": {
                    required: true,
                    number: true,
                },
                "shipping_fees": {
                    number: true,

                }
            },
            messages: {

                "shipping_fees": {
                    required: "Votre colis ne peut être pris en charge par Colissimo compte tenu de son poids et dimensions. Vous avez la possibilité de choisir votre propre transporteur et de renseigner son tarif de livraison."
                }
            }
        });


        $('#product-length, #product-width, #product-height, #product-weight').blur(function() {

            var dim = parseFloat($('#product-height').val()) + parseFloat($('#product-length').val()) + parseFloat($('#product-width').val());

            if ($('#product-weight').val() >= 30 || dim >= 150) {
                $('input[name="shipping_fees"]').rules("add", "required");
            } else {
                $('input[name="shipping_fees"]').rules("remove", "required");
            }
        });

        $("#add-address-form").validate({
            rules: {
                "name": {
                    required: true,
                    minlength: 3,
                    maxlength: 100,
                },
                "street": {
                    required: true,
                    minlength: 3,
                    maxlength: 250,
                },
                "city": {
                    required: true,
                },
                "phone": {
                    required: true,
                    minlength: 10,
                    maxlength: 20,
                }
            },
            errorPlacement: function(error, element) {
                if(element.hasClass('select-select2')) {
                    error.insertAfter(".select2-container");
                } else {
                    error.insertAfter(element);
                }
            },
            highlight: function(element, errorClass) {
                if ($(element).hasClass('select-select2')) {
                    $('.select2').addClass(errorClass);
                } else {
                    $(element).addClass(errorClass);
                }
            },
            unhighlight: function(element, errorClass) {
                if ($(element).hasClass('select-select2')) {
                    $('.select2').removeClass(errorClass);
                } else {
                    $(element).removeClass(errorClass);
                }
            }
        });
    }

};