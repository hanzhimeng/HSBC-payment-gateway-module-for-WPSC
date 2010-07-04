<?php
/**
 * HSBC payment gateway module for WPSC
 * 
 * 
 * @author Allen Han <hanzhimeng[at]gmail[dot]com>
 * @version 0.1 - July 3, 2010
 * 
 */
$nzshpcrt_gateways[$num]['name'] = 'HSBC ePayments';
$nzshpcrt_gateways[$num]['internalname'] = 'hsbc';
$nzshpcrt_gateways[$num]['function'] = 'gateway_hsbc';
$nzshpcrt_gateways[$num]['form'] = "form_hsbc";
$nzshpcrt_gateways[$num]['submit_function'] = "submit_hsbc";
$nzshpcrt_gateways[$num]['payment_type'] = "hsbc";

if(in_array('hsbc',(array)get_option('custom_gateway_options'))) {
	$curryear = date('Y');
	
	//generate year options
	for($i=0; $i < 10; $i++){
		$years .= "<option value='".$curryear."'>".$curryear."</option>\r\n";
		$curryear++;
	}
 
	$gateway_checkout_form_fields[$nzshpcrt_gateways[$num]['internalname']] = "
	<tr id='wpsc_hsbc_cc_number'>
		<td class='wpsc_hsbc_cc_number1'>Card Number: *</td>
		<td class='wpsc_hsbc_cc_number2'>
			<input type='text' value='' name='card_number' />
		</td>
	</tr>
	<tr id='wpsc_hsbc_cc_expiry'>
		<td class='wpsc_hsbc_cc_expiry1'>Expiry: *</td>
		<td class='wpsc_hsbc_cc_expiry2'>
			<select class='wpsc_ccBox' name='expiry[month]'>
			".$months."
			<option value='01'>01</option>
			<option value='02'>02</option>
			<option value='03'>03</option>
			<option value='04'>04</option>
			<option value='05'>05</option>						
			<option value='06'>06</option>						
			<option value='07'>07</option>					
			<option value='08'>08</option>						
			<option value='09'>09</option>						
			<option value='10'>10</option>						
			<option value='11'>11</option>																			
			<option value='12'>12</option>																			
			</select>
			<select class='wpsc_ccBox' name='expiry[year]'>
			".$years."
			</select>
		</td>
	</tr>
	<tr id='wpsc_hsbc_cc_code' class='card_cvv'>
		<td class='wpsc_hsbc_cc_code1'>CVV: *</td>
		<td class='wpsc_hsbc_cc_code2'><input type='text' size='4' value='' maxlength='4' name='card_code' /></td>
	</tr>";
}

function gateway_hsbc($seperator, $sessionid) {
	global $wpdb;
	$purchase_log_sql = "SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `sessionid`= ".$sessionid." LIMIT 1";
	$purchase_log = $wpdb->get_row($purchase_log_sql,ARRAY_A) ;
	
	$card_no = $_POST['card_number'];
	$cvv = $_POST['card_code'];
	$expiry = $_POST['expiry']['month']."/".substr($_POST['expiry']['year'],2);
	
	$xml = "<EngineDocList>
				<DocVersion DataType='String'>1.0</DocVersion>
				<EngineDoc>
					<ContentType DataType='String'>OrderFormDoc</ContentType>
					<User>
						<Name DataType='String'>".get_option('hsbc_username')."</Name>
						<Password DataType='String'>".get_option('hsbc_password')."</Password>
						<ClientId DataType='S32'>".get_option('hsbc_id')."</ClientId>
					</User>
					<Instructions>
						<Pipeline DataType='String'>Payment</Pipeline>
					</Instructions>
					<OrderFormDoc>
						<Mode DataType='String'>Y</Mode>
						<Consumer>
							<PaymentMech>
								<Type DataType='String'>CreditCard</Type>
								<CreditCard>
									<Number DataType='String'>" . $card_no ."</Number>
									<Cvv2Val DataType='String'>" . $cvv . "</Cvv2Val>
									<Cvv2Indicator DataType='String'>1</Cvv2Indicator>
									<Expires DataType='ExpirationDate'>" . $expiry ."</Expires>
								</CreditCard>
							</PaymentMech>
						</Consumer>
						<Transaction>
							<Type DataType='String'>PreAuth</Type>
							<CurrentTotals>
								<Totals>
									<Total DataType='Money' Currency='".get_option('hsbc_currency')."'>".$perchase_log['totalprice']."</Total>
								</Totals>
							</CurrentTotals>
						</Transaction>
					</OrderFormDoc>
				</EngineDoc>
			</EngineDocList>";
	$ch = curl_init();
	$url = 'https://www.secure-epayments.apixml.hsbc.com';
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
	$data = curl_exec($ch);
	curl_close ($ch);
	
	exit("----><pre>".print_r($data,1)."</pre>");
}

function form_hsbc() {
 	global $wpdb, $wpsc_gateways;
 	$hsbc_currency = get_option('hsbc_currency');
 	$currencies = array(
 		036 => 'Australia, Dollars', 
 		124 => 'Canada, Dollars', 
 		208 => 'Denmark, Kroner',
 		978 => 'Euro Member Countries, Euro', 
 		344 => 'Hong Kong, Dollars', 
 		392 => 'Japan, Yen', 
 		554 => 'New Zealand, Dollars', 
 		578 => 'Norway, Krone', 
 		702 => 'Singapore, Dollars', 
 		752 => 'Sweden, Kronor', 
 		756 => 'Switzerland, Francs', 
 		826 => 'United Kingdom, Pounds', 
 		840 => 'United States of America, Dollars'
 	);
 	$hsbc_currency_options='';
 	foreach($currencies as $k => $c){
 		if ($k == get_option('hsbc_currency')){
 			$hsbc_currency_options .= "<option selected='selected' value='$k'>$c</option>";
 		} else {
 			$hsbc_currency_options .= "<option value='$k'>$c</option>";
 		}
 	}
	
 	$output = "
 	<tr>
 	    <td>HSBC username:</td>
 	    <td>
 	    	<input type='text' size='40' value='".get_option('hsbc_username')."' name='hsbc_username' />
 	    </td>
 	</tr>
 	<tr>
		<td>Password:</td>
		<td>
			<input type='text' size='40' value='".get_option('hsbc_password')."' name='hsbc_password' />
		</td>
 	</tr>
 	<tr>
		<td>Customer ID:</td>
		<td>
			<input type='text' size='40' value='".get_option('hsbc_id')."' name='hsbc_id' />
		</td>
 	</tr>
 	<tr>
		<td>Store Currency:</td>
		<td>
			<select name='hsbc_currency'>
				".$hsbc_currency_options."
			</select>
		</td>
 	</tr>";   
  
$output .= "
   <tr class='update_gateway' >
		<td colspan='2'>
			<div class='submit'>
			<input type='submit' value='".__('Update &raquo;', 'wpsc')."' name='updateoption'/>
		</div>
		</td>
	</tr>
	
	<tr class='firstrowth'>
		<td style='border-bottom: medium none;' colspan='2'>
			<strong class='form_group'>Forms Sent to Gateway</strong>
		</td>
	</tr>
   
    <tr>
      <td>
      First Name Field
      </td>
      <td>
      <select name='hsbc_form[first_name]'>
      ".nzshpcrt_form_field_list(get_option('hsbc_form_first_name'))."
      </select>
      </td>
  </tr>
    <tr>
      <td>
      Last Name Field
      </td>
      <td>
      <select name='hsbc_form[last_name]'>
      ".nzshpcrt_form_field_list(get_option('hsbc_form_last_name'))."
      </select>
      </td>
  </tr>
    <tr>
      <td>
      Address Field
      </td>
      <td>
      <select name='hsbc_form[address]'>
      ".nzshpcrt_form_field_list(get_option('hsbc_form_address'))."
      </select>
      </td>
  </tr>
  <tr>
      <td>
      City Field
      </td>
      <td>
      <select name='hsbc_form[city]'>
      ".nzshpcrt_form_field_list(get_option('hsbc_form_city'))."
      </select>
      </td>
  </tr>
  <tr>
      <td>
      State Field
      </td>
      <td>
      <select name='hsbc_form[state]'>
      ".nzshpcrt_form_field_list(get_option('hsbc_form_state'))."
      </select>
      </td>
  </tr>
  <tr>
      <td>
      Postal code/Zip code Field
      </td>
      <td>
      <select name='hsbc_form[post_code]'>
      ".nzshpcrt_form_field_list(get_option('hsbc_form_post_code'))."
      </select>
      </td>
  </tr>
  <tr>
      <td>
      Country Field
      </td>
      <td>
      <select name='hsbc_form[country]'>
      ".nzshpcrt_form_field_list(get_option('hsbc_form_country'))."
      </select>
      </td>
  </tr> ";
  
  return $output;
}

function submit_hsbc(){
	if($_POST['hsbc_username'] != null) {
		update_option('hsbc_username', $_POST['hsbc_username']);
	}
	  
	if($_POST['hsbc_password'] != null) {
		update_option('hsbc_password', $_POST['hsbc_password']);
	}
	  
	if($_POST['hsbc_id'] != null) {
		update_option('hsbc_id', $_POST['hsbc_id']);
	}
	
	if($_POST['hsbc_currency'] != null) {
		update_option('hsbc_currency', $_POST['hsbc_currency']);
	}
	
	foreach((array)$_POST['hsbc_form'] as $form => $value) {
		update_option(('hsbc_form_'.$form), $value);
	}
	return true;
}
?>