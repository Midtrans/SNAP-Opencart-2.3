<?php
class ControllerExtensionPaymentSnapio extends Controller {

  private $error = array();

  public function index() {
    $this->load->language('extension/payment/snapio');

    $this->document->setTitle($this->language->get('heading_title'));

    $this->load->model('setting/setting');
    $this->load->model('localisation/order_status');
	$this->config->get('curency');


    if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
      $this->model_setting_setting->editSetting('snapio', $this->request->post);

      $this->session->data['success'] = $this->language->get('text_success');

      $this->response->redirect($this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true));
    }

    if (isset($this->error['warning'])) {
      $data['error_warning'] = $this->error['warning'];
    } else {
      $data['error_warning'] = '';
    }

    if (isset($this->error['display_name'])) {
      $data['error_display_name'] = $this->error['display_name'];
    } else {
      $data['error_display_name'] = '';
    }
    
    if (isset($this->error['merchant_id'])) {
      $data['error_merchant'] = $this->error['merchant_id'];
    } else {
      $data['error_merchant'] = '';
    }

    if (isset($this->error['server_key'])) {
      $data['error_server_key'] = $this->error['server_key'];
    } else {
      $data['error_server_key'] = '';
    }

    if (isset($this->error['client_key'])) {
      $data['error_client_key'] = $this->error['client_key'];
    } else {
      $data['error_client_key'] = '';
    }

    if (isset($this->error['min_txn'])) {
      $data['error_min_txn'] = $this->error['min_txn'];
    } else {
      $data['error_min_txn'] = '';
    }

    $language_entries = array(

      'heading_title',
      'text_enabled',
      'text_disabled',
      'text_yes',
      'text_live',
      'text_successful',
      'text_fail',
      'text_all_zones',
	    'text_edit',

      'entry_environment',
      'entry_merchant_id',
      'entry_server_key',
      'entry_client_key',
      'entry_geo_zone',
      'entry_status',
      'entry_sort_order',
      'entry_min_txn',
      'entry_mixpanel',
      'entry_currency_conversion',
      'entry_client_key',
      'entry_display_name',
      'entry_custom_field',
      'entry_installment_term',
      'entry_acq_bank',
      'entry_bin_number',
      'entry_success_mapping',
      'entry_pending_mapping',
      'entry_failure_mapping',
      'entry_redirect',
      
      'help_min',
      'help_custom_field',
      'help_success_mapping',
      'help_pending_mapping',
      'help_failure_mapping',
      'help_redirect',

      'button_save',
      'button_cancel'
      );

    foreach ($language_entries as $language_entry) {
      $data[$language_entry] = $this->language->get($language_entry);
    }

    if (isset($this->error['warning'])) {
      $data['error_warning'] = $this->error['warning'];
    } else {
      $data['error_warning'] = '';
    }

    $data['breadcrumbs'] = array();

    $data['breadcrumbs'][] = array(
      'text' => $this->language->get('text_home'),
      'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
    );

    $data['breadcrumbs'][] = array(
      'text' => $this->language->get('text_extension'),
      'href' => $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true)
    );

    $data['breadcrumbs'][] = array(
      'text' => $this->language->get('heading_title'),
      'href' => $this->url->link('extension/payment/snapio', 'token=' . $this->session->data['token'], true)
    );

    $data['action'] = $this->url->link('extension/payment/snapio', 'token=' . $this->session->data['token'], true);

    $data['cancel'] = $this->url->link('extension/extension', 'token=' . $this->session->data['token'].'&type=payment', true);

    $inputs = array(
      'snapio_environment',
      'snapio_merchant_id',
      'snapio_server_key',
      'snapio_client_key',
      'snapio_geo_zone_id',
      'snapio_sort_order',
      'snapio_min_txn',
      'snapio_mixpanel',
      'snapio_payment_type',
      'snapio_installment_term',
      'snapio_acq_bank',
      'snapio_currency_conversion',
      'snapio_status',
      'snapio_number',
      'snapio_display_name',
      'snapio_custom_field1',
      'snapio_custom_field2',
      'snapio_custom_field3',
      'snap_redirect',
      'snapio_sanitization'
    );

    foreach ($inputs as $input) {
      if (isset($this->request->post[$input])) {
        $data[$input] = $this->request->post[$input];
      } else {
        $data[$input] = $this->config->get($input);
      }
    }

    if (isset($this->request->post['snapio_status_success'])) {
      $data['snapio_status_success'] = $this->request->post['snapio_status_success'];
    } elseif ($this->config->get('snapio_status_success')) {
      $data['snapio_status_success'] = $this->config->get('snapio_status_success');
    } else {
      $data['snapio_status_success'] = '2';
    }

    if (isset($this->request->post['snapio_status_pending'])) {
      $data['snapio_status_pending'] = $this->request->post['snapio_status_pending'];
    } elseif ($this->config->get('snapio_status_pending')) {
      $data['snapio_status_pending'] = $this->config->get('snapio_status_pending');
    } else {
      $data['snapio_status_pending'] = '1';
    }

    if (isset($this->request->post['snapio_status_failure'])) {
      $data['snapio_status_failure'] = $this->request->post['snapio_status_failure'];
    } elseif ($this->config->get('snapio_status_failure')) {
      $data['snapio_status_failure'] = $this->config->get('snapio_status_failure');
    } else {
      $data['snapio_status_failure'] = '7';
    }


    $this->load->model('localisation/order_status');

    $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

    $this->load->model('localisation/geo_zone');

    $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

    $this->template = 'extension/payment/snapio.tpl';
	$data['column_left'] = $this->load->controller('common/column_left');
	$data['header'] = $this->load->controller('common/header');
	$data['footer'] = $this->load->controller('common/footer');
	
	
	if(!$this->currency->has('IDR'))
	{
		$data['curr'] = true;
	}
	else
	{
		$data['curr'] = false;
	}
	$this->response->setOutput($this->load->view('extension/payment/snapio.tpl',$data));
	
  }

  protected function validate() {

    if (!$this->user->hasPermission('modify', 'extension/payment/snapio')) {
      $this->error['warning'] = $this->language->get('error_permission');
    }

    // check for empty values
    if (!$this->request->post['snapio_display_name']) {
      $this->error['display_name'] = $this->language->get('error_display_name');
    }
        
    // check for empty values
    if (!$this->request->post['snapio_client_key']) {
      $this->error['client_key'] = $this->language->get('error_client_key');
    }

    // check for empty values
    if (!$this->request->post['snapio_server_key']) {
      $this->error['server_key'] = $this->language->get('error_server_key');
    }

    // default values
    if (!$this->request->post['snapio_environment'])
      $this->request->post['snapio_environment'] = 1;

      // check for empty values
    if (!$this->request->post['snapio_merchant_id']) {
       $this->error['merchant_id'] = $this->language->get('error_merchant');
    }
      // check for empty values
    if (!$this->request->post['snapio_min_txn']) {
       $this->error['min_txn'] = $this->language->get('error_min_txn');
    }

    // currency conversion to IDR
    if (!$this->request->post['snapio_currency_conversion'] && !$this->currency->has('IDR'))
      $this->error['currency_conversion'] = $this->language->get('error_currency_conversion');

    return !$this->error;
  }
}
?>