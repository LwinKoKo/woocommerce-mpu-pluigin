<?php

class WC_mpu_Validation_Helper{

	public $wc_mpu_error = array(		    
		"payment_description" 	=> "",
		"order_id" 				=> "",			
		"amount" 				=> "",
		"customer_email"		=> "",
		);

	function __construct() { }

	function wc_mpu_is_valid_merchant_request($parameter){

		if(empty($parameter['order_id'])){
			$this->wc_mpu_error['order_id'] = "Order id cannot be blank.";
		}
		if(empty($parameter['payment_description'])){
			$this->wc_mpu_error['payment_description'] = "Payment Description cannot be blank.";
		}
		if(empty($parameter['amount'])){
			$this->wc_mpu_error['amount'] = "Amount cannot be blank.";
		}
		if(!empty($parameter['order_id'])){		
			if(strlen($parameter['order_id']) > 20){
				$this->wc_mpu_error['order_id'] = "Order id is limited to 20 character.";
			}
		}
		if(!empty($parameter['amount'])){
			if($parameter['amount'] <= 0){
				$this->wc_mpu_error['amount'] = "Amount must be greater than 0.";
			}
		}
		if(!empty($parameter['amount'])){
			if(strlen($parameter['amount']) > 12){
				$this->wc_mpu_error['amount'] = "Amount is limited to 12 character.";
			}
			else{
				//Calculate currency by methods.
				if(!is_numeric($parameter['amount'])){
					$this->wc_mpu_error['amount'] = "Please enter amount is digit's.";
				}				
			}
		}

		if(!is_email($parameter['customer_email'])){
			$this->wc_mpu_error['amount'] = "Please enter valid email address.";
		}

		foreach ($this->wc_mpu_error as $key => $value) {
			if(!empty($value)){
				return false;
			}
		}
		return true;
	}
	//This function is used to validate the CurrencyExponent.
	public function wc_mpu_validate_currency_exponent($amount){

		$objWC_mpu_currency = new WC_mpu_currency();

		$exponent = 0;
		$isFouned = false;

		$currenyCode = get_option('woocommerce_currency');
		$currencyCodeArray = $objWC_mpu_currency->get_currency_code();

		foreach ($currencyCodeArray as $key => $value) {
			if($key === $currenyCode){				
				$exponent = $value['exponent'];
				$isFouned = true;
				break;
			}
		}

		if($isFouned){
			if($exponent == 0 || empty($exponent)){
				$amount = (int) $amount;
			}
			else{		
				$pg_mpu_exponent = $objWC_mpu_currency->get_currency_exponent();
				$multi_value = $pg_mpu_exponent[$exponent];
				$amount = ($amount * $multi_value);
			}

			$amount = str_pad($amount, 12, '0', STR_PAD_LEFT);
		}		
		return $amount;
	}
}

?>