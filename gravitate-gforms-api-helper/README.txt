=== Gravitate Gforms API Helper ===
Contributors: (Gravitate)
Tags: Gravitate, Gravity Forms, API
Requires at least: 3.5
Tested up to: 3.9
Stable tag: trunk

This is Plugin Helper to allow you to easily setup an External API on Gravity Form Submissions

== Description ==

Author: Gravitate http://www.gravitatedesign.com

Description: This is Plugin Helper to allow you to easily setup an External API on Gravity Form Submissions. You can link multiple API calls to a single Gravity Form Submission.

== Requirements ==

- Gravity Forms Plugin
- WordPress 3.5 or above


== Installation ==

1. Upload the `gravitate-gforms-api-helper` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== HOW TO USE ==

Once installed and Activated you will be able to go into your Gravity Forms Settings and Add in a Custom API Call.
Ex. Sales Force
* You can create multiple calls separated by commas

Once you save that field you will now be able to go into a field in the the Form Editor and go the the Advanced Tab and place in the Field Name for the API you named.

Now everytime the form is submitted it will call any actions your create and submit the fields and their values.


To create actions just place the below code in your functions.php file of your theme or in another plugin.
replace {your_api_name} with the sanitized (lowercase with underscores) API name you added to the Form Settings.
Then place your own code in the "Send $data to API Here".


<pre>
add_action("ggah_submission_{your_api_name}", "your_function_hook_name_here_{your_api_name}", 10, 3);

function your_function_hook_name_here_{your_api_name} ($data, $form, $api)
{
	$api_failed = false;


	// Send $data to API Here


	// If Api Failed then set $api_failed = true;


	// This will send a Simple Email to the Admin to notify them of the error.
	if($api_failed)
	{
		if(function_exists('ggah_error_notification'))
		{
			$email = ''; 	// leave blank to use Admin Email
			$subject = ''; 	// leave blank to use Default
			$message = ''; 	// leave blank to use Default

			ggah_error_notification($email, $api, $subject, $message);
		}
	}
}
</pre>


If using multiple calls then duplicate the code and place in your own code accordingly.


== Changelog ==

= 1.0.0 =
* Initial Creation