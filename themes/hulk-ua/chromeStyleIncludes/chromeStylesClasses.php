<?
/////////////////////////////////////////////
/////////////////////////////////////////////
##########################################
class IncentiveQuery extends IncentiveOutput{
	/////////////////////////////////////////////
	/////////////////////////////////////////////
	function getIncentives($atts='') {
		//set up our query based on the query criteria we have
		$wp_upload = wp_upload_dir();
		$urlArray = explode("/", $wp_upload['url']);
		$url = $urlArray[2];
		
		//////////////////////
		//exception for volkswagen
		/////////////////////
		if(strstr($url, 'vwofportland.com')){
			$this->divisionId = 13;
			$this->divisionZip = 97233;
		}elseif(strstr($url, 'dickhannahvolkswagen.com')){
			$this->divisionId = 12;
			$this->divisionZip = 98662;
		}
		//////////////////////
		//end exception for volkswagen
		/////////////////////
		
		if(is_array($inputs) && $atts['mode']=='plugin'){
			echo "Division ID is ".$this->divisionId."<br />";
			echo "Division zip is ".$this->divisionZip."<br />";
		}
		#####################################################
		#####################################################
		$sql = $this->createSql($atts);
		//echo $sql;
		#####################################################
		#####################################################

		$dbresults = get_results($sql); //run the query through global db access function and return result
		$out = array(); //this is the placeholder for the container array for the separate model incentives that we'll gather
		
				foreach($dbresults as $row){ //loop through and arrange data arrays for each incentive
					$out = $this->formatChromeStylesOutputYearAis($row, $out);
				}
			$this->output = $out; //store the output array for later use
			$percentagePrograms = $this->arrangeIncentivesAis();
			$this->programs = $percentagePrograms;
			$incentivesToShow = $this->getVisibleIncentivesAis();
			$this->createHtmlYearAis($percentagePrograms, $incentivesToShow); //create the html that we'll present to the website and store it in the object
	}
		
		#####################################################
		#####################################################
		function createSql($atts=''){
			global $wpdb;
			//we need to determine if this dealership is associated with other dealerships and should be shown with them 
			$assocSql = "SELECT * FROM cs_IncentivesDivisionAssoc WHERE divisionAssocDivId1 = ".$this->divisionId." OR divisionAssocDivId2 = ".$this->divisionId;
			$myAssociations = get_results($assocSql);
			$makeAssocArray = array();
				foreach($myAssociations as $assoc){
						echo "<br>";
						$test = $assoc['divisionAssocDivId1'];
						$test2 = $assoc['divisionAssocDivId2'];
						if(!in_array($test, $makeAssocArray)){ array_push( $makeAssocArray, $test); }
						if(!in_array($test2, $makeAssocArray)){ array_push( $makeAssocArray, $test2); }

				}
			
			$sql = "
			SELECT Distinct 
			cs_IncentiveInfoNew.*, cs_IncentivesToShowAssoc.cs_IncentiveOrder
			FROM cs_IncentiveInfoNew, cs_IncentivesToShowAssoc ";
		
		$multDivOrder = ''; //this is an empty flag that we'll popualte if we determine that we have a multiple dealership visibility situation
		
		//////////////////////////////////////////////////////////////
		if(count($makeAssocArray) < 1){ //we don't have a multiple dealership visibility situation - just filter on the one division id
			$sql .=	" WHERE cs_IncentivesToShowAssoc.cs_Division = cs_IncentiveInfoNew.DivisionID";
			$sqlPost .= " AND cs_IncentiveInfoNew.DivisionID = ".$this->divisionId."";
		}else{ //we do have a multiple dealership visibility situation - set up the query to look for the multiple division ids
			$multCounter = 0;
			$start = '';
			$multDivOrder = 'cs_IncentiveInfoNew.Division,';

			foreach($makeAssocArray as $mk =>$mv){ //loop through the array of dealership ids we have and set them up in the query
				if($multCounter == 0){ //this is the first one so we need the where statement
					$start = " WHERE (";
					$startPost =" AND (";
				}else{ //this is not the first division id so we should use the or statement
					$start = "OR ";
					$startPost = "OR ";
				}
				$sql .= $start." cs_IncentivesToShowAssoc.cs_Division = ".$mv." ";
				$sqlPost .= $startPost." cs_IncentiveInfoNew.DivisionID = ".$mv." ";
				$multCounter++;
			}
			$sql .= ")"; //close the where parentheses since we know we opened it up becuase we have multiple dealerships to display
			$sqlPost .= ")"; //close the where parentheses since we know we opened it up becuase we have multiple dealerships to display

		}
		//////////////////////////////////////////////////////////////
		$sql .= " AND cs_IncentivesToShowAssoc.cs_IncentiveValue = 1 AND cs_IncentivesToShowAssoc.cs_IncentiveCatID = cs_IncentiveInfoNew.CategoryID";
		$sql .= $sqlPost;
		//$sql .= " AND cs_IncentiveInfoNew.DivisionID = ".$this->divisionId."";
		
		if( (strlen($this->model)) > 0 && $this->model != 'none'){ $sql .= " AND cs_IncentiveInfoNew.Model = '".$this->model."' "; }
		
		//test for the presence of the email plugin request envelope - loop through the $_GET envelope and remember the years noted there for the query
		$testGetForIncentiveYear = 0;
		$incentiveYears = array();
		foreach($_GET as $k=>$v){
			if(strstr($k, 'year')){
				array_push($incentiveYears, $v);
				$testGetForIncentiveYear++;
			} //end if
		} //end for
		if($testGetForIncentiveYear > 0){ //we have years that have been populated from the $_GET envelope from the Chrome Incentives plugin - use those years in the query
			$sql .= " AND ";
			if($testGetForIncentiveYear>1){ //we have a range of years so we need parentheses
				$sql .= "(";
			}
			for($i=0;$i<$testGetForIncentiveYear;$i++){ 
				if($i>0) $sql .= " OR ";
				$sql .= " cs_IncentiveInfoNew.Year = ".$incentiveYears[$i]." ";
			}
			if($testGetForIncentiveYear>1){ //we have a range of years so we need parentheses
				$sql .= ")";
			}
		}else{
			if( (strlen($this->year)) > 0 && $this->year != 'none'){ 
				$sql .= " AND cs_IncentiveInfoNew.Year = '".$this->year."' "; 
			}
		}

		if(is_array($inputs) && $atts['mode']=='plugin'){
			$sql .= " ORDER BY ".$multDivOrder."cs_IncentiveInfoNew.Model_Order, cs_IncentiveInfoNew.Year, cs_IncentiveInfoNew.Model, cs_IncentivesToShowAssoc.cs_IncentiveOrder, cs_IncentiveInfoNew.ProgramDescription";
		}else{
			$sql .= " ORDER BY ".$multDivOrder."cs_IncentiveInfoNew.Year, cs_IncentiveInfoNew.Model, cs_IncentivesToShowAssoc.cs_IncentiveOrder, cs_IncentiveInfoNew.ProgramDescription";
		}
		//echo $sql;
		return $sql;
		
		}
		#####################################################
		#####################################################


	/////////////////////////////////////////////
	/////////////////////////////////////////////
} //end class
##########################################
class IncentiveOutput{
	public $leftIncentiveOutput;
	public $rightIncentiveOutput;
	/////////////////////////////////////////////
	//called from getIncentives
	//returns array of visible incentive
	/////////////////////////////////////////////
	function getVisibleIncentivesAis(){
		$sql = "
		SELECT cs_IncentivesToShowAssoc.cs_IncentiveValue, cs_offerlogixDivision.divisionName, cs_IncentivesToShow.iType, cs_IncentivesToShow.iCat
		FROM cs_IncentivesToShowAssoc, cs_offerlogixDivision, cs_IncentivesToShow 
		WHERE cs_IncentivesToShowAssoc.cs_Division = ".$this->divisionId." 
		AND cs_offerlogixDivision.divisionID = ".$this->divisionId."
		AND cs_IncentivesToShow.cs_typeId = cs_IncentivesToShowAssoc.cs_IncentiveCatID
		ORDER BY cs_IncentivesToShowAssoc.cs_IncentiveOrder ASC
		";
		
		$dbresults = get_results($sql); //run the query through global db access function and return result		
		$cash = array(); //buckets for the visible incentives returned
		$rate = array();
		
		foreach($dbresults as $row){ //loop through and arrange data arrays for each visible incentive type
			if($row['cs_IncentiveValue']==1){ //this has been marked to be shown
				if($row['iCat']=='cash'){
					array_push($cash, $row['iType']);
				}elseif($row['iCat']=='rate'){
					array_push($rate, $row['iType']);
				}
			}
		} //end for
		return array('cash'=>$cash, 'rate'=>$rate);
	}
	/////////////////////////////////////////////
	//called from getIncentives
	/////////////////////////////////////////////
	function formatChromeStylesOutputYearAis($row, $out){	
		$yearKey = $row['Year']."_".$row['DivisionZip'];
		$yearKey = $row['Year'];
		if(!array_key_exists($yearKey, $out)){ //we have the master incentives array broken up into years - if the year numbered key doesn't exist, then create it
			$out[$yearKey] = array();
		}				
		
		$indivIncentive = array(); //this is the individual loan program 
		$indivIncentive['Division'] = $row['Division'];
		$indivIncentive['setToWeb'] = $row['setToWeb'];
		$indivIncentive['DivisionID'] = $row['DivisionID'];
		$indivIncentive['IncentiveID'] = $row['IncentiveID'];
		$indivIncentive['IncentiveRowID'] = $row['cs_IncentinveInfo'];
		$indivIncentive['IncentiveType'] = $row['IncentiveType'];
		$indivIncentive['APR'] = $row['APR'];
		$indivIncentive['Lease'] = $row['Lease'];
		$indivIncentive['Down'] = $row['Down'];
		$indivIncentive['Term'] = $row['Term'];
		$indivIncentive['CategoryDescription'] = $row['CategoryDescription'];
		$indivIncentive['EffectiveDate'] = $row['EffectiveDate'];
		$indivIncentive['ExpiryDate'] = $row['ExpiryDate'];
		$indivIncentive['ProgramDescription'] = $row['ProgramDescription'];
		$indivIncentive['Requirements'] = $row['Requirements'];
		$indivIncentive['ProgramText'] = $row['ProgramText'];
		$indivIncentive['Bullet1'] = $row['Bullet1'];

		$indivIncentive['Disclaimer'] = $row['Disclaimer'];
		$indivIncentive['PreviousOwnershipReq'] = $row['PreviousOwnershipReq'];
		$indivIncentive['Model'] = $row['Model'];
		$indivIncentive['Model_Order'] = $row['Model_Order'];
		$indivIncentive['Acode'] = $row['Acode'];
		$indivIncentive['MSRP'] = $row['MSRP'];
		$indivIncentive['StyleID'] = $row['StyleID'];
		$indivIncentive['Variation'] = $row['Variation'];
		$indivIncentive['Trim'] = $row['Trim'];
		$indivIncentive['Institution'] = $row['Institution'];
		$indivIncentive['Description'] = $row['Description'];
		$indivIncentive['SignatureHistoryID'] = $row['SignatureHistoryID'];
		$indivIncentive['TermTo'] = $row['TermTo'];
		$indivIncentive['TermFrom'] = $row['TermFrom'];
		$indivIncentive['TermValue'] = $row['TermValue'];
		if(strlen($row['ValueType'])==0){ $row['ValueType']='r'; }
		$indivIncentive['ValueType'] = $row['ValueType'];
		$indivIncentive['TermFinDisc'] = $row['TermFinDisc'];
		
			if(!array_key_exists($row['Model'],$out[$row['Year']])){ $out[$row['Year']][$row['Model']] = array(); }
			
			//$out[$row['Year']][$row['Model']][$row['IncentiveID']] = $indivIncentive;
			$out[$yearKey][$row['Model']][$row['IncentiveID']] = $indivIncentive;

			//$out = $this->findModelImageYear($out, $row['Model'], $row['Year'], $row['Division']);
			/*if(!is_array($out[$yearKey][$row['Model']]['image'])){
				$out[$yearKey][$row['Model']]['image'] = $this->findModelImage($out, $row['Model'], $row['Year'], $row['Division']);
			}*/
		return $out;
	}
	/////////////////////////////////////////////
	//called from formatChromeStylesOutputYear
	/////////////////////////////////////////////
	function findModelImage($outArray, $model, $year, $division){ //this is temporary and will need to be replaced with a real image retrieval function
		  //get the vehicle image
		  $query = 'SELECT * FROM model WHERE model_year = "';
		  $query .= $year;
		  $query .= '" AND model = "'.$model.'"';
		  //$query .= $model'].'"';
		  if($volkswagenFlag >0){ //we're on a volkswagen page - alter the query to add the divId and zip
			$query .= ' AND dealership_id = '.$division;
		  }
		  $query .= ' LIMIT 1';
		  $imageRes = get_results($query);
	
	
		  foreach($imageRes as $rowImage){ //we have the raw data list separated by '|'					
				$image = $rowImage; 
		  }
		 return $image;
	}	
	/////////////////////////////////////////////
	//called from formatChromeStylesOutputYear
	/////////////////////////////////////////////
	function findModelImageYear($outArray, $model, $year, $division){ //this is temporary and will need to be replaced with a real image retrieval function
		$sql = "SELECT cs_Image from cs_VehicleImages WHERE cs_Model = '$model' LIMIT 1";
		$dbresults = get_results($sql); //run the query through global db access function and return result
			foreach($dbresults as $row){
				$image = $row['cs_Image'];
			}
			
		$urlPrefix = $this->urlPrefix;
		$urlBase = "wp-content/uploads/ChromeData/vehicleImages/".strtolower($division)."/".$year."/";
		$fallbackBase = "wp-content/uploads/ChromeData/vehicleImages/".strtolower($division)."/";
		
		if( $image ){
				$fileToCheck = $urlBase.$image;
				$fallback = $fallbackBase.$image;
			if(file_exists($fileToCheck)){
				
				$outArray[$year][$model]['image'] = $urlPrefix.$fileToCheck;
			}else{
				$outArray[$year][$model]['image'] = $urlPrefix.$fallback;
			}
		}
		return $outArray;
	}
	/////////////////////////////////////////////
	//called from arrangeIncentives
	//returns updated incentives array
	/////////////////////////////////////////////
	function filterPercentageProgramsByYearAis($percentagePrograms, $vv, $kv, $year, $model_name){
		$rightOutputPercentProgram = ""; //flag to see if the program name has changed for the percentage offers. this reduces duplicate name listings
		$rightOutputPercentProgramHigh = ""; //flag to see if the program high RATE has changed for the percentage offers.
		$rightOutputPercentProgramLow = ""; //flag to see if the program high RATE has changed for the percentage offers.
		
		$incentiveTypes = array();
		$incentiveTypes['cash'] = array('Bonus Cash', 'Dealer Cash', 'Stand Alone Cash', 'Deferred Payment', 'Stock Allowance', 'Special Program', 'Residual');
		$incentiveTypes['rate'] = array('Lease Pull Ahead', 'Lease Rate', 'Finance Rate');
	
			if($vv["ValueType"]=="p") $vv['TermValue'] = number_format($vv['TermValue']); //we should get rid of the decimal for the dollar amounts, as opposed to rates
			if( !is_array($percentagePrograms[$year]) ){ //we do not have this program name on file yet
				$percentagePrograms[$year] = array();
			}
			if( !is_array($percentagePrograms[$year][$model_name]) ){ //we do not have this program name on file yet
				$percentagePrograms[$year][$model_name] = array();
			}
				
				//$incentiveKey = $vv["ProgramDescription"];
				$incentiveKey = $vv["ProgramDescription"]."_".$kv;
				if( !array_key_exists($incentiveKey, $percentagePrograms[$year][$model_name]) ){ //we do not have this program name on file yet
					if($vv["PreviousOwnershipReq"] == "No Previous"){ $vv["PreviousOwnershipReq"] = "No previous ownership required."; }
					$percentagePrograms[$year][$model_name][$incentiveKey] = array(); //set the flag to that program name inside the program category - rate vs residual
					$percentagePrograms[$year][$model_name][$incentiveKey]['Division'] = $vv['Division'];
					$percentagePrograms[$year][$model_name][$incentiveKey]['DivisionID'] = $vv['DivisionID'];
					$percentagePrograms[$year][$model_name][$incentiveKey]['setToWeb'] = $vv['setToWeb'];
					$percentagePrograms[$year][$model_name][$incentiveKey]['IncentiveID'] = $kv;
					$percentagePrograms[$year][$model_name][$incentiveKey]['IncentiveRowID'] = $vv['IncentiveRowID'];

					$percentagePrograms[$year][$model_name][$incentiveKey]['IncentiveType'] = $vv['IncentiveType'];
					$percentagePrograms[$year][$model_name][$incentiveKey]['APR'] = $vv['APR'];
					$percentagePrograms[$year][$model_name][$incentiveKey]['Lease'] = $vv['Lease'];
					$percentagePrograms[$year][$model_name][$incentiveKey]['Down'] = $vv['Down'];
					$percentagePrograms[$year][$model_name][$incentiveKey]['Term'] = $vv['Term'];
					$percentagePrograms[$year][$model_name][$incentiveKey]['Year'] = $year;
					$percentagePrograms[$year][$model_name][$incentiveKey]['ModelName'] = $model_name;
					$percentagePrograms[$year][$model_name][$incentiveKey]['Model_Order'] = $vv['Model_Order'];
					$percentagePrograms[$year][$model_name][$incentiveKey]['Acode'] = $vv['Acode'];
					$percentagePrograms[$year][$model_name][$incentiveKey]['MSRP'] = $vv['MSRP'];
					$percentagePrograms[$year][$model_name][$incentiveKey]['StyleID'] = $vv['StyleID'];
					$percentagePrograms[$year][$model_name][$incentiveKey]['Variation'] = $vv['Variation'];
					$percentagePrograms[$year][$model_name][$incentiveKey]['Trim'] = $vv['Trim'];
					$percentagePrograms[$year][$model_name][$incentiveKey]['CategoryDescription'] = $vv['CategoryDescription'];
					$percentagePrograms[$year][$model_name][$incentiveKey]['EffectiveDate'] = $vv['EffectiveDate'];
					$percentagePrograms[$year][$model_name][$incentiveKey]['ExpiryDate'] = $vv['ExpiryDate'];
					$percentagePrograms[$year][$model_name][$incentiveKey]['ProgramDescription'] = $vv['ProgramDescription'];
					$percentagePrograms[$year][$model_name][$incentiveKey]['Institution'] = $vv['Institution'];
					$percentagePrograms[$year][$model_name][$incentiveKey]['Description'] = $this->getIncentiveDescription($kv);
					$percentagePrograms[$year][$model_name][$incentiveKey]['ProgramText'] = $vv['ProgramText'];
					$percentagePrograms[$year][$model_name][$incentiveKey]['Bullet1'] = $vv['Bullet1'];

					$percentagePrograms[$year][$model_name][$incentiveKey]['Disclaimer'] = $vv['Disclaimer'];
					$percentagePrograms[$year][$model_name][$incentiveKey]['PreviousOwnershipReq'] = $vv['PreviousOwnershipReq'];
				}
		
		return $percentagePrograms;
	}
	/////////////////////////////////////////////
	//called from filterPercentageProgramsByYearDev
	/////////////////////////////////////////////
	function getIncentiveDescription($incentive){
		$sql = "SELECT * FROM cs_ProgramRule WHERE IncentiveID = $incentive";
		$dbresults = get_results($sql); //run the query through global db access function and return result
			foreach($dbresults as $row){
				return $row['Description'];
			}
	}
	/////////////////////////////////////////////
	//called from createHtmlYear
	/////////////////////////////////////////////
	function showPercentageProgramsYearAis($program, $model, $year, $incentivesToShow){
					
					$output = ""; //placeholder for html output					
					$v = $program;
					//print_r($v);
					//echo "<br /><br />";
					if( strlen($v["ProgramText"])>0 || strlen($v["Term"])>0 ){ 
						if($v["PreviousOwnershipReq"] == "No Previous"){ $v["PreviousOwnershipReq"] = "No previous ownership required"; }
					}
							$output .= "<div class='incentiveDetails ".$v['IncentiveID']." ".str_replace(" ", "-", $model)." ".$v['Year']."'>Details</div>";
							$output .= "<a href='http://".$this->weblink."/new/".strtolower($v["Division"])."/".$model."/' target='_blank'><div class='incentiveInventory ".$kv." ".$k." ".$vv["Division"]."'>Inventory</div></a>";
							$output .= "<li><h4 class='program_header'>".$v["Year"]." ".$v["ModelName"]." ".$v["Variation"]."</h4>";
							
							if(strstr(strtolower($v["IncentiveType"]), 'featured')){ $output .= "<div class='featured-header'>".$v["IncentiveType"]."</div>"; }

							$output .= "<div style='margin-top:3px; margin-left:3px; font-size:11pt'>".$v["ProgramDescription"]."</div>";
							
							/////////////////////////////////////////////////////////////////
							if(in_array($v["CategoryDescription"], $incentivesToShow['cash'])){ $incentiveType = "cash"; }
							if(in_array($v["CategoryDescription"], $incentivesToShow['rate'])){ $incentiveType = "rate"; }
							############################
							$thisTerm = array(); //placeholder array for output
							############################
							/////////////////////////////////////////////////////////////////////////
							/////////////////////////////////
							
							/////////////////////////////////
							/////////////////////////////////
							/////////////////////////////////////////////////////////////////////////						
								if(!in_array($i_output, $thisTerm)){ 
									array_push($thisTerm, $i_output); 
									$output .= $pOutput; //add this line of output since we don't have it already
								}
								
								$output .=  "<div class='clear'></div><div id='details_".$v["IncentiveID"]."_".str_replace(" ", "-", $model)."_".$v['Year']."' class='program_details'>"; //check and set up the program details for collapsible detail section
								//$disclaimer = $this->createDisclaimerAis($v, $incentiveType);
								$output .= $v['ProgramText'];
								$output .= "<div style='clear:both; height:20px'></div>";
								if(!strstr($v['CategoryDescription'],'APR')){
								 	$output .= $v['Bullet1'];
									$output .= "<div style='clear:both; height:20px'></div>";
								}
								$output .= $v['Disclaimer'];
								$output .=  "</div><br />";
								$output .= "</li>";
					return $output; 
	}
	/////////////////////////////////////////////
	//called from createHtmlYear
	/////////////////////////////////////////////
	function showPercentageProgramsYearAisBack($program, $model, $year, $incentivesToShow){
					$output = ""; //placeholder for html output					
					$v = $program;
					
					if( strlen($v["ProgramText"])>0 || strlen($v["TermFinDisc"])>0 || strlen($v["PreviousOwnershipReq"])>0 ){ 
						if($v["PreviousOwnershipReq"] == "No Previous"){ $v["PreviousOwnershipReq"] = "No previous ownership required"; }
					}
							$output .= "<div class='incentiveDetails ".$v['IncentiveID']." ".str_replace(" ", "-", $model)." ".$v['Year']."'>Details</div>";
							$output .= "<a href='http://".$this->weblink."/new/".strtolower($v["Division"])."/".$model."/' target='_blank'><div class='incentiveInventory ".$kv." ".$k." ".$vv["Division"]."'>Inventory</div></a>";
							$output .= "<li><h4 class='program_header'>".$v["CategoryDescription"]."</h4>";
							
							if(strstr(strtolower($v["IncentiveType"]), 'featured')){ $output .= "<div class='featured-header'>".$v["IncentiveType"]."</div>"; }

							$output .= "<div style='margin-top:3px; margin-left:3px; font-size:11pt'>".$v["ProgramDescription"]."</div>";
							
							/////////////////////////////////////////////////////////////////
							if(in_array($v["CategoryDescription"], $incentivesToShow['cash'])){ $incentiveType = "cash"; }
							if(in_array($v["CategoryDescription"], $incentivesToShow['rate'])){ $incentiveType = "rate"; }
							############################
							$thisTerm = array(); //placeholder array for output
							############################
							
							for($i=0; $i<count($v['Terms']); $i++){ //loop through the terms and pick the conditional for the output for this particular incentive
							
								$i_output = ""; //this is the output that goes to the UI
								$pOutput = ""; //this is the placeholder variable that can go into the $thisTerm array to check against, and can go into the output if it hasnt been shown already
								/////////////////////////////////////////
								if($incentiveType == "cash"){
									switch($v["CategoryDescription"]){	
										case "Bonus Cash":
											$pOutput = "$".number_format($v['Terms'][$i]['TermValue'])." bonus cash at time of purchase<br />";
											$i_output = $pOutput;
										break;
										case "Special Program":
											$pOutput = "$".number_format($v['Terms'][$i]['TermValue'])." bonus cash at time of purchase<br />";
											$i_output = $pOutput;
										break;
									} //end switch
								}elseif($incentiveType == "rate"){
								
									if( ($v['Terms'][$i]['FromVal'] != $v['Terms'][$i]['ToVal']) && ($v['Terms'][$i]['FromVal'] > 0) ){ //get the time range for display
										$monthRange = $v['Terms'][$i]['FromVal']."-".$v['Terms'][$i]['ToVal'];
									}else{
										$monthRange = $v['Terms'][$i]['ToVal'];
									}
								
									
									switch($v["CategoryDescription"]){	
										case "Lease Rate":
											if( ($v['Terms'][$i]['FromVal'] > 0) && ($v['Terms'][$i]['FromVal'] != $v['Terms'][$i]['ToVal']) ){
												$mid = "for";
											}else{
												$mid = "up to";
											}
											$pOutput = number_format(($v['Terms'][$i]['TermValue'] * 2400), 2)."% APR $mid $monthRange months<br />";
											$i_output = $pOutput;
										break;
										case "Finance Rate":
											if( ($v['Terms'][$i]['FromVal'] > 0) && ($v['Terms'][$i]['FromVal'] != $v['Terms'][$i]['ToVal']) ){
												$mid = "for";
											}else{
												$mid = "up to";
											}
											$pOutput = number_format($v['Terms'][$i]['TermValue'], 2)."% APR ".$mid." ".$monthRange." months<br />";
											$i_output = $pOutput;
										break;
										case "Residual":
											$pOutput .= $v['Terms'][$i]['TermValue']."% Remaining value at end of lease<br />";
											$i_output = $pOutput;
										break;
									} //end switch
									
								} //end if
								////////////////////////////////////////////////////
								if(!in_array($i_output, $thisTerm)){ 
									array_push($thisTerm, $i_output); 
									$output .= $pOutput; //add this line of output since we don't have it already
								}
								/////////////////////////////////
							} //end terms output (for)	
							/////////////////////////////////////////////////////////////////////////
							/////////////////////////////////
							//start programValue
							for($i=0; $i<count($v['ProgramValues']); $i++){ //loop through the terms and pick the conditional for the output for this particular incentive
								$i_output = ""; //this is the output that goes to the UI
								$pOutput = ""; //this is the placeholder variable that can go into the $thisTerm array to check against, and can go into the output if it hasnt been shown already
								////////////////////////////////////
								if($incentiveType == "cash"){
									switch($v["CategoryDescription"]){	
										case "Dealer Cash":
											$pOutput = "$".number_format($v['ProgramValues'][$i]['Cash'])." dealer cash<br />";
											$i_output = $pOutput;
										break;
										case "Bonus Cash":
											$pOutput = "$".number_format($v['ProgramValues'][$i]['Cash'])." bonus cash at time of purchase<br />";
											$i_output = $pOutput;
										break;
										case "Stand Alone Cash":
											$pOutput = "$".number_format($v['ProgramValues'][$i]['Cash'])." stand alone cash at time of purchase<br />";
											$i_output = $pOutput;
										break;
										case "Special Program":
											$pOutput = trim($v['ProgramValues'][$i]['Description'], '"')."<br />";
											$i_output = $pOutput;
										break;
									} //end switch
								}elseif($incentiveType == "rate"){
									switch($v["CategoryDescription"]){	
										case "Lease Pull Ahead":											
											$pOutput = "Up to ".$v['ProgramValues'][$i]['MaxNumberOfPaymentsWaived']." payments waived upon signing new lease";
											$i_output = $pOutput;
										break;
										case "Residual":
											$pOutput .= $v['Terms'][$i]['TermValue']."% Remaining value at end of lease<br />";
											$i_output = $pOutput;
										break;
									} //end switch
									
								} //end if
								////////////////////////////////////////
							}
							//end programValue 
							/////////////////////////////////
							/////////////////////////////////
							/////////////////////////////////////////////////////////////////////////						
								if(!in_array($i_output, $thisTerm)){ 
									array_push($thisTerm, $i_output); 
									$output .= $pOutput; //add this line of output since we don't have it already
								}
								
								$output .=  "<div class='clear'></div><div id='details_".$v["IncentiveID"]."_".str_replace(" ", "-", $model)."_".$v['Year']."' class='program_details'>"; //check and set up the program details for collapsible detail section
								$disclaimer = $this->createDisclaimerAis($v, $incentiveType);
								$output .= $disclaimer;
								$output .=  "</div><br />";
								$output .= "</li>";
					return $output; 
	}
	/////////////////////////////////////////////
	//called from createHtmlYear
	/////////////////////////////////////////////
	function createDisclaimerAis($v, $incentiveType){ //create the disclaimer info to display in the UI for a given incentive
		$template .= $v["ProgramText"];
		if($v["MSRP"]>0) $template .= "<br /><p><b>MSRP:</b> $".number_format($v['MSRP'])."</p>";
		if(strlen($v["Disclaimer"])>0) $template .= "<p class='disclaimer_text'>*".$v["Disclaimer"]."</p>";
		return $template;
	}
	/////////////////////////////////////////////
	//called from getIncentives
	/////////////////////////////////////////////
	function arrangeIncentivesAis(){
		$percentagePrograms = array(); //array to hold the percentage programs
		foreach($this->output as $yk=>$yv){
				foreach($yv as $k=>$v){ //assemble our output for this year
						
						foreach($v as $kv=>$vv){ //loop through the incentives and assemble our content							
							if($kv != "image"){ //we want to ignore the image key and just loop through the incentiveID keys here
								$model_name = $k;
								
								if(!is_array($percentagePrograms[$yk][$k]['image'])){
									//$output[$yk][$k]['image'] = $this->findModelImage($out, $k, $yk, $this->divisionId);
									$percentagePrograms[$yk][$k]['image'] = $this->findModelImage($out, $k, $yk, $this->divisionId);
									//echo "<br />image for $yk $k is now ";
									//print_r($percentagePrograms[$yk][$k]['image']);
								}
								
								$percentagePrograms = $this->filterPercentageProgramsByYearAis($percentagePrograms, $vv, $kv, $yk, $model_name);
							}//end image if
						}//end $v for
						
				} //end $yv for
		} //end year for
		return $percentagePrograms;
	}
	/////////////////////////////////////////////
	//called from getIncentives
	/////////////////////////////////////////////
	function createHtmlYearAis($percentagePrograms, $incentivesToShow){
		$yearsToShow = array(); //we're going to test for visible incentives so we need an array to set which years this applies to
		//print_r($percentagePrograms);
		
		foreach($percentagePrograms as $yk=>$yv){
			foreach($yv as $y1=>$v1){
				$yearsToShow[$y1] = array();
					foreach($v1 as $xk=>$xv){
							if(!in_array($yk, $yearsToShow[$y1])) array_push($yearsToShow[$y1], $yk);
					} //end for
			} //end for
		} //end for
		//print_r($yearsToShow);
		
		$leftOutput = ""; //html container
		$rightOutput = ""; //html container
		$rightOutputPercent = ""; //html container
		$rightYearTabs = ""; //for the year tabs

		//create the placeholder div for the landing page
		$placeholder = "
		<div class='i_contentPlaceholder landing'>
				<div class='i_content_container'>
					<div class='i_content_inner_container_placeholder' style='display:block'>
						<img class='landing' src='http://www.dickhannahacuraofportland.com/wp-content/themes/hulk-ua/images/incentives/".strtolower($this->division)."-incent-landing.jpg'>
					</div>
				</div>
		</div>";
		
			$rightOutput .= "<div class='i_content ".$class."'><div id='year-tabs'>###YEARTABS###</div>";
			$loopCounter = 0;
			$leftArray = array();
			$yearsShown = array();
			
			foreach($percentagePrograms as $yk=>$yv){
							$rightYearTabs .= "<div class='i_year_header'>".$yk."</div>";
					
					foreach($yv as $k=>$v){ //assemble our output
						$class = str_replace(" ", "-", $k);
						$m = str_replace("-", " ", $k);
						$modelShowName = ucwords($m);
												
							$leftOutputLine = "<div class='i_button ".str_replace(" ", "-", $k)." ib_unselected'>".$k."</div><div class='clear'></div>";
							if(!in_array($leftOutputLine, $leftArray)){ 
								$leftOutput .= $leftOutputLine;
								array_push($leftArray, $leftOutputLine);
							}
						
						$rightOutput .= "<div class='i_content_container' id='content_".$loopCounter."'>";
						$rightOutput .= "<div class='i_content_inner_container ".$class."-".$yk."'>";
						$rightOutput .= "###".$yk."_".$k."_IMAGE###";
						//$rightOutput .= "<img class='vehicleIncentiveImage' src='".$this->output[$yk][$k]['image']."'><br />";		
						$rightOutput .= "<h3 class='incentive_year_model'>".$yk." ".$k."</h3><hr>";
						foreach($v as $kk=>$vvv){
							$rightOutput .= "<ul class='i_list'>";
							if($kk != "image") $rightOutput .= $this->showPercentageProgramsYearAis($vvv, $k, $yk, $incentivesToShow);
							$rightOutput .= "</ul>";
							$loopCounter++;
						}
						$rightOutput .= "</div>";
						$rightOutput .= "</div>";
						
					} //end for
					
			} //end year for
			$rightOutput .= "</div>";
					
		//add the placeholder
		$rightWtabs = str_replace("###YEARTABS###", $rightYearTabs, $rightOutput);
		$rightOutput = $placeholder.$rightWtabs;
		$this->leftIncentiveOutput = $leftOutput;
		$this->rightIncentiveOutput = $rightOutput;
	}
	/////////////////////////////////////////////
	//called from shortcode - this is for the UI
	/////////////////////////////////////////////
	function showHtml(){
		echo "<div id='left_incentive_container'>".$this->leftIncentiveOutput."</div>";
		echo "<div id='right_incentive_container'>".$this->rightIncentiveOutput."</div>";
	}
	/////////////////////////////////////////////
	//called from shortcode - this is for the UI - just return the data
	/////////////////////////////////////////////
	function returnData(){
		$myReturn = $this->output;
		return $myReturn;
	}
	/////////////////////////////////////////////
	/////////////////////////////////////////////
	function checkNameException($divisionId, $year, $currentName){
		$modelExceptionSql = "Select * FROM cs_ModelNameException WHERE cs_DivisionID = ".$divisionId." AND cs_ModelYear = ".$year."  AND cs_ModelNameAis LIKE '".$currentName."' LIMIT 1";						
		$dbresults = get_results($modelExceptionSql);
		$excepFlag = "";
		$excepID = "";
		
			if(count($dbresults)> 0 ){ //we have a result - return it
				$excepFlag = "<span style='color:red'> *</span>";
				foreach($dbresults as $rowExcep){
					$newInfo = array('id'=>$rowExcep['cs_ModelExceptionID'], 'name'=>$rowExcep['cs_ModelNameHannah']);
				}
				return $newInfo;
			}else{
				return null;
			}
	}
	/////////////////////////////////////////////
	/////////////////////////////////////////////
	//this is for the plugin incentive preview display
	/////////////////////////////////////////////
	function showPluginPreview($visible){
		$yearsToShow = array();
		$make = dh_get_make_from_dealership();
		$htmlInc = get_theme_root()."/hulk-ua/chromeStyleIncludes/chromeStylesIncentivesHtml.php";
		foreach($_GET as $gk=>$gv){
			if(strstr($gk, 'year')) {
				array_push($yearsToShow, $gv); 
			}
		}
		
		//print_r($this->programs);
		foreach($this->programs as $k=>$v){			
			$splitYearArray = explode("_", $k);
			$splitYear = $splitYearArray[0];
			echo "<div class='yearIncentiveGroup' ";
				if(!in_array($splitYear, $yearsToShow)) echo " style='display:none' ";
			echo ">";
			//////////////////
			echo "<h3>".$k."</h3>";
			echo "*Check the checkbox to set this incentive to the outgoing email<br />
			*Select the drop-down to change the vehicle order<br />
			<span style='color:red'>*</span> Indicates a model name override from the incoming AIS data<br /> 
			";
			$counter = 0;
			foreach($v as $k1=>$v1){
				//print_r($v1);
				//echo "<br /><br />";
				
				$counter++;
				$currentModel = "";
				foreach($v1 as $k2=>$v2){
					//print_r($k2);
					//echo "<br /><br />";
					$leaseApr = '';
					$leaseTerm = '';
					$leaseDown = '';
					
					/*$sql = "SELECT * FROM cs_LeaseInfoEmail WHERE cs_Acode = '".$v2["StyleID"]."' LIMIT 1";
					$dbresults = get_results($sql);
						
						if(count($dbresults)> 0 ){
							foreach($dbresults as $row){
								$leaseApr = $row['cs_LeaseRate'];
								$leaseTerm = $row['cs_LeaseMonths'];
								$leaseDown = $row['cs_AmountDown'];
							}
							$action = "Edit";
						}else{
							$action = "Add";
						}*/
					if($k2 != 'image'){
					//if($v2["ModelName"] != 'h'){
						$excepFlag = "";
						$excepID = "";
						$newName = $this->checkNameException($v2['DivisionID'], $v2['Year'], $v2['ModelName']);
						if(is_array($newName)){
							 $excepFlag = "<span style='color:red'> *</span>";
							 $excepID = $newName['id'];
							 $v2['ModelName'] = $newName['name'];
						}
						
						if($v2["ModelName"] != $currentModel){
							echo "<h4 class='modelName' style='width:135px'>".$v2["ModelName"]."$excepFlag</h4>";
							
							echo "<!--<select id='".$k."_".str_replace(" ", "_", $v2["ModelName"])."' class='modelOrder'>";
							
							for($i=0;$i<count($v);$i++){
									echo "<option value='$i'";
									if($v2["Model_Order"]==$i)echo " selected ";
									echo ">$i</option>";
							}
							
							echo "</select>-->";
							echo "
								<!--<form id='' name=''  method='post' action='admin-post.php' style='margin-left:50px'>
									<input type='hidden' id='action' name='action' value='cloneModel' />
									<input type='submit' value='Clone this model to:' /><input type='text' name='cloneName' id='cloneName' />
									<input type='hidden' name='cloneDivisionId' id='cloneDivisionId' value='".$v2["DivisionID"]."' />
									<input type='hidden' name='cloneDivision' id='cloneDivision' value='".$v2["Division"]."' />

									<input type='hidden' name='cloneYear' id='cloneYear' value='".$v2["Year"]."' />
									<input type='hidden' name='cloneModel' id='cloneModel' value='".$v2["ModelName"]."' />
									";
							foreach($_GET as $kk=>$vv){
								if(strstr($kk, 'year')){ echo "<input type='hidden' name='$kk' id='$kk' value='".$vv."' />"; }
							}
							echo "</form>-->";
							echo "
								<form id='' name=''  method='post' action='admin-post.php' style='margin-left:50px'>
									<input type='hidden' id='action' name='action' value='createModelNameException' />
									<input type='submit' value='Rename this model to:' /><input type='text' name='exceptionName' id='exceptionName' />
									<input type='hidden' name='exceptionDivisionId' id='exceptionDivisionId' value='".$v2["DivisionID"]."' />
									<input type='hidden' name='exceptionDivision' id='exceptionDivision' value='".$v2["Division"]."' />
									<input type='hidden' name='exceptionYr' id='exceptionYr' value='".$v2["Year"]."' />
									<input type='hidden' name='exceptionModel' id='exceptionModel' value='".$v2["ModelName"]."' />
									";
							foreach($_GET as $kk=>$vv){
								if(strstr($kk, 'year')){ echo "<input type='hidden' name='$kk' id='$kk' value='".$vv."' />"; }
							}
							echo "</form>";
							if(strlen($excepFlag)>0){
								echo "<a id='remove_".$excepID."' name='remove_".$excepID."' class='removeNameOverride' style='cursor:pointer; cursor:hand; margin-left:50px; color:red'>Remove the current AIS model name override</a>";
								echo "<form id='form_remove_".$excepID."' name='form_remove_".$excepID."' method='post' action='admin-post.php' class='emailDataForm'>
										<input type='hidden' id='action' name='action' value='removeNameException' />
										<input type='hidden' id='exceptionToRemove' name='exceptionToRemove' value='".$excepID."' />
										<input type='hidden' name='exceptionDivisionId' id='exceptionDivisionId' value='".$v2["DivisionID"]."' />
										<input type='hidden' name='exceptionDivision' id='exceptionDivision' value='".$v2["Division"]."' />
										<input type='hidden' name='exceptionYr' id='exceptionYr' value='".$v2["Year"]."' />";
										
										foreach($_GET as $kk=>$vv){
											if(strstr($kk, 'year')){ echo "<input type='hidden' name='$kk' id='$kk' value='".$vv."' />"; }
										}
										
								echo " </form>
								";
							}
							echo "<div style='display:none' class='leaseForm' id='hider_".$v2["Acode"]."_".$counter."'>";
							echo "</div>";
							$currentModel = $v2["ModelName"];
						}
							echo "<div class='yearIncentiveIndiv' style='margin-left:54px; margin-top:5px'>";
							echo "<input type='checkbox' class='indivIncentiveVisibleCheckbox' name='".$v2["DivisionID"]."_".$v2["IncentiveRowID"]."' id='".$v2["DivisionID"]."_".$v2["IncentiveRowID"]."'";
									if($v2["setToWeb"]==1) echo " checked ";
									echo " /> 
									<b class='pDesc'>".$v2["ProgramDescription"]."</b> for ".$k." ".$v2["ModelName"]." ".$v2["Variation"]."  ".$v2["Trim"];
							echo "<div style='margin-left:25px'>".$v2['ProgramText']."</div>";
							echo "</div>";
                    } //end if
				} //end for

			} //end for
			echo "<input class='noClass' style='margin:20px 0px 0px 30px' value='Set incentives to output' type='submit' name='incentiveEmailSubmit' id='incentiveEmailSubmit'>";
			//////////////////
			echo "</div>";
			echo "
				<form method='post' action='admin-post.php' id='setIncentiveForEmailForm' name='setIncentiveForEmailForm'>
					<input type='hidden' id='action' name='action' value='setIncentiveForEmail' />
					<input type='hidden' id='divisionSelected' name='divisionSelected' value='".$_GET['divisionSelected']."' />
					<input type='hidden' id='division' name='division' value='".$_GET['division']."' />";
						
						foreach($_GET as $ky=>$vy){
							if(strstr($ky, "year")){ echo "<input type='hidden' id='$ky' name='$ky' value='".$vy."' />"; }
						}
						
			echo "
				</form>
				
				<form method='post' action='admin-post.php' id='modelOrderForm' name='modelOrderForm'>
					<input type='hidden' id='action' name='action' value='setModelOrder' />
					<input type='hidden' id='modelMake' name='modelMake' value='".$make."' />
					<input type='hidden' id='modelOrderId' name='modelOrderId' value='' />
					<input type='hidden' id='modelOrderValue' name='modelOrderValue' value='' />
					<input type='hidden' id='modelOrderReturn' name='modelOrderReturn' value='' />
				</form>
			";
		
		} //end for
		
		?>
		<script>
			///////////////////////////////////////////////////////
			///////////////////////////////////////////////////////
			jQuery('.removeNameOverride').on('click', function(){
				 var myId = jQuery(this).attr('id');
				 var formToSubmit = "form_" + myId;
				 jQuery('#' + formToSubmit).submit();
			});
			///////////////////////////////////////////////////////
			///////////////////////////////////////////////////////
			//jquery to toggle form visibility for lease details
			///////////////////////////////////////////////////////
			jQuery('.hideable').on('click', function() {
			 
			  var myId = jQuery(this).attr('id'); //get the id of the link that was clicked
			  var rawId = myId.split("_"); //split it to get the acode
			  var targetDiv = "hider_" + rawId[1] + "_" + rawId[2]; //reassemble the id to know which form id to show
			  
				  if( jQuery('#' + targetDiv).is(":visible") ){ //toggle the lease form visibility 
					jQuery('#' + targetDiv).hide();
				  }else{
					jQuery('#' + targetDiv).show();
				  }
			});
			///////////////////////////////////////////////////////
			///////////////////////////////////////////////////////
			jQuery('#incentiveEmailSubmit').on('click', function() {
				var counter = 0;
				jQuery(".yearIncentiveIndiv").children().each(function() {
					var thisClass = jQuery(this).attr('class');
					if(thisClass === undefined){ //do nothing
					}else if(thisClass.indexOf('indivIncentiveVisibleCheckbox') > -1){
						counter++;
						var myId = jQuery(this).attr('id');
						var idSplit = myId.split("_", myId);
						var myIdField = myId + '_check_'+myId;
							
						if(jQuery(this).attr('checked')) {
							var myValue = 1;
						}else{
							var myValue = 0;
						}
							
							jQuery("<input type='hidden' value='" + myValue +"' />")
							.attr("id", myIdField)
							.attr("name", myIdField)
							.appendTo("#setIncentiveForEmailForm");
						
					}
					
				});
				jQuery('#setIncentiveForEmailForm').submit();
			});
			///////////////////////////////////////////////////////
			///////////////////////////////////////////////////////
			//jquery to toggle model reordering for the email incentives
			///////////////////////////////////////////////////////
			jQuery('.modelOrder').on('change', function() { 
			 	 var myId = jQuery(this).attr('id');
				 var myVal = jQuery(this).val();
				 var idSplit = myId.split("_");
				 var loc = document.location;
				 jQuery("#modelOrderId").val(myId);
				 jQuery("#modelOrderValue").val(myVal);
				 jQuery("#modelOrderReturn").val(loc);
				 jQuery("#modelOrderForm").submit();
			});
			///////////////////////////////////////////////////////
			///////////////////////////////////////////////////////
		</script>
		<style>
			.shortLine { width:50px; }
			.leaseForm { margin-left:80px; margin-bottom:10px; }
			.leaseSmall { font-size:11px; }
			.emailDataForm { display:inline-block; }
		</style>
		<?
	
	
	}
	/////////////////////////////////////////////
} //end class
##########################################
class VehicleIncentiveGroup extends IncentiveQuery {
    public $division; //the brand like 'Acura', 'Chrysler'
	public $divisionId; //integer
	public $divisionZip; //integer
	public $urlPrefix; //live vs dev
	public $divisionSplashImage; //integer
	public $weblink; //this is the division website
	public $model; //like 'RDX' or 'Bronco'
	public $year; //year of manufacture
	public $output; //stored data array for website functionality
	public $html; //html for the website
	public $programs; //this is the full filtered array of incentives
}
##############################################
wp_enqueue_style( 'chromeIncentives', get_template_directory_uri() . '/css/chromeIncentives.css' );
##############################################################################################
##############################################################################################
##############################################################################################
?>
