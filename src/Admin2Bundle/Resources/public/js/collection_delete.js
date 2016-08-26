/**
 * Created by francois on 26/08/16.
 */
var Collection = {
    init: function () {
        $(".btn.btn-danger").click(function () {
            var button = $(this);
            bootbox.confirm({
                buttons: {
                    confirm: {
                        label: 'Supprimer'
                    },
                    cancel: {
                        label: 'Annuler'
                    }
                },
                message: 'Cette action est irréversible.',
                callback: function (result) {
                    if (result) {
                        Collection.removeCategory(button.data('id'));
                    }
                },
                title: "Voulez-vous supprimer cet élément ?"
            });
        })
    },

    removeCategory: function (id) {
        $.ajax({
            url: Routing.generate('ad2_collection_remove'),
            type: 'POST',
            data: {collection_id: id},
            success: function (result) {
                window.location.reload();
            },
            error: function (result) {
                if (result.status == 401) {
                    window.location.href = Routing.generate('fos_user_security_login');
                }
            }
        });
    }
};

$(document).ready(function () {
    Collection.init();
});
