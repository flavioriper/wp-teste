<div  
    class="wpuf-message-box-container wpuf-message-receiver" 
    v-else
>
    <div class="wpuf-user-picture">
        <img 
            v-if="message.avatar"
            :src="message.avatar" 
            alt="Avatar"
        >
        <img
            v-else 
            src="<?php echo WPUF_PM_DIR. 'assets/images/default-avatar.png'; ?>" 
            alt=""
        >
    </div>
    <div class="wpuf-message-box">
        <div 
            class="wpuf-receiver-message-remove-icon"
            @click="deleteSingleMessage(messageIndex)"
        >
            <svg 
                width="20" 
                height="20" 
                viewBox="0 0 20 20" fill="none" 
                xmlns="http://www.w3.org/2000/svg"
            >    
            <path fill-rule="evenodd" clip-rule="evenodd" d="M6.68083 5.56182C6.37186 5.25286 5.87092 5.25286 5.56195 5.56182C5.25299 5.87079 5.25299 6.37173 5.56195 6.6807L9.10481 10.2236L5.56187 13.7665C5.2529 14.0755 5.2529 14.5764 5.56187 14.8854C5.87084 15.1943 6.37178 15.1943 6.68075 14.8854L10.2237 11.3424L13.767 14.8858C14.076 15.1948 14.577 15.1948 14.8859 14.8858C15.1949 14.5768 15.1949 14.0759 14.8859 13.7669L11.3426 10.2236L14.8858 6.68027C15.1948 6.3713 15.1948 5.87036 14.8858 5.56139C14.5769 5.25242 14.0759 5.25242 13.767 5.56139L10.2237 9.10468L6.68083 5.56182Z" fill="#C4C4C4"/>
            </svg>
        </div>
        <div class="wpuf-user-message">
            <p>{{ message.message.text }}</p>
            <template v-if="message.message.files">
                <div 
                    :class="
                    [
                        'message-image-container', 
                        {'file-item-1': message.message.files.length == 1}, 
                        {'file-item-2': message.message.files.length == 2}, 
                        {'file-item-3': message.message.files.length >= 3}
                    ]"
                >
                    <div 
                        class="single-message-image"
                        v-for="(file, fileIndex) in message.message.files"
                    >
                        <div 
                            class="wpuf-image-file"
                            v-if="isImage(file.name)"
                        >
                            <img 
                                :src="file.url" 
                                alt=""
                            >
                            <span class="wpuf-file-name">{{ file.name }}</span>
                        </div>
                        <div 
                            class="wpuf-file"
                            v-else
                        >
                            <img 
                                src="<?php echo  WPUF_PM_DIR . '/assets/images/file-icon.png'; ?>" 
                                alt=""
                            >
                            <span class="wpuf-file-name">{{ file.name }}</span>
                        </div>
                        <div class="wpuf-file-operation">

                            <div class="wpuf-file-download">
                                <a :href="file.url" :download="file.name">
                                    <img src="<?php echo WPUF_PM_DIR . '/assets/images/download-icon.svg'; ?>" alt="">
                                </a>
                                <span class="wpuf-file-download-tooltip" ><?php esc_html_e( 'Download', 'wpuf-pro' ); ?></span>
                            </div>
                            
                            <div class="wpuf-file-remove">
                                <a href="" @click.prevent="removeAttachment(messageIndex, fileIndex)">
                                    <img src="<?php echo WPUF_PM_DIR . '/assets/images/remove-icon.svg'; ?>" alt="">
                                </a>
                                <span class="wpuf-file-remove-tooltip"><?php esc_html_e( 'Remove', 'wpuf-pro' ); ?></span>
                            </div>
                        </div>
                    </div>
                </div>    
            </template>
            <p class="wpuf-message-send-time">
                {{ message.time }}
            </p>
        </div>
    </div>
</div>
