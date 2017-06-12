<?php
/*
Template Name: Fact Incentive AIS Data
* written by keith bonarrigo keith@keithbonline.com
* $Id: factory-incentives.php 90 2011-09-29 21:01:09Z jgates@dickhannah.com $
*/
get_header(); 
global $wpdb;
global $dealership; 
$currentDate = date("Y-m-d");

$sql = getUiQuery($dealership);
$sqlSplit = explode("ORDER",$sql);
//$sqlSplitNew = $sqlSplit[0]." AND cs_IncentiveInfoNew.setToEmail=1 ORDER ".$sqlSplit[1];
$sqlSplitNew = $sqlSplit[0]." AND cs_IncentiveInfoNew.setToWeb=1 ORDER ".$sqlSplit[1];
$sql = $sqlSplitNew;
//echo $sql;

$incentivesRaw = get_results($sql); //fetch the results from the single incentive table 'IncentiveInfoNew'
$incentives = array(); //this is the container for the inline variable set that we will ultimately populate

foreach($incentivesRaw as $k=>$v){ //this is the first round where the incentives get set up into an array with the models and the years that we'll set up
	$incentives = checkIncentives($v, $incentives, $v['DivisionID']); //this loops through the result set and arranges the multiple row format to the single row format that was originally set up for the 'incentives' table
} //end for

##########################################################
$wp_upload = wp_upload_dir();
$urlArray = explode("/", $wp_upload['url']);
$url = $urlArray[2]; //get the url of the site we're navigating
$make = dh_get_make_from_dealership();
$splashUrl = "http://".$url."/wp-content/themes/hulk-ua/images/incentives/".$make."-incent-landing.jpg";
$makeUpper = strtoupper($make);
##########################################################
	

foreach($incentivesRaw as $k=>$v) { //now we loop through the individual incentives and set them up in their respective model arrays
	$modelKey = checkModelKey($v, $incentives); //this gets the key in the array that applies to the model that the incentive is bound to
	$incentives = checkRaw($modelKey, $v, $incentives); //this is what actually sets the incentives into variables for each incentive. It loops through and checks if the appropriate variable allocations have been made for this data beneath the model in the incentive array - if not, it should create them
} //end for
?>
<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>

<?php if ($dealership['incentive_src'] AND $dealership['incentive_src'] != "") { ?>
<script type="text/javascript">
$(document).ready(function() {
	if (screen.width > 760) {
		var htmlStr = '<iframe class="factory_incentives" src="<?php echo $dealership['incentive_src'] ?>" frameborder="1" scrolling="no"></iframe>';
		$(".mobile_hide").html(htmlStr);
	}
});
</script>
<?php } ?>

<div id="Incentives_Wrapper">
<div id="Incentives_Wrap_Wrapper">	
	<div id="IncentMenu">
		
		<!-- Desktop IncentMenu -->
		<div id="Incent_DeskMenu">
			<?php
			foreach ($incentives as $ki=>$incentive) { // Iterates through every offer returned for the dealership that is active and non-expired	
					
					$incentives[$ki] = checkNameOverride($incentive); //check the cs_ModelNameException table to see if someone has written in a custom name here - if so, use it.
					$incentive = $incentives[$ki];
					
					$years = array();
					for ($x=1; $x<=3; $x++) {
						if (!empty($incentive['model_year_'.$x])) {
							${"sortvalue" . $x} = $incentive['model_year_'.$x];
							array_push($years, ${"sortvalue" . $x});
						}
					}
					?>
					<div class="IndySidebarSelection NearlyTooLate_<?php echo $incentive['IncentiveID']; ?>" onclick="LEG_Radio('InvisIncent_<?php echo $incentive['IncentiveId']; ?>'); LEG_HideMobileMENUSitems();"><?php echo $incentive['model']; ?></div>
					<?php
			}
			?>
		</div>
		
		<!-- Mobile IncentMenu -->
		<div id="Incent_MobileMenu">
			
			<?php
			foreach ($incentives as $incentive) { // Iterates through every offer returned for the dealership that is active and non-expired
				rsort($years);
				?>
				
			<div class="IndySidebarSelection NearlyTooLate_<?php echo $incentive['IncentiveId']; ?>" onclick="LEG_Radio('InvisIncent_<?php echo $incentive['IncentiveId']; ?>'); LEG_HideMobileMENUSitems();"><?php echo $incentive['model']; ?></div>
			
			<div class="Incent_MobileMenu_Item">
				<div class="Incent_MobileMenu_Title"><?php echo $incentive['model']; ?></div>
				
				<div class="Incent_MobileMenu_LabelDivWrapper">
						<?php
						foreach ($years as $year) {
							$modelyearr = array_search($year, $incentive['rawData']['years']);
							$incentive['Year'] = $incentive['rawData']['years'][$modelyearr];
							if($modelyearr > 0) {
							?>
								<div class="Incent_MobileMenu_LabelDiv Btn_3d_LightGreen">
									<div class="IndySidebarSelection AButtonDiv NearlyTooLate_<?php echo $incentive['IncentiveId']; ?> PracticallyTuLate_<?php echo $incentive['IncentiveId']; ?> AlmostTooLate_<?php echo $incentive['IncentiveId']; ?>_<?php echo $incentive['Year']; ?>" onclick="LEG_Radio('InvisIncent_<?php echo $incentive['IncentiveId']; ?>');LEG_Radio('InvisIncent_<?php echo $incentive['IncentiveId']; ?>_<?php echo $incentive['Year']; ?>'); LEG_ClickOnMobileMENUSitems();"><?php echo $incentive['Year']; ?></div>
								</div>
							<?php
							} //end modelyearr if statement
						} //end for
						?>
				</div>
				<div class="clearfix"></div>
				
			</div>
			<?php
			}
			?>
			
		</div>
		
		<div class="clearfix"></div>
	</div>
	
	
	<div id="displaysomeshadow"></div>
	<div id="hidesomeshadow"></div>
	
	<div id="IncentContent">
		
		<div id="Incentiv3_DefaultMessage" class="JTG_HiddenSectionDiv_Radio_Gen JTG_Show_HiddenContainer">
			<?php
			echo "<img src='".$splashUrl."' alt='".ucwords($make)." factory offers' />";
			?>	
		</div>
		
		<?php
		foreach ($incentives as $incentive) { // Iterates through every offer returned for the dealership that is active and non-expired
		
				$years = array();
				for ($x=1; $x<=3; $x++) {
					if (!empty($incentive['model_year_'.$x])) {
					${"sortvalue" . $x} = $incentive['model_year_'.$x];
					array_push($years, ${"sortvalue" . $x});
					}
				}		
		
		rsort($years);
		?>
		
		<div id="InvisIncent_<?php echo $incentive['IncentiveId']; ?>" class="JTG_HiddenSectionDiv_Radio_Gen JTG_Hide_HiddenContainer">
			<div class="Incentives_TabsWrapper PracticallyTuLate_<?php echo $incentive['IncentiveID']; ?>">
				<?php
				foreach ($years as $year) {
					
					$modelyearr = array_search($year, $incentive);
					$modelyearkey = str_replace('model_year_', '', $modelyearr);
					
					if (!empty($incentive['model_year_'.$modelyearkey]) && $incentive['is_active_'.$modelyearkey] == 1 && (empty($incentive['expiration_dt_'.$modelyearkey]) || $incentive['expiration_dt_'.$modelyearkey] == 0000-00-00 || $incentive['expiration_dt_'.$modelyearkey] >= $currentDate) && (empty($incentive['start_dt_'.$modelyearkey]) || $incentive['start_dt_'.$modelyearkey] == 0000-00-00 || $incentive['start_dt_'.$modelyearkey] <= $currentDate)) {
					?>
					<div class="Incent_Tabs AlmostTooLate_<?php echo $incentive['IncentiveId']; ?>_<?php echo $incentive['model_year_'.$modelyearkey]; ?>" onclick="LEG_Radio('InvisIncent_<?php echo $incentive['IncentiveId']; ?>_<?php echo $incentive['model_year_'.$modelyearkey]; ?>');"><?php echo $incentive['model_year_'.$modelyearkey]; ?></div>
					<?php
					}
				}
				?>
				<div class="clearfix"></div>
			</div>
			
			
			<div class="HideAll_DivWrapper">
				<div class="Incentive_backbutton">
					<div class="HideAll" onclick="LEG_Radio('Incentiv3_DefaultMessage'); LEG_ShowMobileIncentMenu();">
						<div class="Incentive_rightchevronwrapper">
							<div class="Incentive_rightchevron">
								<span class="Incentive_chevron_purecss"></span>
							</div><div class="clearfix"></div>
						</div>
						<span>Back</span>
					</div>
				</div>
				
				<div class="Incentive_phonebutton">
					<a href="tel:360-256-5000"><img alt="Call us" src="http://www.dickhannah.com/wp-content/themes/dh_responsive/images/icon_phone.png"></a>
				<div class="clearfix"></div>
				</div>
				
				<div class="clearfix"></div>
			</div>
			
			
			<div class="IncentiveMainContent">
				
				<?php
				
				foreach ($years as $year) {
					
					$modelyearr = array_search($year, $incentive);
					$modelyearkey = str_replace('model_year_', '', $modelyearr);
					
					if (!empty($incentive['model_year_'.$modelyearkey]) && $incentive['is_active_'.$modelyearkey] == 1 && (empty($incentive['expiration_dt_'.$modelyearkey]) || $incentive['expiration_dt_'.$modelyearkey] == 0000-00-00 || $incentive['expiration_dt_'.$modelyearkey] >= $currentDate) && (empty($incentive['start_dt_'.$modelyearkey]) || $incentive['start_dt_'.$modelyearkey] == 0000-00-00 || $incentive['start_dt_'.$modelyearkey] <= $currentDate)) {
				?>
				<div class="Incent_inD_Year_Wrap">
				<div id="InvisIncent_<?php echo $incentive['IncentiveId']; ?>_<?php echo $incentive['model_year_'.$modelyearkey]; ?>" class="JTG_HiddenSectionDiv_Radio_<?php echo $incentive['IncentiveId']; ?> JTG_HideShow_SpecialClass JTG_Hide_HiddenContainer">
					
					<div class="IncentTITLEIMGwrap">
						<div class="IncentiveImageDiv">
						<?
							$makes = array('chrysler', 'dodge', 'jeep', 'ram');
							$imageFile = $incentive['image_url_'.$modelyearkey];
							$lowerMake = strtolower($incentive['rawData']['make']);

							if( in_array($lowerMake, $makes) ){
								$domain = 'http://www.dickhannah'.$lowerMake.'.com';
								$imagePathSplit = explode('.com', $imageFile);
								$imageFile = $domain.$imagePathSplit[1];
							}

						?>
						<img alt="<?php echo $incentive['model_year_'.$modelyearkey]; ?> <?php echo ucfirst($lowerMake);?> <?php echo $incentive['model']; ?>" src="<?php echo $imageFile ?>" />
						</div>
					</div>
						
					<div class="IncentiveSubTitleDiv">
					<?php echo $incentive['model_year_'.$modelyearkey]; ?> <?php echo ucfirst(strtolower($incentive['rawData']['make']));//echo dh_get_StoreIdentifier();?> <?php echo $incentive['model']; ?>
					</div>
					
					
					<?php
					for ($TwoX=1; $TwoX<=10; $TwoX++) {
						if (!empty($incentive['item_'.$modelyearkey.'_'.$TwoX])) {
					?>
					<div class="IncentWrapper">
						
						<div class="Incentive_OfferDetails">
							<ul class="Incent_ContentList">
								<li><?php echo $incentive['item_'.$modelyearkey.'_'.$TwoX]; ?></li>
							</ul>
						</div>
													

						<div class="Incentive_CTA_Desktop">
							<div class="Btn_3d_LightGreen Incentive_CTA_Div">
								<a href="<?php echo $incentive['global_cta_url']; ?>">Inventory</a>
							</div>
							
							<?php
							if (!empty($incentive['item_disclaimer_'.$modelyearkey.'_'.$TwoX])) {
							?>
								<div class="Btn_3d_Blue Incentive_CTA_Div">
									<div class="Incent_Label AButtonDiv" onclick="LEG_CheckBox('InvisIncent_<?php echo $incentive['IncentiveId']; ?>_item_disclaimer_<?php echo $modelyearkey.'_'.$TwoX; ?>');">Details</div>
								</div>
							<?php
							}
							?>
						</div>
						
						<div class="clearfix"></div>
						
						<?php
						if (!empty($incentive['item_disclaimer_'.$modelyearkey.'_'.$TwoX])) {
						?>
							<div id="InvisIncent_<?php echo $incentive['IncentiveId']; ?>_item_disclaimer_<?php echo $modelyearkey.'_'.$TwoX; ?>" class="JTG_HiddenSectionDiv_CheckBox_<?php echo $incentive['IncentiveId']; ?> JTG_Hide_HiddenContainer HSIncentive_MoreDetails">
							<?php echo $incentive['item_disclaimer_'.$modelyearkey.'_'.$TwoX]; ?>
							</div>
						<?php
						}
						?>
					</div>
					
					<?php
						}
					}
					?>
					
					<?php
					if (!empty($incentive['fine_print_'.$modelyearkey])) {
					?>
					<div class="IncentWrapper" style="font-size: 12px;">
						<?php echo $incentive['fine_print_'.$modelyearkey]; ?>
					</div>
					<?php
					}
					?>
					<div class="clearfix"></div>
				</div>
				</div>
				<?php
					}
				}
				?>
				
				
				
			</div>
				
		</div>
		
		
		
		
		<?php
		}
		?>
		
		
	</div>
		
	<div class="clearfix"></div>
	
</div>
</div>






<?php endwhile; ?>
<?
//foreach($incentives as $k=>$incentive) { echo $k."<br />"; print_r($incentive); echo "<br /><br />"; }
?>
<?php get_footer(); ?>