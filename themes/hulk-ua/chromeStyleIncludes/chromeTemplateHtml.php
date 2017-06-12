<?
session_start();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Incentive Email Template</title>
</head>

<body>
<?

$con = mysqli_connect("localhost","devdickhannahco","ohEpC67cHhm","devdickhannahco");
if (mysqli_connect_errno()){ echo "Failed to connect to MySQL: " . mysqli_connect_error(); } // Check connection
//include("../../plugins/dickhannah/plugin.php");
$templateTable = '
		
		<table cellpadding="0" cellspacing="0" class="pattern" width="100%" style="background-color:white">
			<tr>
				<td class="grid-block" width="600" style="padding-bottom: 8px;">
					<table cellpadding="0" cellspacing="0">
						<tr>
							<td class="section-title" width="200" height="144" align="center" valign="middle" style="font-family: arial,sans-serif; font-size: 14px; color: #000;">
								<span style="font-size:18px;color:#00427a;font-weight:bold;">NEW ###YEARINFO### ###MODELINFO###</span><br>
								<!--<img src="http://www.dickhannahhonda.com/files/dh/models/600/MY2016-HR-V.png" style="max-width:200px;" />-->
								###IMAGEINFO###
							</td>
							<td class="spacer" width="8" style="font-size: 1px;">&nbsp;</td>
							<td class="section-row" width="600">
								<table cellpadding="0" cellspacing="0">
									<tr>
										<td class="section" width="200" height="144" align="center" valign="top" style="font-family: arial,sans-serif; font-size: 14px; color: #000;">
											<span style="font-size:16px">###APRINFO###</span>
										</td>
										<td class="spacer" width="8" style="font-size: 1px;">&nbsp;</td>
										<td class="section" width="200" height="144" align="center" valign="top" style="font-family: arial,sans-serif; font-size: 14px; color: #000;">
											<span style="font-size:16px">###LEASEINFO###</span>
										</td>
										<td class="spacer" width="8" style="font-size: 1px;">&nbsp;</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
					<table cellpadding="0" cellspacing="0" width="600">
						<tr>
							<td align="center">
								<!-- BULLETPROOF BUTTON -->
								<table width="100%" border="0" cellspacing="0" cellpadding="0">
									<tr>
										<td align="center" class="padding" bgcolor="#00427a">
											<table border="0" cellspacing="0" cellpadding="0">
												<tr>
													<td align="center" style="border-radius: 3px;"><a href="https://litmus.com" target="_blank" style="font-size: 18px; font-family: Helvetica, Arial, sans-serif; color: #ffffff; text-decoration: none; color: #ffffff; text-decoration: none; border-radius: 3px; padding: 15px 5px; display: inline-block;" class="mobile-button">Find Your ###MODELINFO### Here</a></td>
												</tr>
											</table>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
';
		$templateOutput = "";
		$templateOutput .= '
		<style>
			
			@media only screen and (max-width: 500px) {
				table[class="pattern"] table { width: 100%; }
				table[class="pattern"] .spacer { display: none; }
				table[class="pattern"] .section-title,
				table[class="pattern"] .section-row {
					display: block;
					height: auto;
				}
				table[class="pattern"] .section-title {
					width: 100%;
					padding: 10px 0;
					margin-bottom: 8px;
				}
				
				.mobile-button {
					padding: 15px !important;
					border: 0 !important;
					font-size: 16px !important;
					display: block !important;
				}
				
				table[class="pattern"] .section-row { width: 100%; }
				table[class="pattern"] .section-row .section {
					display: block;
					float: left;
					width: 49%;
					height: auto;
					margin-left: 2%;
					padding: 20px 20px;
					-moz-box-sizing: border-box;
					-webkit-box-sizing: border-box;
					box-sizing: border-box;
				}
				table[class="pattern"] .section-row .section:first-child { margin-left: 0; }
			}
			@media only screen and (max-width: 400px) {
				table[class="pattern"] .grid-block { padding-bottom: 0 !important; }
				table[class="pattern"] .section-row .section {
					float: none;
					width: 100%;
					margin: 0 0 8px 0;
				}
			}
		</style>';
		
		$models = array();
		$resultDiv = $con->query("SELECT * FROM cs_IncentiveInfoNew WHERE setToEmail = 1 ORDER BY Division, Year, Model ASC");
		$counter = 0;

			foreach($resultDiv as $row){ //loop through the query result and put together the lease and apr for each year/model
				$newKey = $row['Division']."_".$row['Year']."_".$row['Model'];
				
				if(!array_key_exists($newKey, $models)){ //we don't have this year/model yet - create it
					  $models[$newKey] = array('Model'=>$row['Model'], 'Year'=>$row['Year'], 'Image'=>'', 'APR'=>'', 'Lease'=>'');
					  
					  //get the vehicle image
					  $query = 'SELECT * FROM model WHERE model_year = "';
					  $query .= $row['Year'];
					  $query .= '" AND model = "';
					  $query .= $row['Model'].'"';
					 // $query .= '" AND dealership_id = "';
					 // $query .= $row['DivisionID'].'"';
					  $query .= ' LIMIT 1';
					  // $inventoryQuery = 'SELECT * FROM inventory WHERE year = "'. $row['Year'].'" AND make = "'.$row['Division'].'" AND model = "'.str_replace(" and", " &", $row['Model']).'" AND new_used = "N" LIMIT 1';
					  //$imageRes = $con->query($inventoryQuery);
					  //echo $query."<br />";
					 $imageRes = $con->query($query);

			
					  foreach($imageRes as $rowImage){ //we have the raw data list separated by '|'
							//$imageArray = explode("|",$rowImage['photo_url_list']); //break up the list
							//$models[$newKey]["Image"] = $imageArray[0]; //take the first image
							//$models[$newKey]["Image"] = $rowImage['url'].$rowImage['image_file']; //take the first image
							
							$models[$newKey]["Image"] = dh_upload_path($_SESSION['email_incentives_baseurl'], "Model", $rowImage, 'image_file');
							//if(strlen($models[$newKey]["Image"])<1) $models[$newKey]["Image"] = "No Image";
					  }
				}
				$models = testModels($models, $row, $newKey); //run this data through the function to set the incentives up under the year/model
			}
		
			$counter = 0;
			$blankAPR = "<span style='font-size:24px;font-weight:bold;color:#00427a;'></span><br /><span style='font-size:40px;font-weight:bold;color:#00427a;'>&nbsp;&nbsp;</span><sup style='font-size:20px;'></sup><span style='font-weight:bold;color:#00427a;'>&nbsp;&nbsp;&nbsp;</span><sup></sup><br /><span style='font-size:16px;'></span><br />";
			$blankLease = "<span style='font-size:24px;font-weight:bold;color:#00427a;'></span><br /><span style='font-size:40px;font-weight:bold;color:#00427a;'></span> <br /<br />";
			foreach($models as $k=>$v){
			
				//replacement info for the flags
				$newTable = str_replace("###MODELINFO###", $v['Model'], $templateTable);
				//$newTable = str_replace("###IMAGEINFO###",'<img src="'.$v['Image'].'" style="max-width:200px;" />', $newTable);
				$newTable = str_replace("###IMAGEINFO###","<div style='max-width:100px'>".$v['Image']."</div>", $newTable);

				$newTable = str_replace("###YEARINFO###", $v['Year'], $newTable);
				$aprFormatted = "<span style='font-size:24px;font-weight:bold;color:#00427a;'>APR</span><br /><span style='font-size:40px;font-weight:bold;color:#00427a;'>".number_format($v['APR'], 1)."</span><sup style='font-size:20px;'>%</sup><span style='font-weight:bold;color:#00427a;'>APR</span><sup>###APRCOUNTER###</sup><br /><span style='font-size:16px;'>For ".$v['APR_Term']." months</span><br />ON APPROVED CREDIT";
				$leaseFormatted = "<span style='font-size:24px;font-weight:bold;color:#00427a;'>LEASE</span><br /><span style='font-size:40px;font-weight:bold;color:#00427a;'>$".$v['Lease']."</span> PER MO.<sup>###LEASECOUNTER###</sup><br />For ".$v['Lease_Term']." months<br />$".$v['Down']." DUE AT SIGNING";
				
				if($v['APR_Term']>0){ //we have an APR value to display
					$counter++; //increment the incentive disclaimer number for display in the sup tag
					$newTable = str_replace("###APRINFO###", $aprFormatted, $newTable);
					$newTable = str_replace("###APRCOUNTER###", $counter, $newTable);
				}else{ //we dont have an apr value - fill it in with a blank placeholder
					$newTable = str_replace("###APRINFO###", $blankAPR, $newTable);
				}
				
				if($v['Lease_Term']>0){ //we have a lease value to display
					$counter++; //increment the incentive disclaimer number for display in the sup tag
					$newTable = str_replace("###LEASEINFO###", $leaseFormatted, $newTable);
					$newTable = str_replace("###LEASECOUNTER###", $counter, $newTable);
				}else{ //we dont have a lease value - fill it in with a blank placeholder
					$newTable = str_replace("###LEASEINFO###", $blankLease, $newTable);
				}
				$templateOutput .= $newTable;
			}
echo "<div style='height:30px'></div>";
echo $templateOutput;
///////////////////////////////////////////
function dh_upload_path($webpath, $model, $obj, $upload)
{
	//print_r($obj);
    //if (WP_DEBUG) error_log(' !!! dh_upload_path called with args ['. $root .'|'.$model.'|'.$obj.'|'.$upload.']');
    //$wp_upload = wp_upload_dir();
   // if (WP_DEBUG) error_log(' !!! wp_upload[root] = '. $wp_upload[$root] );
    $table = dh_table($model);
   // if (WP_DEBUG) error_log(' !!! table = '.$table);
    $pk_column = dh_pk_column($model);
    switch($model){
        case 'Coupon':
		case 'Service Coupon':
		case 'Newspaper Ad':
		case 'Award':
        case 'Web Ad':
		case 'Web Tile':
		case 'Department':
		case 'Star Performer':
        default:
			$rootPath = "http://dev.dickhannah.com/wp-content/uploads";
            $path = "$webpath/dh/{$table}s/{$obj[$pk_column]}/{$obj[$upload]}";
            break;
    }
    return $path;
}
///////////////////////////////////////////
function now() { return date("Y-m-d H:i:s"); }
function dh_table($model) {  return str_replace(" ", "_", strtolower ($model)); }
function dh_form_page($model) {  return "dh_".dh_table($model)."s_form"; }
function dh_index_page($model) {  return "dh_".dh_table($model)."s_index"; }
function dh_pk_column($model) {  return dh_table($model)."_id"; }
function dh_join($value, $key) { echo " $key='$value'";}
///////////////////////////////////////////
function testModels($models, $row, $newKey){
	if(strstr($row['IncentiveType'], 'APR')){
			$models[$newKey]['APR'] = $row['APR'];
			$models[$newKey]['APR_Term'] = $row['Term'];
	}elseif(strstr($row['IncentiveType'], 'Lease')){
			$models[$newKey]['Lease'] = $row['Lease'];
			$models[$newKey]['Down'] = $row['Down'];
			$models[$newKey]['Lease_Term'] = $row['Term'];
	}
	$counter++;
	return $models;
}
?>
</body>
</html>
