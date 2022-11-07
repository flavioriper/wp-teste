<div class="user-list-table-area">
    <table class="user-list-table">
        <thead>
            <tr>
                <?php
                if ( isset( $user_meta['settings']['avatar'] ) && true === $user_meta['settings']['avatar'] ) {
                    ?>
                    <th class="wpuf-user-list-avatar-column">
                        <?php esc_html_e( 'Avatar', 'wpuf-pro' ); ?>
                    </th>
                    <?php
                }

                foreach ( $unique_meta as $key => $val ) {
                    ?>
                        <th><?php echo esc_attr( $val ); ?></th>
                    <?php
                }
                ?>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ( $users as $user ) { ?>
            <tr>
                <?php
                if ( isset( $user_meta['settings']['avatar'] ) && true === $user_meta['settings']['avatar'] ) {
                    ?>
                    <td><?php echo get_avatar( $user->user_email, 40 ); ?></td>
                    <?php
                }

                foreach ( $unique_meta as $meta_key => $label ) {
                    ?>
                        <td>
                        <?php
                        if ( is_array( $user->$meta_key ) && ! empty( $user->$meta_key ) ) {
                            $output  = '<p>';
                            $output .= implode( ', ', $user->$meta_key );
                            $output .= '</p>';

                            echo $output;
                        } elseif ( ! empty( $user->$meta_key ) ) {
                            echo links_add_target( make_clickable( $user->$meta_key ) );
                        }
                        ?>
                        </td>
                        <?php
                }
                ?>
                <td>
                    <a class="button" href="<?php echo WPUF_User_Listing()->shortcode->get_user_link( $user->ID, $query_args ); ?>">
                        <?php esc_html_e( 'View Profile', 'wpuf-pro' ); ?>
                    </a>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<script>
    document.addEventListener('DOMContentLoaded',function () {
        var tr = document.querySelector('.user-list-table-area tbody').querySelectorAll('tr');

        var search_input = document.querySelector('.wpuf-user-list-search-section .search-area input');

        search_input.addEventListener('keyup', function (e) {
            var search_value = e.target.value;

            if (search_value.length) {
                tr.forEach(function (row, index) {
                    if (row.innerText.toLowerCase().includes( search_value ) !== true) {
                        tr[index].style.display = 'none';
                    }else {
                        tr[index].style.display = '';
                    }
                })
            }else {
                tr.forEach(function (row, index) {
                    tr[index].style.display = '';
                })
            }
        });
    });
</script>
