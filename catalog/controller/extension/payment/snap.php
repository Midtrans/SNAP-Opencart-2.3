<?php
/*
status code
1 pending
2 processing
3 shipped
5 complete
7 canceled
8 denied
9 canceled reversal
10 failed
11 refunded
12 reversed
13 chargeback
14 expired
15 processed
16 voided
*/

require_once(DIR_SYSTEM . 'library/midtrans-php/Midtrans.php');

class ControllerExtensionPaymentSnap extends Controller {

  public function index() {

    if ($this->request->server['HTTPS']) {
      $data['base'] = $this->config->get('config_ssl');
    } else {
      $data['base'] = $this->config->get('config_url');
    }

    $data['errors'] = array();
    $data['button_confirm'] = $this->language->get('button_confirm');

    $env = $this->config->get('snap_environment') == 'production' ? true : false;
    $data['mixpanel_key'] = $env == true ? "17253088ed3a39b1e2bd2cbcfeca939a" : "9dcba9b440c831d517e8ff1beff40bd9";
    $data['merchant_id'] = $this->config->get('snap_merchant_id');
    
    $data['pay_type'] = 'snap';
    $data['client_key'] = $this->config->get('snap_client_key');
    $data['environment'] = $this->config->get('snap_environment');
    $data['text_loading'] = $this->language->get('text_loading');
    $data['disable_mixpanel'] = $this->config->get('snap_mixpanel');
    $data['redirect'] = $this->config->get('snap_redirect');

    $data['process_order'] = $this->url->link('extension/payment/snap/process_order'); 
     
    return $this->load->view('extension/payment/snap', $data);
      
  }

  /**
   * Called when a customer checkouts.
   * If it runs successfully, it will redirect to VT-Web payment page.
   */
  public function process_order() {
    $this->load->model('extension/payment/snap');
    $this->load->model('checkout/order');
    $this->load->model('extension/total/shipping');
    $this->load->language('extension/payment/snap');

    $data['errors'] = array();

    $data['button_confirm'] = $this->language->get('button_confirm');

    $order_info = $this->model_checkout_order->getOrder(
      $this->session->data['order_id']);

    $transaction_details                 = array();
    $transaction_details['order_id']     = $this->session->data['order_id'];
    $transaction_details['gross_amount'] = $order_info['total'];

    $billing_address                 = array();
    $billing_address['first_name']   = $order_info['payment_firstname'];
    $billing_address['last_name']    = $order_info['payment_lastname'];
    $billing_address['address']      = $order_info['payment_address_1'];
    $billing_address['city']         = $order_info['payment_city'];
    $billing_address['postal_code']  = $order_info['payment_postcode'];
    $billing_address['phone']        = $order_info['telephone'];
    $billing_address['country_code'] = strlen($order_info['payment_iso_code_3'] != 3) ? 'IDN' : $order_info['payment_iso_code_3'];

    if ($this->cart->hasShipping()) {
      $shipping_address = array();
      $shipping_address['first_name']   = $order_info['shipping_firstname'];
      $shipping_address['last_name']    = $order_info['shipping_lastname'];
      $shipping_address['address']      = $order_info['shipping_address_1'];
      $shipping_address['city']         = $order_info['shipping_city'];
      $shipping_address['postal_code']  = $order_info['shipping_postcode'];
      $shipping_address['phone']        = $order_info['telephone'];
      $shipping_address['country_code'] = strlen($order_info['payment_iso_code_3'] != 3) ? 'IDN' : $order_info['payment_iso_code_3'];
    } else {
      $shipping_address = $billing_address;
    }

    $customer_details                     = array();
    $customer_details['billing_address']  = $billing_address;
    $customer_details['shipping_address'] = $shipping_address;
    $customer_details['first_name']       = $order_info['payment_firstname'];
    $customer_details['last_name']        = $order_info['payment_lastname'];
    $customer_details['email']            = $order_info['email'];
    $customer_details['phone']            = $order_info['telephone'];

    $products = $this->cart->getProducts();
    
    $item_details = array();

    foreach ($products as $product) {
      if (($this->config->get('config_customer_price')
            && $this->customer->isLogged())
          || !$this->config->get('config_customer_price')) {
        $product['price'] = $this->tax->calculate(
            $product['price'],
            $product['tax_class_id'],
            $this->config->get('config_tax'));
      }

      $item = array(
          'id'       => $product['product_id'],
          'price'    => $product['price'],
          'quantity' => $product['quantity'],
          'name'     => substr($product['name'], 0, 49)
        );
      $item_details[] = $item;
    }

    unset($product);

    $num_products = count($item_details);

    if ($this->cart->hasShipping()) {
      $shipping_info = $this->session->data['shipping_method'];
      if (($this->config->get('config_customer_price')
            && $this->customer->isLogged())
          || !$this->config->get('config_customer_price')) {
        $shipping_info['cost'] = $this->tax->calculate(
            $shipping_info['cost'],
            $shipping_info['tax_class_id'],
            $this->config->get('config_tax'));
      }

      $shipping_item = array(
          'id'       => 'SHIPPING',
          'price'    => $shipping_info['cost'],
          'quantity' => 1,
          'name'     => 'SHIPPING'
        );
      $item_details[] = $shipping_item;
    }

    // convert all item prices to IDR
    if ($this->config->get('config_currency') != 'IDR') {
      if ($this->currency->has('IDR')) {
        foreach ($item_details as &$item) {
          $item['price'] = intval($this->currency->convert(
              $item['price'],
              $this->config->get('config_currency'),
              'IDR'
            ));
        }
        unset($item);

        $transaction_details['gross_amount'] = intval($this->currency->convert(
            $transaction_details['gross_amount'],
            $this->config->get('config_currency'),
            'IDR'
          ));
      }
      else if ($this->config->get('snap_currency_conversion') > 0) {
        foreach ($item_details as &$item) {
          $item['price'] = intval($item['price']
              * $this->config->get('snap_currency_conversion'));
        }
        unset($item);

        $transaction_details['gross_amount'] = intval(
            $transaction_details['gross_amount']
            * $this->config->get('snap_currency_conversion'));
      }
      else {
        $data['errors'][] = "Either the IDR currency is not installed or "
            . "the snap currency conversion rate is valid. "
            . "Please review your currency setting.";
      }
    }

    $total_price = 0;
    foreach ($item_details as $item) {
      $total_price += $item['price'] * $item['quantity'];
    }

    if ($total_price != $transaction_details['gross_amount']) {
      $coupon_item = array(
          'id'       => 'COUPON',
          'price'    => $transaction_details['gross_amount'] - $total_price,
          'quantity' => 1,
          'name'     => 'COUPON'
        );
      $item_details[] = $coupon_item;
    }

    \Midtrans\Config::$serverKey = $this->config->get('snap_server_key');
    \Midtrans\Config::$isProduction = $this->config->get('snap_environment') == 'production' ? true : false;
    \Midtrans\Config::$isSanitized = true;

    $payloads = array();
    $payloads['transaction_details'] = $transaction_details;
    $payloads['item_details']        = $item_details;
    $payloads['customer_details']    = $customer_details;

    if($this->config->get('snap_oneclick') == 1){
      $payloads['credit_card']['save_card'] = true;
      $payloads['user_id'] = crypt( $order_info['email'], $serverKey );;
    }  
    $payloads['credit_card']['secure'] = true;

    $custom_field = array();
    $custom_field[1] = $this->config->get('snap_custom_field1');
    $custom_field[2] = $this->config->get('snap_custom_field2');
    $custom_field[3] = $this->config->get('snap_custom_field3');

    $expiry_unit = $this->config->get('snap_expiry_unit');
    $expiry_duration = $this->config->get('snap_expiry_duration');

    if (!empty($expiry_unit) && !empty($expiry_duration)){
          $time = time();
          $payloads['expiry'] = array(
            'unit' => $expiry_unit, 
            'duration'  => $expiry_duration
          );
    }

    if(!empty($custom_field[1])){$payloads['custom_field1'] = $custom_field[1];}
    if(!empty($custom_field[2])){ $payloads['custom_field2'] = $custom_field[2];}
    if(!empty($custom_field[3])){ $payloads['custom_field3'] = $custom_field[3];}

    try {
      $snapResponse = \Midtrans\Snap::createTransaction($payloads);
      $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], 1, $snapResponse->redirect_url);
      $this->cart->clear();

      if ($this->config->get('snap_redirect') == 1) {
        $this->response->setOutput($snapResponse->redirect_url);
      }
      else{
        $this->response->setOutput($snapResponse->token);
      }
    }
    catch (Exception $e) {
      $data['errors'][] = $e->getMessage();
      error_log($e->getMessage());
      echo $e->getMessage();
    }
  }

  /**
   * Landing page when payment is finished or failure or customer pressed "back" button
   * The Cart is cleared here, so make sure customer reach this page to ensure the cart is emptied when payment succeed
   * payment finish/unfinish/error url :
   * http://[your shop’s homepage]/index.php?route=payment/snap/payment_notification
   */
  public function landing_redir() {
    $this->load->model('checkout/order');
    $this->load->model('account/order');
    $redirUrl = $this->config->get('config_ssl');

    // Handling for SNAP REDIRECT
    if (isset($_GET['status_code'])) {
      if ($_GET['status_code'] == 200) {
          $transaction_status = 'processing';
      }
      else if ($_GET['status_code'] == 201) {
        $order_id = $_GET['order_id'];
        $order_details = (object) $this->model_checkout_order->getOrder($order_id);
        $transaction_status = strtolower($order_details->order_status);
      }
      else {
        $redirUrl = $this->url->link('extension/payment/snap/failure');
        $this->response->redirect($redirUrl); 
      }
    }

    // Handling for SNAP POPUP
    else if (isset($_POST['result_data'])) {
      if ($_POST['result_type'] == 'success') {
        $transaction_status = 'processing';
      }
      else {
        $response = isset($_POST['result_data']) ? json_decode($_POST['result_data']) : json_decode($_POST['response']);
        $order_id = $response->order_id;
        $order_details = (object) $this->model_checkout_order->getOrder($order_id);
        $transaction_status = strtolower($order_details->order_status);
      }    
    }
      // handle bca klikpay
    else if(isset($_GET['?id'])){
        $id = isset($_GET['?id']) ? $_GET['?id'] : null;
        \Midtrans\Config::$serverKey = $this->config->get('snap_server_key');
        \Midtrans\Config::$isProduction = $this->config->get('snap_environment') == 'production' ? true : false;
        $bca_status = \Midtrans\Transaction::status($id);
        $transaction_status = null;
        // error_log(print_r($bca_status,TRUE));
        $payment_type = $bca_status->payment_type;
        if($payment_type == "bca_klikpay"){
          if($bca_status->transaction_status == "settlement")
              {
                $data['data']= array(
                'payment_type' => "bca_klikpay",    
                'payment_method' => "BCA KlikPay",  
                'payment_status'  => "Success"
                );   
                $data['column_left'] = $this->load->controller('common/column_left');
                $data['column_right'] = $this->load->controller('common/column_right');
                $data['content_top'] = $this->load->controller('common/content_top');
                $data['content_bottom'] = $this->load->controller('common/content_bottom');
                $data['footer'] = $this->load->controller('common/footer');
                $data['header'] = $this->load->controller('common/header');                
                $redirUrl = $this->url->link('checkout/success&');
                $this->response->redirect($redirUrl);
            
              }
              else{
                $redirUrl = $this->url->link('extension/payment/snap/failure','','SSL');
                $this->response->redirect($redirUrl); 
              }
        }
      }

    if( $transaction_status == 'processing') {
      $redirUrl = $this->url->link('checkout/success&');
      $this->response->redirect($redirUrl);
    }

    else if( $transaction_status == 'pending'){
      $comment_history = $this->model_account_order->getOrderHistories($order_id); 
      $data['data']['comment'] = $comment_history[0]['comment'];
      $data['data']['total'] = floor($order_details->total) . ' '. $order_details->currency_code;
      $data['data']['payment_method'] = $order_details->payment_method;
      $data['data']['order_id'] = $order_details->order_id;          

        $this->document->setTitle('Thank you. Your order has been received.'); //Optional. Set the title of your web page.
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');
        $data['button_continue'] = 'Continue';
        $data['continue'] = $this->url->link('common/home');
        $this->response->setOutput($this->load->view('extension/payment/snap_exec',$data));
      }

    else{
        $redirUrl = $this->url->link('extension/payment/snap/failure');
        $this->response->redirect($redirUrl); 
    }
  }

  /*
  * redirect to payment failure using template & language (text template)
  */
  public function failure() {

    $this->load->language('extension/payment/snap');
    $this->document->setTitle($this->language->get('heading_title'));

    $data['heading_title'] = $this->language->get('heading_title');
    $data['text_failure'] = $this->language->get('text_failure');

    $data['column_left'] = $this->load->controller('common/column_left');
    $data['column_right'] = $this->load->controller('common/column_right');
    $data['content_top'] = $this->load->controller('common/content_top');
    $data['content_bottom'] = $this->load->controller('common/content_bottom');
    $data['footer'] = $this->load->controller('common/footer');
    $data['header'] = $this->load->controller('common/header');
    $data['checkout_url'] = $this->url->link('checkout/cart');

    $this->response->setOutput($this->load->view('extension/payment/snap_checkout_failure',$data));
  }

  /**
   * Called when snap server sends notification to this server.
   * It will change order status according to transaction status and fraud
   * status sent by snap server.
   */

  // Response early with 200 OK status for Midtrans notification & handle HTTP GET
  public function earlyResponse(){
    if ( $_SERVER['REQUEST_METHOD'] == 'GET' ){
      die('This endpoint should not be opened using browser (HTTP GET). This endpoint is for Midtrans notification URL (HTTP POST)');
      exit();
    }

    ob_start();

    $input_source = "php://input";
    $raw_notification = json_decode(file_get_contents($input_source), true);
    echo "Notification Received: \n";
    print_r($raw_notification);
    
    header('Connection: close');
    header('Content-Length: '.ob_get_length());
    ob_end_flush();
    ob_flush();
    flush();
  }

  public function payment_notification() {
    //http://your_website/index.php?route=extension/payment/snap/payment_notification

    \Midtrans\Config::$serverKey = $this->config->get('snap_server_key');
    \Midtrans\Config::$isProduction = $this->config->get('snap_environment') == 'production' ? true : false;
    $this->earlyResponse();
    $this->load->model('checkout/order');
    $this->load->model('extension/payment/snap');
    $notif = new \Midtrans\Notification();
    //error_log(print_r($notif,TRUE));
    $transaction = $notif->transaction_status;
    $fraud = $notif->fraud_status;
    $payment_type = $notif->payment_type;
    $order_note = 'Midtrans HTTP notification received: '.$transaction.'. Midtrans-'.$payment_type;

    $logs = '';
    // error_log(print_r($notif,true)); // debugan
    if ($transaction == 'capture') {
      $logs .= 'capture ';
      if ($fraud == 'challenge') {
        $logs .= 'challenge ';
        $this->model_checkout_order->addOrderHistory(
            $notif->order_id,$this->config->get('snap_status_pending'),$order_note . 
            'Payment status challenged. Please take action on '
              . 'your Merchant Administration Portal.');
      }
      else if ($fraud == 'accept') {
        $logs .= 'accept ';
        $this->model_checkout_order->addOrderHistory(
            $notif->order_id,$this->config->get('snap_status_success'),$order_note);
      }
    }
    else if ($transaction == 'cancel') {
        $logs .= 'cancel ';
        $this->model_checkout_order->addOrderHistory(
            $notif->order_id,$this->config->get('snap_status_failure'),$order_note);
    }
    else if ($transaction == 'pending') {
      $logs .= 'pending ';
      $this->model_checkout_order->addOrderHistory(
          $notif->order_id,$this->config->get('snap_status_pending'),$order_note);
    }
    else if ($transaction == 'expire') {
      $logs .= 'pending ';
      $this->model_checkout_order->addOrderHistory(
          $notif->order_id,$this->config->get('snap_status_failure'),$order_note);
    }
    else if ($transaction == 'settlement') {
          if($payment_type != 'credit_card'){
              $logs .= 'complete ';
              $this->model_checkout_order->addOrderHistory(
              $notif->order_id,$this->config->get('snap_status_success'),$order_note);
          }
    }
    //error_log($logs); //debugan to be commented
  }

  public function payment_cancel() {
    
    $this->load->model('checkout/order');
    error_log($this->session->data['order_id']);
    $current_order_id = $this->session->data['order_id'];
    $this->model_checkout_order->addOrderHistory($current_order_id,$this->config->get('snap_status_failure'),'Cancel from snap close.');
    error_log('cancel order'. $this->session->data['order_id']. 'success');
    echo 'ok';
  }
}
