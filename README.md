Official Midtrans SNAP OpenCart Extension
===================================

Midtrans :heart: OpenCart!

This is the official Midtrans extension for the OpenCart E-commerce platform.

## Compatibility
1. PHP v5.4 and higher
2. Opencart v2.3

## Installation

1. Download and extract the zip file.

2. Locate the root _OpenCart_ directory of your shop via FTP connection.

3. Copy the `admin`, `catalog`, and `system` folders into your _OpenCart's_ root folder, and merge it.

4. In your _OpenCart_ admin area, go to `Extensions` - `Extensions`.

5. Filter by `Payments`, scroll down untill you find `Snap` by `Midtrans`.

6. Click the `Install` green button and edit the plugin.

5. Insert your merchant details (server key and client key).

6. Login into your Midtrans account and change the following options:

  * **Payment Notification URL** in Settings to `http://[your shop's homepage]/index.php?route=extension/payment/snap/payment_notification`

  * **Finish Redirect URL** in Settings to `http://[your shop’s homepage]/index.php?route=extension/payment/snap/landing_redir&`

  * **Error Redirect URL** in Settings to `http://[your shop’s homepage]/index.php?route=extension/payment/snap/landing_redir&`

  * **Unfinish Redirect URL** in Settings to `http://[your shop’s homepage]/index.php?route=extension/payment/snap/landing_redir&`

Created by CBY