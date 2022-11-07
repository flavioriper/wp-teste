<div class="user-social-profile">
    <?php
	if ( ! $user ) {
		return;
	}
        $facebook  = get_user_meta( $user->ID, 'facebook', true );
        $twitter   = get_user_meta( $user->ID, 'twitter', true );
        $linkedin  = get_user_meta( $user->ID, 'linkedin', true );
        $instagram = get_user_meta( $user->ID, 'instagram', true );
    ?>
    <ul>
        <?php if ( $facebook ) { ?>
        <li>
            <a href="<?php echo esc_attr( $facebook ); ?>" target="_blank">
                <svg width="45" height="45" viewBox="0 0 45 45" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M26.6778 22.4836H24.305V31H20.7098V22.4836H19V19.4906H20.7098V17.5538C20.7098 16.1688 21.3814 14 24.337 14L27 14.0109V16.9161H25.0678C24.7509 16.9161 24.3052 17.0713 24.3052 17.732V19.4934H26.9919L26.6778 22.4836Z" fill="#3B5998"/>
                </svg>
            </a>
        </li>
        <?php } ?>

        <?php if ( $twitter ) { ?>
        <li>
            <a href="<?php echo esc_attr( $twitter ); ?>" target="_blank">
                <svg width="45" height="45" viewBox="0 0 45 45" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M30 17.539C29.4851 17.7999 28.9311 17.9764 28.3502 18.0552C28.9433 17.6491 29.3985 17.0067 29.6134 16.2399C29.0584 16.616 28.4434 16.8889 27.7894 17.036C27.2655 16.3985 26.519 16 25.6923 16C24.1064 16 22.8201 17.4697 22.8201 19.2819C22.8201 19.5391 22.8456 19.7895 22.8948 20.0298C20.5077 19.893 18.3912 18.5865 16.9744 16.6004C16.7272 17.085 16.5854 17.6491 16.5854 18.2505C16.5854 19.3888 17.0929 20.3938 17.8632 20.9822C17.3925 20.9653 16.9494 20.8177 16.5623 20.5712C16.5621 20.5852 16.5621 20.5991 16.5621 20.6128C16.5621 22.203 17.5526 23.5293 18.8663 23.8306C18.6255 23.906 18.3712 23.9459 18.1097 23.9459C17.9242 23.9459 17.7445 23.9256 17.5693 23.8874C17.9347 25.1909 18.9952 26.1398 20.2524 26.1665C19.2692 27.0469 18.0309 27.5714 16.6848 27.5714C16.4535 27.5714 16.2243 27.556 16 27.5254C17.2704 28.4569 18.7806 29 20.4026 29C25.6857 29 28.5749 23.9992 28.5749 19.662C28.5749 19.5198 28.5721 19.3781 28.5665 19.2376C29.1282 18.775 29.6151 18.1971 30 17.539Z" fill="#1DA1F3"/>
                </svg>
            </a>
        </li>
        <?php } ?>

        <?php if ( $linkedin ) { ?>
        <li>
            <a href="<?php echo esc_attr( $linkedin ); ?>" target="_blank">
                <svg width="45" height="45" viewBox="0 0 45 45" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M30 23.5828V29.0001H26.9987V23.9459C26.9987 22.6768 26.5654 21.8102 25.4787 21.8102C24.6494 21.8102 24.1567 22.3936 23.9392 22.9586C23.8602 23.1605 23.8398 23.4409 23.8398 23.7241V28.9999H20.8383C20.8383 28.9999 20.8786 20.4397 20.8383 19.5536H23.84V20.8922C23.834 20.9027 23.8255 20.9131 23.8201 20.9231H23.84V20.8922C24.2389 20.2499 24.9502 19.3318 26.545 19.3318C28.5196 19.3318 30 20.6819 30 23.5828ZM17.6984 15.0001C16.6717 15.0001 16 15.7054 16 16.632C16 17.539 16.6522 18.2646 17.659 18.2646H17.6785C18.7253 18.2646 19.3762 17.539 19.3762 16.632C19.3563 15.7054 18.7253 15.0001 17.6984 15.0001ZM16.1784 29.0001H19.1788V19.5536H16.1784V29.0001Z" fill="#007AB9"/>
                </svg>
            </a>
        </li>
        <?php } ?>

        <?php if ( $instagram ) { ?>
        <li>
            <a href="<?php echo esc_attr( $instagram ); ?>" target="_blank">
                <svg width="45" height="45" viewBox="0 0 45 45" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M31.9429 18.5832C31.8966 17.572 31.736 16.8814 31.5012 16.2772C31.2627 15.6433 30.8887 15.0691 30.4055 14.5945C29.931 14.1113 29.3567 13.7371 28.7228 13.4985C28.1184 13.2638 27.428 13.1034 26.4168 13.0574C25.4037 13.011 25.08 13 22.5 13C19.92 13 19.5963 13.011 18.5832 13.0571C17.572 13.1034 16.8816 13.264 16.2772 13.4988C15.6433 13.7373 15.0691 14.1113 14.5945 14.5945C14.1113 15.069 13.7371 15.6432 13.4985 16.2771C13.2638 16.8814 13.1034 17.572 13.0574 18.5831C13.011 19.5963 13 19.9199 13 22.4999C13 25.08 13.011 25.4037 13.0574 26.4168C13.1035 27.4279 13.2641 28.1184 13.4989 28.7228C13.7374 29.3565 14.1114 29.9309 14.5947 30.4053C15.0691 30.8886 15.6435 31.2626 16.2774 31.5011C16.8816 31.736 17.5721 31.8965 18.5834 31.9427C19.5966 31.989 19.9202 31.9999 22.5001 31.9999C25.0801 31.9999 25.4038 31.989 26.4169 31.9427C27.4282 31.8965 28.1186 31.736 28.7229 31.5011C29.999 31.0076 31.0078 29.9988 31.5012 28.7228C31.7362 28.1184 31.8966 27.4279 31.9429 26.4168C31.989 25.4035 32 25.08 32 22.5C32 19.9199 31.989 19.5963 31.9429 18.5832V18.5832ZM30.2328 26.3389C30.1906 27.2652 30.0358 27.7682 29.9058 28.1029C29.5862 28.9315 28.9314 29.5863 28.1028 29.9059C27.7681 30.036 27.2651 30.1908 26.3388 30.233C25.3373 30.2788 25.0368 30.2883 22.5 30.2883C19.9631 30.2883 19.6627 30.2788 18.6611 30.233C17.7349 30.1908 17.2319 30.036 16.8971 29.9059C16.4845 29.7536 16.1112 29.5108 15.8048 29.1952C15.4892 28.8888 15.2464 28.5156 15.0941 28.1029C14.964 27.7682 14.8092 27.2652 14.767 26.3389C14.7214 25.3373 14.7117 25.0368 14.7117 22.5001C14.7117 19.9634 14.7214 19.663 14.767 18.6612C14.8094 17.7349 14.964 17.2319 15.0941 16.8972C15.2464 16.4845 15.4894 16.1112 15.8048 15.8048C16.1112 15.4892 16.4845 15.2464 16.8972 15.0942C17.2319 14.964 17.7349 14.8094 18.6612 14.767C19.6629 14.7214 19.9634 14.7117 22.5 14.7117H22.4999C25.0365 14.7117 25.337 14.7214 26.3388 14.7672C27.2651 14.8094 27.7679 14.9642 28.1028 15.0942C28.5153 15.2466 28.8886 15.4894 29.1951 15.8048C29.5106 16.1112 29.7534 16.4845 29.9056 16.8972C30.0358 17.2319 30.1906 17.7349 30.2328 18.6612C30.2785 19.6629 30.2882 19.9634 30.2882 22.5C30.2882 25.0368 30.2786 25.3371 30.2328 26.3389Z" fill="url(#paint0_linear_938:20)"/>
                    <path d="M22.4998 18.4302C20.1891 18.4302 18.3159 20.3035 18.3159 22.6142C18.3159 24.9249 20.1891 26.7981 22.4998 26.7981C24.8107 26.7981 26.6839 24.9249 26.6839 22.6142C26.6839 20.3035 24.8107 18.4302 22.4998 18.4302V18.4302ZM22.4998 25.3301C21 25.33 19.7839 24.1141 19.7841 22.6141C19.7841 21.1142 21 19.8982 22.5 19.8982C23.9999 19.8983 25.2158 21.1142 25.2158 22.6141C25.2158 24.1141 23.9998 25.3301 22.4998 25.3301V25.3301Z" fill="url(#paint1_linear_938:20)"/>
                    <path d="M27.8266 18.0503C27.8266 18.5901 27.389 19.0277 26.8492 19.0277C26.3092 19.0277 25.8716 18.5901 25.8716 18.0503C25.8716 17.5103 26.3092 17.0727 26.8492 17.0727C27.389 17.0727 27.8266 17.5103 27.8266 18.0503V18.0503Z" fill="url(#paint2_linear_938:20)"/>
                    <defs>
                        <linearGradient id="paint0_linear_938:20" x1="14.5944" y1="30.4054" x2="30.4055" y2="14.5943" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#FFD600"/>
                            <stop offset="0.5" stop-color="#FF0100"/>
                            <stop offset="1" stop-color="#D800B9"/>
                        </linearGradient>
                        <linearGradient id="paint1_linear_938:20" x1="19.5414" y1="25.5727" x2="25.4584" y2="19.6556" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#FF6400"/>
                            <stop offset="0.5" stop-color="#FF0100"/>
                            <stop offset="1" stop-color="#FD0056"/>
                        </linearGradient>
                        <linearGradient id="paint2_linear_938:20" x1="26.1579" y1="18.7414" x2="27.5403" y2="17.359" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#F30072"/>
                            <stop offset="1" stop-color="#E50097"/>
                        </linearGradient>
                    </defs>
                </svg>
            </a>
        </li>
        <?php } ?>
    </ul>
</div>
