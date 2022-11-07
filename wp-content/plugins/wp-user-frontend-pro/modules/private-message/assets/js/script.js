(function($) {
    window.wpufpopup = {
        init: function() {
            $(document).on('click', '.mailbox button.new-message', this.openModal);
            $(document).on( 'click', '.wpuf-create-new-message-icon, .wpuf-select-different-user', this.openModal);
            $('.wpuf-form-template-modal-backdrop, .wpuf-form-template-modal .close').on('click', $.proxy(this.closeModal, this) );

            $('.content-container').on('keyup', '.user-search input', this.userSearch);

            $('body').on( 'keydown', $.proxy(this.onEscapeKey, this) );

            $('.pm-user-list li').slice(0, 6).addClass('flex');
            $('.wpuf-user-list-load-more').on('click', this.loadMoreUser);
        },

        openModal: function(e) {
            e.preventDefault();

            $('.wpuf-form-template-modal').show();
            $('.wpuf-form-template-modal-backdrop').show();
        },

        onEscapeKey: function(e) {
            if ( 27 === e.keyCode ) {
                this.closeModal(e);
            }
        },

        closeModal: function(e) {
            if ( typeof e !== 'undefined' ) {
                e.preventDefault();
            }

            $('.wpuf-form-template-modal').hide();
            $('.wpuf-form-template-modal-backdrop').hide();
        },

        userSearch: function function_name() {
            self = $(this);
            var data = {
                action: 'wpuf_pm_fetch_users',
                s: self.val(),
            };

            $.post( wpuf_frontend.ajaxurl, data, function( res ) {
                if ( res.success ) {
                    $('.pm-user-list' ).html( res.data.list );
                }
            });
        },

        loadMoreUser: function(e) {
            e.preventDefault();
            var userContainer = $('.pm-user-list li:hidden');

            if ( 0 == userContainer.length) {
                $(this).hide();
                $('.no-more-users').show();
            }

            userContainer.slice(0, 6).addClass('flex');   
        }
    };

    $(function() {
        wpufpopup.init();
    });

})(jQuery);