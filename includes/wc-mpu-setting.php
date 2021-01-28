 <?php

return array(   
    
    'enabled' => array(
        'title'   => __( 'Enable/Disable', 'wc-mpu-gateway' ),
        'type'    => 'checkbox',
        'label'   => __( 'Enable MPU Payment', 'wc-mpu-gateway' ),
        'default' => 'yes'
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
    'test_mode' => array(
        'title' => __('Mode', 'woo_mpu'),
        'type' => 'select',
        'label' => __('mpu Tranasction Mode.', 'woo_mpu'),    
        'default' => 'test',
        'description' => __('Mode of mpu activities'),
        'desc_tip' => true,
        'class'        => 'wc-enhanced-select',
        'options' => array(
            'demo2' => 'Test Mode',
            't' => 'Live Mode'
        ),
    ),
    'wc_mpu_stored_card_payment' => array(
        'title' => __('Enable/Disable', 'woo_mpu'),
        'type' => 'checkbox',
        'label' => __('Stored Card Payment', 'woo_mpu'),            
    ),   
    'wc_mpu_default_lang' => array(
        'title' => __('Select Language', 'woo_mpu'),
        'type' => 'select',
        'label' => __('Select Language.', 'woo_mpu'),    
        'default' => 'test',
        'description' => __('mpu currently supports 6 languages'),
        'desc_tip' => true,
        'class'        => 'wc-enhanced-select',
        'options' => array(
            'en' => 'English',
            'ja' => 'Japanese',
            'th' => 'Thailand',
            'id' => 'Bahasa Indonesia',
            'my' => 'Burmese',
            'zh' => 'Simplified Chinese',
        ),
    ),    
    'wc_mpu_fixed_description' => array(
        'title' => __('Fixed Description', 'woo_mpu'),
        'type' => 'textarea',
        'default' => __('Fixed Description.', 'woo_mpu'),
        'description' => __('Fixed product description which display during payment.', 'woo_mpu'),
        'desc_tip' => true,        
    ),
);
?> 