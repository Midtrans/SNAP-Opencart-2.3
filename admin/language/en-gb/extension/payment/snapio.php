<?php
// Heading
$_['heading_title']      = 'Midtrans Offline Installment';

// Text
$_['text_payment']       = 'Payment';
$_['text_success']       = 'Success: You have modified Midtrans Offline Installment configuration!';
$_['text_snapio']     = '<a href="https://midtrans.com" target="_blank"><img src="view/image/payment/midtrans.png" width="120px" alt="Midtrans" title="Snap" style="border: 1px solid #EEEEEE;" /></a>';
$_['text_live']          = 'Production';
$_['text_successful']    = 'Always Successful';
$_['text_fail']          = 'Always Fail';
$_['text_edit']          = 'Configure Midtrans Offline Installment';

// Entry
$_['entry_environment']  = 'Environment';
$_['entry_merchant_id']  = 'Merchant Id';
$_['entry_client_key']   = 'Client Key';
$_['entry_server_key']   = 'Server Key';
$_['entry_min_txn']      = 'Minimum Transaction';
$_['entry_mixpanel']	 = 'Midtrans Mixpanel';
$_['entry_geo_zone']     = 'Geo Zone:';
$_['entry_status']       = 'Status:';
$_['entry_sort_order']   = 'Sort Order:';
$_['entry_currency_conversion'] = 'Currency conversion to IDR';
$_['entry_display_name'] = 'Display name:';
$_['entry_acq_bank']	 = 'Acquiring Bank';
$_['entry_installment_term'] = 'Installment Terms';
$_['entry_bin_number']  = 'Bin Number(s)';
$_['entry_custom_field'] = 'Custom Field';
$_['entry_success_mapping'] = 'Success Order Status';
$_['entry_pending_mapping'] = 'Pending Order Status';
$_['entry_failure_mapping'] = 'Failure Order Status';
$_['entry_redirect'] = 'Redirect Payment Page';

// Help
$_['help_min'] = 'Minimum amount of transaction.';
$_['help_custom_field'] = 'This will allow you to set custom fields that will be displayed on Midtrans dashboard.';
$_['help_success_mapping'] = 'Change to the following order status once the payment success';
$_['help_pending_mapping'] = 'Change to the following order status once the payment pending';
$_['help_failure_mapping'] = 'Change to the following order status once the payment failure';
$_['help_redirect'] = 'This will redirect customer to Midtrans hosted payment page instead of popup payment page on your website.';

// Error
$_['error_permission']   = 'Warning: You do not have permission to modify Midtrans Installment Offline Snap!';
$_['error_merchant']     = 'Merchant ID is required!';
$_['error_client_key']   = 'Client Key is required!';
$_['error_server_key']   = 'Server Key is required!';
$_['error_currency_conversion'] = 'Currency conversion rate is required when IDR currency is not installed in the system!';
$_['error_display_name'] = 'Please specify a name for this payment method!';
$_['error_min_txn'] = 'Please specify a minimum amount for installment!';
?>