(function ($) {
    if (!$('#wpuf-private-message').length) {
        return;
    }

    var RouterView = {
        template: '<router-view></router-view>'
    };

    var IndexPage = {
        template: '#tmpl-wpuf-private-message-index',

        data: function () {
            return {
                searching: false,
                search: '',
                messages: [],
                loading: false,
            }
        },

        created: function () {
            var vm = this;
            this.loading = true;
            $.ajax({
                url: wpufPM.ajaxurl,
                method: 'get',
                type: 'json',
                data: {
                    action: 'wpuf_pm_route_data_index'
                }
            }).done(function (response) {
                if (response.data && response.data.messages) {
                    vm.messages = response.data.messages;
                    vm.loading = false;
                }
            });
        },
        filters: {
            strLimit(string, size) {
                if (string.length <= size) {
                    return string
                }

                return string.substr(0, size) + ' ....'
            }
        },
        methods: {
            searchMessage: function () {
                var vm = this;

                vm.searching = true;

                $.ajax({
                    url: wpufPM.ajaxurl,
                    method: 'get',
                    type: 'json',
                    data: {
                        action: 'wpuf_pm_message_search',
                        content: this.search,
                    }
                }).done(function (response) {
                    if (response.data && response.data.messages) {
                        vm.messages = response.data.messages;
                    }

                    vm.searching = false;
                });
            },
        }
    };

    // Vue.component('wpuf-pm-index-page', indexPage);

    var SinglePage = {
        template: '#tmpl-wpuf-private-message-single',

        data: function () {
            return {
                messages: [],
                attachments: [],
                previewLists: [],
                message: '',
                chat_with: '',
                file: '',
                loading: false,
                isSent: false,
            }
        },

        created: function () {
            this.selectUser()
        },

        watch: {
            $route(to, from) {
              // react to route changes...
              this.selectUser()
            }
        },


        computed: {
            userId: function () {
                return this.$route.params.id;
            }
        },

        methods: {
            sendMessage: function () {

                if ( ! this.message.length && ! this.attachments.length ) {
                    return
                }

                var vm = this;
                var data = new FormData();

                data.append('message', this.message);
                data.append('action', 'wpuf_pm_message_send');
                data.append('user_id', this.$route.params.id);

                for(var index = 0; index < this.attachments.length; index++) {
                    data.append('files['+ index +']', this.attachments[index]);
                }

                this.isSent = true
                $.ajax({
                    url: wpufPM.ajaxurl,
                    method: 'post',
                    dataType: 'json',
                    data: data,
                    processData: false,
                    contentType: false,

                }).done(function (response) {
                    if (response.data) {
                        vm.messages.push( response.data )
                        vm.isSent = false
                    }
                });
                this.message     = ''
                this.previewLists = []
                this.attachments = []

            },
            deleteSingleMessage: function (messageIndex) {
                if ( confirm( 'Are you sure want to delete this ?' ) ) {
                    var conversation = this.messages[messageIndex];
                    this.messages.splice( messageIndex, 1 );

                    $.ajax({
                        url: wpufPM.ajaxurl,
                        method: 'get',
                        type: 'json',
                        data: {
                            action: 'wpuf_pm_delete_single_message',
                            id: conversation.message_id,
                        }
                    }).done(function (response) {

                    });
                }
            },
            deleteMessage: function () {

                if ( confirm( 'Are you sure want to delete this conversation ?' ) ) {
                    var vm = this;
                    this.messages = [];

                    $.ajax({
                        url: wpufPM.ajaxurl,
                        method: 'get',
                        type: 'json',
                        data: {
                            action: 'wpuf_pm_delete_message',
                            id: this.userId,
                        }
                    }).done(function (response) {

                    });
                }

            },
            uploadFile(e) {
                var files = e.target.files;
                this.file = e.target.files[0];

                for (var i = 0; i < files.length; i++) {
                    var reader = new FileReader();
                    var type = files[i]['type'];

                    reader.onload = (e) => {
                        var file = {
                            src: e.target.result,
                            type: type
                        }
                        this.previewLists.push(file);
                    }
                    this.attachments.push(files[i]);
                    reader.readAsDataURL(files[i]);
                }
            },
            removeFile(index) {
                this.previewLists.splice(index, 1)
                this.attachments.splice(index, 1)
            },
            removeAttachment(conversatioIndex, attachmentIndex) {
                if ( confirm('Are sure want to remove this file ?') ) {
                    var conversation = this.getConversationById(conversatioIndex)
                    var attachments  = conversation.message.files
                    var attachment_id = attachments[attachmentIndex].id
                    attachments.splice(attachmentIndex, 1)

                    $.ajax({
                        url: wpufPM.ajaxurl,
                        method: 'post',
                        type: 'json',
                        data: {
                            action: 'wpuf_pm_remove_attachment',
                            conversation_id: conversation.message_id,
                            attachment_id: attachment_id,

                        },
                        success: function(response) {

                        }
                    })
                }
            },
            getConversationById(conversationId) {
                return this.messages[conversationId];
            },
            isImage( file_name ) {
                if ( ! file_name.match( /.(jpg|jpeg|png|gif)$/i ) ) {
                    return false;
                }

                return true;
            },
            selectUser() {
                var vm = this;
                this.messages = []
                this.chat_with = ''
                this.loading = true
                $.ajax({
                    url: wpufPM.ajaxurl,
                    method: 'get',
                    type: 'json',
                    data: {
                        action: 'wpuf_pm_route_data_message',
                        user_id: this.$route.params.id
                    }
                }).done(function (response) {
                    if (response.data && response.data.messages) {
                        vm.messages = response.data.messages;
                        vm.chat_with = response.data.chat_with;
                        vm.loading = false
                    }
                });
            }
        }
    };

    // Vue.component('wpuf-pm-single-page', singlePage);

    var routes = [
        {
            path: '',
            name: 'wpufPMIndex',
            component: IndexPage
        },
        {
            path: '/user',
            component: RouterView,
            // redirect: {
            //     name: 'wpufPMUIndex'
            // },
            children: [
                {
                    path: ':id',
                    name: 'wpufPMSingle',
                    component: SinglePage
                }
            ]
        }
    ];

    var router = new VueRouter({
        routes: routes
    });

    new Vue({
        el: '#wpuf-private-message',
        router: router,

        watch: {
            '$route': function(to, from) {
                if ( 'wpufPMSingle' == to.name ) {
                    wpufpopup.closeModal();
                }
            }
        }
    });
})(jQuery)
