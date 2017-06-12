<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Incentive HTML</title>
</head>

<body>
	<div>
	<?
	/////////////////////////////////////////////////
	function checkFinanceRates($financeRates, $TermValue, $ToVal){ //compares the array keys to see if the incoming rate is already set in the array. if it is already set then it should check to set the lowest month range value
			if(!array_key_exists($TermValue,$financeRates)){ //we don't have this key in the array - set it
				$financeRates[$TermValue] = $ToVal; //set the onth range value
			}else{
				if($ToVal > $financeRates[$TermValue]) $financeRates[$TermValue] = $ToVal; //this one is lower so record it in the place of its predecessor
			}
		return $financeRates;
	}
	/////////////////////////////////////////////////
		$con = mysqli_connect("localhost","dickhannahcom","w62h081o6R","dickhannahcom");
		if (mysqli_connect_errno()){ echo "Failed to connect to MySQL: " . mysqli_connect_error(); } // Check connection
		
		$sqlVis = "
		SELECT * FROM cs_IncentivesToShow, cs_IncentivesToShowAssoc 
		WHERE cs_IncentivesToShowAssoc.cs_Division = ".$_GET['divisionID']."
		AND cs_IncentivesToShowAssoc.cs_IncentiveValue  = 1
		AND cs_IncentivesToShowAssoc.cs_IncentiveCatID = cs_IncentivesToShow.cs_typeId
		";
		$resultDiv = $con->query($sqlVis); //get the name of the division from the database for the URL
		
		$orQuery = "";
		$counter = 0;
		foreach($resultDiv as $row){ 
			if($counter ==0){ $orQuery .= "(" ; }else{ $orQuery .= " OR "; }
			$orQuery .= "cs_IncentiveInfo.CategoryDescription = '".$row['iType']."' ";
			$counter++;
		}
		$orQuery .= ")";
		
		$sql = "
		SELECT cs_IncentiveInfo.Division, cs_IncentiveInfo.CategoryDescription, cs_IncentiveInfo.Year, cs_IncentiveInfo.Model, cs_IncentiveInfo.IncentiveType, cs_IncentiveInfo.MSRP,
		cs_ValueVariation.ValueVariationID,
		cs_ProgramValue.ProgramValueID,
		cs_Term.FromVal, cs_Term.ToVal, cs_Term.TermValue, cs_Term.ValueType, cs_Term.Variance
	
		FROM cs_IncentiveInfo, cs_ValueVariation,cs_ProgramValue, cs_Term
		
		WHERE cs_IncentiveInfo.Division = '".$_GET['division']."'
		AND cs_IncentiveInfo.Model = '".$_GET['model']."'
		AND cs_IncentiveInfo.Year = ".$_GET['year']." 
		AND cs_ValueVariation.IncentiveID = cs_IncentiveInfo.IncentiveID
		AND cs_ProgramValue.ValueVariationID = cs_ValueVariation.ValueVariationID
		AND cs_Term.ProgramValueID = cs_ProgramValue.ProgramValueID
		
		AND $orQuery
		";
		
		$lease = array();
		$finance = array();
		$resultDiv = $con->query($sql); //get the name of the division from the database for the URL
		$financeRates = array();
		
		foreach($resultDiv as $row){
			$msrp = $row["MSRP"];
			/*if($row["CategoryDescription"] == "Lease Rate"){
				if($row["ToVal"] == 36){ 
					array_push($lease, $row["TermValue"]); 
				} 
			}else*/
			if($row["CategoryDescription"] == "Finance Rate"){
				$finance = checkFinanceRates($finance, $row["TermValue"], $row["ToVal"]); //check to see if this rate is already accounted with its lowest month range value
			} 
		}
		###############################################
			$sql = "SELECT cs_Image from cs_VehicleImages WHERE cs_Model = '".$_GET['model']."' LIMIT 1";
			$dbresults = $con->query($sql); //run the query through global db access function and return result
				foreach($dbresults as $row){
					$image = $row['cs_Image'];
				}
			$urlPrefix = "http://www.dickhannah.com/";
			$urlBase = "wp-content/uploads/ChromeData/vehicleImages/".strtolower($_GET['division'])."/".$_GET['model']."/";
			$fallbackBase = "wp-content/uploads/ChromeData/vehicleImages/".strtolower($_GET['division'])."/";
			
			if( $image ){
					$fileToCheck = $urlBase.$image;
					$fallback = $fallbackBase.$image;
				if(file_exists($fileToCheck)){
					
					$image = $urlPrefix.$fileToCheck;
				}else{
					$image = $urlPrefix.$fallback;
				}
			}
		
		###############################################
		$sql = "SELECT * FROM cs_LeaseInfoEmail WHERE cs_Acode = '".$_GET['styleId']."' LIMIT 1"; //get the manually set lease details for this model
		$dbresults = $con->query($sql); //run the query through global db access function and return result
			foreach($dbresults as $row){
				$leaseRate = $row['cs_LeaseRate'];
				if($leaseRate <= 0) $leaseRate = 0;
				$leaseMonths = $row['cs_LeaseMonths'];
				if($leaseMonths <= 0) $leaseMonths = 0;
				$amountDown = $row['cs_AmountDown'];
				if($amountDown <= 0) $amountDown = 0;
			}
		?>
		<style>
			html *{ font-family: font-family:Arial, Helvetica, sans-serif; }
			body { font-family:Arial, Helvetica, sans-serif; background-color:#999999; }
			#modelHolder{ border:solid 1px #000000; width:600px; }
			.emailBox { width:600px; padding-top:5px; margin-left:auto; margin-right:auto; background-color:#FFFFFF; }
			.floater { float:left; text-align:center; }
			.emailAprHeader { margin-top:9px; margin-bottom:9px; font-weight:bold; font-size:24px; color:#00437a;  }
			.aprBig { font-size:52px; font-weight:bold; color:#00437a; line-height: 62px; }
			.aprBigLease { font-size:32px; font-weight:bold; color:#00437a; line-height: 46px; }
			
			.aprPerc { vertical-align:top; color:#00437a; font-size:22px; font-weight:bold; line-height: 42px; }
			.aprDollar { vertical-align:top; color:#00437a; font-size:22px; font-weight:bold; line-height: 42px;  }
			.aprBox { float:left; text-align:center; padding:-18px 5px 5px 15px; margin-left:20px; }
			
			.aprSmall { font-size: 13px; font-weight:bold; color:#00437a; float:right; margin-left:-12px; margin-top:38px; }
			.perMoSmall { font-size: 10px; font-weight:bold; color:#00437a; margin-top:-4px; color:#272727; }
			.perMoMed { font-size: 10px; color:#00437a; margin-top:-4px; color:#272727; }
			.leaseBox { float:left; text-align:center; padding:-18px 5px 5px 15px; margin-left:20px; }
			.lowerLease { margin-top:11px; }
			.lowerMonth { font-weight:bold; font-size:16px; }
			.lowerText { margin-top:10px; }
			.stripe { margin-top:7px; margin-left: 40px; }
			.findBox { font-size:18pt; background-color:#00437a; color:#FFFFFF; text-align:center; padding:5px; width:70%; margin-left:15%; }
		</style>
		<?
		//print_r($finance);
		//$myRateKeys = array_keys($finance);
		//$myRate = $myRateKeys[0];
		if(is_array($finance)){
			$minKey = min(array_keys($finance));
			if($minKey <= 0){ $minKey=0; }
			echo $finance[$minKey];
			$myRate = $minKey;
		}else{
			$myRate = 0;
		}
		?>
		<div id='#modelHolder' class='emailBox'>
			<div class='floater'>
				<div style='padding:8px 0px 0px 20px'>NEW <?=$_GET['year'] ?> <b style='color:#00437a'><?=$_GET['model'] ?></b></div>
				<img src='<?=$image ?>' style='width:180px' />
			</div>
			<div class='floater'>
				<div class='aprBox'>
					<h3 class='emailAprHeader'>APR</h3>
					<span class='aprBig'><?=number_format($myRate,1) ?></span><span class="aprPerc">%</span> <div class="aprSmall">APR</div><div class="lowerText">For <?=$finance[$myRate] ?> months<div class='perMoMed' style="margin-top:0px" ">ON APPROVED CREDIT</div></div>
				</div>
			</div>
			<div class='floater'><img src="vertStripe.jpg" class="stripe" /></div>
			<div class='floater'>
				<div class='leaseBox'>
					<h3 class='emailAprHeader'>LEASE</h3>
					<span class="aprDollar">$</span><span class='aprBigLease'><?=$leaseRate ?></span> <div class="perMoSmall">PER MO.</div>
					<div style="clear:both"></div>
					<div class="lowerLease">
						<div class="perMoMed" style="display:inline-block"><span class="lowerMonth"><?=$leaseMonths ?></span><br />MONTHS</div><div style="display:inline-block; margin:0px 7px;"><img src="vertStripeSmall.jpg" /></div><div class="perMoMed" style="display:inline-block"><span class='lowerMonth'>$<?=$amountDown ?></span><br />DUE AT SIGNING</div>
					</div>
				</div>
			</div>
			<div style='clear:both;'></div>
			<div class="findBox">Find Your <?=$_GET['model'] ?> Here</div>
			<div style="height:5px"></div>
		</div>
	</div>
</body>
</html>
