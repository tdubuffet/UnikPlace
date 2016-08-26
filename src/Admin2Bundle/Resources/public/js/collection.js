/**
 * Created by francois on 25/08/16.
 */
var Collection = {
    init:function () {
        var selectCat = $("#collection_form_categories");
        selectCat.select2();
        $("#collection_form_products").select2();
        if (selectCat.val()) {
            Collection.updateProducts();
        }
        Collection.categoriesChange();
    },

    categoriesChange: function () {
        $("#collection_form_categories").change(function () {
            Collection.updateProducts();
        });
    },

    updateProducts: function () {
        $.ajax({
            url: Routing.generate('ad2_collections_products'),
            type: 'POST',
            data: $('form[name="collection_form"]').serializeArray(),
            success: function (result) {
                var options = '';
                $.each(result['products'], function (key, val) {
                    options+= "<option value='"+val['value']+"'>"+val['text']+"</option>";
                });

                var selectProducts = $("#collection_form_products");
                selectProducts.html(options).trigger('change');
                selectProducts.val(result['list']).trigger('change');
            },
            error: function (result) {
                if (result.status == 401) {
                    window.location.href = Routing.generate('fos_user_security_login');
                }
            }
        });

    }
};

$(document).ready(function() {
    Collection.init();
});
