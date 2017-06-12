<?php
/**
* Plugin Name: AisAdminControl
* Plugin URI: http://keithbonline.com/
* Description: A custom plugin for Ais admin controls for the outgoing email template
* Version: 1.0
* Author: Keith Bonarrigo
* Author URI: http://keithbonline.com/
**/
$inc = get_theme_root()."/hulk-ua/chromeStyleIncludes/chromeStylesClasses.php";
require_once($inc);
add_action('admin_menu', 'showAisAdminIncentives'); //base level plugin configuration
add_action('admin_post_aisAdminModelEdited', 'editAisModels'); //base level plugin configuration

////////////////////////////////////////////////////
////////////////////////////////////////////////////
////////////////////////////////////////////////////
////////////////////////////////////////////////////
function showAisAdminIncentives(){ //this is the base landing slug for the plugin - set up the UI
    add_menu_page( 'Control Ais Incentives Admin', 'Ais Admin Control', 'manage_options', 'control-ais-admin', 'initAisAdminIncentives' );
}
////////////////////////////////////////////////////
function editAisModels(){
	global $wpdb;
	print_r($_POST);
	//print_r($_FILES);
	$headerImageUploaded = 0;
	if($_FILES['fileToUpload']){ //upload the header image
		//echo "fire";
		// These files need to be included as dependencies when on the front end.
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/media.php' );
		
		// Let WordPress handle the upload.
		// Remember, 'my_image_upload' is the name of our file input in our form above.
		$attachment_id = media_handle_upload( 'fileToUpload', 0 );
		$thisUp = wp_upload_dir();
		$thisImage = $thisUp['url']."/".$_FILES['fileToUpload']['name'];
		//echo "<img src='$thisImage' />";
		if ( is_wp_error( $attachment_id ) ) {
			//echo "<br />There was an error uploading the image.";
		} else {
			//echo "<br />The image was uploaded successfully!";
			$headerImageUploaded = 1;
		} 
	}
	
	$testSql = "SELECT ais_Header_Id From cs_AisIncentiveHeader WHERE ais_Dealer_Id = ".$_POST['dealership_id']." LIMIT 1";
	
	$resultDiv = $wpdb->get_results($testSql);
	if(count($resultDiv)>0){ //we have a header already - update it
		foreach($_POST as $k=>$v){ $headerToUpdate = $v->ais_Header_Id; }
	
		$sql = "UPDATE cs_AisIncentiveHeader SET ";
		if($headerImageUploaded == 1) { $sql .= " ais_Header_Image = '$thisImage', "; }
		$sql .= "hero_disclosure = '".$_POST['hero_disclosure']."',  ais_Pre_Header_Text = '".$_POST['pre_header_text']."', ais_Alt_Tag = '".$_POST['alt_tag_top_image']."', ais_Title_Tag = '".$_POST['title_tag_top_image']."', ais_Href_Tag = '".$_POST['href_tag_top_image']."', ais_Text_Cta = '".$_POST['text_Cta']."', ais_Href_Cta= '".$_POST['href_Cta']."', ais_Campaign_Code = '".$_POST['campaign_code']."' WHERE ais_Dealer_Id = ".$_POST['dealership_id']." LIMIT 1";
		//echo $sql;
		//exit;
		$resultDiv = $wpdb->get_results($sql);
	
		foreach($_POST as $k=>$v){
			$sql = ""; //set up the empty query
			if(strstr($k, "ais_select_")){ //we have hit a model's order - check for the image upload
				$thisEx = explode("_", $k);
				$sql = "UPDATE cs_AisIncentiveInfoEmail SET ais_Order = $v";
				$sql .= " WHERE email_Info_Id = ".$thisEx[2]." LIMIT 1";
				$resultDiv = $wpdb->get_results($sql);
				//echo $sql;
				//exit;
			}
		}
	}else{ //we don't have the header - insert it
		$sql = "INSERT INTO cs_AisIncentiveHeader(ais_Dealer_Id, ";
		if($headerImageUploaded == 1) { $sql .= "ais_Header_Image, "; }
		$sql .= "hero_disclosure, ais_Pre_Header_Text, ais_Alt_Tag, ais_Title_Tag, ais_Href_Tag, ais_Text_Cta, ais_Href_Cta, ais_Campaign_Code) VALUES (".$_POST['dealership_id'].",";
		if($headerImageUploaded == 1) { $sql .= "'$thisImage', "; }
		$sql .= "'".$_POST['hero_disclosure']."','".$_POST['pre_header_text']."', '".$_POST['alt_tag_top_image']."', '".$_POST['title_tag_top_image']."', '".$_POST['href_tag_top_image']."', '".$_POST['text_Cta']."', '".$_POST['href_Cta']."', '".$_POST['campaign_code']."')";
		$resultDiv = $wpdb->get_results($sql);
	}
	
	
	$adminUrl = "admin.php?page=control-ais-admin";
	wp_redirect(  admin_url( $adminUrl) ); //go back to the admin page
}
////////////////////////////////////////////////////
////////////////////////////////////////////////////
function findIncentive($incentive){
	global $wpdb;
	$fetchSql = "SELECT * FROM cs_IncentiveInfoNew WHERE cs_IncentinveInfo = $incentive LIMIT 1";
	$resultDiv = $wpdb->get_results($fetchSql);
	foreach($resultDiv as $k=>$v){
		$output .= $v->IncentiveType." ".$v->ProgramText;
	}
	return $output;
}
////////////////////////////////////////////////////
function outputModelForTemplate($model, $thisCount){
	$output = "<div id='' style='margin-left:20px; margin-top:10px;'><b>".$model->ais_Year." ".str_replace(" ", "_", $model->ais_Model)."</b>";
	$output .= "<select style='margin-left:20px' name='ais_select_".$model->email_Info_Id."' id='ais_select_".$model->email_Info_Id."'>";
	for($i=1; $i<=$thisCount; $i++){
		
		$output .= "<option value=".$i." ";
		if($i == $model->ais_Order) $output .= " selected ";
		$output .= ">".$i."</option>";
	}
	$output .= "</select>";
	$output .= "<div style='margin-left:20px; margin-top:10px;'>";
	$output .= "Number Available: ".$model->ais_Number."<br />";
	$output .= "Price: $".number_format($model->ais_Price)."<br />";
	$output .= "MSRP: $".number_format($model->ais_Msrp)."<br />";
	$output .= "<br /><b>Incentives:</b>";
	$output .= "<div style='margin-left:20px'> - ".findIncentive($model->ais_Incentive_1)."<br />";
	$output .= " - ".findIncentive($model->ais_Incentive_2)."</div>";
	$output .= "</div>";
	$output .= "</div>";
	return $output;
}
////////////////////////////////////////////////////
////////////////////////////////////////////////////
function initAisAdminIncentives(){
	global $wpdb; //global database connection		
	global $dealership;
	$wp_upload = wp_upload_dir();
	$urlArray = explode("/", $wp_upload['url']);
	$url = $urlArray[2];
		
	$make = dh_get_make_from_dealership();
		
	echo "<br />Current Domain is <b>".$url."</b><br />";
	echo "Current make is <b>".$make."</b><br />";
	echo "<a target='_blank' href='http://".$url."/aisemailoutput'>See current email template output</a><br /><br />";
	$fetchSql = "SELECT * FROM `cs_AisIncentiveInfoEmail` WHERE ais_Div_Id = ".$dealership['dealership_id']." ORDER BY cs_AisIncentiveInfoEmail.ais_Order ASC";
	
	$resultDiv = $wpdb->get_results($fetchSql);
	echo "<div style='color:red' id='errorMessages' name='errorMessages'></div>";
	echo "<form name='adminForm' id='adminForm' action='admin-post.php' method='post' enctype='multipart/form-data' />";
	echo "<input type='hidden' name='action' id='action' value='aisAdminModelEdited' />";
	echo "<input type='hidden' name='dealership_id' id='action' value='".$dealership['dealership_id']."' />";

	$sqlMeta = "SELECT * FROM cs_AisIncentiveHeader WHERE ais_Dealer_Id = ".$dealership['dealership_id']." LIMIT 1";
	$resultMeta = get_results($sqlMeta);
	$headerImage = '';
		foreach($resultMeta as $mk=>$mv){
			$headerDisclosure = $mv['hero_disclosure'];
			$headerImage = $mv['ais_Header_Image'];
			$preHeaderText = $mv['ais_Pre_Header_Text'];
			$alt_Tag_Hero = $mv['ais_Alt_Tag'];
			$title_Tag_Hero = $mv['ais_Title_Tag'];
			$href_Tag_Hero = $mv['ais_Href_Tag'];
			$text_CTA = $mv['ais_Text_Cta'];
			$href_CTA = $mv['ais_Href_Cta'];
			$campaign_Code = $mv['ais_Campaign_Code'];
		}
				
	$headerUploaded = 0;
	$styleFill = '';
	$headerFill = '';
	
	//foreach($resultDiv as $k=>$v){
		if(strlen($headerImage) > 1){ 
		$styleFill = 'display:none';
		$headerFill = $headerImage;
		}
	//}
	
		echo "Header file: <input style='".$styleFill."' type='file' name='fileToUpload' id='fileToUpload'> ".$headerFill;
		if(strlen($headerFill) > 1){
			echo " <a id='changeHeaderImage' name='changeHeaderImage' style='text-decoration:underline'>Change Header Image</a>";
		}
		echo "<br />"; 
	?>
	<style>
	.header_data { width:200px }
	.header_field { width:400px; margin-left:20px; }
	</style>
	<?
	echo "<br /><div class='header_data'>Pre Header Disclosure:</div> <input type='text' name='hero_disclosure' id='hero_disclosure' class='header_field' value='".$headerDisclosure."' /><br />";

		
	echo "<br /><br /><div class='header_data'>Pre Header Text:</div> <input type='text' name='pre_header_text' id='pre_header_text' class='header_field' value='".$preHeaderText."' /><br />";
	echo "<div class='header_data'>Alt tag top image:</div> <input type='text' name='alt_tag_top_image' id='alt_tag_top_image' class='header_field' value='".$alt_Tag_Hero."' /><br />";
	echo "<div class='header_data'>Title tag top image:</div> <input type='text' name='title_tag_top_image' id='title_tag_top_image' class='header_field' value='".$title_Tag_Hero."' /><br />";
	echo "<div class='header_data'>Href tag top image:</div> <input type='text' name='href_tag_top_image' id='href_tag_top_image' class='header_field' value='".$href_Tag_Hero."' /><br />";
	echo "<div class='header_data'>Text CTA:</div> <input type='text' name='text_Cta' id='text_Cta' class='header_field' value='".$text_CTA."' /><br />";
	echo "<div class='header_data'>Href CTA:</div> <input type='text' name='href_Cta' id='href_Cta' class='header_field' value='".$href_CTA."' /><br />";
	echo "<div class='header_data'>Campaign Code:</div> <input type='text' name='campaign_code' id='campaign_code' class='header_field' value='".$campaign_Code."' /><br />";

	$thisCount = count($resultDiv);
	foreach($resultDiv as $k=>$v){
		$output = outputModelForTemplate($v, $thisCount);
		echo $output;
	}
	echo "<input type='submit' value='Submit Changes' /></form>";	
	echo "<div style='color:red' id='errorMessagesLower' name='errorMessagesLower'></div>";

	//echo "SELECT Distinct cs_IncentiveInfoNew.Year FROM cs_IncentiveInfoNew WHERE divisionId = $divisionId  ORDER BY Year";
	//$resultDiv = $wpdb->get_results("SELECT Distinct cs_IncentiveInfoNew.Year FROM cs_IncentiveInfoNew WHERE divisionId = $divisionId  ORDER BY Year");
	?>
	<script>
		///////////////////////////////////////////////////////
		//jquery to toggle model reordering for the email incentives
		///////////////////////////////////////////////////////
		jQuery('#changeHeaderImage').on('click', function() { 
			var myFile = jQuery("#fileToUpload");
			myFile.css('display', 'block');
		});
		///////////////////////////////////////////////////////
		///////////////////////////////////////////////////////
		///////////////////////////////////////////////////////
	///////////////////////////////////////////////////////
	//jquery to toggle model reordering for the email incentives
	///////////////////////////////////////////////////////
	jQuery('#adminForm').on('submit', function() {
		var numToTest = 1;
		var errorCount = 0;
		var myPreHeader = jQuery("#pre_header_text");
		var myAltTagTop = jQuery("#alt_tag_top_image");
		var myTitleTagTop = jQuery("#title_tag_top_image");
		var myHrefTagTop = jQuery("#href_tag_top_image");
		var textCta = jQuery("#text_Cta");
		var hrefCta = jQuery("#href_Cta");
		var campaignCode = jQuery("#campaign_code");
		
		if(myPreHeader.val().length < numToTest){
			myPreHeader.css("background-color", "#fcd9d9");
			errorCount++;
		}
		if(myAltTagTop.val().length < numToTest){
			myAltTagTop.css("background-color", "#fcd9d9");
			errorCount++;
		}
		if(myTitleTagTop.val().length < numToTest){
			myTitleTagTop.css("background-color", "#fcd9d9");
			errorCount++;
		}
		if(myHrefTagTop.val().length < numToTest){
			myHrefTagTop.css("background-color", "#fcd9d9");
			errorCount++;
		}
		if(textCta.val().length < numToTest){
			textCta.css("background-color", "#fcd9d9");
			errorCount++;
		}
		if(hrefCta.val().length < numToTest){
			hrefCta.css("background-color", "#fcd9d9");
			errorCount++;
		}
		if(campaignCode.val().length < numToTest){
			campaignCode.css("background-color", "#fcd9d9");
			errorCount++;
		}
		
			if(errorCount > 0){
				jQuery("#errorMessages").text("You need to have values filled in for all fields. Please see the highlighted fields below.");
				jQuery("#errorMessagesLower").text("You need to have values filled in for all fields. Please see the highlighted fields above.");
				return false;
			}else{
				return true;
			}
	});
		</script>
	<?
}
////////////////////////////////////////////////////
?>