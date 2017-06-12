<?php
/**
* Plugin Name: AisIncentivesControl
* Plugin URI: http://keithbonline.com/
* Description: A custom plugin to show Ais incentives in the Email Template
* Version: 1.0
* Author: Keith Bonarrigo
* Author URI: http://keithbonline.com/
**/
$inc = get_theme_root()."/hulk-ua/chromeStyleIncludes/chromeStylesClasses.php";
require_once($inc);
$CJDR = array(3,4,7,21); //these are the ids for Chrysler, Jeep, Ram, and Dodge

add_action('admin_menu', 'showAisIncentives'); //base level plugin configuration
add_action('admin_post_aisYearSelected', 'showAisYearRange'); //first round of selection - year form selection form has been submitted
add_action('admin_post_aisModelSelected', 'showAisModelRange'); //second phase of selection - base level plugin configuration
add_action('admin_post_aisModelPopulated', 'populateAisModel'); //third phase of selection - base level plugin configuration

////////////////////////////////////////////////////
////////////////////////////////////////////////////
function showAisIncentives(){ //this is the base landing slug for the plugin - set up the UI
    add_menu_page( 'Control Ais Incentives', 'Ais Email Incentives', 'manage_specials', 'control-ais', 'initAisIncentives' ); //adds the button to the menu
}
////////////////////////////////////////////////////
function populateAisModel(){ 
	global $wpdb;
	$incentiveUpdated = '';
	$checkboxSql = ""; //this is added to track the checkboxes as we loop through the post object
	
	$custom_checkboxes_inserted = array(); //this is a placeholder for any custom leases inserted
	
	foreach($_POST as $k=>$v){
		if($k != 'action' && $k != 'divisionId' && !strstr($k, 'year') ){
			if(!stristr($k, 'checkbox')){
				$thisField = explode("_", $k);
				$thisCount = count($thisField);
				$thisIdIndex = $thisCount-1;
				$thisId = $thisField[$thisIdIndex];
				$stopValue = 'end_model_'.$thisId;
				$lease_to_find = "checkbox_lease_".$thisId;
				$finance_to_find = "checkbox_finance_".$thisId;
			}else{ //we're dealing with a checkbox so we need to get the last set of digits to know which incentive to bind to the model
				$thisIncentiveField = explode("_", $k);
				$thisICount = count($thisIncentiveField);
				$thisICount--;
				$thisIncentive = $thisIncentiveField[$thisICount];
			}
			
			if( stristr($k, $finance_to_find) ){
				$checkboxSql .= ", ais_Incentive_1 = ".$thisIncentive.", ais_Incentive_1_Type = 'Finance'";
			}
			if( stristr($k, $lease_to_find) ){
				$checkboxSql .= ", ais_Incentive_2 = ".$thisIncentive.", ais_Incentive_2_Type = 'Lease'";
			}
			
				
				if($thisId != $incentiveUpdated){

					$aisDivisionNumber = 'ais_division_number_'.$thisId;
					$aisYearFinder = 'ais_year_'.$thisId;
					$aisNumberFinder = 'ais_number_'.$thisId;
					$aisOverrideFinder = 'ais_override_'.$thisId;
					$aisOverrideCurrentModelFinder = 'ais_current_model_'.$thisId;
					$aisOverridePreviousFinder = 'ais_override_previous_'.$thisId;
					$aisPriceFinder = 'ais_price_point_'.$thisId;
					
						if(strlen($_POST[$aisPriceFinder])<1){ //there is no value so default it to zero
							$aisPriceFinder = 0;
						}else{
							$aisPriceFinder = str_replace(",", "", $_POST[$aisPriceFinder]);
						}
						
					$aisRebateFinder = 'ais_rebate_'.$thisId;
					$aisMsrpFinder = 'ais_msrp_'.$thisId;
	
						if(strlen($_POST[$aisMsrpFinder])<1){ //there is no value so default it to zero
							$aisMsrpFinder = 0;
						}else{
							$aisMsrpFinder = str_replace(",", "", $_POST[$aisMsrpFinder]);
						}
						
					$aisVin1Finder = 'vin_1_'.$thisId;
					$aisVin2Finder = 'vin_2_'.$thisId;
					$aisVin3Finder = 'vin_3_'.$thisId;
					$aisVin4Finder = 'vin_4_'.$thisId;
					$aisVin5Finder = 'vin_5_'.$thisId;
					
					$aisCusLease_MonthlyPaymentFinder = 'custom_lease_monthly_'.$thisId;
					$aisCusLease_LengthFinder = 'custom_lease_length_'.$thisId;
					$aisCusLease_FirstFinder = 'custom_lease_first_'.$thisId;
					$aisCusLease_DisclosureFinder = 'custom_lease_disclosure_'.$thisId;
					
					$customMonthly = '';
					$customLength = '';
					$customFirst = '';
					$customDisclosure = '';
					$returnExtras = '';
					$returnSerialized = '';
					
					if( !in_array($aisCusLease_DisclosureFinder, $custom_checkboxes_inserted) && strlen($_POST[$aisCusLease_MonthlyPaymentFinder]) > 0 && strlen($_POST[$aisCusLease_LengthFinder]) > 0 && strlen($_POST[$aisCusLease_FirstFinder]) > 0 && strlen($_POST[$aisCusLease_DisclosureFinder]) > 0 ){
						$customMonthly = $_POST[$aisCusLease_MonthlyPaymentFinder];
						$customLength = $_POST[$aisCusLease_LengthFinder];
						$customFirst = $_POST[$aisCusLease_FirstFinder];
						$customDisclosure = $_POST[$aisCusLease_DisclosureFinder]; 
						$returnExtras = array('monthly'=>$customMonthly, 'length'=>$customLength, 'first'=>$customFirst, 'disclosure'=>$customDisclosure);
						$returnSerialized = base64_encode(serialize($returnExtras)); 						
						$checkboxSql .= ", ais_Incentive_2 = 1, ais_Incentive_2_Type = 'Custom Lease', ais_Incentive_2_Extra = '".$returnSerialized."' ";
						if(!in_array($aisCusLease_DisclosureFinder, $custom_checkboxes_inserted)){ array_push($custom_checkboxes_inserted, $aisCusLease_DisclosureFinder); }
						
					}elseif(!in_array($aisCusLease_DisclosureFinder, $custom_checkboxes_inserted)){ //we don't have a custom lease so set the extras field to blank
						$checkboxSql .= ", ais_Incentive_2_Extra = '' ";
						$returnExtras = null;
					}
					
					if($k == $stopValue){ //we've hit the end of the loop for this model so we should update the user input
						
						$incentiveUpdated = $thisId;
						
						$sql = "UPDATE cs_AisIncentiveInfoEmail SET ais_Incentive_1 = '', ais_Incentive_1_Type = '', ais_Incentive_2 = '', ais_Incentive_2_Type = '', ais_Incentive_2_Extra = '' WHERE email_Info_Id = ".$thisId." LIMIT 1";
						$resultDiv = $wpdb->get_results($sql);
						
						$sql = "UPDATE cs_AisIncentiveInfoEmail SET ais_Model_Override = '".$_POST[$aisOverrideFinder]."', ais_Number = ".$_POST[$aisNumberFinder].", ais_Price=".$aisPriceFinder.", ais_Msrp=".$aisMsrpFinder.", ais_Rebate=".str_replace(",", "", $_POST[$aisRebateFinder]).", ais_Vin_1 = '".$_POST[$aisVin1Finder]."', ais_Vin_2 = '".$_POST[$aisVin2Finder]."', ais_Vin_3 = '".$_POST[$aisVin3Finder]."', ais_Vin_4 = '".$_POST[$aisVin4Finder]."', ais_Vin_5 = '".$_POST[$aisVin5Finder]."'".$checkboxSql." WHERE email_Info_Id = ".$thisId." LIMIT 1";
						$resultDiv = $wpdb->get_results($sql);
						$checkboxSql = ''; //reset this for the next model
					}
				}
			
		}
	}
	$yearCount = 0;
	
	foreach($_POST as $k=>$v){
		if(strstr($k, 'year')){
			$urlAppend .= "&year".$yearCount."=".$v;
			$yearCount++;
		}
	}
	
	if($_POST['selectOutside']==1){
		$urlAppend .= "&selectOutside=1";
	}
	
	$adminUrl = "admin.php?page=control-ais&divisionId=".$_POST['divisionId']."&showIncentiveInfo=1".$urlAppend."&showPreview=1";
	wp_redirect(  admin_url( $adminUrl) ); //go back to the admin page
}
////////////////////////////////////////////////////
////////////////////////////////////////////////////
////////////////////////////////////////////////////
function showAisModelInfo(){
	$yearCount = 0;
		foreach($_POST as $k=>$v){
			if(strstr($k, "year")){
				$urlAppend .= "&year".$yearCount."=".$v;
				$yearCount++;
			}
		}
	$adminUrl = "admin.php?page=control-ais&divisionId=".$_POST['divisionId']."&showIncentiveInfo=1".$urlAppend;
	wp_redirect(  admin_url( $adminUrl) ); //go back to the admin page
}
////////////////////////////////////////////////////
function showAisModelRange(){
	global $wpdb; //global database connection
	global $CJDR; //these are the ids for Chrysler, Jeep, Ram, and Dodge
	
	$sqlAdd = "";
	$postCount = 0;
	foreach($_POST as $k=>$v){
		if(strstr($k, "info_vehicle")){
			$thisVal = explode("_", $k);
			$thisInt = $thisVal[2];
			$checkboxToFetch = "model_checkbox_".$thisInt;
			$thisCheck = $_POST[$checkboxToFetch];
			if($thisCheck != "on"){
				$thisVal = explode("_", $v);
				if($postCount == 0){  
					$sqlAdd .= " AND ( "; 
				}else{
					$sqlAdd .= " OR ";
				}
				$sqlAdd .= "( ais_Model = '".str_replace("-", " ", $thisVal[1])."' AND ais_Year = ".$thisVal[0].")";
				$postCount++;
			}
		}	
	}
	if($postCount > 0){ $sqlAdd .= ")"; }
	$sql = "DELETE FROM cs_AisIncentiveInfoEmail WHERE ais_Div_Id = ".$_POST['divisionId'].$sqlAdd;
	$sql = "DELETE FROM cs_AisIncentiveInfoEmail WHERE ais_Div_Id > 1 ".$sqlAdd;
	$resultDiv = $wpdb->get_results($sql);
	
	foreach($_POST as $k=>$v){ //deal with clearing outside vehicles
		if(strstr($k, "info_outside_vehicle")){
			$thisOutsideSplit = explode("_", $k);
			$outsideToCheck = "outside_".$thisOutsideSplit[3]."_".$thisOutsideSplit[4]."_".str_replace(" ", "-", $thisOutsideSplit[5]);
			
			if($_POST[$outsideToCheck] != 'on'){
				$ex = explode("_", $v);
				$ex2 = explode("_", $k);
				$sqlOutsideAdd = " AND ais_Model = '".str_replace("-", " ", $ex[1])."' AND ais_Year = ".$ex[0]." AND ais_Div_Id = ".$ex2[3]." LIMIT 1";
				$sql = "DELETE FROM cs_AisIncentiveInfoEmail WHERE ais_Div_Id > 1 ".$sqlOutsideAdd;
				$resultDiv = $wpdb->get_results($sql);
			}
			
		}
	} //end deal with clearing outside vehicles
			
	foreach($_POST as $k=>$v){
		$carName = "";
		if(strstr($k, 'model_checkbox')){
			$thisVal = explode("_", $k);
			$thisInt = $thisVal[2];
			$vehicleToFetch = "info_vehicle_".$thisInt;
			$thisVehicleRaw = $_POST[$vehicleToFetch];
			
			$thisCar = explode('_', $thisVehicleRaw);
				for($i=1;$i<=count($thisCar)-2;$i++){
					$carName .= $thisCar[$i]." ";
				}
				
			$thisCarDivision = $thisCar[count($thisCar)-1];
			
			$carName = trim(str_replace("-", " ", $carName));
			$sql = "SELECT email_Info_Id FROM cs_AisIncentiveInfoEmail WHERE ais_Div_Id = ".$thisCarDivision." AND ais_Year = ".$thisCar[0]." AND ais_Model = '".$carName."' LIMIT 1";			
			$resultDiv = $wpdb->get_results($sql);

			if(count($resultDiv)==1){
				//do nothing
			}else{
				$sql = "INSERT INTO cs_AisIncentiveInfoEmail(ais_Div_Id, ais_Year, ais_Model) VALUES(".$thisCarDivision.", ".$thisCar[0].", '".$carName."')";
				$resultDiv = $wpdb->get_results($sql);
			}
		}
		
		//outside models
		if(strstr($k, 'outside') && $v == 'on'){
			$outsideExplode = explode("_", $k);
			
			$checkOutsideSelect = "SELECT * FROM cs_AisIncentiveInfoEmail WHERE ais_Div_Id = ".$outsideExplode[1]." AND ais_Model = '".str_replace("-", " ", $outsideExplode[3])."' AND ais_Year = ".$outsideExplode[2]." LIMIT 1";
			$outsideDiv = $wpdb->get_results($checkOutsideSelect);
				
				if(count($outsideDiv)==0){
					$insertOutside = "INSERT INTO cs_AisIncentiveInfoEmail(ais_Div_Id, ais_Model, ais_Year) VALUES(".$outsideExplode[1].", '".str_replace("-", " ", $outsideExplode[3])."', ".$outsideExplode[2].")";
					$wpdb->get_results($insertOutside);
				}
		}
		//end outside models
	}
	$yearCount = 0;
	foreach($_POST as $k=>$v){
		if(strstr($k, 'year')){
			$urlAppend .= "&year".$yearCount."=".$v;
			$yearCount++;
		}
	}
	
	if($_POST['all_models_checkbox']){
			$urlAppend .= "&showAllModels=1";
	}
	if($_POST['selectOutside'] && $_POST['selectOutside'] == 1){
			$urlAppend .= "&selectOutside=1";
	}
	
	$adminUrl = "admin.php?page=control-ais&divisionId=".$_POST['divisionId']."&showIncentiveInfo=1".$urlAppend;
	wp_redirect(  admin_url( $adminUrl) ); //go back to the admin page
}
////////////////////////////////////////////////////
////////////////////////////////////////////////////
function showAisYearRange(){
	global $wpdb; //global database connection
	$yearSql = "";
	$urlAppend = '';
	$yearCount = 0;
		
		foreach($_POST['ais_year_select'] as $v){
			$urlAppend .= "&year".$yearCount."=".$v;
			$yearCount++;
		}	
		if($_POST['modelCustomYears']){
			$urlAppend .= "&selectOutside=1&year0=2016&year1=2017";
		}
		
	$adminUrl = "admin.php?page=control-ais&divisionId=".$_POST['divisionId'].$urlAppend;
	wp_redirect(  admin_url( $adminUrl) ); //go back to the admin page
}
////////////////////////////////////////////////////
function setAisIncentive(){
	global $wpdb; //global database connection
	foreach($_POST as $k=>$v){

		if(strstr($k, 'checkbox')){
			if($v == "on") { 
				$formEx = explode("_", $k);
				$thisNumber = $formEx[1];
				$aisNum = "ais_number_".$thisNumber;
				$aisPrice = "ais_price_point_".$thisNumber;
				
				$vinNumbersUpdate = "";
				$vinNumbersInsert1 = "";
				$vinNumbersInsert2 = "";
				
				if($_POST[$aisNum] < 6){
					
					$vinsCounted = 0;
					for($i=1;$i<6;$i++){
						$vinToCheck = "vin_".$i."_".$thisNumber;
						$vinPost = $_POST[$vinToCheck];
						if(strlen($vinPost) > 0){
							$vinsCounted++;
							$vinNumbersInsert1 .= ", ais_VIN_".$i;
							$vinNumbersInsert2 .= ", '".$vinPost."'";
							$vinNumbersUpdate .= ", ais_VIN_$i = '".$vinPost."'";
						}
					}
				}
				$sql = "SELECT email_Info_Id FROM cs_IncentiveInfoEmail WHERE ais_Id = ".$thisNumber." LIMIT 1";
				$resultDiv = $wpdb->get_results($sql);
					if(count($resultDiv)<1){ //we don't have this registered yet - set it
						$sql = "INSERT INTO cs_IncentiveInfoEmail(ais_Id, ais_Number, ais_Price".$vinNumbersInsert1.") VALUES(".$thisNumber.", ".$_POST[$aisNum].", ".$_POST[$aisPrice].$vinNumbersInsert2.")"; 
					}else{
						$sql = "UPDATE cs_IncentiveInfoEmail SET ais_Number = ".$_POST[$aisNum].", ais_Price=".str_replace(",", "", $_POST[$aisPrice]).$vinNumbersUpdate." WHERE ais_Id = ".$thisNumber." LIMIT 1";
					}
				$resultDiv = $wpdb->get_results($sql);
					foreach($_POST['ais_year_select'] as $v){
						$urlAppend .= "&year".$yearCount."=".$v;
						$yearCount++;
					}
				$adminUrl = "admin.php?page=control-ais&divisionId=".$_POST['divisionId']."&showIncentiveInfo=1".$urlAppend;
				wp_redirect(  admin_url( $adminUrl) ); //go back to the admin page	
			}
		}
	}
}
////////////////////////////////////////////////////
function checkEmailData($resultDiv2, $incentiveId){
	foreach($resultDiv2 as $k=>$v){
		if($v->ais_Id == $incentiveId){
			return $v; //we've found incentive info - return it
		}
	}
}
////////////////////////////////////////////////////
////////////////////////////////////////////////////
function formatAisEmailIncentives_New($resultDiv, $resultDiv2, $divisionId, $yearArray){
	global $wpdb; //global database object
	$yearShown = "";
	$modelShown = "";
	echo "<div style='margin-left:50px'>";
	?>
	<style>
		.ais_form_inner_data { width: 150px; display:inline-block; }
	</style>
	<?
	echo "<form action='admin-post.php' method='post' id='populateAisInfo' name='populateAisInfo' />";
	echo "<input type='hidden' name='action' id='action' value='aisModelPopulated' />";
	echo "<input type='hidden' name='divisionId' id='divisionId' value='".$divisionId."' />";
	
		if($_GET['selectOutside'] && $_GET['selectOutside']==1){
			echo "<input type='hidden' name='selectOutside' id='selectOutside' value='1' />";
		}
	
	echo "<div id='error_messages' name='error_messages' style='font-weight:bold; color:red'></div>";
	
	for($i=0;$i<count($yearArray);$i++){
		echo "<input type='hidden' name='year".$i."' id='year".$i."' value='".$yearArray[$i]."' />";
	} 
			
			foreach($resultDiv2 as $rk=>$rv){				
				
				if(strlen($rv->ais_Model_Override)>0){
					//we need to build the end of the url with the years
					$yearLooper = 0;
					$getAdd = '';
					echo "<h3 style='margin-bottom:4px'>".$rv->ais_Year." ".$rv->ais_Model_Override."</h3>";
					echo "<div class='ais_form_inner_data' style='width:90%; margin-left:66px; margin-bottom:15px'>";
					echo "(*Default incentive model name '".$rv->ais_Model."'). Clear the 'Name Override' field to revert back to default name ";
					echo "</div>";
				}else{
					echo "<h3>".$rv->ais_Year." ".$rv->ais_Model."</h3>";
				}
				
				if($rv->Model != $modelShown){ $modelShown = $rv->ais_Model; echo "<div style='height:10px'></div>"; }

				echo "<div style='margin-left:20px'>";
				echo "<div style='margin-left:50px;' ";
				echo "id='lower_info_".$rv->email_Info_Id."' name='lower_info_".$rv->email_Info_Id."' >";
				echo "<input type='hidden' class='ais_number' id='ais_current_model_".$rv->email_Info_Id."' name='ais_current_model_".$rv->email_Info_Id."' value='".$rv->ais_Model."' />";
				echo "<div class='ais_form_inner_data'>Name Override:</div> <input type='text' id='ais_override_".$rv->email_Info_Id."' name='ais_override_".$rv->email_Info_Id."'";
				echo " value= '";
					
					if(strlen($rv->ais_Model_Override)>0){
						echo $rv->ais_Model_Override;
					}
					
				echo "' ";
				echo " style='width:300px' /><br />";
				
					if(strlen($newName['name'])>0){
						echo "<input type='hidden' name='ais_override_previous_".$rv->email_Info_Id."' id='' value='".$newName['id']."' />";
					}
				
				echo "<input type='hidden' id='ais_division_number_".$rv->email_Info_Id."' name='ais_division_number_".$rv->email_Info_Id."' value='".$rv->ais_Div_Id."' />";
				echo "<div class='ais_form_inner_data'>Number Available:</div> <input type='text' class='ais_number' id='ais_number_".$rv->email_Info_Id."' name='ais_number_".$rv->email_Info_Id."'";
				echo " value= '".$rv->ais_Number."' ";
				echo " style='width:50px' /><br />";
				
				
				echo "<div style='margin:15px 0px 15px 20px'>
						<input type='checkbox' class='show_price_check' name='show_price_info_".$rv->email_Info_Id."' id='show_price_info_".$rv->email_Info_Id."' ";
				if($rv->ais_Price > 0 || $rv->ais_Msrp > 0 || $rv->ais_Rebate > 0) echo " checked ";
				echo " /> Show Price/MSRP/Rebate Info
					 </div>";
				?>
				<style>
				.ais_price_container { margin-left:25px; }
				</style>
				<?
				echo "<div class='ais_price_container' id='ais_price_container_".$rv->email_Info_Id."' name='ais_price_container_".$rv->email_Info_Id."' ";
					
				if($rv->ais_Price == 0 && $rv->ais_Msrp == 0 && $rv->ais_Rebate == 0){ echo " style='display:none' "; }
				
				echo ">";
					echo "<div class='ais_form_inner_data' style='width:125px'>";
					echo "Price Point:</div> <input type='text' id='ais_price_point_".$rv->email_Info_Id."' name='ais_price_point_".$rv->email_Info_Id."'";
					echo " value= '".$rv->ais_Price."' ";
					echo "/><br />";
					echo "<div class='ais_form_inner_data' style='width:125px'>";
					echo "MSRP:</div> <input type='text' id='ais_msrp_".$rv->email_Info_Id."' name='ais_msrp_".$rv->email_Info_Id."'";
					echo " value= '".$rv->ais_Msrp."' ";
					echo "/><br />";
					echo "<div class='ais_form_inner_data' style='width:125px'>";
					echo "Rebate:</div> <input type='text' id='ais_rebate_".$rv->email_Info_Id."' name='ais_rebate_".$rv->email_Info_Id."'";
					echo " value= '".$rv->ais_Rebate."' ";
					echo "/><br />";
				echo "</div>";
				
				
				echo "<div ";
				echo " class='ais_vins' id='vin_container_".$rv->email_Info_Id."' name='vin_container_".$rv->email_Info_Id."'";
				echo " style='padding-left:4px;";
				
				if( $rv->ais_Number == 0 || $rv->ais_Number > 6) echo "display:none ";

				echo "'"; //close style
				echo " style='padding-left:4px' >";
				echo "<div class='ais_form_inner_data'>VINs:</div>";
				echo "<input type='text' class='ais_vin' id='vin_1_".$rv->email_Info_Id."' name='vin_1_".$rv->email_Info_Id."' ";
				echo " value= '".$rv->ais_Vin_1."' ";
				echo "/> 
					  <input type='text' class='ais_vin' id='vin_2_".$rv->email_Info_Id."' name='vin_2_".$rv->email_Info_Id."' ";
				echo " value= '".$rv->ais_Vin_2."' ";
				echo "/> 
					  <input type='text' class='ais_vin' id='vin_3_".$rv->email_Info_Id."' name='vin_3_".$rv->email_Info_Id."' ";
				echo " value= '".$rv->ais_Vin_3."' ";
				echo "/> 
					  <input type='text' class='ais_vin' id='vin_4_".$rv->email_Info_Id."' name='vin_4_".$rv->email_Info_Id."' ";
				echo " value= '".$rv->ais_Vin_4."' ";
				echo "/> 
					  <input type='text' class='ais_vin' id='vin_5_".$rv->email_Info_Id."' name='vin_5_".$rv->email_Info_Id."' ";
				echo " value= '".$rv->ais_Vin_5."' ";
				echo "/>
					  </div>";
					  
					  $sql = "SELECT cs_IncentiveInfoNew.* FROM cs_IncentiveInfoNew WHERE divisionId = ".$rv->ais_Div_Id." AND Year = ".$rv->ais_Year." AND Model = '".$rv->ais_Model."' AND IncentiveType LIKE '%APR%' ORDER BY Year, Model";
					  $resultDivIncentive = $wpdb->get_results($sql);
					 
					 if(count($resultDivIncentive)>0){
						  echo "<h4>Finance Offers (Choose one)</h4>";
					  }else{
						  echo "<h4>No Finance Offers Found For This Year/Model</h4>";
					  }
					  
					  echo "<div style='margin-left:20px' id='finance_offers_".$rv->ais_Div_Id."' name='finance_offers_".$rv->ais_Div_Id."'>";
						
						foreach($resultDivIncentive as $nk=>$nv){
							echo "<input class='ais-checkbox' type='checkbox' name='checkbox_finance_".$rv->email_Info_Id."_".$nv->cs_IncentinveInfo."' id='checkbox_finance_".$rv->email_Info_Id."_".$nv->cs_IncentinveInfo."' ";
								if($rv->ais_Incentive_1 == $nv->cs_IncentinveInfo ) echo " checked='checked' ";
							echo "> ".$nv->ProgramDescription." - ".$nv->ProgramText;
							echo "<br />";
						}
						
					  echo "</div>";
					  
					  
					  $sql = "SELECT cs_IncentiveInfoNew.* FROM cs_IncentiveInfoNew WHERE divisionId = ".$rv->ais_Div_Id." AND Year = ".$rv->ais_Year." AND Model = '".$rv->ais_Model."' AND IncentiveType LIKE '%Lease%' ORDER BY Year, Model";
					  $resultDivIncentive = $wpdb->get_results($sql);
					 
   					  echo "<h4>Lease Offers ";
					  if(count($resultDivIncentive)>0) echo "(Choose one or create a custom lease)";
					  echo "</h4>";
					  
					  echo "<div style='margin-left:20px' id='lease_offers_".$rv->ais_Div_Id."' name='lease_offers_".$rv->ais_Div_Id."'>";
						
						foreach($resultDivIncentive as $nk=>$nv){
							echo "<input class='ais-checkbox' type='checkbox' name='checkbox_lease_".$rv->email_Info_Id."_".$nv->cs_IncentinveInfo."' id='checkbox_lease_".$rv->email_Info_Id."_".$nv->cs_IncentinveInfo."' ";
								if($rv->ais_Incentive_2 == $nv->cs_IncentinveInfo ) echo " checked='checked' ";
							echo "> ".$nv->ProgramDescription." - ".$nv->ProgramText;
							echo "<br />";
						}
						
					   //custom lease section
					   $extras = array();
					   if(strlen($rv->ais_Incentive_2_Extra)){
						 $extras = unserialize(base64_decode($rv->ais_Incentive_2_Extra));
					   }
						
						echo "<input class='ais-checkbox-custom' type='checkbox' name='checkbox_custom_lease_".$rv->email_Info_Id."' id='checkbox_custom_lease_".$rv->email_Info_Id."' ";
						
						if(count($extras)>0){
							echo " checked ";
						}
						
						echo "> Add a Custom Lease";
					  echo "</div>";
					  
					  echo "<div style='margin:10px 0px 0px 20px' id='custom_lease_offers_".$rv->email_Info_Id."' name='custom_lease_offers_".$rv->email_Info_Id."'></div>";
					  echo "<div style='";
					  
					  if(count($extras) == 0)echo "display:none;";
					  
					  echo "margin:10px 0px 0px 45px' name='lease_custom_div_".$rv->email_Info_Id."' id='lease_custom_div_".$rv->email_Info_Id."' style='display:block'>";
					  echo "<div style='width:200px; display: inline-block'>Monthly Payment: </div><input type='text' class='custom_lease_field' name='custom_lease_monthly_".$rv->email_Info_Id."' id='custom_lease_monthly_".$rv->email_Info_Id."' value='".$extras['monthly']."'><br />";
					  echo "<div style='width:200px; display: inline-block'>Lease Length in Months: </div><input type='text' class='custom_lease_field' name='custom_lease_length_".$rv->email_Info_Id."' id='custom_lease_length_".$rv->email_Info_Id."' value='".$extras['length']."'><br />";
					  echo "<div style='width:200px; display: inline-block'>Due at Signing/First Payment: </div><input type='text' class='custom_lease_field' name='custom_lease_first_".$rv->email_Info_Id."' id='custom_lease_first_".$rv->email_Info_Id."' value='".$extras['first']."'><br />";
					  echo "<br />Disclosure Text:<br />";
					  echo "<textarea class='custom_lease_field' name='custom_lease_disclosure_".$rv->email_Info_Id."' id='custom_lease_disclosure_".$rv->email_Info_Id."' style='vertical-align:top; width:600px; height:250px'>";
					  echo stripslashes($extras['disclosure']);
					  echo "</textarea>";
					  echo "</div>";
					  //end custom lease section
					  echo "<input type='hidden' name='end_model_".$rv->email_Info_Id."' id='end_model_".$rv->email_Info_Id."' value='1' />";
				echo "</div>"; 
				echo "</div>";	
			}
			if(count($resultDiv2)>0){
				echo "<input style='margin:20px 0px 0px 60px' type='submit' value='Set Incentive Values' /><br />";
			}else{
				echo "<br />No models selected";
			} 
	echo "</form>";
	echo "</div>";
	?>
	<script>
	///////////////////////////////////////////////////////
	///////////////////////////////////////////////////////
	jQuery('.customLease').on('click', function() {
		var myId = jQuery(this).attr('id');
		var idSplit = myId.split("_");
		var divToShow = "custom_lease_div_" + idSplit[1];
		jQuery("#" + divToShow).show();
	});
	///////////////////////////////////////////////////////
	///////////////////////////////////////////////////////
	jQuery('.ais_number').on('blur', function() {
		var myId = jQuery(this).attr('id');
		var idSplit = myId.split("_");
		var divToShow = "vin_container_" + idSplit[2];
		var myVal = jQuery(this).val();
		if(myVal <= 5){ 
			jQuery('#' + divToShow).show();
		}else{
			jQuery('#' + divToShow).hide();
		}
	});
	///////////////////////////////////////////////////////
	///////////////////////////////////////////////////////
	jQuery('.custom_lease_field').on('focus', function() {
		var myId = jQuery(this).attr('id');
		var idSplit = myId.split("_");
		var checksToCheck = "checkbox_lease_" + idSplit[3];
		jQuery(".ais-checkbox").each(function(){
			var myId = jQuery(this).attr('id');
				if( myId.indexOf(checksToCheck) > -1 ){
					jQuery(this).removeAttr('checked');
				}
		});
	});
	///////////////////////////////////////////////////////
	///////////////////////////////////////////////////////
	jQuery('.ais-checkbox-custom').on('change', function() {
		var myId = jQuery(this).attr('id');		
		var idSplit = myId.split("_");
		
		if(myId.indexOf('checkbox_custom_lease_') > -1){
			var myCustomLease = "lease_custom_div_" + idSplit[3];
			
			if (jQuery(this).is(':checked')) {
				jQuery("#" + myCustomLease).show();
				
				//uncheck the rest
				var toDeselect = "checkbox_lease_" + idSplit[3];
				jQuery('.ais-checkbox').each(function(){
					
					var myIdToCompare = jQuery(this).attr('id'); //this is the id of the checkbox we might be deselecting
					console.log(" Im looking to deselect " + toDeselect);
					var match = myIdToCompare.indexOf( toDeselect );
					console.log(match);
					if(match > -1){ //we've matched the checkbox series - now we need to compare the full id
							jQuery("#" + myIdToCompare).removeAttr('checked');
					}
				});
				//end uncheck
				
			}else{ //they have deselected the custom lease checkbox so we need to clear out all the values so it doesn't stick
				var montlyToReset = "custom_lease_monthly_" + idSplit[3]; //get out naming values to find, clear, and hide
				var lengthToReset = "custom_lease_length_" + idSplit[3];
				var firstToReset = "custom_lease_first_" + idSplit[3];
				var disclosureToReset = "custom_lease_disclosure_" + idSplit[3];
				var checkboxToReset = "checkbox_custom_lease_" + idSplit[3];
				var divToHide = "lease_custom_div_" + idSplit[3];
				
				jQuery("#" + montlyToReset).val("");
				jQuery("#" + lengthToReset).val("");
				jQuery("#" + firstToReset).val("");
				jQuery("#" + disclosureToReset).val("");
				jQuery("#" + myCustomLease).hide();
			}	
		}
		//
	});
	///////////////////////////////////////////////////////
	///////////////////////////////////////////////////////
	jQuery('.ais-checkbox').on('change', function() {
		var myId = jQuery(this).attr('id');	//get the checkbox id	
		var idSplit = myId.split("_");	//break it up to get the number
		
		checksToCheck = idSplit[0] + "_" + idSplit[1] + "_" + idSplit[2]; //this is the naming convention we're seeking to unset if the id isn't the same as the one that was just checked
		
		if( idSplit[1].indexOf('lease') > -1 ){ //we've clicked a regular lease checkbox so we need to deselect the custom lease

			var montlyToReset = "custom_lease_monthly_" + idSplit[2]; //get out naming values to find, clear, and hide
			var lengthToReset = "custom_lease_length_" + idSplit[2];
			var firstToReset = "custom_lease_first_" + idSplit[2];
			var disclosureToReset = "custom_lease_disclosure_" + idSplit[2];
			var checkboxToReset = "checkbox_custom_lease_" + idSplit[2];
			var divToHide = "lease_custom_div_" + idSplit[2];

			jQuery("#" + checkboxToReset).removeAttr('checked'); //uncheck our custom lease box
			
			jQuery("#" + montlyToReset).val("");
			jQuery("#" + lengthToReset).val("");
			jQuery("#" + firstToReset).val("");
			jQuery("#" + disclosureToReset).val("");
			jQuery("#" + divToHide).hide();

		}
		
		jQuery('.ais-checkbox').each(function(){
			var myIdToCompare = jQuery(this).attr('id');
			var match = myIdToCompare.indexOf( checksToCheck );

			if(match > -1){ //we've matched the checkbox series - now we need to compare the full id
				var match2 = myIdToCompare.indexOf( myId );
				if(match2 < 0){
					jQuery("#" + myIdToCompare).removeAttr('checked');
				}

			}
		});
		
		var thisForm = "lower_info_" + idSplit[1];
		if( jQuery('#' + thisForm).is(":visible") ){ //toggle the lease form visibility 
			jQuery('#' + thisForm).hide();
		}else{
			jQuery('#' + thisForm).show();
		}
		
	});
	///////////////////////////////////////////////////////
	//jquery to toggle model reordering for the email incentives
	///////////////////////////////////////////////////////
	jQuery('#populateAisInfo').on('submit', function() { 
		var errorCount = 0;
		var numberErrorCount = 0;
		var vinErrorCount = 0;
		var priceErrorCount = 0;
		var msrpErrorCount = 0;
		
		jQuery(this).children().children().each( function(){
			var myId = jQuery(this).attr('id');
			var idSplit = myId.split("_");
			
			var vinContainerToShow = "vin_container_" + idSplit[2];
			var numberToShow = "ais_number_" + idSplit[2];
			var numberToCheck = jQuery('#' + numberToShow);

						
			var priceToShow = "ais_price_point_" + idSplit[2];
			priceToShow = jQuery("#" + priceToShow);
			
			var msrpToShow = "ais_msrp_" + idSplit[2];
			msrpToShow = jQuery("#" + msrpToShow);
			
			if(numberToCheck.val() <= 0){
				numberToCheck.css("background-color", "#fcd9d9");
				errorCount++;
				numberErrorCount++;
			}
			
			var vin_1 = "vin_1_" + idSplit[2];
			var vin_2 = "vin_2_" + idSplit[2];
			var vin_3 = "vin_3_" + idSplit[2];
			var vin_4 = "vin_4_" + idSplit[2];
			var vin_5 = "vin_5_" + idSplit[2];
			
			vin_1 = jQuery("#" + vin_1);
			vin_2 = jQuery("#" + vin_2);
			vin_3 = jQuery("#" + vin_3);
			vin_4 = jQuery("#" + vin_4);
			vin_5 = jQuery("#" + vin_5);
			
			if(numberToCheck.val() < 6){ //we need to check the VINs to make sure that they're filled in
				if( vin_1.val().length < 1 && numberToCheck.val()>=1 ){
					vin_1.parent().show();
					vin_1.css("background-color", "#fcd9d9");
					errorCount++;
					vinErrorCount++;
				}
				if( vin_2.val().length < 1 && numberToCheck.val()>=2 ){
					vin_2.parent().show();
					vin_2.css("background-color", "#fcd9d9");
					errorCount++;
					vinErrorCount++;
				}
				if( vin_3.val().length < 1 && numberToCheck.val()>=3 ){
					vin_3.parent().show();
					vin_3.css("background-color", "#fcd9d9");
					errorCount++;
					vinErrorCount++;
				}
				if( vin_4.val().length < 1 && numberToCheck.val()>=4 ){
					vin_4.parent().show();
					vin_4.css("background-color", "#fcd9d9");
					errorCount++;
					vinErrorCount++;
				}
				if( vin_5.val().length < 1 && numberToCheck.val()>=5 ){
					vin_5.parent().show();
					vin_5.css("background-color", "#fcd9d9");
					errorCount++;
					vinErrorCount++;
				}
			}
			
		});
		if(errorCount > 0){
			jQuery("#error_messages").text('You have errors or omissions that need your attention');
			if(numberErrorCount > 0){ 
				jQuery("#error_messages").append('<br />You need to have a number available filled in for all models and it must be greater than zero');	
			}

			if(vinErrorCount > 0){ jQuery("#error_messages").append('<br />You need to have VIN numbers filled in for the available vehicles if the number available for any model is less than 6');	}	
			jQuery("#error_messages").append('<br /><br />Please see the areas in red below for correction');
			
			return false;
		}else{
			return true;
		}
		
	});
	///////////////////////////////////////////////////////
	//jquery to toggle model reordering for the email incentives
	///////////////////////////////////////////////////////
	jQuery('.show_price_check').on('change', function() { 
		var myId = jQuery(this).attr('id');
		var idSplit = myId.split("_");
		var thisPriceDiv = "ais_price_container_" + idSplit[3];
		var thisPricePoint = "ais_price_point_" + idSplit[3];
		var thisMsrp = "ais_msrp_" + idSplit[3];
		var thisRebate = "ais_rebate_" + idSplit[3];
		
		if( jQuery('#' + thisPriceDiv).is(":visible") ){ //toggle the lease form visibility 
			jQuery('#' + thisPriceDiv).hide();
			
			jQuery('#' + thisPricePoint).val('0');
			jQuery('#' + thisMsrp).val('0');
			jQuery('#' + thisRebate).val('0');
			
		}else{
			jQuery('#' + thisPriceDiv).show();
		}
	}); 
	///////////////////////////////////////////////////////
	//jquery to toggle model reordering for the email incentives
	///////////////////////////////////////////////////////
	jQuery('.ais_number').on('change', function() { 
		var myId = jQuery(this).attr('id');
		var idSplit = myId.split("_");
		myId = idSplit[2];
		
		var myVal = jQuery(this).val();
		if(myVal < 6){
			var vinsToShow = "vin_container_" + myId;
			jQuery('#' + vinsToShow).show();
		}
		
	});
	///////////////////////////////////////////////////////
	</script>
	<?	
}
////////////////////////////////////////////////////
////////////////////////////////////////////////////
function initAisIncentives(){
	global $wpdb; //global database connection		
	global $dealership;
	global $CJDR; //these are the ids for Chrysler, Jeep, Ram, and Dodge
	
	$wp_upload = wp_upload_dir();
	$urlArray = explode("/", $wp_upload['url']);
	$url = $urlArray[2]; //get the url of the site we're navigating
	$make = dh_get_make_from_dealership();
	
	//print out basic dealership info
	echo "<br />Current Domain is <b>".$url."</b><br />";
	echo "Current make is <b>".$make."</b><br />";
	$makeUpper = strtoupper($make);
	
		if($_GET['showPreview']){ //we have the flag showing that someone has populated this so show the preview link
			echo "<a href='http://".$url."/aisemailoutput' target='_blank'>Show Current Email Output</a><br />";
		}
		
	$resultDiv = $wpdb->get_results("SELECT * FROM cs_offerlogixDivision WHERE divisionName LIKE '".$makeUpper."'  ORDER BY divisionName LIMIT 1");
	$divisionId = $resultDiv[0]->divisionID;
	
		//////////////////////
		//exception for volkswagen
		/////////////////////
		if(strstr($url, 'vwofportland.com')){
			$divisionId = 13;
			$_GET['divisionId'] = 13; //reset the GET variable for the rest of the script to pass the right division id
		}elseif(strstr($url, 'dickhannahvolkswagen.com')){
			$divisionId = 12;
			$_GET['divisionId'] = 12; //reset the GET variable for the rest of the script to pass the right division id
		}
		//////////////////////
		//end exception for volkswagen
		/////////////////////
	
	/////////////////////////
	$yearFinder = ""; //this is a flag for the CJDR exception where we tag on the other division ids to pull them all together
	$incentiveFinder = ""; //this is a flag for the CJDR exception where we tag on the other division ids to pull them all together

		if(in_array($dealership['dealership_id'], $CJDR)){ //if this dealership is one of the CJRD makes
			for($i=0;$i<count($CJDR);$i++){ //add the other 3 dealership makes to show along with this one
				if( $CJDR[$i]!= $dealership['dealership_id'] ){ //build the query additions for the cjdr brands
					$yearFinder .= " OR divisionId = ".$CJDR[$i];
					$incentiveFinder .= " OR ais_Div_Id = ".$CJDR[$i];
				}
			} //end for
		} //end if
	/////////////////////////
	
	$resultDiv = $wpdb->get_results("SELECT Distinct cs_IncentiveInfoNew.Year FROM cs_IncentiveInfoNew WHERE divisionId = $divisionId $yearFinder ORDER BY Year"); //get our year range
			
			$yearArray = array();
			foreach($_GET as $k=>$v){ //check to see if we have a year range
				if(strstr($k, 'year')){ //this is a get value that holds year data
					array_push($yearArray, $v); //push it to the years array for later use
				} //end if
			} //end for
			
	if(!$_GET['divisionId']){ //show the year range select
		
		echo "<form action='admin-post.php' method='post' id='ais_year_select' name='ais_year_select'>";
		echo "<input type='hidden' name='action' id='action' value='aisYearSelected' />";
		echo "<input type='hidden' name='divisionId' id='divisionId' value='".$divisionId."' />";
		
		if(count($resultDiv) > 0){ //we have incentives
			echo "<b>Select Model Year(s)</b><br /><br />";
			echo "<select id='ais_year_select[]' name='ais_year_select[]' multiple>"; //set up the year range toggle 
				foreach($resultDiv as $rk=>$rv){
					echo "<option value='".$rv->Year."' ";
						if(in_array($rv->Year, $yearArray)){ echo " selected "; }
					echo ">".$rv->Year."</option>";
				} //end for
			echo "</select><br />";
			echo "<input type='submit' style='vertical-align:top' value='Select Year Range' />";
			
		}else{ //we don't have any incentives for this brand
			echo "<br /><br />No AIS Incentives for this brand<br /><br />";
			echo "<input type='hidden' name='modelCustomYears' id='modelCustomYears' value='1' />";
			echo "<input type='submit' style='vertical-align:top' value='Select year range from Inventory table for custom model entries' />";
		}
		echo "</form>";
		
	}else{ //noone has sent the year range toggle through yet
		echo "<br /><a href='admin.php?page=control-ais'>Select Years</a>";
	}
	echo "<br />";
	
	if( $_GET['divisionId'] && !$_GET['showIncentiveInfo'] ){ //we have hit the year range submit button
			
			foreach($_GET as $k=>$v){ //test to see if we have a year range
				if(strstr($k, 'year')){ //this field applies to a year range - add it to the query
					if(strlen($yearSql) == 0) { $yearSql .= " AND ( "; } //open the year clause of the query
					if(strlen($aisYearSql) == 0) { $aisYearSql .= " AND ( "; } //open the year clause of the query

					if($yearCounter > 0) { 
						$yearSql .= " OR "; 
						$aisYearSql .= " OR ";
					}
					$yearSql .= "Year = $v ";
					$aisYearSql .= "ais_Year = $v ";
					$yearCounter++;
				} //end if
			} //end for
			
			if(strlen($yearSql) > 0){ //close the parens if we've been adding a year clause
				$yearSql .= ")";
				$aisYearSql .= ")";				
			} //end if

		
		$sql = "SELECT *  FROM cs_AisIncentiveInfoEmail WHERE ( ais_Div_Id = ".$_GET['divisionId']." $incentiveFinder ) $aisYearSql ORDER BY ais_Year, ais_Model";
		$resultDiv = $wpdb->get_results($sql);
		
		$checkedModels = array(); //empty container to place the models into with their respective data
		$nameOverrides = array(); 
		
			foreach($resultDiv as $k=>$v){
				if(!array_key_exists($v->ais_Year, $nameOverrides)){ $nameOverrides[$v->ais_Year]=array(); } 
				if(strlen($v->ais_Model_Override)>0){ $nameOverrides[$v->ais_Year][str_replace(" ", "_", $v->ais_Model)] =  $v->ais_Model_Override; }
				
				$modelName = $v->ais_Year."_".str_replace(" ", "-", $v->ais_Model)."_".$v->ais_Div_Id; //replace any spaces
				array_push($checkedModels, $modelName); //add it to the model data array
			} //end for
		
		$yearCounter = 0;
		
		$sql = "
		SELECT DISTINCT cs_IncentiveInfoNew.Model, cs_IncentiveInfoNew.Year, cs_IncentiveInfoNew.DivisionId 
		FROM cs_IncentiveInfoNew 
		WHERE (divisionId = ".$_GET['divisionId']." ".$yearFinder." ) ".$yearSql." 
		ORDER BY Year, divisionId, Model";
		
		$resultDiv = $wpdb->get_results($sql);
		
		echo "<form action='admin-post.php' method='post' />";
		echo "<input type='hidden' name='action' id='action' value='aisModelSelected' />";
		echo "<input type='hidden' name='divisionId' id='divisionId' value='".$_GET['divisionId']."' />";
		
			
			foreach($yearArray as $k=>$v){
				echo "<input type='hidden' name='year".$k."' id='year".$k."' value='".$v."' />";
			} //end for
		
		echo "<div style='margin-left:30px'>";		
		$modelCounter = 0; //flag to set the form value for the model that we're iterating through
		$thisYear = ''; //flag to see what year we're iterating through
		
		$modelsAlreadyShown = array();
		$thisResultCount =  count($resultDiv); //get the number of models that have ais incentives as a flag to know when we're finished and show the custom models checkbox
		
		foreach($resultDiv as $k=>$v){
			
			if($v->Year != $thisYear){ //we need to update the year
				$thisYear = $v->Year;
				echo "<h3>".$v->Year."</h3>";
			}
			
			$thisVehicle = $v->Year."_".str_replace(" ", "-", $v->Model)."_".$v->DivisionId;
			
			echo "<input class='model_checkbox' style='margin-left:15px' type='checkbox' name='model_checkbox_".$modelCounter."' id='model_checkbox_".$modelCounter."'";	
				if(in_array($thisVehicle, $checkedModels)) echo " checked='checked' "; //we have this model set up already - check it in the form	
			echo "/> ";
				$modelNameChecker = str_replace(" ", "_", $v->Model);
				if( array_key_exists($modelNameChecker, $nameOverrides[$thisYear]) ){ //we do have a name override so output it
					echo "<span style='color:red'>".$nameOverrides[$thisYear][$modelNameChecker]."</span> (".$v->Model.")";
				}else{ //we don't have an override so just show the regular name
					echo $v->Model;
				}
			echo "<br />";
			
			$thisYearsModel = $thisYear."_".$v->Model;
			if(!in_array($thisYearsModel, $modelsAlreadyShown)) array_push($modelsAlreadyShown, strtolower($thisYearsModel) );
			
			echo "<input type='hidden' value='".$thisVehicle."' name='info_vehicle_".$modelCounter."' id='info_vehicle_".$modelCounter."' />";
			$modelCounter++;
			
			//check ahead to see if we need to show the outside models checkbox
			$ahead = $k + 1; //key incremented to look ahead
			
			$outsideModels = '';
			 
			if($resultDiv[$ahead]->Year != $thisYear){
				echo "<div id='all_models_checkbox_".$thisYear."_div' name='all_models_checkbox_".$thisYear."_div'><input class='all_model_checkbox' style='margin-left:15px' type='checkbox' name='all_models_checkbox_".$thisYear."' id='all_models_checkbox_".$thisYear."' /> <b>* Show me models for this division without AIS incentives</b></div>";	
				$outsideModels = getOutsideModels($divisionId, $thisYear, $modelsAlreadyShown, $nameOverrides);
				echo $outsideModels;
			} //end if
		} //end for
		
		if($_GET['selectOutside']){
			echo "<input type='hidden' name='selectOutside' id='selectOutside' value='1' />";
			$sqlOutside = "
			SELECT DISTINCT inventory.year, dealership.dealership_id
			FROM inventory, dealership 
			WHERE dealership.dealership_id = ".$divisionId."
			AND dealership.dealer_id = inventory.dealer_id
			ORDER BY inventory.year DESC
			";
			
			$resultOutside = $wpdb->get_results($sqlOutside);	
			foreach($resultOutside as $k=>$v){
				$thisYear = $v->year;
				$outsideModels = getOutsideModels($divisionId, $thisYear, $modelsAlreadyShown, $nameOverrides);
				echo $outsideModels;
			}
			?>
			<script>
			<?
				foreach($resultOutside as $k=>$v){
					$thisYear = $v->year;
					?>
					jQuery("#outside_" + <?=$thisYear ?>).show();
					<?
				}
			?>
			</script>
			<?
		} //end if
		
		echo "<br /><br /><input type='submit' value='Add/Remove Models to Email Template' />";
		echo "</form>";
		echo "</div>";
	}

	if( $_GET['divisionId'] && $_GET['showIncentiveInfo'] ){ //we have hit the year range submit button
		
		$yearSql = "";
		$incentiveYearSql = "";
		$counter = 0;
		$yearArray = array();
		foreach($_GET as $k=>$v){
			
			if(strstr($k, "year")){
				array_push($yearArray, $v);
				if($counter == 0){
					$yearSql .= "AND (";
					$incentiveYearSql .= "AND (";
				}else{
					$yearSql .= "OR ";
					$incentiveYearSql .= "OR ";
				}
				$yearSql .= " Year = $v ";
				$incentiveYearSql .= " ais_Year = $v ";
				$counter++;
			} //end if
		} //end for
		
		if(strlen($yearSql)>0){ $yearSql .= ")"; }
		if(strlen($incentiveYearSql)>0){ $incentiveYearSql .= ")"; }

		if($_GET['selectOutside']==1){
			$yearSql = "";
			$incentiveYearSql = "";
		}
		
		$sql = "SELECT cs_IncentiveInfoNew.* FROM cs_IncentiveInfoNew WHERE divisionId = ".$_GET['divisionId']." ".$yearFinder." ".$yearSql." ORDER BY Year, Model";
		$resultDiv = $wpdb->get_results($sql);
		
		$sql = "SELECT * FROM cs_AisIncentiveInfoEmail WHERE (ais_Div_Id = ".$_GET['divisionId']." ".$incentiveFinder.") ".$incentiveYearSql." Order by ais_Year, ais_Model, ais_Order";
		$resultDiv2 = $wpdb->get_results($sql);	
			
		formatAisEmailIncentives_New($resultDiv, $resultDiv2, $_GET['divisionId'], $yearArray); // create the output with retrieved info
		
		if($_GET['showAllModels']){ //someone has checked the box to show all models
				
				$restQuery = "
					SELECT DISTINCT dealership.dealer_id, inventory.dealer_id, inventory.model, inventory.body as inventoryDealerId, cs_offerlogixDivision.divisionID
					FROM dealership, inventory, cs_offerlogixDivision
					WHERE dealership.dealer_id = inventory.dealer_id 
					AND dealership.dealership_id = $divisionId
					AND $divisionId = cs_offerlogixDivision.divisionID
					AND inventory.make LIKE cs_offerlogixDivision.divisionName
					ORDER BY inventory.model, inventory.body
				";
				
				$resultAll = $wpdb->get_results($restQuery);
				if(count($resultAll) > 0){
					echo "<div style='margin-left:50px'>";
					echo "<h3>Outside Models</h3>";
					echo "<div style='margin-left:50px'>";
						foreach($resultAll as $kr => $vr){
							echo "<input type='checkbox' name='' id='' />".$vr->model." ".$vr->body."<br />";
						}
					echo "</div>";
					echo "</div>";
				} //end if
		} //end if
	}
	?>
		<script>
		///////////////////////////////////////////////////////
		///////////////////////////////////////////////////////
		jQuery('.all_model_checkbox').on('change', function() {
			
			var myVal = jQuery(this).prop('checked'); // Checks it
			var myId = jQuery(this).attr('id');
			var idSplit = myId.split("_");
			var divToShow = "outside_" + idSplit[3];
			
			if(myVal == false){
				jQuery("#" + divToShow).hide();
			}
			if(myVal == true){
				jQuery("#" + divToShow).show();
			}		
		});
		///////////////////////////////////////////////////////
		//jquery to toggle model reordering for the email incentives
		///////////////////////////////////////////////////////
		jQuery('.model_checkbox').on('change', function() { 
			
			var myVal = jQuery(this).prop('checked'); // Checks it
			if(myVal == false){
				jQuery(this).removeAttr('checked');
			}
			if(myVal == true){
				jQuery(this).addAttr('checked');
			}
		
		});
		///////////////////////////////////////////////////////
		///////////////////////////////////////////////////////
		</script>
		<?
}
//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////
function getOutsideModels($divisionId, $thisYear, $modelsAlreadyShown, $nameOverrides){
	global $wpdb;
	global $CJDR; //get the chrysler jeep dodge ram ids to pull them together
	
	if(in_array($divisionId, $CJDR))
	
	$restQuery = "
					SELECT DISTINCT dealership.dealer_id, inventory.dealer_id, inventory.year, inventory.model, inventory.body as inventoryDealerId, cs_offerlogixDivision.divisionID
					FROM dealership, inventory, cs_offerlogixDivision
					WHERE dealership.dealer_id = inventory.dealer_id ";
		
		if(!in_array($divisionId, $CJDR)){ //this is not a cjdr brand so we need to only worry about this one dealership id
			$restQuery .= "
							AND dealership.dealership_id = $divisionId
							AND cs_offerlogixDivision.divisionID = $divisionId ";
		}else{ //it is so we need to combine the ids
			$restQuery .= " AND (";
			for($i=0; $i<count($CJDR); $i++){ //loop through and add each id to the query
				if($i > 0) $restQuery .= " OR ";
				$restQuery .= " dealership.dealership_id = ".$CJDR[$i]." ";
			}
			$restQuery .= ")";
		}
					
	$restQuery .= "	AND inventory.make LIKE cs_offerlogixDivision.divisionName
					AND inventory.year = ".$thisYear." 
					AND inventory.new_used = 'N'
					ORDER BY inventory.model, inventory.body
				";
							
			$resultAll = $wpdb->get_results($restQuery);
			$outsideOutput = '';
			$outsideCounter = 0;
			$thisDisplay = 'display:none';
			
			if(count($resultAll) > 0){
				$modelCounter = 0;
				$outsideOutput .= "<div name='outside_".$thisYear."' id='outside_".$thisYear."' style='margin-left:0px; #thisDisplay#'>";
				$outsideOutput .= "<h3>Outside $thisYear Models (without available AIS incentives)</h3>";
				$outsideOutput .= "<div style='margin-left:20px'>";
								
				$outsideModelsShown = 0;
				foreach($resultAll as $kr => $vr){
					$thisYearTest = $thisYear."_".$vr->model;
										
					if(!in_array( strtolower($thisYearTest), $modelsAlreadyShown)){
						
						array_push($modelsAlreadyShown, strtolower($thisYearTest));
						
						$outsideModelsShown++;
						$thisVehicle = $thisYear."_".str_replace(" ", "-", $vr->model);
						$outsideOutput .= "<input type='hidden' value='".$thisVehicle."' name='info_outside_vehicle_".$vr->divisionID."_".$thisYear."_".str_replace("-", " ", $vr->model)."' id='info_outside_vehicle_".$vr->divisionID."_".$thisYear."_".str_replace("-", " ", $vr->model)."' />";
						$modelCounter++;
						$checkCheckSql = "SELECT * FROM cs_AisIncentiveInfoEmail WHERE ais_Div_Id = ".$vr->divisionID." AND ais_Model='".str_replace("-", " ", $vr->model)."' AND ais_Year = ".$thisYear." LIMIT 1";
						$resultThisCheck = $wpdb->get_results($checkCheckSql);
						$checked = " ";

						if(count($resultThisCheck)>0){
							$checked = " checked ";
							$outsideCounter++;
						}
						
						$thisOutsideModel = str_replace(" ", "-", $vr->model);
						$thisOutsideModel = str_replace("_", "-", $thisOutsideModel);
						
						$outsideOutput .= "<input type='checkbox' name='outside_".$vr->divisionID."_".$thisYear."_".$thisOutsideModel."' id='outside_".$vr->divisionID."_".$thisYear."_".$thisOutsideModel."' $checked />";
						$thisCar = $vr->model." ".$vr->body;
						$thisCar = str_replace("_", " ", trim($thisCar));
						if(array_key_exists($thisCar, $nameOverrides[$thisYear])){ //we have a name override so show it
							$outsideOutput .= "<span style='color:red'>".$nameOverrides[$thisYear][$thisCar]."</span> (".str_replace("_", " ", trim($thisCar)).")<br />";
						}else{ //we don't have a name override so show the normal model name
							$outsideOutput .= $thisCar."<br />";
						}
					}
				}
				$outsideOutput .= "</div>";
				$outsideOutput .= "</div>";
			}
			
			if($outsideCounter > 0){ //we have 
					$thisDisplay = 'display:block';
					?>
					<script>
						///////////////////////////////////////////////////////
						///////////////////////////////////////////////////////
						var checkboxToCheck = 'all_models_checkbox_' + <?=$thisYear ?>;
						jQuery("#" + checkboxToCheck).prop('checked', 'true'); // Checks it
						///////////////////////////////////////////////////////
						///////////////////////////////////////////////////////
					</script>
					<?
			}elseif($outsideModelsShown == 0){
					?>
					<script>
						///////////////////////////////////////////////////////
						///////////////////////////////////////////////////////
						var checkboxToCheck = 'all_models_checkbox_' + <?=$thisYear ?> + "_div";
						jQuery("#" + checkboxToCheck).hide(); // Checks it
						///////////////////////////////////////////////////////
						///////////////////////////////////////////////////////
					</script>
					<?
			}
			$outsideOutput = str_replace("#thisDisplay#", $thisDisplay, $outsideOutput);
	return $outsideOutput;
}
////////////////////////////////////////////////////
?>