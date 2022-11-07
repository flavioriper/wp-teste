<script type="text/x-template" id="tmpl-wpuf-private-message-index">
    <div class="wpuf-private-message-index-overlay" v-if="loading">
        <img src="<?php echo WPUF_PM_DIR . 'assets/images/loading-image.gif'; ?>" alt="">
    </div>
    <div id="wpuf-private-message-index" v-else>
        <!-- {{ messages.length }} -->
        <div class="wpuf-inbox-message-container" v-if="messages.length">
            <div class="wpuf-inbox-message">
                <div 
                    :class="['wpuf-inbox-message-body', {'wpuf-unread-conversation':message.unread}]" 
                    v-for="message in messages"
                >
                    <router-link :to="{name: 'wpufPMSingle', params: {id: message.user_id}}" class="wpuf-single-message">
                        <div class="wpuf-message-avatar">
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
                        <div class="wpuf-message-body">
                            <div class="wpuf-message-body-heading">
                                <div>
                                    <h5>
                                        {{ message.user_name }}
                                        <span 
                                            class="wpuf-message-status"
                                            v-if="message.unread"
                                        >
                                            <?php esc_html_e( 'Unread', 'wpuf-pro' ); ?>
                                        </span>
                                    </h5>
                                </div>
                                <span class="wpuf-mesage-time">{{ message.time }}</span>
                            </div>
                            <div class="wpuf-message-body-content">
                                <p>{{ message.message.text | strLimit(200) }}</p> 
                            </div>
                        </div>
                    </router-link>
                </div> 
            </div>
            <span class="wpuf-create-new-message-icon">
                <img src="<?php echo WPUF_PM_DIR . 'assets/images/plus.svg'; ?>" alt="">
            </span>
        </div>
        <div 
            class="mailbox" 
            v-else
        >
            <div class="wpuf-private-message-icon">
                <svg width="134" height="124" viewBox="0 0 134 124" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M112.277 43.4453C124.274 43.4453 134 33.7197 134 21.7227C134 9.72556 124.274 0 112.277 0C100.28 0 90.5547 9.72556 90.5547 21.7227C90.5547 33.7197 100.28 43.4453 112.277 43.4453ZM85.5984 46.0625H41.3516C39.906 46.0625 38.7344 44.8909 38.7344 43.4453C38.7344 41.9997 39.906 40.8281 41.3516 40.8281H85.5984C87.044 40.8281 88.2156 41.9997 88.2156 43.4453C88.2156 44.8909 87.044 46.0625 85.5984 46.0625ZM29.5579 83.75H85.5984C87.044 83.75 88.2156 82.5784 88.2156 81.1328C88.2156 79.6872 87.044 78.5156 85.5984 78.5156H29.5579C28.1123 78.5156 26.9407 79.6872 26.9407 81.1328C26.9407 82.5784 28.1123 83.75 29.5579 83.75ZM73.7863 104.968C74.2729 105.454 74.9487 105.734 75.6367 105.734C76.3248 105.734 77.0005 105.454 77.4872 104.968C77.9738 104.481 78.2539 103.805 78.2539 103.117C78.2539 102.429 77.9738 101.753 77.4872 101.267C77.0005 100.78 76.3248 100.5 75.6367 100.5C74.9487 100.5 74.2729 100.78 73.7863 101.267C73.2997 101.753 73.0195 102.429 73.0195 103.117C73.0195 103.805 73.2997 104.481 73.7863 104.968ZM27.7085 45.2957C28.1951 45.7824 28.8699 46.0625 29.5589 46.0625C30.247 46.0625 30.9217 45.7824 31.4094 45.2957C31.896 44.8091 32.1761 44.1333 32.1761 43.4453C32.1761 42.7573 31.896 42.0815 31.4094 41.5949C30.9217 41.1082 30.247 40.8281 29.5589 40.8281C28.8699 40.8281 28.1951 41.1082 27.7085 41.5949C27.2219 42.0815 26.9417 42.7573 26.9417 43.4453C26.9417 44.1333 27.2219 44.8091 27.7085 45.2957ZM85.5984 64.9062H29.5579C28.1123 64.9062 26.9407 63.7346 26.9407 62.2891C26.9407 60.8435 28.1123 59.6719 29.5579 59.6719H85.5984C87.044 59.6719 88.2156 60.8435 88.2156 62.2891C88.2156 63.7346 87.044 64.9062 85.5984 64.9062ZM84.1565 18.8438H14.5213C6.51434 18.8438 0 25.3581 0 33.3651V91.2131C0 99.22 6.51434 105.734 14.5213 105.734H65.168C66.6136 105.734 67.7852 104.563 67.7852 103.117C67.7852 101.672 66.6136 100.5 65.168 100.5H14.5213C9.40041 100.5 5.23438 96.334 5.23438 91.2131V33.3651C5.23438 28.2442 9.40041 24.0781 14.5213 24.0781H84.1085C84.0444 23.3014 84.0117 22.5159 84.0117 21.7227C84.0117 20.7508 84.0608 19.7904 84.1565 18.8438ZM112.277 49.9883C113.249 49.9883 114.21 49.9392 115.156 49.8435V120.914C115.156 121.972 114.518 122.927 113.54 123.332C113.217 123.466 112.876 123.531 112.54 123.531C111.858 123.531 111.19 123.265 110.689 122.764L93.6585 105.734H85.5984C84.1528 105.734 82.9812 104.563 82.9812 103.117C82.9812 101.672 84.1528 100.5 85.5984 100.5H94.7422C95.4364 100.5 96.1019 100.776 96.5926 101.267L109.922 114.596V49.8915C110.699 49.9556 111.484 49.9883 112.277 49.9883ZM118.82 22.9282H113.375V28.5273H111.18V22.9282H105.734V20.7788H111.18V15.1797H113.375V20.7788H118.82V22.9282Z" fill="#C0C5D8"/>
                </svg>
            </div>
            <div class="wpuf-message-description">
                <h2 class="wpuf-message-title"><?php esc_html_e( 'Start Conversation', 'wpuf-pro' ); ?></h2>
                <p class="wpuf-message-sub-title"><?php esc_html_e( 'Using the private message option you can send private messages to any registered user from your profile', 'wpuf-pro' ); ?></p>
            </div>
            <button class="new-message wpuf-new-message-button"><?php _e( 'New Message', 'wpuf-pro' ); ?></button>
        </div>
    </div>
</script>
