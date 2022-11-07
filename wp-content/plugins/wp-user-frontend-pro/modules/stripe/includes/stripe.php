<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( '\Stripe\Stripe' ) ) {
    require_once dirname( __FILE__ ) . '/../lib/stripe/init.php';
}

/**
 * WP User Frontend Stripe gateway
 *
 * @since 0.1
 */
class WPUF_Gateway_Stripe {

    public function __construct() {
        add_action( 'wpuf_options_payment', array( $this, 'payment_options' ) );
        add_action( 'wpuf_gateway_form_stripe', array( $this, 'gateway_form' ), 10, 3 );
        add_action( 'wp_ajax_wpuf_create_stripe_payment_intent', array( $this, 'process_payment' ) );
        add_action( 'wp_ajax_nopriv_wpuf_create_stripe_payment_intent', array( $this, 'process_payment' ) );
        add_action( 'wpuf_gateway_stripe', array( $this, 'insert_payment' ) );
        add_action( 'init', array( $this, 'check_response' ) );
        add_action( 'wpuf_stripe_ipn_response', array( $this, 'update_transaction_status' ) );
        add_action( 'wpuf_cancel_subscription_stripe', array( $this, 'handle_cancel_subscription' ) );
    }

    /**
     * Adds stripe specific options to the admin panel
     *
     * @param array $options
     *
     * @return string
     * @since  0.1
     */
    public function payment_options( $options ) {
        $options[] = array(
            'name'    => 'gate_instruct_stripe',
            'label'   => __( 'Stripe Instruction', 'wpuf-pro' ),
            'type'    => 'wysiwyg',
            'default' => __( 'Enter your credit card information in order to proceed the payment.', 'wpuf-pro' ),
        );

        $options[] = array(
            'name'    => 'use_legacy_stripe_api',
            'label'   => __( 'Enable Legacy Stripe API', 'wpuf-pro' ),
            'type'    => 'checkbox',
            'default' => 'off',
            'desc'    => __( 'Check if you want to use legacy Stripe API.', 'wpuf-pro' ),
        );

        $options[] = array(
            'name'    => 'use_stripe_3ds',
            'label'   => __( 'Enable Stripe 3D Security', 'wpuf-pro' ),
            'type'    => 'checkbox',
            'default' => 'off',
            'desc'    => __( 'Check if you want to use Stripe 3D Security.', 'wpuf-pro' ),
        );

        $options[] = array(
            'name'  => 'stripe_api_secret',
            'label' => __( 'Stripe Secret Key', 'wpuf-pro' ),
        );

        $options[] = array(
            'name'  => 'stripe_api_key',
            'label' => __( 'Stripe Publishable Key', 'wpuf-pro' ),
        );

        $options[] = [
            'name'    => 'stripe_endpoint',
            'label'   => __( 'Stripe Webhook endpoint', 'wpuf-pro' ),
            'default' => add_query_arg( 'action', 'wpuf_stripe_payment_response', home_url( '' ) ),
            'desc'    => __( 'Set this to your webhook endpoint', 'wpuf-pro' ),
            'class'   => 'disabled',
        ];

        $options[] = array(
            'name'  => 'stripe_signing_secret',
            'label' => __( 'Stripe Signing Secret', 'wpuf-pro' ),
        );

        return $options;
    }

    /**
     * Display the credit card form
     *
     * @param string $type
     * @param int $post_id
     * @param int $pack_id
     *
     * @return void
     * @since  0.1
     */
    public function gateway_form( $type, $post_id, $pack_id ) {
        ?>
        <div class="form-row">
            <div id="card-element">
                <!-- a Stripe Element will be inserted here. -->
            </div>

            <!-- Used to display form errors -->
            <div id="wpuf-stripe-card-errors" role="alert"></div>
            <div id="wpuf-stripe-card-success" role="alert"></div>
        </div>

        <?php
    }

    /**
     * Configure Stripe
     *
     * @since  3.1.9
     */
    public function configure_stripe() {
        $stripe_api_secret = wpuf_get_option( 'stripe_api_secret', 'wpuf_payment' );

        \Stripe\Stripe::setApiKey( $stripe_api_secret );
        \Stripe\Stripe::setApiVersion( '2019-05-16' );
        \Stripe\Stripe::setAppInfo(
            'WP User Frontend Pro',
            WPUF_PRO_VERSION,
            'https://wedevs.com/wp-user-frontend-pro/',
            'pp_partner_Ee9F0QbhSGowvH' //weDevs' Stripe partner ID,
        );
    }

    /**
     * Configure Stripe webhook
     *
     * @since  3.1.9
     */
    public function configure_stripe_webhook() {
        // Register webhook to receive payment notification
        $listener_url = add_query_arg( 'action', 'wpuf_stripe_payment_response', home_url( '/' ) );
        //         $listener_url = 'http://b8fdcedb8660.ngrok.io?action=wpuf_stripe_payment_response';

        $webhooks = \Stripe\WebhookEndpoint::all( [ 'limit' => 16 ] );

        if ( empty( $webhooks->data ) ) {
            \Stripe\WebhookEndpoint::create(
                [
                    'url'            => $listener_url,
                    'enabled_events' => [
                        'payment_intent.succeeded',
                        'customer.subscription.created',
                        'customer.subscription.deleted',
                        'invoice.payment_failed',
                    ],
                ]
            );
        } else {
            foreach ( $webhooks->data as $webhook ) {
                if ( self::check_urls_same( $listener_url, $webhook->url ) ) {
                    //invoice.payment_failed for already configured client
                    if ( ! in_array( 'invoice.payment_succeeded', $webhook->enabled_events, true ) ) {
                        \Stripe\WebhookEndpoint::update(
                            $webhook->id, [
                                'enabled_events' => [
                                    'payment_intent.succeeded',
                                    'customer.subscription.created',
                                    'customer.subscription.deleted',
                                    'invoice.payment_failed',
                                ],
                            ]
                        );
                    }
                    break;
                }
                \Stripe\WebhookEndpoint::create(
                    [
                        'url'            => $listener_url,
                        'enabled_events' => [
                            'payment_intent.succeeded',
                            'customer.subscription.created',
                            'customer.subscription.deleted',
                            'invoice.payment_failed',
                        ],
                    ]
                );
            }
        }
    }

    /**
     * Process Payment
     *
     * @since  3.1.9
     */
    public function process_payment() {
        $request_data = wp_unslash( $_POST );
        $use_3ds = wpuf_get_option( 'use_stripe_3ds', 'wpuf_payment', 'off' );

        if ( empty( $request_data['nonce'] ) || ! wp_verify_nonce( $request_data['nonce'], 'wpuf_nonce' ) ) {
            return;
        }

        parse_str( $request_data['form_data'], $form_data );

        $data           = $this->get_payment_data( $form_data );
        $billing_amount = $data['billing_amount'];
        $stripe_token   = isset( $request_data['token'] ) ? $request_data['token'] : '';
        $user_id        = intval( $data['user_info']['id'] );
        $user_email     = $data['user_info']['email'];
        $user_name      = $data['user_info']['first_name'] . $data['user_info']['last_name'];

        if ( (int) $billing_amount === 0 ) {
            WPUF_Subscription::init()->new_subscription( $user_id, $data['item_number'], $profile_id = null, false, 'free' );

            wp_send_json( array( 'status' => 'succeed' ) );
        }

        $old_api = wpuf_get_option( 'use_legacy_stripe_api', 'wpuf_payment', 'off' );

        $post_data     = $data['post_data'];
        $stripe_amount = $this->handle_decimal( $data['currency'], $billing_amount );

        $this->configure_stripe();
        $this->configure_stripe_webhook();

        if ( empty( $data['custom']['recurring_pay'] ) ) {
            if ( $use_3ds === 'on' ) {
                $old_api = 'off';
            }
        }

        if ( 'on' === $old_api && $use_3ds !== 'on' ) {
            \Stripe\Stripe::setApiVersion( '2015-01-26' );
        }

        $tax_rate = wpuf_current_tax_rate();

        $new_plan = array();
        $subscription = array();

        if ( empty( $this->get_stripe_customer_id( $user_id ) ) ) {
            $this->create_customer( $user_id, $user_email, $stripe_token );
        }

        $stripe_customer_id = $this->get_stripe_customer_id( $user_id );
        $metadata           = [
            'user_id' => $user_id,
            'tax_rate' => $tax_rate,
            'pack_id' => $form_data['pack_id'],
            'post_id' => $form_data['post_id'],
            'email' => $user_email,
            'user_name' => $user_name,
        ];

        if ( $data['type'] === 'pack' && $data['custom']['recurring_pay'] === 'yes' ) {
            $subscription_package_name = $data['custom']['post_title'];
            $subscription_package_id   = intval( $post_data['pack_id'] );
            $trial_period_days         = 0;

            if ( $data['custom']['trial_status'] === 'yes' ) {
                $trial_duration_type = $data['custom']['trial_duration_type'];
                $trial_duration      = absint( $data['custom']['trial_duration'] );

                switch ( $trial_duration_type ) {
                    case 'week':
                        $trial_period_days = $trial_duration * 7;
                        break;

                    case 'month':
                        $trial_period_days = $trial_duration * 30;
                        break;

                    case 'year':
                        $trial_period_days = $trial_duration * 365;
                        break;

                    case 'day':
                    default:
                        $trial_period_days = $trial_duration;
                        break;
                }
                //Set trial meta for once per user
                if ( ! get_user_meta( $user_id, '_wpuf_used_trial', true ) ) {
                    update_user_meta( $user_id, '_wpuf_used_trial', 'yes' );
                }
            }

            try {
                $stripe_plan = \Stripe\Plan::retrieve( $subscription_package_id );

                if ( absint( $stripe_plan->trial_period_days ) !== $trial_period_days ) {
                    $stripe_plan = $stripe_plan->update(
                        $stripe_plan->id, array(
                            'trial_period_days' => $trial_period_days,
                        )
                    );
                }

                $subscription_package_id = $stripe_plan->id;
            } catch ( Exception $e ) {
                $plan_data = array(
                    'product'        => [
                        'name' => $subscription_package_name,
                        'type' => 'service',
                    ],
                    'nickname'       => $subscription_package_name,
                    'interval'       => $data['custom']['cycle_period'],
                    'interval_count' => intval( $data['custom']['billing_cycle_number'] ),
                    'currency'       => $data['currency'],
                    'amount'         => $stripe_amount,
                    'id'             => $subscription_package_id,
                );

                if ( $trial_period_days ) {
                    $plan_data['trial_period_days'] = $trial_period_days;
                }

                $stripe_plan = \Stripe\Plan::create( $plan_data );
                $subscription_package_id = $stripe_plan->id;
            }

            if ( ! empty( $stripe_customer_id ) ) {
                try {
                    if ( $old_api === 'on' ) {
                        $customer     = \Stripe\Customer::retrieve( $stripe_customer_id );
                        $subscription = $customer->subscriptions->create(
                            array(
                                'plan'        => $subscription_package_id,
                                'tax_percent' => $tax_rate,
                                'metadata'    => $metadata,
                            )
                        );
                    } else {
                        $subscription_data = array(
                            array(
                                'expand'      => [ 'latest_invoice.payment_intent' ],
                                'customer'    => $stripe_customer_id,
                                'items'       => [ [ 'plan' => $subscription_package_id ] ],
                                'tax_percent' => $tax_rate,
                                'metadata'    => $metadata,
                            ),
                        );

                        if ( $data['custom']['trial_status'] === 'yes' ) {
                            $subscription_data['trial_from_plan'] = true;
                        }

                        try {
                            $subscription = \Stripe\Subscription::create( $subscription_data );
                            update_user_meta( $subscription->id, '_wpuf_stripe_subscription_id', $user_id );

                            if ( isset( $tax_rate ) ) {
                                update_user_meta( $user_id, '_wpuf_tax_rate', $tax_rate );
                            }

                            wp_send_json( $subscription );
                        } catch ( Exception $e ) {
                            WP_User_Frontend::log( 'creating subscription', $e->getMessage() );
                        }
                    }
                } catch ( Exception $e ) {
                    WP_User_Frontend::log( 'payment', $e->getMessage() );
                }
            }
        } else {
            try {
                $intent = \Stripe\PaymentIntent::create(
                    [
                        'amount'               => $this->handle_decimal( $data['currency'], $data['billing_amount'] ),
                        'currency'             => $data['currency'],
                        'confirmation_method'  => 'automatic',
                        'payment_method_types' => [ 'card' ],
                        'metadata'             => $metadata,
                    ]
                );

                wp_send_json( $intent );
            } catch ( Exception $e ) {
                $message = $e->getMessage();

                WP_User_Frontend::log( 'payment', $message );
            }
        }
    }

    /**
     * Get payment/subscription pack data
     *
     * @since  3.1.9
     */
    public function get_payment_data( $form_data ) {
        $cost         = 0;
        $post_id      = isset( $form_data['post_id'] ) ? intval( $form_data['post_id'] ) : 0;
        $pack_id      = isset( $form_data['pack_id'] ) ? intval( $form_data['pack_id'] ) : 0;
        $gateway      = isset( $form_data['wpuf_payment_method'] ) ? $form_data['wpuf_payment_method'] : 'stripe';
        $type         = isset( $form_data['type'] ) ? $form_data['type'] : '';
        $current_user = wpuf_get_user();
        $current_pack = $current_user->subscription()->current_pack();

        if ( is_user_logged_in() ) {
            $userdata = wp_get_current_user();
        } else {
            //$user_id = isset( $form_data['user_id'] ) ? $form_data['user_id'] : 0;
            $user_id = isset( $_REQUEST['user_id'] ) ? intval( wp_unslash( $_REQUEST['user_id'] ) ) : 0;

            if ( $user_id && ! is_user_logged_in() ) {
                $userdata = get_userdata( $user_id );
            } elseif ( $type === 'post' && ! is_user_logged_in() ) {
                $post     = get_post( $post_id );
                $user_id  = $post->post_author;
                $userdata = get_userdata( $user_id );
            } else {
                $userdata             = new stdClass();
                $userdata->ID         = 0;
                $userdata->user_email = '';
                $userdata->first_name = '';
                $userdata->last_name  = '';
            }
        }

        switch ( $type ) {
            case 'post':
                $post          = get_post( $post_id );
                $form_id       = get_post_meta( $post_id, '_wpuf_form_id', true );
                $form          = new WPUF_Form( $form_id );
                $form_settings = $form->get_settings();
                $force_pack    = $form->is_enabled_force_pack();
                $fallback_on   = $form->is_enabled_fallback_cost();
                $post_count    = $current_user->subscription()->has_post_count( $form_settings['post_type'] );

                if ( $force_pack && $fallback_on && ! is_wp_error( $current_pack ) && ! $post_count ) {
                    $amount = $form->get_subs_fallback_cost();
                } else {
                    $amount = $form->get_pay_per_post_cost();
                }
                $item_number = $post->ID;
                $item_name   = $post->post_title;
                break;

            case 'pack':
                $pack        = WPUF_Subscription::init()->get_subscription( $pack_id );
                $custom      = $pack->meta_value;
                $cost        = $pack->meta_value['billing_amount'];
                $amount      = $cost;
                $item_name   = $pack->post_title;
                $item_number = $pack->ID;
                break;
        }

        $data = array(
            'currency'    => wpuf_get_option( 'currency', 'wpuf_payment' ),
            'price'       => $amount,
            'item_number' => $item_number,
            'item_name'   => $item_name,
            'type'        => $type,
            'user_info'   => array(
                'id'         => $userdata->ID,
                'email'      => $userdata->user_email,
                'first_name' => $userdata->first_name,
                'last_name'  => $userdata->last_name,
            ),
            'date'        => gmdate( 'Y-m-d H:i:s' ),
            'post_data'   => $form_data,
            'custom'      => isset( $custom ) ? $custom : '',
        );

        $address_fields = wpuf_get_user_address();

        if ( ! empty( $address_fields ) ) {
            update_user_meta( $userdata->ID, 'wpuf_address_fields', $address_fields );
        }

        /**
         * Using thousand separator arg for number_format to quick fix
         *
         * @see Issue #193
         *
         * @todo: Payment gateways should be refactored. Calculation should be
         *        in one place, not in gateway classes.
         */
        $billing_amount = empty( $data['price'] ) ? 0 : number_format( floatval( $data['price'] ), 2, '.', '' );
        $coupon_id      = '';

        $tax_enabled = wpuf_tax_enabled();

        if ( isset( $form_data['coupon_id'] ) && ! empty( $form_data['coupon_id'] ) ) {
            $billing_amount = WPUF_Coupons::init()->discount( $billing_amount, $form_data['coupon_id'], $data['item_number'] );
            $coupon_id      = $form_data['coupon_id'];
        }

        if ( $tax_enabled ) {
            $tax_rate       = wpuf_current_tax_rate();
            $tax_amount     = floatval( $tax_rate / 100 * $billing_amount );
            $billing_amount = number_format( floatval( $tax_amount + $billing_amount ), 2, '.', '' );
            $data['tax']    = $tax_amount;
        }

        $data['subtotal']       = floatval( $billing_amount );
        $data['billing_amount'] = $billing_amount;
        $data['coupon_id']      = $coupon_id;
        $billing_amount         = apply_filters( 'wpuf_payment_amount', $data['subtotal'] );

        return $data;
    }

    /**
     * Get stripe customer id from user profile
     *
     * @since  3.1.9
     */
    public function get_stripe_customer_id( $user_id ) {
        return get_user_meta( $user_id, '_wpuf_stripe_customer_id', true );
    }

    /**
     * Create Stripe customer if not exist
     *
     * @since  3.1.9
     */
    public function create_customer( $user_id, $user_email, $stripe_token ) {
        $customer = \Stripe\Customer::create(
            array(
                'email'  => $user_email,
                'source' => $stripe_token,
            )
        );

        update_user_meta( $user_id, '_wpuf_stripe_customer_id', $customer->id );
    }

    /**
     * Process the payment form with stripe
     *
     * @param array $data payment info
     *
     * @return void
     * @since  0.1
     */
    public function insert_payment( $data ) {
        $data                 = $this->get_payment_data( $data['post_data'] );
        $status               = 'completed';
        $user_id              = intval( $data['user_info']['id'] );
        $user_email           = $data['user_info']['email'];
        $transaction_id       = isset( $data['post_data']['stripePaymentIntentId'] ) ? $data['post_data']['stripePaymentIntentId'] : '';
        $trial_payment        = isset( $data['post_data']['trialPayment'] ) ? $data['post_data']['trialPayment'] : 'no';
        $subtotal             = $data['subtotal'];
        $tax                  = isset( $data['tax'] ) ? $data['tax'] : 0;
        $billing_amount       = $data['billing_amount'];
        $return_url           = wpuf_payment_success_page( $data );
        $is_payment_completed = true;

        switch ( $data['type'] ) {
            case 'post':
                $post_id = $data['post_data']['post_id'];
                $pack_id = 0;
                break;

            case 'pack':
                $post_id = 0;
                $pack_id = $data['post_data']['pack_id'];

                if ( $data['post_data']['recurring_pay'] === 'yes' && isset( $data['post_data']['stripeSubscriptionId'] ) && ! empty( $data['post_data']['stripeSubscriptionId'] ) ) {
                    $stripe_subscription_id = $data['post_data']['stripeSubscriptionId'];
                }

                break;
        }

        if ( $is_payment_completed ) {
            $first_name = isset( $data['user_info']['first_name'] ) ? $data['user_info']['first_name'] : '';
            $last_name  = isset( $data['user_info']['last_name'] ) ? $data['user_info']['last_name'] : '';

            $payment_data = array(
                'user_id'          => $user_id,
                'status'           => $status,
                'subtotal'         => $subtotal,
                'tax'              => $tax,
                'cost'             => $billing_amount,
                'post_id'          => $post_id,
                'pack_id'          => $pack_id,
                'payment_type'     => 'Stripe',
                'transaction_id'   => $transaction_id,
                'created'          => current_time( 'mysql' ),
                'payer_first_name' => $first_name,
                'payer_last_name'  => $last_name,
                'payer_email'      => $user_email,
            );

            if ( wpuf_get_option( 'show_address', 'wpuf_address_options', false ) ) {
                $payment_data['payer_address'] = wpuf_get_user_address();
            }

            WP_User_Frontend::log( 'payment', 'inserting payment to database. ' . print_r( $payment_data, true ) );

            $transaction_id = wp_strip_all_tags( $transaction_id );
            $is_recurring   = false;

            if ( isset( $data['post_data']['recurring_pay'] ) && $data['post_data']['recurring_pay'] === 'yes' ) {
                $is_recurring = true;
            }

            // WPUF_Payment::insert_payment( $payment_data, $transaction_id, $is_recurring );

            $coupon_id = isset( $data['post_data']['coupon_id'] ) ? $data['post_data']['coupon_id'] : '';

            if ( ! empty( $coupon_id ) ) {
                $pre_usage = get_post_meta( $coupon_id, '_coupon_used', true );
                $pre_usage = ( empty( $pre_usage ) ) ? 0 : $pre_usage;
                $new_use   = $pre_usage + 1;

                update_post_meta( $coupon_id, '_coupon_used', $new_use );
            }

            delete_user_meta( $user_id, '_wpuf_user_active' );
            delete_user_meta( $user_id, '_wpuf_activation_key' );
        } else {
            WP_User_Frontend::log( 'payment', 'inserting payment failed.' );
        }

        wp_redirect( $return_url );
        exit();
    }

    /**
     * Check for PayPal IPN Response.
     */
    public function check_response() {
        if ( isset( $_GET['action'] ) && $_GET['action'] === 'wpuf_stripe_payment_response' ) {
            do_action( 'wpuf_stripe_ipn_response' );
        }
    }

    /**
     * Check payment response
     *
     * @since  3.1.9
     */
    public function update_transaction_status() {
        if ( isset( $_GET['action'] ) && $_GET['action'] === 'wpuf_stripe_payment_response' ) {
            $payload = @file_get_contents( 'php://input' );
            $event   = null;
            //phpcs:ignore
            $sig_header      = ! empty( $_SERVER['HTTP_STRIPE_SIGNATURE'] ) ? $_SERVER['HTTP_STRIPE_SIGNATURE'] : '';
            $payment_option  = get_option( 'wpuf_payment' );
            $endpoint_secret = $payment_option && ! empty( $payment_option['stripe_signing_secret'] ) ? $payment_option['stripe_signing_secret'] : null;
            try {
                \Stripe\Webhook::constructEvent(
                    $payload, $sig_header, $endpoint_secret
                );
            } catch ( \UnexpectedValueException $e ) {
                WP_User_Frontend::log( 'Invalid Stripe', 'Invalid stripe value' );
                http_response_code( 400 );
                exit();
            } catch ( \Stripe\Exception\SignatureVerificationException $e ) {
                WP_User_Frontend::log( 'Invalid Stripe', 'Invalid stripe signature' );
                http_response_code( 400 );
                exit();
            }

            $this->configure_stripe();

            try {
                $event = \Stripe\Event::constructFrom(
                    json_decode( $payload, true )
                );
            } catch ( UnexpectedValueException $e ) {
                $message = $e->getMessage();
                WP_User_Frontend::log( 'payment', $message );

                // Invalid payload
                http_response_code( 400 );
                exit();
            }

            // Handle the event
            switch ( $event->type ) {
                case 'payment_intent.succeeded':
                    $payment_intent = $event->data->object; // contains a \Stripe\PaymentIntent
                    $this->handle_payment_intent_succeeded( $payment_intent );
                    break;
                case 'customer.subscription.created':
                    $object = $event->data->object;
                    $this->handleUserTrialSubscription( $object );
                    break;
                case 'customer.subscription.deleted':
                    $object = $event->data->object;
                    $this->cancelUserSubscription( $object );
                    break;
                case 'invoice.payment_failed':
                    $failed_invoice = $event->data->object; // contains a \Stripe\PaymentIntent
                    $this->handle_failed_payment( $failed_invoice );
                    break;
                // ... handle other event types
                default:
                    // Unexpected event type
                    http_response_code( 400 );
                    exit();
            }

            http_response_code( 200 );
        }
    }

    /**
     * Cancel trial subscription from user profile
     *
     * @since  3.1.9
     */
    public function handleUserTrialSubscription( $object ) {
        // handle trial payment subscription status
        $status     = $object->status;
        $invoice_id = $object->latest_invoice;

        if ( ! empty( $invoice_id ) && $status === 'trialing' ) {
            $this->update_trial_transaction( $object );
        }
    }

    /**
     * Cancel subscription from user profile
     *
     * @since  3.1.9
     */
    public function cancelUserSubscription( $object ) {
        global $wpdb;
        $stripe_customer_id = $object->customer;
        //check if it's already there
        $sql = $wpdb->prepare(
            'SELECT *
                FROM ' . $wpdb->prefix . "usermeta
                WHERE meta_key = '_wpuf_stripe_customer_id' AND meta_value LIKE %s LIMIT 1", '%' . $stripe_customer_id .
            '%'
        );

        $result = $wpdb->get_row( $sql );

        if ( empty( $result ) ) {
            return;
        }

        $data = [];
        $data['user_id'] = $result->user_id;

        if ( ! empty( $data['user_id'] ) ) {
            $this->handle_cancel_subscription( $data );
        }
    }

    /**
     * Handle webhook payment intent succeeded response
     *
     * @since  3.1.9
     */
    public function handle_payment_intent_succeeded( $payment_intent ) {
        $transaction_id = $payment_intent->id;
        $invoice_id     = $payment_intent->invoice;
        $is_recurring   = false;
        $subtotal       = $this->handle_decimal( strtoupper( $payment_intent->currency ), $payment_intent->amount, false );
        //If Recurring pull metadata
        if ( $invoice_id ) {
            $this->configure_stripe();
            $invoice      = \Stripe\Invoice::retrieve( $invoice_id );
            $sub_meta     = $invoice->lines['data'][0]['metadata'];
            $is_recurring = true;

            $user_id    = isset( $sub_meta->user_id ) ? (int) $sub_meta->user_id : '';
            $user_email = isset( $sub_meta->email ) ? $sub_meta->email : '';
            $user_name  = isset( $sub_meta->user_name ) ? $sub_meta->user_name : '';
            $tax_rate   = isset( $sub_meta->tax_rate ) ? (float) $sub_meta->tax_rate : 0;
            $pack_id    = isset( $sub_meta->pack_id ) ? (int) $sub_meta->pack_id : '';
        } else {
            $post_meta  = $payment_intent->metadata;
            $user_id    = ! empty( $post_meta['user_id'] ) ? (int) $post_meta['user_id'] : 0;
            $user_email = ! empty( $post_meta['email'] ) ? $post_meta['email'] : 0;

            $user_name = ! empty( $post_meta['user_name'] ) ? $post_meta['user_name'] : 0;

            $tax_rate   = ! empty( $post_meta['tax_rate'] ) ? (float) $post_meta['tax_rate'] : 0;
            $post_id    = ! empty( $post_meta['post_id'] ) ? (int) $post_meta['post_id'] : 0;
            $pack_id    = ! empty( $post_meta['pack_id'] ) ? (int) $post_meta['pack_id'] : 0;
        }

        if ( ! $user_id ) {
            $user = get_users(
                [
                    'meta_key'    => '_wpuf_stripe_customer_id',
                    'meta_value'  => $payment_intent->customer,
                    'number'      => 1,
                    'count_total' => false,
                ]
            );

            if ( empty( $user ) ) {
                WP_User_Frontend::log( 'user not exist', 'user does not exist on database' );
                return;
            }
        }

        $user_id    = $user_id ? $user_id : $user[0]->ID;
        $tax_rate   = $tax_rate ? $tax_rate : get_user_meta( $user_id, '_wpuf_tax_rate', true );
        $tax_amount = 0;

        if ( $tax_rate ) {
            $tax_amount = $subtotal * $tax_rate / ( 100 + $tax_rate );
        }

        if ( ! empty( $transaction_id ) ) {
            //create transaction data here
            $data = [
                'user_id'          => (int) $user_id,
                'status'           => 'completed',
                'subtotal'         => (float) $subtotal,
                'tax'              => $tax_amount,
                'cost'             => $subtotal - $tax_amount,
                'post_id'          => isset( $post_id ) ? $post_id : '',
                'pack_id'          => isset( $pack_id ) ? $pack_id : '',
                'payer_first_name' => $user_name,
                'payer_last_name'  => '',
                'payer_email'      => $user_email,
                'payment_type'     => 'Stripe',
                'payer_address'    => $payment_intent['billing_details']['address']['country'],
                'transaction_id'   => $transaction_id,
                'created'          => current_time( 'mysql' ),
            ];

            WPUF_Payment::insert_payment( $data, $transaction_id, $is_recurring );
            //            $this->updateStatus( $transaction_id, 'completed' );
        }
    }

    /**
     * Update status
     *
     * @since  3.1.9
     */
    public function updateStatus( $transaction_id, $status ) {
        global $wpdb;

        //check if it's already there
        $sql    = $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'wpuf_transaction WHERE transaction_id = %s LIMIT 1', $transaction_id );
        $result = $wpdb->get_row( $sql );

        $wpdb->update( $wpdb->prefix . 'wpuf_transaction', array( 'status' => $status ), array( 'transaction_id' => $transaction_id ) );

        if ( ! empty( $result ) ) {
            if ( isset( $result->pack_id ) && ! empty( $result->pack_id ) ) {
                $wpdb->update( $wpdb->prefix . 'wpuf_subscribers', array( 'subscribtion_status' => $status ), array( 'transaction_id' => $transaction_id ) );

                $user_pack_meta         = '_wpuf_subscription_pack';
                $pack_details           = get_user_meta( $result->user_id, $user_pack_meta, true );
                $pack_details['status'] = $status;

                if ( ! empty( $pack_details ) ) {
                    update_user_meta( $result->user_id, $user_pack_meta, $pack_details );
                }
            }
        }
    }

    /**
     * Handle the cancel subscription
     *
     * @return void
     *
     * @since  0.1
     */
    public function handle_cancel_subscription( $data ) {
        $sub_meta = 'cancel';
        $user_id  = $data['user_id'];
        if ( empty( $user_id ) ) {
            $user_id = get_current_user_id();
        }
        $sub_info = get_user_meta( $user_id, '_wpuf_subscription_pack', true );

        if ( 'yes' === $sub_info['recurring'] ) {
            // Cancel subscription from stripe end if recurring
            $this->configure_stripe();

            $customer_id     = get_user_meta( $user_id, '_wpuf_stripe_customer_id', true );
            $customer        = \Stripe\Customer::retrieve( $customer_id );
            if ( ! $customer->deleted && ( isset( $customer->subscriptions ) && ! empty(
                $customer->subscriptions->data
            ) ) ) {
                $subscription_id = $customer->subscriptions->data[0]->id;
                $subscription    = \Stripe\Subscription::retrieve( $subscription_id );
                $subscription->cancel();
            }
        }
        delete_user_meta( $user_id, '_wpuf_stripe_customer_id' );
        WPUF_Subscription::init()->update_user_subscription_meta( $user_id, $sub_meta );
    }

    /**
     * Check if two urls are same
     *
     * @param $url1
     * @param $url2
     *
     * @return bool
     */
    public static function check_urls_same( $url1, $url2 ) {
        $must_match = array_flip( [ 'host', 'port', 'path' ] );
        $defaults  = [ 'path' => '/' ]; // if not present, assume these
        $url1      = array_intersect_key( parse_url( $url1 ), $must_match ) + $defaults;
        $url2      = array_intersect_key( parse_url( $url2 ), $must_match ) + $defaults;

        return $url1 === $url2;
    }

    /**
     * Nullify price data if trail avail
     *
     * @since 3.4.7
     *
     * @param $invoice_id
     */
    protected function update_trial_transaction( $object ) {
        $metadata     = $object->metadata;
        $invoice      = ! is_null( $object->latest_invoice ) ? $object->latest_invoice : '';
        $is_recurring = $invoice ? true : false;

        $user_id    = isset( $metadata->user_id ) ? (int) $metadata->user_id : '';
        $user_email = isset( $metadata->email ) ? $metadata->email : '';
        $user_name  = isset( $metadata->user_name ) ? $metadata->user_name : '';
        $tax_rate   = isset( $metadata->tax_rate ) ? (float) $metadata->tax_rate : 0;
        $pack_id    = isset( $metadata->pack_id ) ? (int) $metadata->pack_id : '';

        if ( ! $user_id ) {
            $user = get_users(
                [
                    'meta_key'    => '_wpuf_stripe_customer_id',
                    'meta_value'  => $object->customer,
                    'number'      => 1,
                    'count_total' => false,
                ]
            );

            if ( empty( $user ) ) {
                WP_User_Frontend::log( 'user not exist', 'user does not exist on database' );
                return;
            }
        }

        $user_id    = $user_id ? $user_id : $user[0]->ID;
        $subtotal   = 0;
        $tax_amount = 0;

        //create transaction data here
        $data = [
            'user_id'          => (int) $user_id,
            'status'           => 'completed',
            'subtotal'         => (float) $subtotal,
            'tax'              => $tax_amount,
            'cost'             => $subtotal - $tax_amount,
            'post_id'          => isset( $post_id ) ? $post_id : '',
            'pack_id'          => isset( $pack_id ) ? $pack_id : '',
            'payer_first_name' => $user_name,
            'payer_last_name'  => '',
            'payer_email'      => $user_email,
            'payment_type'     => 'Stripe',
            'payer_address'    => $object['billing_details']['address']['country'],
            'transaction_id'   => $invoice,
            'created'          => current_time( 'mysql' ),
        ];

        WPUF_Payment::insert_payment( $data, $invoice, $is_recurring );
    }

    /**
     * Cancel if failed at user defined times
     *
     * @param $failed_invoice
     */
    public function handle_failed_payment( $failed_invoice ) {
        $count = absint( wpuf_get_option( 'failed_retry', 'wpuf_payment' ) );
        if ( $count === (int) $failed_invoice->attempt_count ) {
            $this->cancelUserSubscription( $failed_invoice );
        }
    }

    /**
     * Handle currency convertion for non decimal currency
     *
     * @param string $currency
     * @param int $amount
     * @param bool $in // decides either into gateway or from gateway
     *
     * @return int
     */
    private function handle_decimal( $currency = 'JPY', $amount = 0, $in = true ) {
        //non decimal currencies
        $zero_dec = [ 'BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'RWF', 'UGX', 'VND', 'VUV', 'XAF', 'XOF', 'XPF' ];

        if ( ! in_array( $currency, $zero_dec, true ) && $in ) {
            return intval( floatval( $amount ) * 100 );
        }

        if ( ! in_array( $currency, $zero_dec, true ) && ! $in ) {
            return intval( floatval( $amount ) / 100 );
        }

        return intval( $amount );
    }
}

new WPUF_Gateway_Stripe();
