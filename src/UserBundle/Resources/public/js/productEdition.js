var ProductEdition = {

    init: function() {
        $(".select-select2").select2({"placeholder": ""});
        ProductEdition.fileUpload();
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