=== WebData Distance Based Fee for WooCommerce ===
Contributors: webdata
Tags: woocommerce, plugin, custom fee, distance, google, matrix, api
Requires at least: 4.0
Tested up to: 5.7
Stable tag: 1.1.15
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

This plugin uses Google Matrix API to add custom fee based on distance between customers destination city and online stores location. Fee will be calculated by dividing the distance with user entered divider, and then multiplicated by the price. For example, if distance is 100km and the divider is 2 and price is 5 euros (Fee will be 5 euros for every 2 kilometers). The calculation formula will be (100 / 2) * 5 = 250 euros. 

<a target="_blank" href="https://web-data.online/docs/woocommerce-distance-based-fee">Installation instructions here.</a>

If you are looking for a shipping method with distance based fee support, then use <a target="_blank" href="https://wordpress.org/plugins/webdata-custom-shipping-methods-for-woocommerce/">this plugin</a> instead.

Works with any currency and you can use either kilometers or miles.

You can use different logics, based on minimum and maximum distance or minimun cart price. Also fixed fee is available if the conditions are not met and you can disable the fee on virtual products. If you need to create your own logic, the filters to be used can be found here: <a target="_blank" href="https://web-data.online/docs/woocommerce-distance-based-fee/">https://web-data.online/docs/woocommerce-distance-based-fee/</a>

Plugin settings can be found at Settings - Distance based fee settings. 

You need to have a Google API key in order to use this plugin. More info: <a href="http://tiny.cc/aab75y" target="_blank">http://tiny.cc/aab75y</a>

Google now also requires billing to be enabled for your account. Enable it here: <a href="https://console.cloud.google.com/project/_/billing/enable" target="_blank">https://console.cloud.google.com/project/_/billing/enable</a> 
More info: <a href="https://developers.google.com/maps/premium/new-plan-migration" target="_blank">https://developers.google.com/maps/premium/new-plan-migration</a>

This plugin requires WooCommerce version 3.2.0 or later.

== Installation ==

Installing "WooCommerce distance based fee" can be done either by searching for "WooCommerce distance based fee" via the "Plugins > Add New" screen in your WordPress dashboard, or by using the following steps:

1. Download the plugin via WordPress.org
2. Upload the ZIP file through the 'Plugins > Add New > Upload' screen in your WordPress dashboard
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Adjust settings at: Settings - Distance based fee settings

== Frequently Asked Questions ==

= I can not see any fee on my checkout =

1) Google has changed their policy, and now you need to have a billing account enabled, in order to use their API. More info: <a href="https://console.cloud.google.com/project/_/billing/enable" target="_blank">https://console.cloud.google.com/project/_/billing/enable</a> 

2) Check your plugin settings, you need to have enabled atleast one shipping method for the fee.

3) Check error log for possible errors. You can find the log at the plugin settings page on "Logs" tab.

4) Choose one of the WooCommerce shipping methods at WooCommerce - Shipping and save any of the shipping methods settings once to clear the cache.

5) Make sure you have entered your store location correctly on WooCommerce settings, and Google can locate it through Google Maps. 

6) Make sure you have enabled correct API. Try to enable the following APIs: Distance Matrix API, Places API for Web, Google Maps Geocoding API

7) Try to generate new API key and do NOT choose restricted mode.

8) If you still have issues, contact through the <a href="https://wordpress.org/support/plugin/woo-distance-based-fee/">Support forum</a>.

= My settings does not take effect on checkout page =

This may be caching issue. Try to choose one of the WooCommerce shipping methods at WooCommerce - Shipping and save shipping methods settings once. Also if you use any caching plugins, clear those plugins' cache also.

= I can not see any shipping methods  =

You may have some issue in your WooCommerce settings or another plugins can cause this as well. Check Distance Based Fee settings and be sure to select at least one shipping method for the fee. If address cannot be found, selected shipping method will be disabled.

== Changelog ==
= 1.1.15 =
* PHP8 & WC5 check

= 1.1.14 =
* Only zip and city required API fields

= 1.1.13 =
* Added support for Google API status: ZERO_RESULTS

= 1.1.12 =
* Better mielage support

= 1.1.11 =
* No API calls if destination is not set

= 1.1.10 =
* No API call if hiding is disabled based on distance limits

= 1.1.9 =
* Minor bug fixes

= 1.1.8 =
* Added minimun cart price
* Added option for alternative starting point address
* Added hooks for from shipping address, zip and city

= 1.1.7 =
* Billing address can be selected instead of shipping address

= 1.1.6 =
* Bug fixes

= 1.1.5 =
* Added option to disable on virtual products

= 1.1.4 =
* Improved logging

= 1.1.3 =
* Improved logging

= 1.1.2 =
* Minor bug fixes

= 1.1.1 =
* Readme update

= 1.1 =
* New logics: Fee based on minimum and maximum distances
* New logics: If conditions are met, how to handle the chosen shipping methods
* Fixed fee
* Better support for error logging

= 1.0.9 =
* FAQ updated

= 1.0.8 =
* Author updated

= 1.0.7 =
* Readme.txt update

= 1.0.6 =
* Minor issues
* Readme.txt update

= 1.0.5 =
* Improved debugging
* FAQ Added

= 1.0.4.3 =
* Added filter: dbf_calculated_fee for filtering the price

= 1.0.4.2 =
* Updated readme

= 1.0.4.1 =
* WordPress 5.2 support

= 1.0.4 =
* WordPress 5.0.3 support

= 1.0.3 =
* WordPress 4.9.1 support

= 1.0.2 =
* Curl changed to wp_remote_get() function

= 1.0.1 =
* Hide shipping methods if destination cannot be found

= 1.0 =
* Initial release