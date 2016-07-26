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
            $("#deleteProductModalConfirm").data("id", button.data("id"));
            $("#deleteProductModalTitle").html("Annonce " + name);
            $("#deleteProductModalBody").html("Confirmez-vous la suppression de votre annonce " + name + " ?");
        });

        $("#deleteProductModalConfirm").click(function () {
            var id = $("#deleteProductModalConfirm").data("id");

            $.ajax({
                url: Routing.generate('ajax_product_action'),
                type: 'POST',
                data: {'product_id': id, action: 'remove'},
                success: function (result) {
                    window.location.reload();
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
            var name = button.data("name");
            $("#updateProductModalConfirm").data("id", button.data("id"));
            $("#updateProductModalTitle").html("Annonce " + name);
            $("input[name='price']").val(button.data('price'));
        });

        $("#updateProductModalConfirm").click(function () {
            var id = $(this).data("id");
            var price = $("input[name='price']").val();

            $.ajax({
                url: Routing.generate('ajax_product_action'),
                type: 'POST',
                data: {'product_id': id, action: 'update', field: 'price', price : price},
                success: function (result) {
                    window.location.reload();
                },
                error: function (result) {
                    if (result.status == 401) {
                        window.location.href = Routing.generate('fos_user_security_login');
                    }
                }
            });
        })

    }
};