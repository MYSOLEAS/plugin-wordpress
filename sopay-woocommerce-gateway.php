<?php

/**
 *
 * Plugin Name:       Sopay Payment Gateway for WooCommerce
 * Plugin URI:        
 * Description:       Sopay Payment Gateway for WooCommerce allows you to easily integrate the SoleasPay online payment platform into your WooCommerce store. It provides your customers with the ability to carry out financial transactions easily, securely and conveniently. Using this plugin, you can offer multiple payment options such as credit cards, Orange Money, PayPal, and many others.
 * Version:           1.0
 * Author:            Mysoleas
 * Author URI:        https://www.mysoleas.com
 * License:           GPLv2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       sopay
 */

if(!defined('ABSPATH')) exit;

/**
 * Require the WooCommerce Payment Gateway class
 */
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    return;
}

/**
 * Add callback function to Hook on Woocommerce
 *
 * @param array $methods
 * @return array
 */
function Sopay_Add_To_Woocommerce(array $methods): array
{
    $methods[] = 'Sopay_Woocommerce_Gateway';
    return $methods;
}
add_filter('woocommerce_payment_gateways', 'Sopay_Add_To_Woocommerce');

/**
 * return callback function to WooCommerce
 */
function init_sopay_gateway ()
{
    class Sopay_Woocommerce_gateway extends WC_Payment_Gateway {

        const ID = "sopay";

        /**
         * const API URI for SoPay Payment
         */
        const API_URI = 'https://checkout.soleaspay.com';

        /**
         * @var string
         */
        private string $apiKey;

        /**
         * @var string
         */
        private string $shopName;

        public function __construct()
        {
            /*
             *Unique ID for gateway
            */
            $this->id = self::ID;
              
              $image_url = $this->get_image_url();
              if (@getimagesize($image_url)) {
                  $this->icon = $image_url;
              }
            $this->has_fields = true;
            $this->title = 'SoPay';
            $this->method_title = __('SoPay Payment Gateway for Woocommerce', $this->id);
            $this->method_description = __('SoPay Payment Gateway for WooCommerce is a plugin that allows you to sell wherever your customers are.
            Offer your customers an intuitive payment experience and let them pay ou the way they want by
            Orange Money, MTN Mobile Money, Express Union, VISA, PayPal, MasterCard, Perfect Money or bitCoin', $this->id);

            /* 
             *Initialize the payment gateway settings
             */
            $this->init_form_fields();
            $this->init_settings();

            /*
            * Define the settings
            */
            $this->order_button_text = $this->get_option('order_button_text');
            $this->description = $this->get_option( 'description' );
            $this->apiKey = $this->get_option( 'apiKey' );
            $this->shopName = $this->get_option( 'shopName' );

            /*
            * Save settings to display in admin
            */
            add_action('woocommerce_update_options_payment_gateways_'.$this->id, [$this, 'process_admin_options']);
            add_action('woocommerce_after_checkout_form', [$this, 'sopay_payment_script']);
            add_action('woocommerce_thankyou', [$this, 'sopay_thankyou_page']);
        }

        /**
         * Image to be displayed to the user
         */
        private function get_image_url(): string
        {
            $image = 'sopay.png';
            return WP_PLUGIN_URL . '/' . plugin_basename(dirname(__FILE__)) . '/images/' . $image;
        }

        /**
         * initialise form_field data
         */
        public function init_form_fields()
        {
            $this->form_fields = [
                'enabled' => [
                    'title'   => __('Enable/Disable', $this->id),
                    'type'    => 'checkbox',
                    'label'   => 'Enable SoPay Gateway',
                    'description' => 'Enable or Disable SoPay Payment',
                    'default' => 'yes',
                ],
                'apiKey' => [
                    'title' => __('API KEY', $this->id),
                    'type' => 'text',
                    'description' => __('Copy and place the ApiKey SoPay for Have Acces to use this Payment. If you have not the ApiKey, please contact the administrator', $this->id),
                    'default' => $this->get_option('apiKey', ''),
                    'desc_tip' => true,
                ],
                'order_button_text' => [
                    'title' => __('Name of button', $this->id),
                    'type' => 'text',
                    'description' => __('Name of the button... ', $this->id),
                    'default' => __("Pay With SoleasPay", $this->id),
                    'desc_tip' => true,
                ],
                'shopName' => [
                    'title' => __('Name of Your Business', $this->id),
                    'type' => 'text',                                            
                    'description' => __('State your business', $this->id),
                    'default' => __("Mysoleas payment App", $this->id),
                    'desc_tip' => true,
                ],
                'currency' => [
                    'title' => __('Sopay currency', $this->id),
                    'type' => 'select',
                    'description' => __('This is the current bill to start a transaction', $this->id),
                    'default' => "XAF",
                    'options' => [
                        "XAF" => __("Franc CFA (FCFA)", $this->id),
                        "EUR" => __("Euro (€)", $this->id),
                        "USD" => __("Dollar ($)", $this->id),
                    ],
                    'desc_tip' => true,
                ],
                'description' => [
                    'title' => __('Description', $this->id),
                    'type' => 'textarea',
                    'description' => __('Payment method description, visible by customers on your checkout page', $this->id),
                    'default' => __('Pay safely using Orange Money, MTN Mobile Money, PayPal, Perfect Money, master Cart, VISA or BitCoin', $this->id),
                    'desc_tip' => true,
                ],
            ];
        }

        public function get_apiKey(): string
        {
            return $this->apiKey;
        }

        public function get_chopName(): string
        {
            return $this->shopName;
        }

        /**
         * Returns user's locale
         */
        public function get_locale(): string
        {
            $lang = get_language_attributes();
            $lang = str_replace('"', '', $lang);
            return substr($lang, 5, 2);
        }

        /**
         * Generate response form to send on the checkout page
         * @param array $content
         * @return string
         */
        private function sopay_generate_form(array $content): string
        {
            $action = self::API_URI;
            $html = "<div class='sopay_fragment_form'>";
            $html .= "<form name='sopay_data_form' id='sopay_data_form' method='post' action='{$action}'>";
            foreach ($content as $name => $value) {
                $html .= "<input type='hidden' name='{$name}' value='{$value}' readonly>";
            }
            $html.= "</form>";
            $script = "<script>document.getElementById('sopay_data_form').submit()</script>";
            $html .= $script;
            $html .= "</div>";
            return $html;
        }

        /**
         * Generate script to receive response request in checkout page
         * @return string
         */
        private function sopay_generate_scripts(): string
        {
            $script ='<script type="text/javascript" defer>
                    jQuery(document).ready(function ($){
                        $(document).ajaxComplete(function (e, xhr, settings){
                            if(settings.url === wc_checkout_params.checkout_url){
                                const responseData = xhr.responseJSON;
                                const name = "'."{$this->id}_response_data".'";
                                if(responseData.result === "success" && responseData[name] !== undefined){
                                    $("form.checkout").before(responseData[name]);
                                }
                            }
                        })
                    })
                </script>';
           return strip_tags($script);
        }

        public function process_payment($order_id): array
        {
            $order = wc_get_order($order_id);
            try{
                $success_url = add_query_arg('status', 'success', $order->get_checkout_order_received_url());
                $failure_url = add_query_arg('status', 'failed', $order->get_checkout_order_received_url());
                $options = [
                    'apiKey' => $this->get_apiKey(),
                    'amount' => $order->get_total(),
                    'currency' => $this->get_option('currency'),
                    'description' => $this->description,
                    'orderId' => $order->get_order_key(),
                    'successUrl' => $success_url,
                    'failureUrl' => $failure_url,
                    'shopName' => $this->get_chopName(),
                ];
                $request = wp_remote_post(self::API_URI, [
                    'body'        => json_encode($options),
                    'timeout'     => '45',
                    "sslverify" => false,
                    'headers'     => ['Content-Type' => 'application/json'],
                    'cookies'     => [],
                ]);

                if(is_wp_error($request) || wp_remote_retrieve_response_code($request) !== 200)
                    throw new Exception("An error has occurred. Please try again later");

                $html = $this->sopay_generate_form($options);
                $order->payment_complete();

                /** 
                 *Clear the cart
                 */
                WC()->cart->empty_cart();

                /**
                 * Return the Response from page redirect URL
                */
                return [
                    "result" => "success",
                    "redirect" => wc_get_checkout_url(),
                    "{$this->id}_response_data" => $html,
                ];
            } catch (Exception $ex) {
                $order->add_order_note("SoPay payment init failed with message: " . $ex->getMessage());
                wc_add_notice(__('Payment error : ', 'woothemes') . $ex->getMessage(), 'error');

                return[
                    'result' => 'failure',
                    'redirect' => '',
                ];
            }
        }

        public function sopay_payment_script()
        {
            if($this->enabled === 'no')
                return;

            if (is_checkout()){ 
                $script = '<script type="text/javascript" defer>
                jQuery(document).ready(function ($){
                    $(document).ajaxComplete(function (e, xhr, settings){
                        if(settings.url === wc_checkout_params.checkout_url){
                            const responseData = xhr.responseJSON
                            const name = "'."{$this->id}_response_data".'"
                            if(responseData.result === "success" && responseData[name] !== undefined){
                                $("form.checkout").before(responseData[name])
                            }
                        }
                    })
                })
            </script>';

                /**
                 * compile code with wc-js-function
                */
                wc_enqueue_js($this->sopay_generate_scripts());
            }
        }

        public function sopay_thankyou_page($order_id)
        {
            $order = wc_get_order($order_id);
            if(isset($_GET['status']) && !empty($_GET['status'])) {
                $status = sanitize_text_field($_GET['status']);
                if ($status === 'success') {
                    $order->update_status('completed', __('Payment successful', $this->id));
                } else{
                    $order->update_status('failed', __('Payment failed', $this->id));
                }
                wp_redirect($order->get_checkout_order_received_url(), 301);
            }
        }
    }
}

/**
 * load the Plugin in last Position
 */
add_action('plugins_loaded', 'init_sopay_gateway');