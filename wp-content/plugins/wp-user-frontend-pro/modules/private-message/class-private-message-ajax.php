<?php
/**
 * Class WPUF_Private_Message_Ajax
 */
class WPUF_Private_Message_Ajax {

    /**
     * Store conversation instance
     *
     * @var object
     */
    private $conversation;

    /**
     * Constructor for the WPUF_Private_Message_Ajax class
     */
    public function __construct() {
        add_action( 'wp_ajax_wpuf_pm_route_data_index', array( $this, 'route_index' ) );
        add_action( 'wp_ajax_wpuf_pm_message_search', array( $this, 'message_search' ) );
        add_action( 'wp_ajax_wpuf_pm_delete_message', array( $this, 'delete_all' ) );
        add_action( 'wp_ajax_wpuf_pm_fetch_users', array( $this, 'fetch_users' ) );

        add_action( 'wp_ajax_wpuf_pm_route_data_message', array( $this, 'personal_message' ) );
        add_action( 'wp_ajax_wpuf_pm_message_send', array( $this, 'message_send' ) );
        add_action( 'wp_ajax_wpuf_pm_delete_single_message', array( $this, 'single_message_delete' ) );
        add_action( 'wp_ajax_wpuf_pm_remove_attachment', [ $this, 'remove_attachment' ] );



        /**
         * Conversaton class
         */
        $this->conversation = new WPUF_Conversation();

    }

    /**
     * Get user conversation
     *
     * @return object
     */
    public function route_index() {
        $data['messages'] = $this->get_messages();

        wp_send_json_success( $data );
    }

    /**
     * Search conversation
     *
     * @return object
     */
    public function message_search() {
        $args = [
            's' => ! empty( $_GET['content'] ) ? $_GET['content'] : ''
        ];

        sleep(0.1);
        $data = [
            'messages' => $this->get_messages( $args )
        ];

        wp_send_json_success( $data );
    }

    /**
     * Get all users
     *
     * @return array
     */
    public function fetch_users() {
        $data = [
            'list' => $this->users_list( $_POST['s'] )
        ];

        wp_send_json_success( $data );
    }

    /**
     * Search users
     *
     * @param string $s
     *
     * @return array
     */
    public function users_list( $s='' ) {
        $users_query = new WP_User_Query( array(
            'search'         => '*'.esc_attr( $s ).'*',
            'search_columns' => array(
                'user_login',
                'user_nicename',
                'user_email',
                'user_url',
            ),
        ) );
        $users = $users_query->get_results();
        ob_start();
        ?>
        <?php
            foreach ($users as $user) {
                $user_info = get_userdata( $user->data->ID );
                $full_name = ( $user_info->first_name && $user_info->last_name ) ? $user_info->first_name . ' ' . $user_info->last_name : '';
        ?>
            <li class="user flex">
                <a
                    href="<?php echo '#/user/'.$user->data->ID; ?>"
                    class="wpuf-private-message-username"
                >
                    <?php echo get_avatar($user->data->ID, 80) ?>
                </a>
                <?php echo $full_name ? ucwords( strtolower( $full_name ) ) : ucwords( strtolower( $user->data->user_login ) ); ?>
            </li>
        <?php }
        return ob_get_clean();
    }

    /**
     * Get all conversation
     *
     * @param array $args
     *
     * @return array
     */
    public function get_messages( $args = [] ) {
        global $wpdb;

        $sql = "SELECT * FROM " . $wpdb->prefix . "wpuf_message WHERE ";
        $sql .= !empty( $args['s'] ) ? "`message` LIKE '%" . $args['s'] . "%' AND " : '';
        $sql .= "((`from` = %d AND `from_del` = 0) OR (`to` = %d AND `to_del` = 0)) ORDER BY `created` DESC";

        $sql = $wpdb->prepare( $sql, get_current_user_id(), get_current_user_id() );

        $results = $wpdb->get_results( $sql );
        $users = array();
        foreach ( $results as $value ) {
            $user_id = get_current_user_id() == $value->from ? $value->to : $value->from;
            if ( !in_array( $user_id, $users ) ) {
                $users[] = $user_id;
            }
        }

        $users = count( $users ) > 10 ? array_slice( $users, 0, 10)  : $users;

        $messages = array();
        foreach ( $users as $user_id) {
            $sql = $wpdb->prepare( "SELECT *
                FROM " . $wpdb->prefix . "wpuf_message
                WHERE ((`from` = %d AND `from_del` = 0) OR (`to` = %d AND `to_del` = 0)) AND (`from` = %d OR `to` = %d) ORDER BY created DESC LIMIT 1", get_current_user_id(), get_current_user_id(), $user_id, $user_id );

            $results = $wpdb->get_results( $sql );

            foreach ($results as $key => $value) {
                $status = 'single';
                $unread = '';
                if ( get_current_user_id() == $value->from ) {
                    $user_id = $value->to;
                } else {
                    $user_id = $value->from;
                    if ( 0 == $value->status ) {
                        $status = 'single unread';
                        $unread = 'Unread';
                    }
                }
                $conversation = [
                    'text'  =>  $this->conversation->get_conversation_text( $value->id ),
                    'files' =>  $this->conversation->get_conversation_attachments( $value->id ),
                ];
                $user_info = get_userdata( $user_id );
                $messages[] = array(
                    'user_id'   => $user_id,
                    'user_name' => $user_info->user_login,
                    'message'   => $conversation,
                    'avatar'    => get_avatar_url( $user_info->user_email ),
                    'status'    => $status,
                    'unread'    => $unread,
                    'to'        => $value->to,
                    'from'      => $value->from,
                    'time'      => get_date_from_gmt( date( 'M d,g:i a', strtotime( $value->created ) ), 'M d,g:i a' ),
                    'del_img'   => WPUF_ASSET_URI . '/images/del-pm.png',
                );
            }
        }

        return $messages;
    }

    /**
     * Delete all messages for a specific user
     *
     * @return object
     */
    public function delete_all(){
        $user_id  = isset( $_GET['id'] ) ? intval( wp_unslash( $_GET['id'] ) ) : 0;
        $messages = $this->conversation->get( $user_id );

        foreach ( $messages as $message ) {
            $this->conversation->delete( $message->id );
        }

        wp_send_json_success();
    }

    /**
     * Get current user conversation with another user
     *
     * @return array
     */
    public function personal_message() {
        global $wpdb;
        $data = [];

        $user_id = isset( $_GET['user_id'] ) ? intval( wp_unslash( $_GET['user_id'] ) ) : 0;

        $this->conversation->update(
            [ 'status' => 1 ],
            [
                'to'     => get_current_user_id(),
                'from'   => $user_id
            ]
        );

        $conversations = $this->conversation->get( $user_id );
        $response      = [];

        foreach ( $conversations as $conversation ) {
            $response[] = $this->prepare_conversation_response( $conversation );
        }

        $chat_with         = get_userdata( $user_id );
        $data['chat_with'] = $chat_with->user_login;
        $data['messages']  = $response;

        wp_send_json_success( $data );
    }

    /**
     * Save user message
     *
     * @return void
     */
    public function message_send() {
        // Message data
        $text       = isset( $_POST['message'] ) ? sanitize_text_field( wp_unslash( $_POST['message'] ) ) : '';
        $user_id    = isset( $_POST['user_id'] ) ? intval( wp_unslash( $_POST['user_id'] ) ) : 0;
        $files      = isset( $_FILES['files'] ) ? wp_unslash( $_FILES['files'] ) : [];

        $attachment_ids = $this->upload_files( $files );

        $message = [
            'text'  => $text,
            'files' => $attachment_ids
        ];

        $message = maybe_serialize( $message );

        // Insert conversation
        $conversation = $this->conversation->insert( [
            'from'     => get_current_user_id(),
            'to'       => $user_id,
            'message'  => $message,
            'created'  => wpuf_date2mysql( date("M d,g:i a") ),
        ] );

        if ( is_wp_error( $conversation ) ) {
            wp_send_json_error();
        }

        wp_send_json_success( $this->prepare_conversation_response( $conversation ) );

    }

    /**
     * Delete single message
     *
     * @return void
     */
    public function single_message_delete(){
        $conversation_id = isset( $_GET['id'] ) ? intval( wp_unslash( $_GET['id'] ) ) : 0;

        if ( $this->conversation->delete( $conversation_id ) ) {
            wp_send_json_success();
        }

        wp_send_json_error();
    }

    /**
     * Delete attachment
     *
     * @return void
     */
    public function remove_attachment() {
        $conversation_id = isset( $_POST['conversation_id'] ) ? intval( wp_unslash( $_POST['conversation_id'] ) ) : 0;
        $attachment_id   = isset( $_POST['attachment_id'] ) ? intval( wp_unslash( $_POST['attachment_id'] ) ) : 0;

        if ( $this->conversation->delete_attachment( $conversation_id, $attachment_id ) ) {
            wp_send_json_success();
        }

        wp_send_json_error();
    }

    /**
     * Prepare response for a single conversation
     *
     * @param int|object $conversation
     *
     * @return array
     */
    public function prepare_conversation_response( $conversation ) {
        if ( ! is_object( $conversation ) ) {
            $conversation = $this->conversation->get_conversation_by_id( $conversation );
        }

        $chat_class = 'chat';

        if ( get_current_user_id() == $conversation->from ) {
            $chat_class = 'chat_darker';
        }

        $user_info   = get_userdata( $conversation->from );
        $attachments = $this->conversation->get_conversation_attachments( $conversation->id );
        $message     = maybe_unserialize( $conversation->message );
        $message = [
            'text'  => $message['text'],
            'files' => $attachments
        ];

        return [
            'message_id' => $conversation->id,
            'user_id'    => $conversation->from,
            'user_name'  => $user_info->user_login,
            'avatar'     => esc_url( get_avatar_url( $conversation->from ) ),
            'message'    => $message,
            'time'       => get_date_from_gmt( date( 'M d,g:i a', strtotime( $conversation->created ) ), 'M d,g:i a' ),
            'chat_class' => $chat_class,
            'del_img'    => WPUF_ASSET_URI . '/images/del-pm.png',
        ];
    }

    /**
     * Upload files
     *
     * @since 3.4.7
     *
     * @param array $files
     *
     * @return array
     */
    private function upload_files( $files ) {
        $total_file = isset( $files['name'] ) ? count( $files['name'] ) : 0;
        $attach_ids = [];
        // Upload files
        for ( $index = 0; $index < $total_file; $index++ ) {
            // Get file  name
            $file_name      = $files['name'][$index];
            $file_temp_name = $files['tmp_name'][$index];

            // Upload details
            $upload_dir = _wp_upload_dir();
            $image_data = file_get_contents( $file_temp_name );

            if ( wp_mkdir_p( $upload_dir['path'] ) ) {
                $file = $upload_dir['path'] . '/' . $file_name;
            } else {
                $file = $upload_dir['basedir'] . '/' . $file_name;
            }

            file_put_contents( $file, $image_data );
            $wp_filetype = wp_check_filetype( $file_name, null );

            $attachment = [
                'post_mime_type' => $wp_filetype['type'],
                'post_title'     => $file_name,
                'post_content'   => '',
                'post_status'    => 'inherit'
            ];

            $attach_id   = wp_insert_attachment( $attachment, $file );
            $attach_data = wp_generate_attachment_metadata( $attach_id, $file );

            wp_update_attachment_metadata( $attach_id, $attach_data );

            $attach_ids[] = $attach_id;

        }

        return $attach_ids;
    }
}
