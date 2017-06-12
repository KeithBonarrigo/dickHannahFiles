<?
include("cs_DealerTargets.php"); //includes the zips/brands we want to check
include("cs_db_connect.php"); //connects to the db and logs errors

$sqlExpire = "DELETE FROM cs_IncentiveInfoNew WHERE ExpiryDate < NOW() - INTERVAL 1 DAY";
$res = $mysqli->query($sqlExpire);

//we potentially just deleted a bunch of expired incentives - now we need to check to see if any of those expired incentives was attached to a template

$sqlClear = "UPDATE cs_IncentiveInfoNew set setToDelete = 1"; //clear the database
$res = $mysqli->query($sqlClear);

$sqlGetDivisions = "SELECT * FROM cs_offerlogixDivision"; //pull the divisions to get the name and zip to assemble our URLs
$res = $mysqli->query($sqlGetDivisions);

$xmlUrls = array();
	while ($row = mysqli_fetch_assoc($res)) { //push the divisions into our URL array
			$indiv = array($row['divisionName'], "https://incentives.homenetiol.com/FindAdvertisedDealScenariosByMakeAndPostalCode/".$row['divisionName']."/".$row['divisionZip']."?format=xml", $row['divisionZip'] ) ;
			array_push($xmlUrls, $indiv);
	}

$totalCount = 0;
$failures = 0;
$newIncentives = 0;
$email_message = ""; //container for email notification
foreach($xmlUrls as $key=>$value){ //loop through the array and process for each division
############################################
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$value[1]);
		$divName = $value[0];
		$divZip = $value[2];
		echo "<b>$divName</b><br />";
		curl_setopt($ch, CURLOPT_TIMEOUT, 200);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		
		$headers = array();
		$headers[] = 'AIS-ApiKey: F2C0B901-6CF6-42B3-9CA2-2B1A409DC578';
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		
		$result = curl_exec($ch);
		
		curl_close($ch);
		$these_incentives_desc = "";
		if($result) {
			$xml=simplexml_load_string($result);
			$counter = 0;
			$preExisting = 0; //counter for the incentives that already existed in the db
			$these_incentives = 0; //counter for the incentives that we have inserted
			
			foreach($xml->Response->AdvertisedDealScenarios->AdvertisedDealScenario as $k=>$v){ //loop through each individual incentives
				$totalCount++;
				$elements =  $v->VehicleElements."<br />"; //trim down the cars's meta info
				$elements = str_replace("{", "", $elements);
				$elements = str_replace("}", "", $elements);
				$vehicleArray = explode(",",$elements);
				echo "<b>";
					######################################
					$body = '';
					foreach($vehicleArray as $k3=>$v3){ //break out the car's meta info
						$vehicle = explode(":", $v3);
						if($vehicle[0] == "YEAR") $year = $vehicle[1];
						if($vehicle[0] == "MODEL") $model = $vehicle[1];
						if($vehicle[0] == "BODY_TYPE") $body = strip_tags($vehicle[1], '<br />');
						echo $vehicle[1]." ";
					}
					######################################
				echo "</b><br />";
				$sqlDiv = "SELECT cs_offerlogixDivision.divisionID FROM cs_offerlogixDivision WHERE cs_offerlogixDivision.divisionName = '$divName' AND cs_offerlogixDivision.divisionZip = $divZip LIMIT 1";
				$res = $mysqli->query($sqlDiv);
					###########################################
					if($res->num_rows > 0){ //we have the named division - now we need to determine whether we have this incentive category on file already or not
						$row = $res->fetch_assoc();
						$divID = $row['divisionID'];
					}
					###########################################
				$categoryId = 0;
				$cSql = "SELECT cs_typeId FROM cs_IncentivesToShow WHERE iType = '".$v->OriginalAdvertisedContent->AdvertisedContent[0]->Content."' LIMIT 1";
				echo $cSql;
				echo "<br />";
				
				$featuredColumn = "";
				$featuredOrder = "";
					#############################################
					if(strstr(strtolower($v->Description), 'featured')){
						$featuredColumn = ",cs_IncentiveOrder";
						$featuredOrder = ",0";
					}
					#############################################
				$resShow = $mysqli->query($cSql); //run the query to check for the category type
					#############################################
					if($resShow->num_rows == 1){ //we have it on file already
						$rowCat = $resShow->fetch_assoc();
						$categoryId = $rowCat['cs_typeId'];
						echo "<br />catId is $categoryId <br />";
					}else{ //we don't have it on file already - insert it
						$assocCatInsertSql = "INSERT INTO cs_IncentivesToShow(iType, iShow, iCat) VALUES('".$v->OriginalAdvertisedContent->AdvertisedContent[0]->Content."', 0, '".$v->AdvertisedDealScenarioKind."')";
						$assocCatInsertRes = $mysqli->query($assocCatInsertSql);
						$categoryId = $mysqli->insert_id; 
						$assocSqlInsert = "INSERT INTO cs_IncentivesToShowAssoc(cs_IncentiveCatID, cs_IncentiveValue, cs_Division".$featuredColumn.") VALUES(".$categoryId.", 1, $divID".$featuredOrder.")";
						$assocSqlInsertRes = $mysqli->query($assocSqlInsert);
					}
					#############################################
					//placeholders for the incentive components
					$APR = '';
					$Lease = '';
					$Down = '';
					$FirstPayment = 0;
					$Term = '';
					
					if(strstr($v->AdvertisedDealScenarioKind, 'APR')){
						$APR = $v->APR;
						$Term = $v->Term;
					}elseif(strstr($v->AdvertisedDealScenarioKind, 'Lease')){
						$Down = $v->DueAtSigning;
						$FirstPayment = $v->FirstPayment;
						$Lease = $v->Payment;
						$Term = $v->Term;
					}
				
				
				//reach into the incentive object and pull the data we'll store for this incentive
				for($i=0; $i< count($v->OriginalAdvertisedContent->AdvertisedContent);$i++){
					if($v->OriginalAdvertisedContent->AdvertisedContent[$i]->AdvertisedContentKind == "Title"){
						$title =  $v->OriginalAdvertisedContent->AdvertisedContent[$i]->Content;
					}elseif($v->OriginalAdvertisedContent->AdvertisedContent[$i]->AdvertisedContentKind == "Description"){
						$description =  $v->OriginalAdvertisedContent->AdvertisedContent[$i]->Content;
					}elseif($v->OriginalAdvertisedContent->AdvertisedContent[$i]->AdvertisedContentKind == "Disclaimer"){
						$disclaimer =  $v->OriginalAdvertisedContent->AdvertisedContent[$i]->Content;
					}
				}
				echo "<br />";
				
				//treat the APR and lease values for the search - shortens a double decimal to one decimal position
				$newApr = '';
				$aprLeaseAdd = '';
				
				if(is_integer($APR) || is_float($APR) || $APR>0){
					$aprExplode = explode('.', $APR);
					if(strlen($aprExplode[1])<2) { 
						$aprExplode[1] = $aprExplode[1].'0'; 
					}
					$newApr = $aprExplode[0].'.'.$aprExplode[1]; 
					$aprLeaseAdd = "AND APR = ".$newApr." ";
				}
				
				//look to see if the incentive already exists
				$sqlCheck = "SELECT cs_IncentinveInfo, setToWeb FROM cs_IncentiveInfoNew WHERE Division = '".$v->Make."' AND DivisionID = $divID AND DivisionZip = $divZip AND Year = $year AND Model = '$model' AND Variation = '$body' AND IncentiveId = $v->AdvPgmVehID AND IncentiveType = '".$v->Description."' ".$aprLeaseAdd." AND Lease = '$Lease' AND Down = '$Down' AND Term = '$Term' AND Cash = '".$v->Cash."' AND EffectiveDate = '".$v->StartDate."' AND ExpiryDate = '".$v->StopDate."' AND CategoryDescription = '".$v->OriginalAdvertisedContent->AdvertisedContent[0]->Content."' AND CategoryID = $categoryId AND Bullet1 = '".$v->BulletPoints->BulletPointContent[0]->BulletPoint."' AND ProgramDescription = '".$title."' LIMIT 1";
				echo $sqlCheck;
				$res = $mysqli->query($sqlCheck);
				if($res->num_rows > 0){ //found
						echo "<br />FOUND<br />";
						$preExisting++;
						$rowNoDelete = $res->fetch_assoc();
						$sqlUpdateDelete = "UPDATE cs_IncentiveInfoNew SET setToDelete = 0, setToWeb = ".$rowNoDelete['setToWeb']." WHERE cs_IncentinveInfo = ".$rowNoDelete['cs_IncentinveInfo']." LIMIT 1";
						echo $sqlUpdateDelete."<br /><br />";
						$resUpdateDelete = $mysqli->query($sqlUpdateDelete);
				}else{ //not found
						echo "<br />NOT FOUND with ".$res->num_rows." rows - Inserting<br />";
						$sql = "INSERT INTO cs_IncentiveInfoNew(StyleId, Division, DivisionID, DivisionZip, Year, Model, MSRP, Variation, IncentiveId, IncentiveType, APR, Lease, Down, FirstPayment, Term, Cash, EffectiveDate, ExpiryDate, CategoryDescription, CategoryID, Bullet1, ProgramDescription, ProgramText, Disclaimer) VALUES(".$v->AdvPgmVehID.",'".$v->Make."', $divID, ".$divZip.", $year, '$model', '".$v->MSRP."', '$body', ".$v->AdvPgmVehID.",'".$v->Description."', '$APR', '$Lease', '$Down', '$FirstPayment', '$Term', '".$v->Cash."', '".$v->StartDate."', '".$v->StopDate."', '".$v->OriginalAdvertisedContent->AdvertisedContent[0]->Content."', $categoryId, '".$v->BulletPoints->BulletPointContent[0]->BulletPoint."','".$title."', '".$description."', '".$disclaimer."')";
						echo $sql."<br /><br />";
						$res = $mysqli->query($sql);
						if($res){ $newIncentives++; }
					//we've just set up the incentive now we need to set up the visibility association
					#############################################
					if($resShow->num_rows == 1){ //we have the category on file so we need to see if this division is associated to it
						$assocSql = "SELECT * FROM cs_IncentivesToShowAssoc WHERE cs_IncentiveCatID = $categoryId AND cs_Division = $divID LIMIT 1";
						$resAssoc = $mysqli->query($assocSql);
						if($resAssoc->num_rows == 0){ //this division is not associated to this category so we need to insert it
							$assocSqlInsert = "INSERT INTO cs_IncentivesToShowAssoc(cs_IncentiveCatID, cs_IncentiveValue, cs_Division".$featuredColumn.") VALUES($categoryId, 1, $divID".$featuredOrder.")";
							$mysqli->query($assocSqlInsert);
						}
					}else{ //this category is not on file so we need to insert the category and then associate this division to it
						$assocCatInsertSql = "INSERT INTO cs_IncentivesToShow(iType, iShow, iCat) VALUES('".$v->OriginalAdvertisedContent->AdvertisedContent[0]->Content."', 0, '".$v->AdvertisedDealScenarioKind."')";
						$assocCatInsertRes = $mysqli->query($assocCatInsertSql);
						$categoryId = $mysqli->insert_id; 
						$assocSqlInsert = "INSERT INTO cs_IncentivesToShowAssoc(cs_IncentiveCatID, cs_IncentiveValue, cs_Division".$featuredColumn.") VALUES(".$categoryId.", 1, $divID".$featuredOrder.")";
						$assocSqlInsertRes = $mysqli->query($assocSqlInsert);
					}
					#############################################
				}
				
				
				$counter++; //update the general loop count
				$these_incentives++; //update the inserted incentives counter
				$these_incentives_desc .= "  -".$v->OriginalAdvertisedContent->AdvertisedContent[1]->Content."\n";
			}
				
				$this_message = "\nSuccess for $divName with $these_incentives incentives"."\n-------------------------------\n";
				$email_message .= $this_message.$these_incentives_desc;
				echo $this_message." ".$newIncentives."\n";
			
		} else { //the CURL response failed - count it and send a notification
			$failures++;
			$email_message .= "Failure for $divName"."\n";
		}
		
############################################
}

echo "<br />".$totalCount."<br />";
//now clear any remaining incentives that weren't expired but don't show in this new batch
$assocSqlInsertRes = $mysqli->query("DELETE FROM cs_IncentivesToShow WHERE setToDelete = 1");

//set up the data report
$curl_count = "Final results for AIS data:\n";
$curl_count .= $preExisting." pre-existing incentives\n";
$curl_count .= $newIncentives." successful attempts to insert new incentives\n";
$curl_count .= $failures." failures\n";
$final_message = $curl_count.$email_message;

//send out the notification
mail("keith@keithbonline.com","My subject",$final_message);
mail("nhill@dickhannah.com","My subject",$final_message);

?>

