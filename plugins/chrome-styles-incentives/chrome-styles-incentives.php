<?php
/**
* Plugin Name: ChromeIncentivesControl
* Plugin URI: http://keithbonline.com/
* Description: A custom plugin to show ChromeStyles incentives in the website UI output
* Version: 1.0
* Author: Keith Bonarrigo
* Author URI: http://keithbonline.com/
**/
$inc = get_theme_root()."/hulk-ua/chromeStyleIncludes/chromeStylesClasses.php";
require_once($inc);

//set up our actions
add_action('admin_menu', 'showChromeIncentives'); //base level plugin configuration
add_action( 'admin_post_selectDivision', 'select_division' ); //we're arriving at the page - display the base division selection options
add_action( 'admin_post_incentivesUpdated', 'process_cs_options_ordered' ); //someone has selected a division - show the incentives that they have selected for visibility
add_action( 'admin_post_previewUpdated', 'process_preview_options' ); //someone has selected a year range to preview the incentives 
add_action( 'admin_post_add_incentive_categories', 'addIncentivesDivision' ); //this aggregates the different incentive types and sets an association to that make
add_action( 'admin_post_setIncentiveForEmail', 'setIncentiveForWeb' ); //marks the setToEmail flag
add_action( 'admin_post_setModelOrder', 'setModelOrder' ); //sets the ordering of Model_Order field in cs_IncentiveInfoNew
add_action( 'admin_post_cloneModel', 'cloneModel' ); //clones instance in cs_IncentiveInfoNew
add_action( 'admin_post_createModelNameException', 'createModelNameException' ); //creates rename instance in cs_ModelNameException table
add_action( 'admin_post_removeNameException', 'removeNameException' ); //removes instance in cs_ModelNameException table
////////////////////////////////////////////////////
////////////////////////////////////////////////////
function showChromeIncentives(){ //this is the base landing slug for the plugin - set up the UI
    add_menu_page( 'Control ChromeStyles Incentives', 'Ais Output', 'manage_options', 'control-chromestyles', 'initChromeStyles' );
}
////////////////////////////////////////////////////
////////////////////////////////////////////////////
function removeNameException(){
	global $wpdb; //global database connection
	$checkSql = "DELETE FROM cs_ModelNameException WHERE cs_ModelExceptionID = ".$_POST['exceptionToRemove']." LIMIT 1";
	$resultDiv = $wpdb->get_results($checkSql);
		foreach($_POST as $k=>$v){
			if(strstr($k, "year")){ $urlAdd .= $k."=".$v; } //see what years the user had selected to return them back to that same page
		}
	wp_redirect(  admin_url( 'admin.php?page=control-chromestyles&preview=1&division='.$_POST['exceptionDivision'].'&divisionSelected='.$_POST['exceptionDivisionId'].'&'.$urlAdd ) ); //go back to the admin page
}
////////////////////////////////////////////////////
////////////////////////////////////////////////////
function createModelNameException(){
	global $wpdb; //global database connection
	if(strlen($_POST['exceptionName']) > 0){ //someone has added a value to override so do it
		
		$checkSql = "SELECT cs_ModelExceptionID FROM cs_ModelNameException WHERE cs_DivisionID = ".$_POST['exceptionDivisionId']." AND cs_ModelYear = ".$_POST['exceptionYr']."  AND cs_ModelNameHannah LIKE '".$_POST['exceptionModel']."' LIMIT 1";
		$resultDiv = $wpdb->get_results($checkSql);
		$nums = $wpdb->num_rows;
			
			if($nums>0){ //we already have an instance of this year/model - so we need to update it
				
				foreach($resultDiv as $row){
					$sql = "UPDATE cs_ModelNameException SET cs_ModelNameHannah = '".$_POST['exceptionName']."' WHERE cs_ModelExceptionID = ".$row->cs_ModelExceptionID ." LIMIT 1";
				}
				
			}else{ //we don't have an instance of this name so we need to create it
					$sql = "INSERT INTO cs_ModelNameException(cs_ModelNameAis, cs_ModelNameHannah, cs_DivisionID, cs_ModelYear) VALUES ('".$_POST['exceptionModel']."', '".$_POST['exceptionName']."', ".$_POST['exceptionDivisionId'].", ".$_POST['exceptionYr'].")";
			}
			$resultDiv = $wpdb->get_results($sql); //run the query
	}
		
		foreach($_POST as $k=>$v){
			if(strstr($k, "year")){ $urlAdd .= $k."=".$v; } //see what years the user had selected to return them back to that same page
		}	
	wp_redirect(  admin_url( 'admin.php?page=control-chromestyles&preview=1&division='.$_POST['exceptionDivision'].'&divisionSelected='.$_POST['exceptionDivisionId'].'&'.$urlAdd ) ); //go back to the admin page
}
////////////////////////////////////////////////////
////////////////////////////////////////////////////
function cloneModel(){ //someone has cloned this model to a new name
	global $wpdb; //global database connection
	
	if(strlen($_POST['cloneName'])>0){
			$sql = "SELECT cs_IncentinveInfo FROM cs_IncentiveInfoNew WHERE DivisionID = ".$_POST['cloneDivisionId']." AND Year = ".$_POST['cloneYear']." AND Model = '".$_POST['cloneModel']."'";
			$resultDiv = $wpdb->get_results($sql);	
				$idsToClone = array();
				foreach($resultDiv as $row){
					array_push($idsToClone, $row->cs_IncentinveInfo); //grab and store the incentive ids that applie to this model
				}
				foreach($idsToClone as $k=>$v){ //now loop through this the array of ids  we need to clone and do it
					$s = "INSERT INTO cs_IncentiveInfoNew(StyleID,Acode,Division,DivisionID,Year,Model,Model_Order,MSRP,Variation,Trim,IncentiveID,IncentiveType,APR,Lease,Down,Term,SignatureHistoryID,SignatureID,Institution,ValueVariationID,Requirements,ProgramValueID,Cash,CategoryDescription,CategoryID,EffectiveDate,ExpiryDate,Bullet1,ProgramDescription,PreviousOwnershipReq,ProgramText,Disclaimer,setToWeb) SELECT StyleID,Acode,Division,DivisionID,Year,Model,Model_Order,MSRP,Variation,Trim,IncentiveID,IncentiveType,APR,Lease,Down,Term,SignatureHistoryID,SignatureID,Institution,ValueVariationID,Requirements,ProgramValueID,Cash,CategoryDescription,CategoryID,EffectiveDate,ExpiryDate,Bullet1,ProgramDescription,PreviousOwnershipReq,ProgramText,Disclaimer,setToWeb FROM cs_IncentiveInfoNew WHERE cs_IncentinveInfo = $v LIMIT 1";

					$result = $wpdb->get_results($s);	
					$lastInserted = $wpdb->insert_id;
					$s = "UPDATE cs_IncentiveInfoNew SET Model = '".$_POST['cloneName']."' WHERE cs_IncentinveInfo = $lastInserted LIMIT 1"; //now that it's copied, set it to the new name the user has selected
					$result = $wpdb->get_results($s);
				}
		}
		
		foreach($_POST as $k=>$v){
			if(strstr($k, "year")){ $urlAdd .= $k."=".$v; } //see what years the user had selected to return them back to that same page
		}
		
	wp_redirect(  admin_url( 'admin.php?page=control-chromestyles&preview=1&division='.$_POST['cloneDivision'].'&divisionSelected='.$_POST['cloneDivisionId'].'&'.$urlAdd ) ); //go back to the admin page
}
////////////////////////////////////////////////////
////////////////////////////////////////////////////
function setModelOrder(){
	global $wpdb; //global database connection
	
	$modelArr = explode("_", $_POST['modelOrderId']);
	$modelOrder = $_POST['modelOrderValue'];
	$modelRedirect = $_POST['modelOrderReturn'];
	$modelEx = explode('admin.php?',$modelRedirect);
	$mUrl = "admin.php?".$modelEx[1];	
	$modelYear = $modelArr[0];
	$model = '';
	$modelCount = count($modelArr);
		
		for($i=1;$i<=$modelCount;$i++){
			if($i>1)$model .= " ";
			$model .= $modelArr[$i];
		}
	
	$sql = "Update cs_IncentiveInfoNew SET Model_Order = ".$modelOrder." WHERE Year = ".$modelYear." AND Model = '".$model."'";
	$wpdb->query($sql);
	wp_redirect(  admin_url( $mUrl ) ); //go back to the admin page	
}
////////////////////////////////////////////////////
////////////////////////////////////////////////////
function setIncentiveForWeb(){
	global $wpdb; //global database connection
	foreach($_POST as $k=>$v){
		if(strstr($k, "check")){
			$new = explode("_", $k);
			$divisionId = $new[0];
			$incentiveToSet = $new[1];
			$sql = "Update cs_IncentiveInfoNew SET setToWeb = $v WHERE cs_IncentiveInfoNew.cs_IncentinveInfo = $incentiveToSet LIMIT 1";			
			$r = $wpdb->query($sql);
		} //if
	} //for
	
	foreach($_POST as $k=>$v){
		if(strstr($k, "year")){ $urlAdd .= $k."=".$v; }
	}
	wp_redirect(  admin_url( 'admin.php?page=control-chromestyles&preview=1&division='.$_POST['division'].'&divisionSelected='.$_POST['divisionSelected'].'&'.$urlAdd ) ); //go back to the admin page
}
////////////////////////////////////////////////////
////////////////////////////////////////////////////
function select_division(){ //simply redirects so that we have a division variable in the URL
	wp_redirect(  admin_url( 'admin.php?page=control-chromestyles&divisionSelected='.$_POST['divisionSelected'] ) ); //go back to the admin page
}
////////////////////////////////////////////////////
////////////////////////////////////////////////////
function addIncentivesDivision(){
	global $wpdb; //global database connection
	$q = "SELECT * FROM cs_IncentivesToShow";
	$resultDiv = $wpdb->get_results($q);	
	foreach($resultDiv as $row){
		$s = "INSERT INTO cs_IncentivesToShowAssoc(cs_IncentiveCatID, cs_IncentiveValue, cs_Division) VALUES(".$row->cs_typeId.", 1, ".$_POST['divisionSelected'].")";
		$r = $wpdb->get_results($s);
	} //end for
	wp_redirect(  admin_url( 'admin.php?page=control-chromestyles&preview=1&division='.$_POST['division'].'&divisionSelected='.$_POST['divisionSelected'] ) ); //go back to the admin page
}
////////////////////////////////////////////////////
////////////////////////////////////////////////////
function process_preview_options(){  //select the incentive options for a given division/year stretch
	global $wpdb; //global database connection
	
	$resultDiv = $wpdb->get_results("SELECT divisionName from cs_offerlogixDivision WHERE divisionID = ".$_POST['divisionSelected']." LIMIT 1"); //get the name of the division from the database for the URL
	foreach($resultDiv as $row){ $divisionName = $row->divisionName; }
		$yearString = "";
		if($_POST['year']){ //we have a year filter - assemble the string for the URL we'll redirect to
			if(count($_POST['year'])==1){
				$yearString = "&year0=".$_POST['year'][0]; //we don't have to loop since we have just one year to filter on
			}else{
				for($i=0; $i<count($_POST['year']); $i++){ //loop through the years listed and place them back to the URL
					$yearString .= "&year$i=".$_POST['year'][$i];
				}
			}
		}
	$url = "admin.php?page=control-chromestyles&preview=1&divisionSelected=".$_POST['divisionSelected']."&division=".$divisionName.$yearString;
	wp_redirect(  admin_url( $url ) ); //go back to the admin page
}
////////////////////////////////////////////////////
////////////////////////////////////////////////////
function process_cs_options_ordered(){
	global $wpdb; //global database connection	
	
	$sql = "UPDATE cs_IncentivesToShowAssoc SET cs_IncentiveValue = 0 WHERE cs_Division = %d"; //clear the visibility options for this division
	$wpdb->query($wpdb->prepare($sql, $_POST['divisionSelected'])); //clear the values that we're about to reset
		
		$extraPost = $_POST;
		foreach($_POST as $k=>$v){ //we have a form submission
			if($k != 'action' && $k != 'divisionSelected' && strstr($k, 'order')){ //i.e. if you are a checkbox that shows up in the post envelope becuase you were checked to be visible in the UI
				$single = explode("_", $k); //get the id from the element's nameing convention
				$thisId = $single[2];
				$target = $single[0]."_".$single[1]."_".$single[2];
				$visibility = $_POST[$target];
					if(strlen($visibility)<2){
						$visibility = 0;
					}else{
						$visibility = 1;
					}
					$thisOrder = $v; 
				$sql = "UPDATE cs_IncentivesToShowAssoc SET cs_IncentiveValue = $visibility, cs_IncentiveOrder = $thisOrder WHERE cs_IncentiveAssocID = $thisId";				
				$wpdb->query($sql); //query the sanitized input
			} //end if
		} //end for
		
	$url = 'admin.php?page=control-chromestyles&divisionSelected='.$_POST['divisionSelected'];
	if($_POST['previewString']) $url = $url.$_POST['previewString'];
	wp_redirect(  admin_url( $url ) ); //go back to the admin page 
}
////////////////////////////////////////////////////
////////////////////////////////////////////////////
function initChromeStyles(){ //begin creating the UI 
		global $wpdb; //global database connection		
		global $dealership;
		$wp_upload = wp_upload_dir();
		$urlArray = explode("/", $wp_upload['url']);
		$url = $urlArray[2];
		$make = dh_get_make_from_dealership();
		
		echo "<br />Current Domain is <b>".$url."</b><br />";
		echo "Current make is <b>".$make."</b><br />";
		$makeUpper = strtoupper($make);
		$resultDiv = $wpdb->get_results("SELECT * FROM cs_offerlogixDivision WHERE divisionName LIKE '".$makeUpper."'  ORDER BY divisionName LIMIT 1");
		
		echo "<table>";
		echo "<form method='post' action='admin-post.php' id='select_Division_Form' name='select_Division_Form'><input type='hidden' name='action' id='action' value='selectDivision' />";
		echo "<tr><td colspan=2>Select sales division: <select name='divisionSelected' id='divisionSelected' >";
			$divisionFoundCounter = 0;
			foreach($resultDiv as $row){
				$showThisOption = 0;
				$op = "<option value='".$row->divisionID."'";
					if($_GET['divisionSelected'] && $_GET['divisionSelected'] === $row->divisionID) $op .= " selected ";
				$op .=  ">".$row->divisionName."</option>";
				if(strstr(strtoupper($make), $row->divisionName)){ 
					echo $op; $divisionFoundCounter++; 
				}
			 }
		echo "</select><input type='submit' value='Select' /></td></tr>";
		echo "</form></table><br /><br />";
		
		
		///////////////////////////////////////
		//////////////////////
		//exception for volkswagen
		/////////////////////
			if(strstr($url, 'vwofportland.com')){
				$divisionId = 13; //set the new id
				$_GET['divisionSelected'] = 13; //reset the get variable for the rest of the form
			}elseif(strstr($url, 'dickhannahvolkswagen.com')){
				$divisionId = 12;
			}
		//////////////////////
		//end exception for volkswagen
		/////////////////////
		///////////////////////////////////////
		
		
		if(!$_GET['divisionSelected']){
			?>
				<script>
					jQuery('#select_Division_Form').submit();
				</script>
			<?
		}
		
			if($_GET['divisionSelected']){ //we have a division to work from so get the division's visibility data 
				
				//pull the incentives
				$sql = "
				SELECT Distinct cs_IncentiveInfoNew.CategoryID, cs_IncentivesToShowAssoc.cs_IncentiveAssocID, cs_IncentivesToShowAssoc.cs_IncentiveValue, cs_IncentivesToShowAssoc.cs_IncentiveOrder, cs_offerlogixDivision.divisionName, cs_IncentivesToShow.iType, cs_IncentivesToShow.iCat
				FROM cs_IncentiveInfoNew, cs_IncentivesToShowAssoc, cs_offerlogixDivision, cs_IncentivesToShow 
				
				WHERE cs_IncentivesToShowAssoc.cs_Division = %d 
				AND cs_offerlogixDivision.divisionID = cs_IncentivesToShowAssoc.cs_Division
				AND cs_IncentivesToShow.cs_typeId = cs_IncentivesToShowAssoc.cs_IncentiveCatID
				AND cs_IncentiveInfoNew.CategoryID = cs_IncentivesToShowAssoc.cs_IncentiveCatID 
				
				ORDER BY cs_IncentivesToShow.iType, cs_IncentivesToShowAssoc.cs_IncentiveOrder ASC;
				"; 
				
				$result = $wpdb->get_results( $wpdb->prepare($sql,$_GET['divisionSelected'] ) );
				
				if($wpdb->num_rows == 0) { 
					echo "<div style='height:100px; width:30%; display:inline-block; text-align:left; clear:both'>";
					echo "No incentive visibility found for this division<br />";
					echo "<form method='post' action='admin-post.php' id='add_Incentive_Categories_Form' name='add_Incentive_Categories_Form' ><input type='hidden' name='action' id='action' value='add_incentive_categories' /><input type='hidden' id='divisionSelected' name='divisionSelected' value='".$_GET['divisionSelected']."'><input type='submit' value='Add Incentive Categories for this Division' /></form>";
					?>
						<script>
							jQuery('#add_Incentive_Categories_Form').submit();
						</script>
					<?
					echo "</div>";
				}else{ //display the AIS incentive categories
					echo "<p><b>Check the box for the incentive type to be visible:</b></p><table>";
					echo "<a style='cursor:hand; cursor:pointer; font-weight:bold' name='selectAll' id='selectAll'>Select All</a><br /><br />";
					echo "<form method='post' action='admin-post.php' name='incentivesUpdateForm' id='incentivesUpdateForm'><input type='hidden' name='action' id='action' value='incentivesUpdated' />";
					echo "<input name='divisionSelected' id='divisionSelected' type='hidden' value='".$_GET['divisionSelected']."' /><br />";
					
					$yearsToShow = array(); //set up a container
					if( $_GET['preview'] && $_GET['preview']==1){ //someone has a preview selected - remember it
						
						$previewString = "&preview=1&division=".$_GET['division'];
						foreach($_GET as $gk=>$gv){ //test the current URL to year filters
							if(strstr($gk, 'year')) array_push($yearsToShow, $gv);
						}
						for($i=0; $i<count($yearsToShow); $i++){ //add the year back to the URL
							$previewString .= "&year".$i."=".$yearsToShow[$i];
						}
						echo "<input name='previewString' id='previewString' type='hidden' value='$previewString' />";
					}
					
						$cash = array(); 
						$rate = array();
						echo "<table style='float:left;'>";
							
							foreach($result as $row){ //loop through incentive resultset and display checkbox list
								 echo "<tr><td>".$row->cs_typeId."</td><td>".$row->iType." (".$row->iCat.")</td><td><input type='checkbox' class='visible_incentive_checkbox' name='visible_incentive_".$row->cs_IncentiveAssocID."' id='visible_incentive_".$row->cs_IncentiveAssocID."' ";
								 if($row->cs_IncentiveValue == 1){ echo " checked "; } //this is visible already so check the box
								 echo "> <select name='visible_incentive_".$row->cs_IncentiveAssocID."_order' id='visible_incentive_".$row->cs_IncentiveAssocID."_order'>";
									for($ia=0;$ia< $wpdb->num_rows; $ia++){ 
										echo "<option value='".$ia."'";
											if($ia==$row->cs_IncentiveOrder) echo " selected ";
										echo ">".$ia."</option>"; 
									}
								 echo "</select></td></tr>";
								 
								 if($row->cs_IncentiveValue==1){ //this has been marked to be shown
									array_push($cash, $row->iType);
								 }
							 }
						 
					$incentivesToShow = array('cash'=>$cash, 'rate'=>$rate);
					echo "<tr><td colspan='3' style='text-align:right'><input type='submit' value='Update Incentive Visibility' /></td></tr>";
					echo "</form>";
					echo "</table>";
					echo "<div style='margin-left:400px; margin-top:-75px'>";
				}
		
		echo "<form method='post' action='admin-post.php'><input type='hidden' name='action' id='action' value='previewUpdated' />";
		echo "<input id='filterSubmit' name='filterSubmit' type='submit' value='See Incentives for:' style='vertical-align:top' />";
		echo "<input name='divisionSelected' id='divisionSelected' type='hidden' value='".$_GET['divisionSelected']."' />";
		
		$yResult = $wpdb->get_results("SELECT DISTINCT Year FROM cs_IncentiveInfoNew WHERE DivisionID = ".$_GET['divisionSelected']); //get all the years we have incentives for in the database
		$years = array();
		foreach($yResult as $row){ array_push($years, $row->Year); }
		
		echo "<select name='year[]' id='year[]' multiple>";
			
			foreach($years as $yk=>$yv){
				echo "<option value='$yv' ";
					if(in_array($yv, $yearsToShow)) echo " selected ";
				echo ">$yv</option>";
			}
			
		echo "</select>";
		echo "</form>";

		$atts = array('mode'=>'plugin', 'visibleIncentives'=>$incentivesToShow);
			if($_GET['division'] && $_GET['preview']==1){
				$div = strtolower($_GET['division']);
				$functionName = "show_".$div."_cs_incentives";
				$functionName($atts); //use the variable we just created to call the shared object's display function
			}
		echo "</div>"; 
		
	}
	?>
	<script>
			///////////////////////////////////////////////////////
			///////////////////////////////////////////////////////
			jQuery('#selectAll').on('click', function(){
				var thisText = jQuery('#selectAll').text();
				var n = thisText.indexOf("All"); //is it set to select or deselect checkboxes
				
				jQuery( "input[class*='visible_incentive_checkbox']" ).each( function(){
					//console.log();
					if(n > -1){
						jQuery(this).prop('checked', true);
						jQuery('#selectAll').text("Select None");
					}else{
						jQuery(this).prop('checked', false);
						jQuery('#selectAll').text("Select All");
					}
				});
			});
			///////////////////////////////////////////////////////
			///////////////////////////////////////////////////////
	</script>
	<?
	
}
////////////////////////////////////////////////////
////////////////////////////////////////////////////
?>