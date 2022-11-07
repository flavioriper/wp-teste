<?php 
/**
 * Contact class
 */
class WPUF_Contact {
    /**
     * Initialize
     *
     * @return void
     */
    public function __construct() {
        add_filter( 'user_contactmethods', [ $this, 'add_contact_methods' ], 8, 2 );
    }

    /**
     * Add contact method multiple contact methods
     *
     * @param array $methods
     * @param object $user
     * 
     * @return array
     */
    public function add_contact_methods( $methods, $user ) {
        $methods['phone']     = __( 'Phone', 'wpuf-pro' );
        $methods['facebook']  = __( 'Facebook', 'wpuf-pro' );
        $methods['twitter']   = __( 'Twitter', 'wpuf-pro' );
        $methods['linkedin']  = __( 'Linkedin', 'wpuf-pro' );
        $methods['instagram'] = __( 'Instagram', 'wpuf-pro' );

        return $methods;
    }
}
