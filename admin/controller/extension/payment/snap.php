<?php
class ControllerExtensionPaymentSnap extends Controller {

  private $error = array();

  public function index() {
    $this->load->language('extension/payment/snap');

    $this->document->setTitle($this->language->get('heading_title'));

    $this->load->model('setting/setting');
    $this->load->model('localisation/order_status');
	$this->config->get('curency');


    if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
      $this->model_setting_setting->editSetting('snap', $this->request->post);

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
      'entry_server_key',
      'entry_merchant_id',
      'entry_oneclick',
      'entry_geo_zone',
      'entry_status',
      'entry_sort_order',
      'entry_3d_secure',
      'entry_expiry',
      'entry_custom_field',
      'entry_mixpanel',
      'entry_currency_conversion',
      'entry_client_key',
      'entry_display_name',
      'entry_success_mapping',
      'entry_pending_mapping',
      'entry_failure_mapping',
      
      'help_savecard',
      'help_expiry',
      'help_custom_field',
      'help_success_mapping',
      'help_pending_mapping',
      'help_failure_mapping',

      'button_save',
      'button_cancel'
      );

    foreach ($language_entries as $language_entry) {
      $data[$language_entry] = $this->language->get($language_entry);
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
      'href' => $this->url->link('extension/payment/snap', 'token=' . $this->session->data['token'], true)
    );

    $data['action'] = $this->url->link('extension/payment/snap', 'token=' . $this->session->data['token'], true);

    $data['cancel'] = $this->url->link('extension/extension', 'token=' . $this->session->data['token'].'&type=payment', true);

    $inputs = array(
      'snap_environment',
      'snap_client_key',
      'snap_server_key',
      'snap_merchant_id',
      'snap_oneclick',
      'snap_order_status_id',
      'snap_geo_zone_id',
      'snap_sort_order',
      'snap_3d_secure',
      'snap_payment_type',
      'snap_installment_terms',
      'snap_currency_conversion',
      'snap_status',
      'snap_expiry_duration',
      'snap_expiry_unit',
      'snap_custom_field1',
      'snap_custom_field2',
      'snap_custom_field3',
      'snap_mixpanel',
      'snap_display_name',
      'snap_enabled_payments',
      'snap_sanitization'
    );

    foreach ($inputs as $input) {
      if (isset($this->request->post[$input])) {
        $data[$input] = $this->request->post[$input];
      } else {
        $data[$input] = $this->config->get($input);
      }
    }

    if (isset($this->request->post['snap_status_success'])) {
      $data['snap_status_success'] = $this->request->post['snap_status_success'];
    } elseif ($this->config->get('snap_status_success')) {
      $data['snap_status_success'] = $this->config->get('snap_status_success');
    } else {
      $data['snap_status_success'] = '2';
    }

    if (isset($this->request->post['snap_status_pending'])) {
      $data['snap_status_pending'] = $this->request->post['snap_status_pending'];
    } elseif ($this->config->get('snap_status_pending')) {
      $data['snap_status_pending'] = $this->config->get('snap_status_pending');
    } else {
      $data['snap_status_pending'] = '1';
    }

    if (isset($this->request->post['snap_status_failure'])) {
      $data['snap_status_failure'] = $this->request->post['snap_status_failure'];
    } elseif ($this->config->get('snap_status_failure')) {
      $data['snap_status_failure'] = $this->config->get('snap_status_failure');
    } else {
      $data['snap_status_failure'] = '7';
    }

    $this->load->model('localisation/order_status');

    $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

    $this->load->model('localisation/geo_zone');

    $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

    $this->template = 'extension/payment/snap.tpl';
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
	$this->response->setOutput($this->load->view('extension/payment/snap.tpl',$data));
	
  }

  protected function validate() {
    if (!$this->user->hasPermission('modify', 'extension/payment/snap')) {
      $this->error['warning'] = $this->language->get('error_permission');
    }

    // check for empty values
    if (!$this->request->post['snap_display_name']) {
      $this->error['display_name'] = $this->language->get('error_display_name');
    }

    // check for empty values
    if (!$this->request->post['snap_client_key']) {
      $this->error['client_key'] = $this->language->get('error_client_key');
    }

    // check for empty values
    if (!$this->request->post['snap_server_key']) {
      $this->error['server_key'] = $this->language->get('error_server_key');
    }      
    
    // default values
    if (!$this->request->post['snap_environment'])
      $this->request->post['snap_environment'] = 1;

    // check for empty values
    if (!$this->request->post['snap_merchant_id']) {
       $this->error['merchant_id'] = $this->language->get('error_merchant');
    }    
    // currency conversion to IDR
    if (!$this->request->post['snap_currency_conversion'] && !$this->currency->has('IDR'))
      $this->error['currency_conversion'] = $this->language->get('error_currency_conversion');

      return !$this->error;
  }
}
?>