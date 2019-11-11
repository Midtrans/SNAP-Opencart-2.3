<?php
// Heading
$_['heading_title']      = 'Midtrans';

// Text
$_['text_payment']       = 'Payment';
$_['text_success']       = 'Success: You have modified Midtrans configuration!';
$_['text_snap']     = '<a href="https://midtrans.com" target="_blank"><img src="view/image/payment/midtrans.png" width="120px" alt="Midtrans" title="Snap" style="border: 1px solid #EEEEEE;" /></a>';
$_['text_live']          = 'Production';
$_['text_successful']    = 'Always Successful';
$_['text_fail']          = 'Always Fail';
$_['text_edit']          = 'Configure Midtrans';

// Entry
$_['entry_environment']  = 'Environment';
$_['entry_merchant_id']  = 'Merchant Id';
$_['entry_client_key']   = 'Client Key';
$_['entry_server_key']   = 'Server Key';
$_['entry_3d_secure']    = '3D Secure';
$_['entry_test']         = 'Test Mode:';
$_['entry_oneclick']     = 'Save Card';
$_['entry_custom_field'] = 'Custom Field';
$_['entry_expiry']		 = 'Custom Expiry';
$_['entry_mixpanel']	 = 'Midtrans Mixpanel';
$_['entry_geo_zone']     = 'Geo Zone';
$_['entry_status']       = 'Status';
$_['entry_sort_order']   = 'Sort Order';
$_['entry_currency_conversion'] = 'Currency conversion to IDR';
$_['entry_success_mapping'] = 'Success Order Status';
$_['entry_pending_mapping'] = 'Pending Order Status';
$_['entry_failure_mapping'] = 'Failure Order Status';
$_['entry_display_name'] = 'Display name';

// Help
$_['help_savecard'] = 'This will allow your customer to save their card on the payment popup, for faster payment flow on the following purchase.';
$_['help_expiry'] = 'This will allow you to set custom duration on how long the transaction available to be paid.';
$_['help_custom_field'] = 'This will allow you to set custom fields that will be displayed on Midtrans dashboard.';
$_['help_success_mapping'] = 'Change to the following order status once the payment success';
$_['help_pending_mapping'] = 'Change to the following order status once the payment pending';
$_['help_failure_mapping'] = 'Change to the following order status once the payment failure';

// Error
$_['error_permission']   = 'Warning: You do not have permission to modify Midtrans Snap!';
$_['error_merchant']     = 'Merchant ID is required!';
$_['error_client_key']   = 'Client Key is required!';
$_['error_server_key']   = 'Server Key is required!';
$_['error_currency_conversion'] = 'Currency conversion rate is required when IDR currency is not installed in the system!';
$_['error_display_name'] = 'Please specify a name for this payment method!';
?>