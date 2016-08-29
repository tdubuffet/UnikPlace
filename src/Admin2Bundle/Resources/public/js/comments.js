/**
 * Created by francois on 29/08/16.
 */
/**
 * Created by francois on 23/08/16.
 */

var Comments = {
    init: function () {
        $('#textEdit').on('shown.bs.modal', function (event) {
            $("textarea[name='text']").val("");
            $("input[name='id']").val("");
            Comments.button = $(event.relatedTarget);
            Comments.getComment();
        });

        $(".save-btn").click(function () {
            Comments.saveComment();
        });

        $(".btn.btn-success").click(function () {
            var button = $(this);
            bootbox.confirm({
                buttons: {
                    confirm: {
                        label: 'Accepter'
                    },
                    cancel: {
                        label: 'Annuler'
                    }
                },
                message: 'Cette action est irréversible.',
                callback: function (result) {
                    if (result) {
                        Comments.validateComment(button.data('id'));
                    }
                },
                title: "Voulez-vous accepter cet élément ?"
            });
        })

    },

    getComment: function () {
        $.ajax({
            url: Routing.generate('ad2_comment_ajax'),
            type: 'POST',
            data: {'comment_id': Comments.button.data('id'), 'action': 'get'},
            success: function (result) {
                var comment = result['comment'];
                $("textarea[name='text']").val(comment.message);
                $("input[name='id']").val(comment.id);
            },
            error: function (result) {
                if (result.status == 401) {
                    window.location.href = Routing.generate('fos_user_security_login');
                }
            }
        });
    },

    saveComment: function () {
        var id = $("input[name='id']").val();
        $.ajax({
            url: Routing.generate('ad2_comment_ajax'),
            type: 'POST',
            data: {'comment_id': id, 'action': 'save', 'comment_message' : $("textarea[name='text']").val()},
            success: function (result) {
                var comment = result['comment'];
                var child = $(Comments.button.parent('td')[0]).children('span')[0];
                $(child).html(comment.message);
                $('h1').after('<p class="text-success">Commentaire modifié</p>');

            },
            error: function (result) {
                if (result.status == 401) {
                    window.location.href = Routing.generate('fos_user_security_login');
                }
            }
        });
    },

    validateComment: function (id) {
        $.ajax({
            url: Routing.generate('ad2_comment_ajax'),
            type: 'POST',
            data: {'comment_id': id, 'action': 'validate'},
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
    Comments.init();
});