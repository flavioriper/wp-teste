(function($){

    var WPUF_Comments = {

        init: function() {
            $('#wpuf-comments-table').on('click', '.wpuf-cmt-action', this.setCommentStatus);
            $('#wpuf-comments-table').on('click', 'button.wpuf-cmt-close-form', this.closeForm);
            $('#wpuf-comments-table').on('click', 'button.wpuf-cmt-submit-form', this.submitForm);
            $('#wpuf-comments-table').on('click', '.wpuf-cmt-edit', this.populateForm);
            $('#wpuf-check-all').on('click', this.toggleCheckbox);
        },

        toggleCheckbox: function() {
            $(".wpuf-check-col").prop('checked', $(this).prop('checked'));
        },

        setCommentStatus: function(e) {
            e.preventDefault();
            // console.log($(this));
            var self = $(this),
                comment_id = self.data('comment_id'),
                comment_status = self.data('cmt_status'),
				page_status = self.data('page_status'),
				post_type = self.data('post_type'),
				curr_page = self.data('curr_page'),
                tr = self.closest('tr'),
                data = {
                    'action': 'wpuf_comment_status',
                    'comment_id': comment_id,
                    'comment_status': comment_status,
					'page_status': page_status,
					'post_type': post_type,
					'curr_page': curr_page,
					'nonce': wpufComment.nonce
                };

            $.post(wpufComment.ajaxurl, data, function(resp){

                if(page_status === 1) {
                    if ( comment_status === 1 || comment_status === 0) {
                        tr.fadeOut(function() {
                            tr.replaceWith(resp.data['content']).fadeIn();
                        });

                    } else {
                        tr.fadeOut(function() {
                            $(this).remove();
                        });
                    }
                } else {
                    tr.fadeOut(function() {
                        $(this).remove();
                    });
                }

                if(resp.data['pending'] == null) resp.data['pending'] = 0;
                if(resp.data['spam'] == null) resp.data['spam'] = 0;
				if(resp.data['trash'] == null) resp.data['trash'] = 0;

                $('.comments-menu-pending').text(resp.data['pending']);
                $('.comments-menu-spam').text(resp.data['spam']);
				$('.comments-menu-trash').text(resp.data['trash']);
            });
        },

        populateForm: function(e) {
            e.preventDefault();

            var tr = $(this).closest('tr');

            // toggle the edit area
            if ( tr.next().hasClass('wpuf-comment-edit-row')) {
                tr.next().remove();
                return;
            }

            var table_form = $('#wpuf-edit-comment-row').html(),
                data = {
                    'author': tr.find('.wpuf-cmt-hid-author').text(),
                    'email': tr.find('.wpuf-cmt-hid-email').text(),
                    'url': tr.find('.wpuf-cmt-hid-url').text(),
                    'body': tr.find('.wpuf-cmt-hid-body').text(),
                    'id': tr.find('.wpuf-cmt-hid-id').text(),
                    'status': tr.find('.wpuf-cmt-hid-status').text(),
                };

            tr.after( _.template(table_form, data) );
        },

        closeForm: function(e) {
            e.preventDefault();

            $(this).closest('tr.wpuf-comment-edit-row').remove();
        },

        submitForm: function(e) {
            e.preventDefault();

            var self = $(this),
                parent = self.closest('tr.wpuf-comment-edit-row'),
                data = {
                    'action': 'wpuf_update_comment',
                    'comment_id': parent.find('input.wpuf-cmt-id').val(),
                    'content': parent.find('textarea.wpuf-cmt-body').val(),
                    'author': parent.find('input.wpuf-cmt-author').val(),
                    'email': parent.find('input.wpuf-cmt-author-email').val(),
                    'url': parent.find('input.wpuf-cmt-author-url').val(),
                    'status': parent.find('input.wpuf-cmt-status').val(),
					'nonce': wpufComment.nonce,
					'post_type' : parent.find('input.wpuf-cmt-post-type').val(),
                };

            $.post(wpufComment.ajaxurl, data, function(res) {
                if ( res.success === true) {
                    parent.prev().replaceWith(res.data);
                    parent.remove();
                } else {
                    alert( res.data );
                }
            });
        }
    };

    $(function(){

        WPUF_Comments.init();
    });

})(jQuery);