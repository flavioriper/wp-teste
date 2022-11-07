<?php 

/**
 * Class WPUF Conversation
 */
class WPUF_Conversation {

    /**
     * Store message table name
     *
     * @var string
     */
    private $table;

    /**
     * Store wordpress global database instance
     *
     * @var object
     */
    private $db;

    /**
     * Constructor for the WPUF Conversation Class
     */
    public function __construct() {
        global $wpdb;
        $this->db = $wpdb;
        $this->table = $wpdb->prefix . 'wpuf_message';
    }

    public function all() {
    
    }

    /**
     * Get all conversation for current user
     *
     * @param integer $user_id
     * 
     * @return array
     */
    public function get( $user_id = 0 ) {

        $sql = $this->db->prepare( "SELECT *
                FROM " . $this->table . " WHERE ((`from` = %d AND `from_del` = 0) OR (`to` = %d AND `to_del` = 0)) AND (`from` = %d OR `to` = %d)", 
                get_current_user_id(), 
                get_current_user_id(), 
                $user_id, 
                $user_id 
            );
        $conversations = $this->db->get_results( $sql );

        return $conversations;
    }

    /**
     * Get single conversation by id
     *
     * @param integer $id
     * 
     * @return object
     */
    public function get_conversation_by_id( $id = 0) {
        $sql          = $this->db->prepare( "SELECT * FROM " . $this->table . " WHERE id = %d", $id );
        $conversation = $this->db->get_row( $sql );

        if ( ! $conversation ) {
            return false;
        }

        return $conversation;
    }

    /**
     * Get all attachments for specific message
     *
     * @param integer $conversation_id
     * 
     * @return array
     */
    public function get_conversation_attachments( $conversation_id = 0 ) {

        $conversation = $this->get_conversation_by_id( $conversation_id );

        if ( ! $conversation ) {
            return false;
        }

        
        $attachment_ids = $this->get_attachment_ids_by_conversation( $conversation_id );

        $attachments = [];

        foreach( $attachment_ids as $attachment_id ) {
            
            $attachment = [
                'id'    => $attachment_id,
                'name'  => get_the_title( $attachment_id ),
                'url'   => wp_get_attachment_url( $attachment_id )
            ];

            array_push( $attachments, $attachment );
        }
        
        return $attachments;
    }

    /**
     * Get conversation text
     *
     * @param integer $conversation_id
     * 
     * @return string
     */
    public function get_conversation_text( $conversation_id ) {
        $conversation = $this->get_conversation_by_id( $conversation_id );
        if ( ! $conversation ) {
            return false;
        }

        $message = maybe_unserialize( $conversation->message );


        return $message['text'];
    }

    /**
     * Get attachment ids for specific conversation
     *
     * @param integer $conversation_id
     * 
     * @return array
     */
    public function get_attachment_ids_by_conversation( $conversation_id = 0 ) {
        $conversation = $this->get_conversation_by_id( $conversation_id );

        if ( ! $conversation ) {
            return false;
        }

        $message        = maybe_unserialize( $conversation->message );
        $attachment_ids = ! empty( $message['files'] ) ? $message['files'] : [];

        return $attachment_ids;
    }

    /**
     * Insert user conversation
     *
     * @param string $message
     * 
     * @param integer $user_id
     * 
     * @return int|bool
     */
    public function insert( $data = [] ) {
        if ( ! $data ) {
            return false;
        }

        $this->db->insert(
            $this->table,
            $data
        );

        return $this->db->insert_id;
    }
    
    /**
     * Update conversation
     *
     * @param array $args
     * @param array $where
     * 
     * @return int|bool
     */
    public function update( $args = [], $where = [] ) {
        
        $conversation = $this->db->update(
            $this->table,
            $args,
            $where
        );

        return $conversation;
    }

    /**
     * Delete conversation
     *
     * @param array $where
     * 
     * @return int|bool
     */
    public function delete( $conversation_id ) {
        if ( ! $conversation_id ) {
            return false;
        }

        $conversation = $this->get_conversation_by_id( $conversation_id );
        $update_row   = get_current_user_id() === intval( $conversation->from ) ? 'from_del' : 'to_del';

        $update_conversation = $this->update(
            [ $update_row => 1 ],
            [ 'id'  =>  $conversation_id ]
        );
        
        return $update_conversation;
    }

    /**
     * Delete attachment
     *
     * @param integer $conversation_id
     * @param integer $attachment_id
     * 
     * @return bool
     */
    public function delete_attachment( $conversation_id, $attachment_id ) {
        if ( ! $attachment_id ) {
            return false;
        }

        $conversation_text = $this->get_conversation_text( $conversation_id );
        $attachment_ids    = $this->get_attachment_ids_by_conversation( $conversation_id );
        $index             = array_search( $attachment_id, $attachment_ids );
        
        if ( $attachment_ids[$index] ) {
            unset( $attachment_ids[$index] );
            $message = [
                'text'  =>  $conversation_text,
                'files' =>  $attachment_ids  
            ];

            $this->update(
                [ 'message' => maybe_serialize( $message ) ],
                [ 'id' => $conversation_id ]
            );

            wp_delete_attachment( $attachment_id );

            return true;
        }

        return false;
    }
}
