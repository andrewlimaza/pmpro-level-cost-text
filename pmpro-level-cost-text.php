<?php
/*
Plugin Name: PMPro Custom Level Cost Test
Plugin URI: http://www.paidmembershipspro.com/wp/pmpro-custom-level-cost-text/
Description: Manually override the level cost text for each level or discount code.
Version: .1
Author: Stranger Studios
Author URI: http://www.strangerstudios.com
*/

//Set up settings in Advanced Settings
//TODO: Put header at the top of this?
function cost_format_settings() {
    $custom_fields = array(
        'pmpro_hide_now' => array(
            'field_name' => 'pmpro_hide_now',
            'field_type' => 'select',
            'label' => 'Remove the word "now" from level cost text.',
            'options' => array(0 => 'No', 1 => 'Yes'),
        ),
        'pmpro_use_free' => array(
            'field_name' => 'pmpro_use_free',
            'field_type' => 'select',
            'label' => 'Use the word "Free" instead of $0.00',
            'options' => array(0 => 'No', 1 => 'Yes'),
        ),
        'pmpro_use_slash' => array(
            'field_name' => 'pmpro_use_slash',
            'field_type' => 'select',
            'label' => 'Use "/" instead of "per"',
            'options' => array(0 => 'No', 1 => 'Yes'),
        ),
        'pmpro_hide_decimals' => array(
            'field_name' => 'pmpro_hide_decimals',
            'field_type' => 'select',
            'label' => 'Hide unnecessary decimals',
            'options' => array(0 => 'No', 1 => 'Yes'),
        ),
        'pmpro_abbreviate_time' => array(
            'field_name' => 'pmpro_abbreviate_time',
            'field_type' => 'select',
            'label' => 'Abbreviate "Month", "Week", and "Year" to "Mo", "Wk", and "Yr"',
            'options' => array(0 => 'No', 1 => 'Yes'),
        )
    );

    return $custom_fields;
}
add_filter('pmpro_custom_advanced_settings','cost_format_settings');

//Adds format options specified in advanced settings
function format_cost($cost) {
	if(pmpro_getOption('pmpro_hide_now') == 'Yes'){
		$cost = str_replace(" now", "", $cost);
		
	}
	if(pmpro_getOption('pmpro_use_free') == 'Yes'){
		global $pmpro_currency_symbol;
		$cost = str_replace($pmpro_currency_symbol.'0.00', 'free', $cost);
		$cost = str_replace('0.00'.$pmpro_currency_symbol, 'free', $cost);
		$cost = str_replace($pmpro_currency_symbol.'0,00', 'free', $cost);
		$cost = str_replace('0,00'.$pmpro_currency_symbol, 'free', $cost);
	}
	if(pmpro_getOption('pmpro_use_slash') == 'Yes'){
		$cost = str_replace(" per ", "/", $cost);
	}
	if(pmpro_getOption('pmpro_hide_decimals') == 'Yes'){
		$cost = str_replace(".00", "", $cost);
		$cost = str_replace(",00", "", $cost);
	}
	if(pmpro_getOption('pmpro_abbreviate_time') == 'Yes'){
		$cost = str_replace("Year", "Yr", $cost);
		$cost = str_replace("Wk", "Wk", $cost);
		$cost = str_replace("Month", "Mo", $cost);
	}
	return $cost;
}

//Switches out variables within '!!' with the intended value
function apply_variables($custom_text, $cost, $level){
	$custom_text = str_replace("!!default_cost_text!!", format_cost($cost), $custom_text);
	$custom_text = str_replace("!!short_cost_text!!", format_cost(str_replace("The price for membership is ", "", $cost)), $custom_text);
	$custom_text = str_replace("!!level_name!!", $level->{'name'}, $custom_text);
	$custom_text = str_replace("!!level_description!!", $level->{'description'}, $custom_text);
	$custom_text = str_replace("!!level_confirmation_message!!", $level->{'confirmation'}, $custom_text);
	$custom_text = str_replace("!!initial_payment!!", $level->{'initial_payment'}, $custom_text);
	$custom_text = str_replace("!!billing_amount!!", $level->{'billing_amount'}, $custom_text);
	$custom_text = str_replace("!!cycle_number!!", $level->{'cycle_number'}, $custom_text);
	$custom_text = str_replace("!!cycle_period!!", $level->{'cycle_period'}, $custom_text);
	$custom_text = str_replace("!!billing_limit!!", $level->{'billing_limit'}, $custom_text);
	$custom_text = str_replace("!!trial_amount!!", $level->{'trial_amount'}, $custom_text);
	$custom_text = str_replace("!!trial_limit!!", $level->{'trial_limit'}, $custom_text);
	$custom_text = str_replace("!!allow_signups!!", $level->{'allow_signups'}, $custom_text);
	$custom_text = str_replace("!!expiration_number!!", $level->{'expiration_number'}, $custom_text);
	$custom_text = str_replace("!!expiration_period!!", $level->{'expiration_period'}, $custom_text);
	return $custom_text;
}

/*
	This first set of functions adds a level cost text field to the edit membership levels page
*/
//add level cost text field to level price settings
function pclct_pmpro_membership_level_after_other_settings()
{
	$level_id = intval($_REQUEST['edit']);
	if($level_id > 0)
		$level_cost_text = pmpro_getCustomLevelCostText($level_id);	
	else
		$level_cost_text = "";
?>
<h3 class="topborder">Custom Level Cost Text</h3>
<p>To override the default level cost text generated by Paid Memberships Pro. Make sure the prices in this text match your settings above.</p>
<table>
	<tbody class="form-table">
		<tr>
			<td>
				<tr>
					<th scope="row" valign="top"><label for="level_cost_text">Level Cost Text:</label></th>
					<td>
						<textarea name="level_cost_text" rows="4" cols="50"><?php echo esc_textarea($level_cost_text);?></textarea>
						<br /><small>If completely blank, the default text generated by PMPro will be used. You can modify the format of the default text in <a href="../wp-admin/admin.php?page=pmpro-advancedsettings">Advanced Settings.</a></small>
					</td>
				</tr>
			</td>
		</tr> 
		<tr>
			<th scope="row" valign="top"><label for="level_cost_text"><label for="variable_references">Variable Reference:</label></th>
			<td>
				<div id="template_reference" style="overflow:scroll;height:250px;width:800px;;">
					<table class="widefat striped">
						<tr>
							<th colspan=2>Level Information To Be Used In Level Cost Text:</th>
						</tr>
						<tr>
							<td>!!default_cost_text!!</td>
							<td>Ex: "The price for membership is $20.00 now and then $10.00 per Year." This will be formated according to the options in <a href="../wp-admin/admin.php?page=pmpro-advancedsettings">Advanced Settings.</a></td>
						</tr>
						<tr>
							<td>!!short_cost_text!!</td>
							<td>Ex: "$20.00 now and then $10.00 per Year." This will be formated according to the options in <a href="../wp-admin/admin.php?page=pmpro-advancedsettings">Advanced Settings.</a></td>
						</tr>
						<tr>
							<td>!!level_name!!</td>
							<td>The name of the level the user is registering for</td>
						</tr>
						<tr>
							<td>!!level_description!!</td>
							<td>The description for the level the user is registering for</td>
						</tr>
						<tr>
							<td>!!level_confirmation_message!!</td>
							<td>The confirmation message of the level the user is registering for</td>
						</tr>
						<tr>
							<td>!!initial_payment!!</td>
							<td>The initial payment for the level the user is registering for</td>
						</tr>
						<tr>
							<td>!!billing_amount!!</td>
							<td>How much the user has to pay for a recurring subscription</td>
						</tr>
						<tr>
							<td>!!cycle_number!!</td>
							<td>How many cycle periods must pass for one recurring subscription cycle to be complete</td>
						</tr>
						<tr>
							<td>!!cycle_period!!</td>
							<td>The unit of time cycle_number uses to measure</td>
						</tr>
						<tr>
							<td>!!billing_limit!!</td>
							<td>The total number of recurring billing cycles. 0 is infinite.</td>
						</tr>
						<tr>
							<td>!!trial_amount!!</td>
							<td>The cost of one recurring payment during the trial period</td>
						</tr>
						<tr>
							<td>!!trial_limit!!</td>
							<td>The number of billing cycles that are at the trial price</td>
						</tr>
						<tr>
							<td>!!allow_signups!!</td>
							<td>whether people are allowed to sign up for this level</td>
						</tr>
						<tr>
							<td>!!expiration_number!!</td>
							<td>The number expiration periods until the membership expires</td>
						</tr>
						<tr>
							<td>!!expiration_period!!</td>
							<td>The unit of time expiration_number is measured in</td>
						</tr>
					
					</table>
				</div>
			</td>
		</tr>
	</tbody>
</table>
<?php
}
add_action("pmpro_membership_level_after_other_settings", "pclct_pmpro_membership_level_after_other_settings");

//save level cost text when the level is saved/added
function pclct_pmpro_save_membership_level($level_id)
{
	pmpro_saveCustomLevelCostText($level_id, $_REQUEST['level_cost_text']);			//add level cost text for this level			
}
add_action("pmpro_save_membership_level", "pclct_pmpro_save_membership_level");

//update subscription start date based on the discount code used
function pclct_pmpro_level_cost_text_levels($cost, $level)
{
	global $wpdb;
		
	$custom_text = pmpro_getCustomLevelCostText($level->id);		
	if(!empty($custom_text))
	{				
		$cost = apply_variables($custom_text, $cost, $level);
	}
	else{
		$cost = format_cost($cost);
	}
	
	return $cost;
}
add_filter("pmpro_level_cost_text", "pclct_pmpro_level_cost_text_levels", 15, 2);		//priority 15, so discount code text will override this

/*	
	This function will save a level_cost_text for a discount code into an array stored in pmpro_code_level_cost_text.
*/
function pmpro_saveCustomLevelCostText($level_id, $level_cost_text)
{	
	$all_level_cost_text = get_option("pmpro_level_cost_text", array());
		
	$all_level_cost_text[$level_id] = $level_cost_text;
	
	update_option("pmpro_level_cost_text", $all_level_cost_text);
}

/*
	This function will return the level cost text for a discount code/level combo
*/
function pmpro_getCustomLevelCostText($level_id)
{
	$all_level_cost_text = get_option("pmpro_level_cost_text", array());
	
	if(!empty($all_level_cost_text[$level_id]))
	{
		return $all_level_cost_text[$level_id];
	}
	
	//didn't find it
	return "";
}


/*
	This next set of functions adds the level cost text field to the edit discount code page
*/
//add level cost text field to level price settings
function pclct_pmpro_discount_code_after_level_settings($code_id, $level)
{
	$level_cost_text = pmpro_getCodeCustomLevelCostText($code_id, $level->id);	
?>
<table>
<tbody class="form-table">
	<tr>
		<td>
			<tr>
				<th scope="row" valign="top"><label for="level_cost_text">Level Cost Text:</label></th>
				<td>
					<textarea name="level_cost_text[]" rows="4" cols="50"><?php echo esc_textarea($level_cost_text);?></textarea>
					<br /><small>If completely blank, the default text generated by PMPro will be used.</small>
				</td>
			</tr>
		</td>
	</tr> 
</tbody>
</table>
<?php
}
add_action("pmpro_discount_code_after_level_settings", "pclct_pmpro_discount_code_after_level_settings", 10, 2);

//save level cost text for the code when the code is saved/added
function pclct_pmpro_save_discount_code_level($code_id, $level_id)
{
	$all_levels_a = $_REQUEST['all_levels'];							//array of level ids checked for this code
	$level_cost_text_a = $_REQUEST['level_cost_text'];			//level_cost_text for levels checked
		
	if(!empty($all_levels_a))
	{	
		$key = array_search($level_id, $all_levels_a);				//which level is it in the list?				
		pmpro_saveCodeCustomLevelCostText($code_id, $level_id, $level_cost_text_a[$key]);			//add level cost text for this level		
	}	
}
add_action("pmpro_save_discount_code_level", "pclct_pmpro_save_discount_code_level", 10, 2);

//update level cost text based on the discount code used
function pclct_pmpro_level_cost_text_code($cost, $level)
{
	global $wpdb;
		
	//check if a discount code is being used
	if(!empty($level->code_id))
		$code_id = $level->code_id;
	elseif(!empty($_REQUEST['discount_code']))
		$code_id = $wpdb->get_var("SELECT id FROM $wpdb->pmpro_discount_codes WHERE code = '" . $wpdb->escape($_REQUEST['discount_code']) . "' LIMIT 1");
	else
		$code_id = false;
	
	//used?
	if(!empty($code_id))
	{				
		//we have a code						
		$level_cost_text = pmpro_getCodeCustomLevelCostText($code_id, $level->id);		
		
		if(!empty($level_cost_text))
		{
			$cost = $level_cost_text;
			return $cost;
		}		
	}
	
	return $cost;
}
add_filter("pmpro_level_cost_text", "pclct_pmpro_level_cost_text_code", 20, 2);

/*	
	This function will save a level_cost_text for a discount code into an array stored in pmpro_code_level_cost_text.
*/
function pmpro_saveCodeCustomLevelCostText($code_id, $level_id, $level_cost_text)
{	
	$all_level_cost_text = get_option("pmpro_code_level_cost_text", array());
	
	//make sure we have an array for the code
	if(empty($all_level_cost_text[$code_id]))
		$all_level_cost_text[$code_id] = array();
	
	$all_level_cost_text[$code_id][$level_id] = $level_cost_text;
	
	update_option("pmpro_code_level_cost_text", $all_level_cost_text);
}

/*
	This function will return the level cost text for a discount code/level combo
*/
function pmpro_getCodeCustomLevelCostText($code_id, $level_id)
{
	$all_level_cost_text = get_option("pmpro_code_level_cost_text", array());
	
	if(!empty($all_level_cost_text[$code_id]))
	{
		if(!empty($all_level_cost_text[$code_id][$level_id]))
			return $all_level_cost_text[$code_id][$level_id];
	}
	
	//didn't find it
	return "";
}
