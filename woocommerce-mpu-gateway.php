<?php
/*
 * Plugin Name: WooCommerce MPU Payment Gateway
 * Plugin URI: https://mtg.com.mm
 * Description: MPU Payment Gateway By MTG
 * Author: MTG
 * Author URI: https://mtg.com.mm
 * Version: 1.0.1
 *
 
 /*
 * This action hook registers our PHP class as a WooCommerce payment gateway
 */
add_filter( 'woocommerce_payment_gateways', 'add_mpu_gateway_class' );
function add_mpu_gateway_class( $gateways ) {
	$gateways[] = 'WC_MPU_Gateway'; // your class name is here
	return $gateways;
}
 
/*
 * The class itself, please note that it is inside plugins_loaded action hook
 */
add_action( 'plugins_loaded', 'init_mpu_gateway_class' );

function init_mpu_gateway_class() {
	
	class AES extends WC_Payment_Gateway{
		
		protected $cipher;
        protected $mode;
        protected $pad_method;
        protected $secret_key;
        protected $iv;
     
        public function __construct($key, $method = 'pkcs7', $iv = '', $mode = MCRYPT_MODE_ECB, $cipher = MCRYPT_RIJNDAEL_128)
        {
            $this->secret_key = $key;
            $this->pad_method =$method;
            $this->iv = $iv;
            $this->mode = $mode;
            $this->cipher = $cipher;
        }
     
        protected function pad_or_unpad($str, $ext)
        {
            if (!is_null($this->pad_method)) {
                $func_name = __CLASS__ . '::' . $this->pad_method . '_' . $ext . 'pad';
                if (is_callable($func_name)) {
                    $size = mcrypt_get_block_size($this->cipher, $this->mode);
                    return call_user_func($func_name, $str, $size);
                }
            }
            return $str;
        }
     
        protected function pad($str)
        {
            return $this->pad_or_unpad($str, '');
        }
     
        protected function unpad($str)
        {
            return $this->pad_or_unpad($str, 'un');
        }
     
        public function encrypt($str)
        {
            $str = $this->pad($str);
            $td = mcrypt_module_open($this->cipher, '', $this->mode, '');
            if (empty($this->iv)) {
                $iv = @mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
            } else {
                $iv = $this->iv;
            }
            
            mcrypt_generic_init($td, $this->secret_key, $iv);
            $cyper_text = mcrypt_generic($td, $str);
            $rt = base64_encode($cyper_text);
            mcrypt_generic_deinit($td);
            mcrypt_module_close($td);
            return $rt;
        }

    	function decrypt($ciphertext) {
    		$chiperRaw = base64_decode($ciphertext);
    		$originalData = openssl_decrypt($chiperRaw, 'AES-256-ECB', $this->secret_key, OPENSSL_RAW_DATA);
    		return $originalData;
    	}
     
        public static function pkcs7_pad($text, $blocksize)
        {
            $pad = $blocksize - (strlen($text) % $blocksize);
            return $text . str_repeat(chr($pad), $pad);
        }
     
        public static function pkcs7_unpad($text)
        {
            $pad = ord($text[strlen($text) - 1]);
            if ($pad > strlen($text)) return false;
            if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) return false;
            return substr($text, 0, -1 * $pad);
        }
	}
 
	class WC_MPU_Gateway extends WC_Payment_Gateway {
 
 		/**
 		 * Class constructor, more about it in Step 3
 		 */
 		public function __construct() {
 
			$this->id = 'mpu'; // payment gateway plugin ID
			$this->icon = ''; // URL of the icon that will be displayed on checkout page near your gateway name
			$this->has_fields = true; // in case you need a custom credit card form
			$this->method_title = 'MPU Payment Gateway';
			$this->method_description = 'MPU Payment Gateway By MTG'; // will be displayed on the options page
		 
			// gateways can support subscriptions, refunds, saved payment methods,
			// but in this tutorial we begin with simple payments
			$this->supports = array(
				'products'
			);
		 
			// Method with all the options fields
			$this->init_form_fields();
		 
			// Load the settings.
			$this->init_settings();
			$this->title = $this->get_option( 'title' );
			$this->description = $this->get_option( 'description' );
			$this->enabled = $this->get_option( 'enabled' );
			$this->company_id = $this->get_option( 'company_id' );
			$this->phone_no = $this->get_option( 'phone_no' );
			$this->testmode = 'yes' === $this->get_option( 'testmode' );
		 
			// This action hook saves the settings
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		 
			// You can also register a webhook here
			add_action( 'woocommerce_api_mpu_payment_complete', array( $this, 'webhook' ) );
			
			//Redirect to reciepe page
			add_action('woocommerce_receipt_mpu', array( $this,'receipt_page' ) );
		}
 
		/**
 		 * Plugin options, we deal with it in Step 3 too
 		 */
 		public function init_form_fields(){
 
			$this->form_fields = array(
				'enabled' => array(
					'title'   => __( 'Enable/Disable', 'wc-mpu-gateway' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable MPU Payment', 'wc-mpu-gateway' ),
					'default' => 'yes',
				),
				'title' => array(
					'title'       => __( 'Title', 'wc-mpu-gateway' ),
					'type'        => 'text',
					'description' => __( 'This controls the title for the payment method the customer sees during checkout.', 'wc-gateway-offline' ),
					'default'     => __( 'MPU Payment', 'wc-mpu-gateway' ),
					'desc_tip'    => true,
				),
				'description' => array(
					'title'       => __( 'Description', 'wc-mpu-gateway' ),
					'type'        => 'textarea',
					'description' => __( 'This controls the title which the user sees during checkout.', 'wc-mpu-gateway' ),
					'default'     => __( 'Pay Securely by MPU Payment Gateway', 'wc-mpu-gateway' ),
					'desc_tip'    => true,
				),
				'mpss_api_request' => array(
					'title'       => __( 'API Request', 'woocommerce' ),
					'type'        => 'title',
					'description' => '',
				),
				'company_id' => array(
					'title' => __('Company ID', 'wc-mpu-gateway'),
					'type' => 'text',
					'description' => 'The company id which is provided by MPSS payment gateway',
					'desc_tip' => true
				),
				'phone_no' => array(
					'title' => __('Phone No', 'wc-mpu-gateway'),
					'type' => 'text',
					'description' => 'The company phone number registered in MPSS payment gateway(optional)',
					'desc_tip' => true
				),
				'testmode' => array(
					'title'       => 'Sandbox',
					'label'       => 'Enable Sandbox Mode',
					'type'        => 'checkbox',
					'description' => 'Place the mpu payment gateway in sandbox mode.',
					'default'     => 'yes',
					'desc_tip'    => true,
				)
			);
		}
 
		/**
		 * You will need it if you want your custom credit card form, Step 4 is about it
		 */
		public function payment_fields() {
 
			// ok, let's display some description before the payment form
			if ( $this->description ) {
				// you can instructions for test mode, I mean test card numbers etc.
				if ( $this->testmode ) {
					$this->description  = ' TEST MODE ENABLED. In test mode, please do not use your real personal information.</a>.';
					$this->description  = trim( $this->description );
				}
				// display the description with <p> tags etc.
				echo wpautop( wp_kses_post( $this->description ) );
			}
		 
		}
 
		/*
 		 * Fields validation, more in Step 5
		 */
		public function validate_fields(){
 
			if( empty( $_POST[ 'billing_first_name' ]) ) {
				wc_add_notice(  'First name is required!', 'error' );
				return false;
			}
			return true;
		 
		}
 
		/*
		 * Processing the payments and redirect to the receipt page
		 */
		public function process_payment( $order_id ) {
 
			global $woocommerce;
			
			$order = new WC_Order($order_id);
			
			if (version_compare(WOOCOMMERCE_VERSION, '2.1.0', '>=')) { // For WC 2.1.0
                $checkout_payment_url = $order->get_checkout_payment_url(true);
            } else {
                $checkout_payment_url = get_permalink(get_option('woocommerce_pay_page_id'));
            }
            
            return array('result' => 'success','redirect' => add_query_arg('order', $order->id, add_query_arg('key', $order->order_key, $checkout_payment_url)));
		 
		}
		
		/* Receipt Page */
        function receipt_page($order) {            
            echo $this->generate_mpu_form($order);
        }
        
        /* Generate button link */
        function generate_mpu_form($order_id) {   

			session_start();
 
			if(isset($_SESSION['count'])){
			  $_SESSION['count'] = $_SESSION['count']+ 1;
			}else{
			  $_SESSION['count'] = 1;
			}
			
			global $woocommerce;
            $order = new WC_Order( $order_id );
			
			$aes = new AES('n6atpjz75hw213yfldg80ocreb9vukiq'); //key //‘transactionid|amount|companyId|paymenttype’
			
            $redirect_url=$this->get_return_url( $order );
			
			// we need it to get any order detailes
			$service_provider = 'MPU';
			$order = wc_get_order( $order_id );
			$amount = $order->order_total;
			$companyId = $this->company_id;
			$transactionid = '0MPUMTG00'.$order_id.$_SESSION['count'];
			$encryptedString = $aes->encrypt($transactionid.'|'.$amount.'|'.$companyId.'|'.$service_provider);
			$phonenumber = $this->phone_no;
			
			if($this->testmode){
				$sandbox = 'ON';
				$url  = 'https://122.248.120.252:60145/UAT/Payment/Payment/pay?';
			}
			else{
				$sandbox = 'GLVE';
				$url  = 'https://www.mpu-ecommerce.com/Payment/Payment/pay?';
			}
			
			$body = array(
				'paymenttype' => $service_provider,
				'encryptedString' => $encryptedString,
				'amount' => $amount,
				'companyId' => $companyId,
				'transactionid' => $transactionid,
				'phoneNo' => $phonenumber,
				'serviceData' => 'Payment',
				'sandbox' => $sandbox,
			);
			
			$result = json_decode($this->httpPost('http://paymentuat.mmpaymal.com/payment/paymentapi', $body, 'json'));
			$decrypted_value = $aes->decrypt($result->encryptedString);
			$mpu = json_decode($decrypted_value);
		
			$mpu_url = array(
				'currencyCode' => $mpu->currencyCode,
				'merchantID' => $mpu->merchant,
				'invoiceNo' => $mpu->invoiceNo,
				'productDesc' => $mpu->productDesc,
				'userDefined1' => $mpu->userDefined1,
				'userDefined2' => $mpu->userDefined2,
				'userDefined3' => $mpu->userDefined3,
				'amount' => $mpu->amount,
				'hashValue' => $mpu->hashValue,
			);
            
            $strHtml = '';
			$strHtml .= '<form action="' . esc_url($url) . '" method="post" id="form">';
            
			foreach($mpu_url as $mk => $mv) {
				$strHtml .= '<input type="hidden" name="'.$mk.'" value="'.$mv.'" />';
			}
            $strHtml .= '<p><strong>Thank you for your order.</strong> <br/>This will redirect to MPU payment page if you click on button "Pay with MPU".</p>';
			$strHtml .= '<input type="submit" class="button-alt" id="submit" value="Pay with MPU" />';
            $strHtml .= '&nbsp;&nbsp;&nbsp;&nbsp<a class="button cancel" href="' . esc_url($order->get_cancel_order_url()) . '">Cancel order &amp; restore cart</a>';
            $strHtml .= '</form>';
			
            return $strHtml;
        }
		
		function httpPost($url, $data){
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_POST, true);
        	curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        	curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($curl);
            curl_close($curl);
            return $response;
        }
 
		/*
		 * In case you need a webhook, like PayPal IPN etc
		 */
		public function webhook() {
 
			$order = wc_get_order( $_GET['id'] );
			$order->payment_complete();
			$order->reduce_order_stock();
		 
			update_option('webhook_debug', $_GET);
		}
 	}
}
