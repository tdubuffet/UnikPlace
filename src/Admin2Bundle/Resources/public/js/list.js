/**
 * Created by francois on 25/08/16.
 */
var List = {
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
                        List.removeCategory(button.data('id'));
                    }
                },
                title: "Voulez-vous supprimer cet élément ?"
            });
        })
    },

    removeCategory: function (id) {
        $.ajax({
            url: Routing.generate('ad2_categories_remove'),
            type: 'POST',
            data: {category_id: id},
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
    List.init();
});