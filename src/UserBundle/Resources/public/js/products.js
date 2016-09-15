/**
 * Created by francois on 26/07/16.
 */
var Products = {
    init: function () {
        Products.confirmDelete();
        Products.updatePrice();

    },

    confirmDelete: function () {
        $('#deleteProductModal').on('shown.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var name = button.data("name");
            var confirmButton = $("#deleteProductModalConfirm");
            confirmButton.data("id", button.data("id"));
            confirmButton.prop('disabled', false);
            $("#deleteProductModalTitle").html("Annonce " + name);
            $("#deleteProductModalBody").html("Confirmez-vous la suppression de votre annonce " + name + " ?");
        });

        $("#deleteProductModalConfirm").click(function () {
            $(this).prop('disabled', true);
            Products.id = $(this).data("id");

            $.ajax({
                url: Routing.generate('ajax_product_action'),
                type: 'POST',
                data: {'product_id': Products.id, action: 'remove'},
                success: function (result) {
                    $("#row"+Products.id).remove();
                    $('#deleteProductModal').modal('hide');
                    $('.page-title').after("<div class='alert alert-success'>Le produit a bien été supprimé</div>");
                },
                error: function (result) {
                    if (result.status == 401) {
                        window.location.href = Routing.generate('fos_user_security_login');
                    }
                }
            });
        });
    },

    updatePrice: function () {
        $('#updateProductModal').on('shown.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var confirmButton = $("#updateProductModalConfirm");
            var name = button.data("name");
            confirmButton.prop('disabled', false);
            confirmButton.data("id", button.data("id"));
            $("#updateProductModalTitle").html("Annonce " + name);
            $("input[name='price']").val(button.data('price'));
        });

        $("#updateProductModalConfirm").click(function () {
            $(this).prop('disabled', true);
            Products.id = $(this).data("id");
            var price = $("input[name='price']").val();

            $.ajax({
                url: Routing.generate('ajax_product_action'),
                type: 'POST',
                data: {'product_id': Products.id, action: 'update', field: 'price', price : price},
                success: function (result) {
                    if (result['price']) {
                        $("#price"+Products.id).html(result['price']);
                        $('#updateProductModal').modal('hide');
                        $('.page-title').after("<div class='alert alert-success'>Le prix du produit a bien été modifié</div>");
                    }
                },
                error: function (result) {
                    if (result.status == 401) {
                        window.location.href = Routing.generate('fos_user_security_login');
                    }else if(result.status == 410) {
                        $("#updateProductModalConfirm").prop('disabled', false);
                        $("input[name='price']").after("<p class='text-danger'>Le nouveau prix entré n'est pas valide</p>");
                    }
                }
            });
        })

    }
};