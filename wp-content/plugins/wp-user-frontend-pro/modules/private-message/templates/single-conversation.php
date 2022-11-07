<script type="text/x-template" id="tmpl-wpuf-private-message-single">
    <div id="wpuf-private-message-single">
        <!-- Single Conversation Header -->
        <?php require_once dirname( __FILE__ ) . '/single-conversation/conversation-header.php'; ?>
        
        <div class="chat-container">
            <div class="chat-box">
                <div class="chat-container-overlay" v-if="loading">
                    <img src="<?php echo WPUF_PM_DIR . 'assets/images/loading-image.gif'; ?>" alt="">
                </div>
                <div class="single-chat-container">
                    <template v-for="(message, messageIndex) in messages">
                        <!-- Sender Conversation -->
                        <?php require_once dirname( __FILE__ ) . '/single-conversation/sender-conversation.php'; ?>

                        <!-- Receiver Conversation -->
                        <?php require_once dirname( __FILE__ ) . '/single-conversation/receiver-conversation.php'; ?>
                        
                    </template>
                </div>
            </div>
            
            <!-- Conversation Send Area -->
            <?php require_once dirname( __FILE__ ) . '/single-conversation/send-conversation.php'; ?>
        </div>
        <button class="wpuf-select-different-user"><?php esc_html_e( 'Select a different user', 'wpuf-pro' ); ?></button>
    </div>
</script>

