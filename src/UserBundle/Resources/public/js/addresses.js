/**
 * Created by francois on 09/08/16.
 */
var Addresses = {
    init: function () {
        $(".btn.btn-danger.btn-xs").click(function () {
            var button = $(this);
            var formated = $(this).data('name');
            bootbox.confirm({
                buttons: {
                    confirm: {
                        label: 'Oui'
                    },
                    cancel: {
                        label: 'Non'
                    }
                },
                message: 'Confirmez vous la suppression de l\'addresse suivante : <br> ' + formated,
                callback: function (result) {
                    if (result) {
                        Addresses.removeAddress(button.data('id'), button);
                    }
                },
                title: "Suppression d'une addresse"
            });
        });
    },


    removeAddress: function (id, button) {
        $.ajax({
            url: Routing.generate('ajax_user_addresses'),
            type: 'POST',
            data: {address_id: id, action: 'remove'},
            success: function (result) {
                $('h2.title-h2').after("<div class='alert alert-success'>L'adresse a bien été supprimée</div>");
                $(button.parents("tr")[0]).remove();
                var table = $('.table-addresses');
                if ($(".table-addresses tbody").children("tr").length == 0) {
                    table.after('<p>Vous n\'avez plus aucune adresse associée à votre compte pour le moment</p>');
                    table.remove();
                }
            },
            error: function (result) {
                if (result.status == 401) {
                    window.location.href = Routing.generate('fos_user_security_login');
                }
            }
        });

    }
};