var ProductEdition = {

    init: function() {
        $(".select-select2").select2({"placeholder": ""});
        ProductEdition.fileUpload();
        ProductEdition.validateForm();
    },

    validateForm: function() {
        var picErrorMessage = "Une photo du produit est obligatoire";
        $('form[name="product"]').validate({
            ignore: [],
            errorPlacement: function(error, element) {
                if(element.hasClass('select-select2')) {
                    error.insertAfter($(element).parent().find(".select2-container"));
                } else if (element.hasClass('attribute-color')) {
                    error.insertAfter(".box-color-choice");
                } else {
                    error.insertAfter(element);
                }
            },
            highlight: function(element, errorClass) {
                if ($(element).hasClass('select-select2')) {
                    $(element).parent().find('.select2').addClass(errorClass);
                } else {
                    $(element).addClass(errorClass);
                }
            },
            unhighlight: function(element, errorClass) {
                if ($(element).hasClass('select-select2')) {
                    $(element).parent().find('.select2').removeClass(errorClass);
                } else {
                    $(element).removeClass(errorClass);
                }
            },
            rules: {
                image0: {
                    require_from_group: [1, ".upload-pic-id"]
                },
                image1: {
                    require_from_group: [1, ".upload-pic-id"]
                },
                image2: {
                    require_from_group: [1, ".upload-pic-id"]
                },
                image3: {
                    require_from_group: [1, ".upload-pic-id"]
                },
                image4: {
                    require_from_group: [1, ".upload-pic-id"]
                }
            },
            messages: {
                image0: {
                    require_from_group: picErrorMessage
                },
                image1: {
                    require_from_group: picErrorMessage
                },
                image2: {
                    require_from_group: picErrorMessage
                },
                image3: {
                    require_from_group: picErrorMessage
                },
                image4: {
                    require_from_group: picErrorMessage
                }
            }
        });
    },

    fileUpload: function () {
        $('#fileupload').fileupload({
            url: Routing.generate('upload_picture'),
            type: 'POST',
            dataType: 'json',
            previewMaxWidth: 550,
            previewMaxHeight: 413,
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
                data.files[0].uploadId = ProductEdition.uniqid();
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

                    uploadPicPreview.append('<img class="upload-pic-img" src="'+jpegUrl+'" title="Glissez-déposez les photos pour en changer l\'ordre" />');
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
                ProductEdition.reorderUploadInput();
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

    reorderUploadInput: function () {
        $('.upload-pic').each(function(k, v) {
            var numpic = k;
            $(this).find('.upload-pic-id').attr('name', 'image' + numpic);
        });
    }

};