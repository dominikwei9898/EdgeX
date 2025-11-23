<?php
if (! defined ( 'ABSPATH' ))
	exit (); // Exit if accessed directly

class CajipayWCPaymentGateway extends WC_Payment_Gateway {
    private $config;

	public function __construct() {
		//支持退款
		array_push($this->supports,'refunds');

		$this->id = CAJI_PAY_GATEWAY_ID;
		$this->icon =CAJI_PAY_GATEWAY_URL. '/images/logo.png';
		$this->has_fields = false;

		$this->method_title = '超集支付'; // checkout option title
	    $this->method_description='超集支付 通过提供各种支付渠道，使您的在线和离线业务更加轻松，提供了安全便捷的在线支付解决方案。多渠道，多账号，多平台，一键导入，快速对接。 ';

		$this->init_form_fields ();
		$this->init_settings ();

		$this->title = $this->get_option ( 'title' );
		$this->description = $this->get_option ( 'description' );
	}
	function init_form_fields() {
	    $this->form_fields = array (
	        'enabled' => array (
	            'title' => __ ( 'Enable/Disable', 'cajipay' ),
	            'type' => 'checkbox',
	            'label' => __ ( 'Enable CajiPay Payment', 'cajipay' ),
	            'default' => 'no'
	        ),
	        'title' => array (
	            'title' => __ ( 'Title', 'cajipay' ),
	            'type' => 'text',
	            'description' => __ ( 'This controls the title which the user sees during checkout.', 'cajipay' ),
	            'default' => __ ( 'CajiPay', 'cajipay' ),
	            'css' => 'width:400px'
	        ),
	        'description' => array (
	            'title' => __ ( 'Description', 'cajipay' ),
	            'type' => 'textarea',
	            'description' => __ ( 'This controls the description which the user sees during checkout.', 'cajipay' ),
	            'default' => 'Pay With Paypal',
	            //'desc_tip' => true ,
	            'css' => 'width:400px'
	        ),
	        'cajipay_gatewayUrl' => array (
	            'title' => __ ( 'Gateway Url', 'cajipay' ),
	            'type' => 'text',
	            'description' => '',
	            'default' => 'https://www.esapi.xyz/',
	            'css' => 'width:400px'
	        ),
	        'cajipay_username' => array (
	            'title' => __ ( 'Username', 'cajipay' ),
	            'type' => 'text',
	            'description' => 'Please enter the Username,If you don\'t have one, <a href="https://www.cajipay.com" target="_blank">click here</a> to get.',
	            'css' => 'width:400px'
	        ),
	        'cajipay_key' => array (
	            'title' => __ ( 'CajiPay Key', 'cajipay' ),
	            'type' => 'text',
	            'description' => 'Please enter your CajiPay Key; this is needed in order to take payment.',
	            'css' => 'width:400px',
	            //'desc_tip' => true
	        ),
            'cajipay_is_auto' => array (
                'title' => __ ( 'Enable/Disable', 'cajipay' ),
                'type' => 'checkbox',
                'label' => '是否开启自动录入',
                'default' => 'yes'
            ),
	        // 'exchange_rate'=> array (
	        //     'title' => __ ( 'Exchange Rate', 'cajipay' ),
	        //     'type' => 'text',
	        //     'default'=>1,
	        //     'description' =>  __ ( "Please set current currency against Chinese Yuan exchange rate, eg if your currency is US Dollar, then you should enter 6.19", 'cajipay' ),
	        //     'css' => 'width:80px;',
	        //     'desc_tip' => true
	        // )
	    );

	}

    /**
     * Payment form on checkout page
     */
    public function payment_fields() {
        echo $this->getPaymentSelect();
    }

	public function process_payment($order_id) {
	    $this->log("=== START process_payment for Order #$order_id ===");
	    $order = new WC_Order ( $order_id );
	    $cajipay_data = isset($_POST['cajipay_data']) ? $_POST['cajipay_data'] : null;
	    $this->log("Raw POST cajipay_data: " . print_r($cajipay_data, true));
	    
	    $encoded_data = base64_encode(json_encode($cajipay_data));
	    $submit_url = $order->get_checkout_payment_url ( true )."&cajipay_data=".$encoded_data;
	    
	    $this->log("Redirect URL generated: $submit_url");
	    return array (
	        'result' => 'success',
	        'redirect' => $submit_url
	    );
	}

	public  function woocommerce_cajipay_add_gateway( $methods ) {
	    $methods[] = $this;
	    return $methods;
	}

	/**
	 *
	 * @param WC_Order $order
	 * @param number $limit
	 * @param string $trimmarker
	 */
	public  function get_order_title($order,$limit=32,$trimmarker='...'){
	    $id = method_exists($order, 'get_id')?$order->get_id():$order->id;
		$title="#{$id}|".get_option('blogname');

		$order_items =$order->get_items();
		if($order_items&&count($order_items)>0){
		    $title="#{$id}|";
		    $index=0;
		    foreach ($order_items as $item_id =>$item){
		        $title.= $item['name'];
		        if($index++>0){
		            $title.='...';
		            break;
		        }
		    }
		}

		return apply_filters('xh_wechat_wc_get_order_title', mb_strimwidth ( $title, 0,32, '...','utf-8'));
	}

	public function get_order_status() {
		$data = $_POST;
		$this->log("=== WEBHOOK CALLBACK RECEIVED ===");
		$this->log("Callback Payload: " . print_r($data, true));
		
		$token = $this->response_hash($data);
		// if($data['token'] != $this->response_hash($data)) {
		// 	$this->log('fail');
  //           echo 'fail';exit;
  //       }

        if ($data['failure_code'] == 'success') {
        	$order_id = $data['order_no'];
        	$this->log("Payment Success for Order #$order_id");
        	$order = new WC_Order ( $order_id );
		    if($order->needs_payment()){
		        $order->payment_complete();
		        $this->log("Order status updated to complete/processing");
		    } else {
		        $this->log("Order does not need payment or already paid");
		    }
        } else {
            $this->log("Payment Failed/Other Status: " . $data['failure_code']);
        }
		echo 'OK';exit;
	}

	public function check_cajipay_response() {


		return true;
	}

	/**
	 *
	 * @param WC_Order $order
	 */
	function receipt_page($order_id) {
	    $order = new WC_Order($order_id);
	    if(!$order||$order->is_paid()){
	       return;
	    }

	    $order_data = $order->get_base_data();
        $order_items =$order->get_items('line_item');
        foreach ($order_items as $key => $item) {
            $attrs = [];
            $itme_data =$item->get_data();
            $meta_data = $itme_data['meta_data'];
            foreach ($meta_data as $attr_item){
                $attr_key = $attr_item->key;
                $attr_value = $attr_item->value;
                $attrs[] = $attr_key.":".$attr_value;
            }
            $itme_data['attr'] = implode(";",$attrs);
            $product_id = $itme_data['product_id'];
            $product = new WC_Product($product_id);
            $get_image_id = $product->get_image_id();
            $itme_data['image'] = wp_get_attachment_url($get_image_id);
            $items[] = $itme_data;
        }

	    $order_data['items'] = $items;
	    $order_data['host'] =  $_SERVER['HTTP_HOST'];
        $order_data['username'] = $this->get_option('cajipay_username');
        $order_data['invoice_id'] = $this->get_invoice_id();
        $order_data['order_no'] = $order_data['id'];

        $order_data['amount'] = $order_data['total'];
        $order_data['country'] = $order_data['billing']['country'];
        // $order_data['currency'] = $order_data['currency'];
        $order_data['client_ip'] = $this->getClientIp();
        $order_data['client_useragent'] = $_SERVER['HTTP_USER_AGENT'];
        $order_data['client_language'] = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        $order_data['email'] = $order_data['billing']['email'];
        $order_data['telephone'] = $order_data['billing']['phone'];
        $notify_url = WC()->api_request_url( 'CAJIPAY_PAYMENT_GET_ORDER' );
        $received_url = $order->get_checkout_order_received_url();
        $cancel_url = $order->get_cancel_order_url();
        $order_data['notify_uri'] = $notify_url;
		$order_data['return_uri'] = $received_url;
        $order_data['cancel_url'] = $cancel_url;
        $order_data['shopping_uri'] = get_option('siteurl');
	    $order_data['cajipay_data'] = $_GET['cajipay_data'];

	    $this->log("=== PREPARE REQUEST for Order #$order_id ===");
	    $this->log("Order Data Payload: " . print_r($order_data, true));

	    $this->getFormUrl($order_data);
	}

	public function getFormUrl($curl_params)
    {

        $curl_params = $this->request_hash($curl_params);
        $gateway_url = $this->get_option('cajipay_gatewayUrl');
        $gateway_url = rtrim($gateway_url,'/').'/';
        
        $this->log("--- SENDING CURL REQUEST ---");
        $this->log("URL: " . $gateway_url);
        $this->log("Final Params (with token): " . print_r($curl_params, true));
        
        // print_r($curl_params);
        // echo $gateway_url;exit;
        $result = $this->curlGet($gateway_url,$curl_params);
        if($result["errcode"] === 0) {
            $this->log("CURL Success. Response Data: " . print_r($result['data'], true));
            echo  $result['data'];
        }else{
            $this->log("CURL Error. ErrMsg: " . $result['errmsg']);
        	$failUrl = $curl_params['cancel_url'];
            echo '<script>window.location.href="'.$failUrl.'";</script>';
        }
        // exit;
    }

    public function getPaymentSelect(){
        global $woocommerce;
        $item_list = $woocommerce->cart->get_cart();
        $total = 0;
		foreach ($item_list as $value){
            $total += $value['line_total'];
		}

        $data['ip'] = self::getClientIp();
        $data['currency'] = get_woocommerce_currency();
        $data['amount'] = $total;

        $curl_params = $data;
        $curl_params['username'] = $this->get_option('cajipay_username');
        $curl_params['web_type'] = 'wordpress';
        $curl_params['is_auto'] = $this->get_option('cajipay_is_auto');
        $curl_params['api_key'] = $this->get_option('cajipay_key');
        $curl_params['host'] = $_SERVER["HTTP_HOST"];
        $curl_params['time'] = time();
        $curl_params = self::request_hash($curl_params);
        $api_url = $this->get_option('cajipay_gatewayUrl');
        $api_url = rtrim($api_url,'/').'/';
        $api_url = str_replace('/pay','',$api_url);
        $result = $this->curlGet($api_url.'PayV2/getPaymentSelects',$curl_params);
        if($result["errcode"] === 0) {
            return  $result['data'];
        }else{
            return $this->get_option ( 'description' );
        }
    }

    public function request_hash($data)
    {
    	$key = $this->get_option('cajipay_key');
        $hash_src = '';
        $hash_key = array('invoice_id', 'order_no');
        foreach ($hash_key as $key) {
            $hash_src .= $data[$key];
        }
        // 密钥放最前面
        $hash_src = $hash_src . $key;
        // sha256 算法
        $hash = hash('sha256', $hash_src);
        $data['token'] = strtoupper($hash);
        return $data;
    }

    public function response_hash($data)
    {
    	$key = $this->get_option('cajipay_key');
    	$this->log("KEY:".$key);
        $hash_src = '';
        $hash_key = array('failure_code', 'invoice_id', 'order_no');
        foreach ($hash_key as $key) {
            $hash_src .= $data[$key];
        }
        $this->log("HASH_SRC:".$hash_src);
        // 密钥放最前面
        //
        $hash_src = $hash_src . $key;
        // sha256 算法
        $hash = hash('sha256', $hash_src);
        $this->log("TOKEN:".$hash.">");
        return strtoupper($hash);
    }

    public function get_invoice_id() {
        // Create random token
        $string = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

        $max = strlen($string) - 1;

        $token = '';

        for ($i = 0; $i < 18; $i++) {
            $token .= $string[mt_rand(0, $max)];
        }

        return md5(uniqid(md5(microtime(true)), true)) . $token;
    }

    public function getClientIp()
    {
        if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {     //使用cloudflare 转发的IP地址
            $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
        } else {
            if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
                $ip = getenv('HTTP_CLIENT_IP');
            } elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
                $ip = getenv('HTTP_X_FORWARDED_FOR');
            } elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
                $ip = getenv('REMOTE_ADDR');
            } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
        }

        return $ip;
    }

	public function curlGet($url,$curl_params, $timeout = 10, $ssl=false)
    {
        $header = [
            "escloak-key: ".$this->get_option('cajipay_key'),
            "username: ".$this->get_option('cajipay_username'),
            "domain: ".$_SERVER["HTTP_HOST"],
        ];
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLINFO_HEADER_OUT, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_FORBID_REUSE, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_URL, $url);
        $arr = http_build_query($curl_params);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $arr);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        $data = curl_exec($curl);
        $curlErrno = curl_errno($curl);
        $curlError = curl_error($curl);
        curl_close($curl);
        if ($curlErrno > 0) {
            $gShow['errcode'] = 1;
            $gShow['errmsg']  = sprintf('警告：CURL错误 %s(%s)！', $curlError, $curlErrno);
            $gShow['data']    = '';

            return $gShow;
        }
        $result = json_decode($data, true);

        if ($result['code'] == 0) {
            $gShow['data']    = $result['data'];
            $gShow['errmsg']  = 'success';
            $gShow['errcode'] = 0;
        } else {
            $gShow['data']    = '';
            $gShow['errmsg']  = 'parse error!';
            $gShow['errcode'] = 2;
        }

        return $gShow;
    }


	public static function log( $message, $level = 'info' ) {
	    // 1. 尝试写入文件 (兼容旧习惯)
	    $timestamp = date("Y-m-d H:i:s");
	    $formatted_message = "[$timestamp] [$level] $message" . PHP_EOL;
		@file_put_contents(WP_CONTENT_DIR.'/cajipay.log', $formatted_message, FILE_APPEND);

		// 2. 同时写入 WooCommerce Logger (推荐)
		if (function_exists('wc_get_logger')) {
		    $logger = wc_get_logger();
		    $context = array('source' => 'cajipay'); 
		    
		    if ($level === 'error' || $level === 'fail') {
		        $logger->error($message, $context);
		    } else {
		        $logger->info($message, $context);
		    }
		}
	}
}

?>
