<?php
/*
Plugin Name: 100Pay Checkout

Plugin URI: https://100pay.co/ 

Description: We power the payment/crypto banking infrastructure for developers/businesses to accept/process crypto payments at any scale.

Version: 1.0

Author: Chika Precious Benjamin 

Author URI: https://100pay.co/

License: GPLv2 or later 

Text Domain: 100pay

*/


add_action( 'woocommerce_payment_gateways', 'add_100Pay_gateway_class' );
function add_100Pay_gateway_class() {
    $gateways[] = 'WC_100Pay_Gateway';
    return $gateways;
}

// Generate Ref ID , Accepts Business Name
// function generate_ref($name) {
    
//     $name = strtoupper($name);

//     $date = date("Ymd");

//     $random_part = substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil(8/strlen($x)) )),1,8);

//     $ref_id = $name . '_' . $date . '_' . $random_part;

//     return $ref_id;
// }


add_action( 'plugins_loaded', 'init_100Pay_gateway_class');
function init_100Pay_gateway_class() {

    class WC_100Pay_Gateway extends WC_Payment_Gateway {

        public function __construct() {

            $this->id = 'pay100';
            $this->method_title = '100Pay Payment Gateway';
            $this->method_description = 'You can make your crypto payments using our payment gateway.';
            $this->method_icon = '';
            $this->method_version = '1.0';
            $this->supports = array(
                'products',
                'subscriptions'
            );

            $this->init_form_fields();
            $this->init_settings();

            $this->title = $this->get_option('title');
            $this->description = $this->get_option('description');
            $this->enabled = $this->get_option('enabled');

            // This action hook saves the settings
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
        }

        public function init_form_fields() {
            $this->form_fields = array(
                'enabled' => array(
                    'title'       => 'Enable/Disable',
                    'label'       => 'Enable 100Pay Payment Gateway',
                    'type'        => 'checkbox',
                    'description' => '',
                    'default'     => 'yes'
                ),
                'title' => array(
                    'title'       => '100Pay Payment Gateway',
                    'type'        => 'text',
                    'description' => 'Pay with 100Pay.',
                    'default'     => 'Pay With 100Pay',
                    'desc_tip'    => true,
                ),
                'business_name' => array(
                    'title'       => '100Pay Business Name',
                    'type'        => 'text',
                    'description' => '100Pay Business Name',
                    'default'     => 'Null',
                    'desc_tip'    => true,
                ),
                'description' => array(
                    'title'       => 'Description',
                    'type'        => 'textarea',
                    'description' => 'You can make crypto payment using our payment gateway.',
                    'default'     => 'You can make crypto payment using our payment gateway.',
                ),
                'public_key' => array(
                    'title'       => 'Public Key',
                    'type'        => 'text',
                    'description' => '100Pay Public Key',
                    'default'     => 'Null',
                ),
                'secret_key' => array(
                    'title'       => 'Secret Key',
                    'type'        => 'text',
                    'description' => '100Pay Secret Key',
                    'default'     => 'Null',
                ),
            );
        }

        public function process_payment( $order_id ) {
            $order = wc_get_order( $order_id );
            // if ( empty( $order ) ) {
            //     return new WP_Error('', __('','') );
            // }
            // $first_name = $order->get_first_name();
            // $last_name = $order->get_last_name();
            // $email = $order->get_email();
            // $country = $order->get_country();
            // $country_symbol = $order->get_country_symbol();
            // $currency = $order->get_currency();
            // $currency_symbol = $order->get_currency_symbol();
            // $amount = $order->get_amount();
            // $vat = $order->get_vat();

            // $api_key = get_option('secret_key');
            // $business_name = get_option('business_name');
            // $ref_id = generate_ref($business_name);

            // Use Script Tag to Execute code

            $order->payment_complete();
            $order->reduce_order_stock();

            $order->add_order_note( 'Hey, your order is paid! Thank You', true);

            WC()->cart->empty_cart();

            return array(
                'result' => 'success',
                'redirect' => $this->get_return_url( $order )
            );

        }
   
    }

    add_action( 'woocommerce_blocks_loaded', 'pay100_gateway_block_support' );
    function pay100_gateway_block_support() {
        require_once __DIR__ . '/includes/class-wc-100pay-gateway-blocks-support.php';

        add_action(
            'woocommerce_blocks_payment_method_type_registration',
            function( Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry ) {
                $payment_method_registry->register( new WC_100Pay_Gateway_Blocks_Support() );
            }
        );
    }

    add_action( 'before_woocommerce_init', 'pay100_cart_checkout_blocks_compatibility' );
    function pay100_cart_checkout_blocks_compatibility() {

        if( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
                    'cart_checkout_blocks',
                    __FILE__,
                    true // you can set it to false, if you want it to display to the user that the plugin is not compatible
                );
        }

    }
}

