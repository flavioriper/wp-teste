<?php
namespace WPUF\UserDirectory;

/**
 * Class Frontend
 */
class Frontend {
    /**
     * Constructor for the Frontend class
     */
    public function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize all hooks
     *
     * @return void
     */
    public function init_hooks() {
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
    }

    /**
     * Enqueue frontend scripts
     *
     * @return void
     */
    public function enqueue_scripts() {
        /**
         * All styles enqueue here
         */
        wp_enqueue_style( 'wpuf-user-directory-frontend-style' );
        wp_enqueue_script( 'wpuf-user-directory-frontend-script' );
    }
}

