<?php

/**
 * @package Gravitate Gforms API Helper
 */
/*
Plugin Name: Gravitate Gforms API Helper
Plugin URI: http://www.gravitatedesign.com
Description: This is Plugin Helper to allow you to easily setup an External API on Gravity Form Submissions.
Version: 1.0.0
*/


add_action("gform_field_advanced_settings", "ggah_gform_field_advanced_settings", 10, 2);
add_action("gform_editor_js", "ggah_gform_editor_js");
add_filter('gform_tooltips', 'ggah_gform_tooltips');
add_filter('gform_pre_form_settings_save', 'ggah_gform_pre_form_settings_save');
add_filter('gform_form_settings', 'ggah_gform_form_settings', 10, 2);
add_action("gform_after_submission", "ggah_gform_after_submission", 10, 2);



////////////////////////////////////////////////////
////////////////////////////////////////////////////
/*
// HOW TO USE
//
// Place the below code in your functions.php file of your theme or in another plugin.
// replace {your_api_name} with the sanitized (lowercase with underscores) API name you added to the Form Settings.
//
////////////////////////////////////////////////////
////////////////////////////////////////////////////



add_action("ggah_submission_{your_api_name}", "your_function_hook_name_here_{your_api_name}", 10, 3);

function your_function_hook_name_here_{your_api_name} ($data, $form, $api)
{
	$api_failed = false;


	// Send $data to api Here


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


////////////////////////////////////////////////////
////////////////////////////////////////////////////
//
// END HOW TO USE
*/
////////////////////////////////////////////////////
// Gravity Forms Filters
////////////////////////////////////////////////////

function ggah_gform_after_submission($entry, $form)
{
	if(!empty($form['ggah_web_form_name']))
	{
		$custom_apis = array_map('trim', explode(',',$form['ggah_web_form_name']));

		if(!empty($custom_apis))
		{
			foreach ($custom_apis as $api)
			{
				$data = array();
				$api = sanitize_title($api);

				foreach($form['fields'] as $field)
				{
					$value = (isset($entry[$field['id']]) ? $entry[$field['id']] : '');
					if($field['type'] == 'checkbox' && !empty($field['inputs']))
					{
						$values = array();
						foreach($field['inputs'] as $input)
						{
							$values[] = $entry[$input['id']];
						}
						$value = implode(', ', $values);
					}

					if(!empty($field['ggah_'.str_replace('-', '_', $api).'_field']) && !empty($field['id']) && !empty($value))
					{
						$data[trim($field['ggah_'.str_replace('-', '_', $api).'_field'])] = trim($value);
					}
				}
				if(!empty($data))
				{
					// Submit
					do_action( 'ggah_submission_'.str_replace('-', '_', $api), $data, $form, ucwords(str_replace('-', ' ', $api)) );
				}
			}
		}
	}
}

function ggah_gform_field_advanced_settings($position, $form_id)
{
	$form = GFFormsModel::get_form_meta( $form_id );

    //create settings on position 50 (right after Admin Label)
    if($position == 50)
	{
		$custom_apis = array_map('trim', explode(',',rgar($form, 'ggah_web_form_name')));

		if(!empty($custom_apis))
		{
			foreach ($custom_apis as $api)
			{

				$api = sanitize_title($api);

	       		 ?>

		        <li class="ggah_setting field_setting">
		            <label for="field_admin_label">
		                Custom API Field Name - <?php echo ucwords(str_replace('-', ' ', $api));?>
		                <?php gform_tooltip("form_field_ggah_value") ?>
		            </label>
		            <input type="text" class="ggah_<?php echo str_replace('-', '_', $api);?>_setting_field" onchange="SetFieldProperty('ggah_<?php echo str_replace('-', '_', $api);?>_field', this.value);" />
		        </li>

		        <?php
		    }
	    }
    }
}

// Action to inject supporting script to the form editor page
function ggah_gform_editor_js()
{
	if(!empty($_GET['id']))
	{
		$form = GFFormsModel::get_form_meta( $_GET['id'] );
		$custom_apis = array_map('trim', explode(',',rgar($form, 'ggah_web_form_name')));

	    ?>
	    <script type='text/javascript'>
	        //adding setting to fields of type "text"
	        fieldSettings["text"] += ", .ggah_setting";
	        fieldSettings["textarea"] += ", .ggah_setting";
	        fieldSettings["phone"] += ", .ggah_setting";
	        fieldSettings["email"] += ", .ggah_setting";
	        fieldSettings["date"] += ", .ggah_setting";
	        fieldSettings["hidden"] += ", .ggah_setting";
	        fieldSettings["select"] += ", .ggah_setting";

	        //binding to the load field settings event to initialize the checkbox
	        jQuery(document).bind("gform_load_field_settings", function(event, field, form)
	        {
	        	<?php

	        	if(!empty($custom_apis))
				{
					foreach ($custom_apis as $api)
					{
						$api = sanitize_title($api);

						?>
						jQuery(".ggah_<?php echo str_replace('-', '_', $api);?>_setting_field").val((field["ggah_<?php echo str_replace('-', '_', $api);?>_field"] !== 'undefined' ? field["ggah_<?php echo str_replace('-', '_', $api);?>_field"] : ''));
						<?php
					}
				}

				?>

				jQuery(".ggah_setting").css("display", 'block');
	        });
	    </script>
	    <?php
	}
}

// Filter to add a new tooltip
function ggah_gform_tooltips($tooltips)
{

   $tooltips["form_field_ggah_value"] = "<h6>Custom API Field</h6>This is the name of the field to be submitted to the Custom API. Leave blank if you do not want this field submitted to the custom API.";
   $tooltips["form_ggah_web_form_name"] = "<h6>Custom API Calls</h6>If you want this form to be associated with a custom API Call then you need to specify the 'Name' of your Api Call. Leave blank to not associate this form. You can use commas to separate multiple calls.";
   return $tooltips;
}

function ggah_gform_form_settings($settings, $form)
{
	ob_start();
	gform_tooltip("form_ggah_web_form_name");
	$tooltip = ob_get_contents();
    ob_end_clean();

    $settings['Form Options']['ggah_web_form_name'] = '
        <tr>
            <th><label for="ggah_web_form_name">Custom API Calls '.$tooltip.'</label></th>
            <td><input value="' . rgar($form, 'ggah_web_form_name') . '" name="ggah_web_form_name"></td>
        </tr>';

    return $settings;
}

function ggah_gform_pre_form_settings_save($form)
{
    $form['ggah_web_form_name'] = rgpost('ggah_web_form_name');
    return $form;
}

function ggah_error_notification($email='', $service='', $subject='', $message='')
{
	if(empty($email))
	{
		$email = get_bloginfo( 'admin_email' );
	}

	if(empty($subject))
	{
		$subject = 'Error connecting to the '.($service ? $service.' ' : '').'API';
	}

	if(empty($message))
	{
		$message = 'Error connecting to the '.($service ? $service.' ' : '').'API so the entry might not have been captured. You can go into Gravity Forms in your WordPress Admin Panel and search for the Entry that was created around this time ('.date('F j, Y g:ia').').';
	}

	return wp_mail( $email, $subject, $message);

}



