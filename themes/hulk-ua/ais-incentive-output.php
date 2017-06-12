<?php
/* Template Name: Ais-Incentive-Output */
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" encoding="UTF-8" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<?php wp_head(); ?>
</head>
<body>
	<?
					global $dealership;
					global $volkswagenFlag;
					global $thisDivisionId;
					$wp_upload = wp_upload_dir();
					$urlArray = explode("/", $wp_upload['url']);
					$url = $urlArray[2];					
					$wp_upload = wp_upload_dir();
					$urlArray = explode("/", $wp_upload['url']);
					$url = $urlArray[2];
					$make = dh_get_make_from_dealership();
					###
					$volkswagenFlag = 0; //set a flag for the volkswagen exception
					//////////////////////
					//exception for volkswagen
					/////////////////////
					if(strstr($url, 'vwofportland.com')){
						$thisDivisionId = 13;
						$thisDivisionZip = 97233;
						$volkswagenFlag = 1;
					}elseif(strstr($url, 'dickhannahvolkswagen.com')){
						$thisDivisionId = 12;
						$thisDivisionZip = 98662;
						$volkswagenFlag = 1;
					}
					//////////////////////
					//end exception for volkswagen
					/////////////////////
					###
				$models = array();
				$make = dh_get_make_from_dealership();
				$make = strtoupper($make);
				
				//set up the coupon links
				$couponLinks = array(
				'www.dickhannahacuraofportland.com' => 'http://www.dickhannahacuraofportland.com/acura-auto-repair-acura-service-portland-vancouver/acura-service-repair-coupons/',
				'www.dickhannahchrysler.com' => 'http://www.dickhannahchrysler.com/chrysler-auto-repair-chrysler-service-vancouver-portland/chrysler-service-repair-coupons/',
				'www.dickhannahdodge.com' => 'http://www.dickhannahdodge.com/dodge-auto-repair-dodge-service-vancouver-portland/dodge-service-repair-coupons/',
				'www.dickhannahhonda.com' => 'http://www.dickhannahhonda.com/honda-auto-repair-honda-service-vancouver-portland/honda-service-repair-coupons/',
				'www.hyundaiofportland.com' => 'http://www.hyundaiofportland.com/hyundai-auto-repair-hyundai-service-portland-vancouver/hyundai-service-repair-coupons/',
				'www.dickhannahjeep.com' => 'http://www.dickhannahjeep.com/jeep-auto-repair-jeep-service-vancouver-portland/jeep-service-repair-coupons/',
				'www.dickhannahkia.com' => 'http://www.dickhannahkia.com/kia-auto-repair-kia-service-vancouver-portland/kia-service-repair-coupons/',
				'www.dickhannahnissan.com' => 'http://www.dickhannahnissan.com/nissan-repair-nissan-service-portland-gladstone/nissan-service-repair-coupons/',
				'www.dickhannahram.com' => 'http://www.dickhannahram.com/ram-truck-repair-ram-service-vancouver-portland/ram-service-repair-coupons/',
				'www.dickhannahsubaru.com' => 'http://www.dickhannahsubaru.com/subaru-auto-repair-subaru-service-vancouver-portland/subaru-service-repair-coupons/',
				'www.dickhannahtoyota.com' => 'http://www.dickhannahtoyota.com/toyota-auto-repair-toyota-service-kelso-longview/toyota-service-repair-coupons/',
				'www.vwofportland.com' => 'http://www.vwofportland.com/volkswagen-auto-repair-volkswagen-service-portland-vancouver/volkswagen-service-repair-coupons/',
				'www.dickhannahvolkswagen.com' => 'http://www.dickhannahvolkswagen.com/volkswagen-auto-repair-volkswagen-service-vancouver-portland/vw-service-repair-coupons/'
				);
				
				$couponLink = $couponLinks[$url];
				$finder =  "ais_Div_Id = ".$dealership['dealership_id']; //this is the base division filter
				
				$CJDR = array(3,4,7,21); //these are the ids for Chrysler, Jeep, Ram, and Dodge

					if(in_array($dealership['dealership_id'], $CJDR)){ //if this dealership is one of the CJRD makes
						for($i=0;$i<count($CJDR);$i++){ //add the other 3 dealership makes to show along with this one
							if( $CJDR[$i]!= $dealership['dealership_id'] ) $finder .= " OR ais_Div_Id = ".$CJDR[$i];
						} //end for
					} //end if
				
				$sql = "SELECT * FROM cs_AisIncentiveInfoEmail  
				WHERE (".$finder.")";
				
				$sql .= " ORDER BY cs_AisIncentiveInfoEmail.ais_Order, cs_AisIncentiveInfoEmail.ais_Year, cs_AisIncentiveInfoEmail.ais_Model ASC";
				$resultDiv = get_results($sql);
				
				$counter = 0;
				
				$sqlMeta = "SELECT * FROM cs_AisIncentiveHeader WHERE ais_Dealer_Id = ".$dealership['dealership_id']." LIMIT 1";
				$resultMeta = get_results($sqlMeta);
				$headerImage = '';
				
				
				foreach($resultMeta as $mk=>$mv){
						$hero_disclosure = $mv['hero_disclosure'];
						$headerImage = $mv['ais_Header_Image'];
						$preHeaderText = $mv['ais_Pre_Header_Text'];
						$alt_Tag_Hero = $mv['ais_Alt_Tag'];
						$title_Tag_Hero = $mv['ais_Title_Tag'];
						$href_Tag_Hero = $mv['ais_Href_Tag'];
						$text_CTA = $mv['ais_Text_Cta'];
						$href_CTA = $mv['ais_Href_Cta'];
						$campaign_Code = $mv['ais_Campaign_Code'];
						$usedVins = $mv['ais_Used_Vins'];
				}
	?>
	<!-- HIDDEN PREHEADER TEXT -->
	<div style="display: none; font-size: 1px; color: #fefefe; line-height: 1px; font-family: Arial, Helvetica, sans-serif; max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden;">
	    <?=$preHeaderText ?>
	</div>
	
	<table width="100%" style="border-collapse:collapse;font-family: Trebuchet MS, sans-serif;">
		<tr style="text-align:center">
			<td align="center">
				
				<!-- Header text containing dealer name, address, and phone number -->
				<table width="600" style="border-collapse:collapse;font-family: Arial, Helvetica, sans-serif; font-size: 13px; width:600px">
					<tr>
						<td style="text-align:center; color:#00427a; font-weight:bold; padding-bottom:5px;">
							<?=$dealership['name'] ?> | <a href="http://<?=$url ?>/contact/hours-and-directions/?utm_source=crm-<?=strtolower($make) ?>-store&utm_medium=email&utm_content=btn_header_address&utm_campaign=<?=$campaign_Code ?>" target="_blank" style="text-decoration:none;color:#00427a;"><?=$dealership["address"] ?>, <?=$dealership["city"] ?>, <?=$dealership["state"] ?></a> | <?=$dealership["oaisys"] ?>
						</td>
					</tr>
				</table>
				
				<!-- Header Logo Banner, links to homepage -->
				<table width="600" style="margin:0 auto; border-collapse:collapse; width:600px;">
					<tr>
						<td width="400" style="border-collapse:collapse;padding: 10px 0; width:400px;">
							<a href="http://<?=$url ?>/?utm_source=crm-<?=strtolower($make) ?>-store&utm_medium=email&utm_content=img_header_logo&utm_campaign=<?=$campaign_Code ?>" target="_blank"><img src="<?php echo image_url("email-images/".dh_get_identifier()."-email-header.jpg"); ?>" border="0" /></a> 
						</td>
					</tr>
				</table>
				
				<!-- Header Menu -->
				<table width="580" style="border-collapse:collapse;font-family: Arial, Helvetica, sans-serif; border-bottom:1px solid #000; font-size: 14px; width:580px;">
					<tr>
						<td style="text-align:center; color:#00427a; font-weight:bold; padding-bottom:5px;">
							<a href="http://<?=$url ?>/new/?utm_source=crm-<?=strtolower($make) ?>-store&utm_medium=email&utm_content=btn_header_new&utm_campaign=<?=$campaign_Code ?>" target="_blank" style="text-decoration: none; color: #00427a; font-size: 14px; a:visited:#00427a; font-weight:bold;">SEARCH NEW</a>
						</td>
						<td style="text-align:center; color:#00427a; font-weight:bold; padding-bottom:5px;">
							<a href="http://<?=$url ?>/used/?utm_source=crm-<?=strtolower($make) ?>-store&utm_medium=email&utm_content=btn_header_used&utm_campaign=<?=$campaign_Code ?>" target="_blank" style="text-decoration: none; color: #00427a; font-size: 14px; a:visited:#00427a; font-weight:bold;">SEARCH USED</a>
						</td>
						<td style="text-align:center; color:#00427a; font-weight:bold; padding-bottom:5px;">
							<a href="<?php echo esc_url( get_permalink( get_page_by_title( 'Service Center' ) ) ); ?>?utm_campaign=<?=$campaign_Code ?>" target="_blank" style="text-decoration: none; color: #00427a; font-size: 14px; a:visited:#00427a; font-weight:bold;">SERVICE</a>
						</td>
						<td style="text-align:center; color:#00427a; font-weight:bold; padding-bottom:5px;">
							<a href="http://<?=$url ?>/specials/<?=strtolower($make) ?>-factory-offers/?utm_source=crm-<?=strtolower($make) ?>-store&utm_medium=email&utm_content=btn_header_offers&utm_campaign=<?=$campaign_Code ?>" target="_blank" style="text-decoration: none; color: #00427a; font-size: 14px; a:visited:#00427a; font-weight:bold;">FACTORY OFFERS</a>
						</td>
						<td style="text-align:center; color:#00427a; font-weight:bold; padding-bottom:5px;">
							
							<a href="<?php echo esc_url( get_permalink( get_page_by_title( 'Vehicle Specials' ) ) ); ?>?utm_campaign=<?=$campaign_Code ?>" target="_blank" style="text-decoration: none; color: #00427a; font-size: 14px; a:visited:#00427a; font-weight:bold;">SPECIALS</a>
						</td>
					</tr>
				</table>
				
				<!-- Hero Image-->
				<table width="580" style="margin:0 auto;border-collapse:collapse;font-family:Arial, Helvetica, sans-serif; width:580px">
					<tr>
						<td style="padding-top:10px;">
							<a href="<? if(strlen($href_Tag_Hero)>1){ echo "http://"; } ?><?=$href_Tag_Hero ?><? if(strlen($href_Tag_Hero)>1){ echo "?utm_source=crm-".strtolower($make)."-store&utm_medium=email&utm_content=img_body_hero&utm_campaign=".$campaign_Code; }?>" target="_blank"><img src="<?=$headerImage ?>" width="580" height="203" alt="<?=$alt_Tag_Hero ?>" title="<?=$title_Tag_Hero ?>" width="580" height="203" border="0" /></a>
						</td>
					</tr>
				</table>
				
				<!-- Main CTA -->
				<table width="580" style="margin:0 auto;border-collapse:collapse;font-family:Arial, Helvetica, sans-serif; width:580px;">
					<tr>
						<td style="padding-top:10px;">
							<table width="580" cellspacing="0" cellpadding="0" border="0" style="background-color:#cc181e;" class="mobileButton">
								<tr>
									<td style="padding: 10px 20px 10px 20px; font-family: Arial, sans-serif; color: #ffffff; font-size: 18px; text-align: center; font-weight:bold;">
										<a href="<? if(strlen($href_CTA)>1){ echo "http://"; } ?><?=$href_CTA ?><? if(strlen($href_CTA)>1){ echo "?utm_source=crm-".strtolower($make)."-store&utm_medium=email&utm_content=btn_body_main&utm_campaign=".$campaign_Code; }?>" style="text-decoration: none; color: #ffffff; font-size: 18px; a:visited:#ffffff;font-weight:bold;" target="_blank"><?=$text_CTA ?></span></a>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
				
				<!--Start individual vehicle section-->
				<?
				//$dispumber = 0;
				$disclaimerNumber = 0; //this is the number we use to track which incentive is being displayed and which disclaimer it should tie to 
				$disclaimers = array(); //holds the disclaimer info for later display
				for($i=0;$i<count($resultDiv); $i++){
					$dispNumber++;
					$row = $resultDiv[$i];
				?>
					<table width="580" style="margin:0 auto;border-collapse:collapse;font-family:Arial, Helvetica, sans-serif;text-align:left; width:580px;">
						<tr>
							<td>
								<?
									$thisOutput = getThisOutput($row, $disclaimerNumber, $disclaimers ); //assemble the output html, the disclaimers, and the disclaimer number
									$disclaimerNumber = $thisOutput[3]; //get the disclaimer to continue to icrement it
									$disclaimers = $thisOutput[4]; //this is the disclaimer array
									echo $thisOutput[1]; //show the html returned from getThisOutput function
									$modelLinked = strtolower($row["ais_Model"]);
									$modelLinked = str_replace(" ", "_", $modelLinked);	//for multiple named models
								?>
								<table width="580" cellspacing="0" cellpadding="0" border="0" style="background-color:#707070;" class="mobileButton; width:580px;">
									<tr>
										<td style="padding: 10px 20px 10px 20px; font-family: Arial, sans-serif; color: #ffffff; font-size: 18px; text-align: center; font-weight:bold;">
											<a href="http://<?=$url ?>/new/<?=strtolower($make) ?>/<?=$modelLinked ?>/?utm_source=crm-<?=strtolower($make) ?>-store&utm_medium=email&utm_content=btn_body_<?=$modelLinked ?>&utm_campaign=<?=$campaign_Code ?>" style="text-decoration: none; color: #ffffff; font-size: 18px; a:visited:#ffffff;font-weight:bold;" target="_blank">FIND YOUR <?=strtoupper($row["ais_Model"]) ?> HERE</span></a>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				<? } ?>
				<!--end individual vehicle section-->
				
				<table width="580" style="margin:0 auto;border-collapse:collapse;font-family:Arial, Helvetica, sans-serif;text-align:center; width:580px;">
					<tr>
						<td style="padding:20px 0;">
							<a href="<?=$dealership['cao_link'] ?>" target="_blank"><img src="http://www.dickhannah.com/wp-content/uploads/MonthlyEmailTemplate_Chat.jpg" alt="Questions? Chat Now" title="Questions? Chat Now" width="279" height="83" border="0" /></a>
						</td>
						<td class="spacer" width="5" style="font-size: 1px;">&nbsp;</td>
						<td style="padding:20px 0;">
							<a href="sms:+1<?=str_replace("-", "", $dealership['sms_number']) ?>"><img src="http://www.dickhannah.com/wp-content/uploads/MonthlyEmailTemplate_Text.jpg" alt="Questions? Text Us" title="Dick Hannah Dealerships" width="279" height="83" border="0" /></a>
						</td>
					</tr>
				</table>
				<!--CPO Section-->
				<?
				
							$thismake = ucfirst(strtolower($make));
							$makeAdd = "AND ( make = '".$thismake."' )";
							if($thismake == 'Dodge' || $make == 'Jeep' || $make == 'Ram' || $make == 'Chrysler'){
								$makeAdd = "AND ( make = 'Jeep' OR make = 'Dodge' OR make = 'Ram' OR make = 'Chrysler' )";
							}
							
								//get the user entered vins first
								$usedVins = unserialize($usedVins);
								$customUsedCount = 0;
								$finalCpo = array(); //this is the array that will contain the combined data from the user entered stock numbers and the auto pulled data from inventory below
								
								if( is_array($usedVins) && count($usedVins)>0 ){
									foreach($usedVins as $uk=>$uv){
										$preCpoSql = "SELECT * FROM inventory WHERE stock_number = '".$uv."'";
										$preCpoSql .= " AND new_used='U' ";
										$preCpoSql .= " AND CHAR_LENGTH(photo_url_list) > 5 ";
										$preCpoSql .= " LIMIT 1";
										$customUsedCount++;
										$preCpoResults = get_results($preCpoSql);
											foreach($preCpoResults as $row){ 
												array_push($finalCpo, $row); //add it to the final array to show
											}
									}
								}
								
							$amountToPull = 3 - $customUsedCount;
							
							$cpoSql = "SELECT * FROM inventory WHERE dealer_id = '".$dealership['dealer_id']."'";
							$cpoSql .= " AND certified='Yes' AND CHAR_LENGTH(photo_url_list) > 5 ";
							$cpoSql .= $makeAdd;
							$cpoSql .= " LIMIT ".$amountToPull;
							$cpoResults = get_results($cpoSql);
								foreach($cpoResults as $row){ 
									array_push($finalCpo, $row); //add it to the final array to show
								}
				
				if(count($finalCpo)>=3){ //end larger php if statement to check is there are a minimum of 3 CPO vehicles available
				?>
				<!-- CPO Section -->
				<table width="600" style="margin:0 auto;border-collapse:collapse;font-family:Arial, Helvetica, sans-serif;text-align:center;background-color:#eeeeee;text-align:center; width:600px;">
					<tr>
						<td>
						<!-- CPO Title Image -->
							<img src="http://<?=str_replace("deb.", "", $url) ?>/wp-content/themes/hulk-ua/images/email-images/<?=strtolower($make) ?>-cpo-header.png" alt="<?=ucfirst(strtolower($make)) ?> Certified Pre-Owned - The next best thing" title="<?=$make ?> Certified Pre-Owned - The next best thing" width="580" height="60" style="padding:10px 0;" border="0" />
						</td>
					</tr>
					<tr>
						<td>
							<table width="580" style="margin:0 auto;border-collapse:collapse;font-family: Arial, Helvetica, sans-serif;text-align:center;background-color:#eeeeee; width:580px;">
								<tr>
									<?
									foreach($finalCpo as $ck=>$cv){
										?>
										<!-- CPO Vehicle <?=$ck ?> -->
										<td class="section-title" align="left" valign="top" style="font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #000;padding-top:10px;">
										<? 
											
											$photoToShow = '';
											if( strlen($cv['photo_url_list']) >0 ){
												$photos = explode("|", $cv['photo_url_list']);
												$photoToShow = $photos[0];
											}
											$vin = $cv['vin'];
											$vinLen = strlen($vin);
											$lastSixVin = substr($vin, $vinLen-6, $vinLen);
										?>
										<a href="http://<?=$url ?>/used/?make=<?=strtolower($make) ?>&certified=yes&utm_source=crm-<?=strtolower($make) ?>-store&utm_medium=email&utm_content=img_body_certified&utm_campaign=<?=$campaign_Code ?>" target="_blank"><img src="<?=$photoToShow ?>" width="190" height="128" border="0" /></a><br>
										<div style="font-size:16px;color:#00427a;font-weight:bold; margin-top:5px;">Used <?=$cv['year'] ?> <?=$cv['model'] ?> </div>
										<span style="font-size:16px;color:#00427a;font-weight:bold;"><?=$cv['body_type'] ?> <?=number_format($cv['odometer']) ?> miles</span><br>
										<span style="font-size:24px;font-weight:bold;color:#ab192d;"><sup style="font-size:13px;">$</sup><?=number_format($cv['price']) ?></span> | KBB <span style="text-decoration: line-through;">$<?=number_format($cv['compare_to_price']) ?></span>
										<br /><span style='color:grey;font-weight:normal'>VIN ends in: <?=$lastSixVin ?></span>
										</td>
										<?
									}
									?>
								</tr>
							</table>
							<!-- CPO CTA -->
							<table width="580" cellspacing="0" cellpadding="0" border="0" align="center" style="background-color:#ab192d; width:580px;" class="mobileButton">
								<tr>
									<td style="padding: 10px 20px 10px 20px; font-family: Arial, sans-serif; color: #ffffff; font-size: 18px; text-align: center; font-weight:bold;">
										<a href="http://<?=$url ?>/used/?make=<?=strtolower($make) ?>&certified=yes&utm_source=crm-<?=strtolower($make) ?>-store&utm_medium=email&utm_content=btn_body_certified&utm_campaign=<?=$campaign_Code ?>" style="text-decoration: none; color: #ffffff; font-size: 18px; a:visited:#ffffff;font-weight:bold;" target="_blank">FIND <?=strtoupper($make) ?> CERTIFIED PRE-OWNED VEHICLES HERE</span></a>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
				<table width="600" style="margin:0 auto;border-collapse:collapse;font-family:Arial, Helvetica, sans-serif;text-align:center;background-color:#eeeeee;text-align:center; width:600px">
					<tr>
						<td class="section-title" width="580" align="left" style="font-family: arial,sans-serif; font-size: 14px; color: #000;padding:10px 15px;">
							<span style="font-size:13px;">All vehicles are one of each and are subject to prior sale. A dealer documentary service fee of up to $150 may be added to the sale price or capitalized cost. Prices exclude tax, title, and license.</span><br>
						</td>
					</tr>
				</table>
				<!--end CPO section-->
				<?
				} //end larget php if statement to check is there are a minimum of 3 CPO vehicles available
				?>
				<!--start coupons and lower section-->
				<? //coupon code						
				$sqlCoupon = "SELECT * FROM coupon WHERE is_promo = 1";
				$sqlCoupon .= " ORDER BY ordernum ASC LIMIT 3";	
				
				$resultCoupon = get_results($sqlCoupon);
				if(count($resultCoupon > 0)){ //we have a resultset for the coupons - now create the output
					?>
					<table width="600" style="margin:0 auto;border-collapse:collapse;font-family:Arial, Helvetica, sans-serif;text-align:center;text-align:center; width:600px;">
					<tr>
						<!-- Service Coupons Title -->
						<td>
							<img src="http://www.dickhannah.com/wp-content/uploads/MonthlyEmailTemplate_Service_Coupons.jpg" title="Service Coupons" alt="Service Coupons" style="padding:10px 0;" width="600" height="45" border="0" />
						</td>
					</tr>
					<tr>
						<td>
							<table width="580" style="margin:0 auto;border-collapse:collapse;font-family:Arial, Helvetica, sans-serif;text-align:center; width:580px;">
								<tr>
								<?
								$couponCount = 0;
								foreach($resultCoupon as $couponRow){
									$couponCount++;
									$minHeightCount = 0;
									if(strlen($couponRow['title']) > 22){
										$minHeightCount++;
									}
									?>
									<!-- Service Coupon 1 -->
									<td class="section-title" align="left" valign="middle" style="vertical-align:top; font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #000;padding:10px 0;border: 1px dashed #000;">
										<table>
											<tr>
												<!-- Service Coupon Image -->
												<td>
													<img src="<?php echo get_bloginfo("template_directory") ?>/images/coupons/<?php echo $couponRow['coupon_image'] ?>.png" alt="<?php echo $couponRow['coupon_image'] ?>" width="175" style="padding:5px;" border="0" />
												</td>
											</tr>
											<tr>
												<!-- Service Coupon Details -->
												<td style="padding: 0  5px; text-align:center;font-family: Arial, Helvetica, sans-serif;">
													<div class='titleSpan' style="min-height:45px; font-size:16px;color:#00427a;font-weight:bold;"><?=$couponRow['title'] ?></div>
													<? if(strlen($couponRow['sub_title'])>0) { ?><span style="font-size:13px;color:#aaa4b4;font-weight:bold;"><?=$couponRow['sub_title'] ?></span><br><? } ?>
													<span style="font-size:24px;font-weight:bold;color:#00427a;"><sup style="font-size:13px;"><? if(!strstr($couponRow['price'], "FREE")){ echo "$"; } ?></sup><?=str_replace("$", "", $couponRow['price']) ?></span>
												</td>
											</tr>
										<? if(strlen($couponRow['bullet_1'])>0 || strlen($couponRow['bullet_2'])>0 || strlen($couponRow['bullet_3'])>0 || strlen($couponRow['bullet_4'])>0){ ?>
											<tr>
												<!-- Service Coupon Bullet Points -->
												<td style="text-align:left;font-size:13px;font-family: Arial, Helvetica, sans-serif;">
													<ul class='ais_bullet_list' id='coupon_bullet_list_<?=$couponCount ?>' name='coupon_bullet_list_<?=$couponCount ?>' style='min-height:100px;' >
														<? if(strlen($couponRow['bullet_1'])>0){ ?><li><?=$couponRow['bullet_1'] ?></li><? } ?>
														<? if(strlen($couponRow['bullet_2'])>0){ ?><li><?=$couponRow['bullet_2'] ?></li><? } ?>
														<? if(strlen($couponRow['bullet_3'])>0){ ?><li><?=$couponRow['bullet_3'] ?></li><? } ?>
														<? if(strlen($couponRow['bullet_4'])>0){ ?><li><?=$couponRow['bullet_4'] ?></li><? } ?>
													</ul>
												</td>
											</tr>
										<? } 
										if($minHeightCount > 0){ //we have a long title so we need to adjust the title heights of all the coupons
											?>
											<style>
												.titleSpan { min-height:45px; }
												.ais_bullet_list {  }
											</style>
											<?
										}
										?>
											<tr>
												<!-- Service Coupon Disclaimers -->
												<td style="padding: 0  5px;text-align:left;font-size:13px;font-family: Arial, Helvetica, sans-serif;">
													<div class='ais_description' id='coupon_description_<?=$couponCount ?>' name='coupon_description_<?=$couponCount ?>' style='min-height:120px' >
													<?=$couponRow['description'] ?>
													</div>
												</td>
											</tr>
											<tr>
												<!-- Service Coupon Expiration Date -->
												<td style="padding: 5px  5px; text-align:center;font-family: Arial, Helvetica, sans-serif;">
												<?
													$thisSplitDate = explode("-", $couponRow['expiration_dt']);
													if($thisSplitDate[1] > 0 && $thisSplitDate[1]> 0 && $thisSplitDate[2] > 0){
												?>
													<span style="font-size:14px;color:#00427a;font-weight:bold;">EXPIRES <? echo $thisSplitDate[1]."-".$thisSplitDate[2]."-".$thisSplitDate[0]; ?></span><br>
												<?
													}
												?>
												</td>
											</tr>
										</table>
									</td>
									<td class="spacer" width="3" style="font-size: 1px;">&nbsp;</td>
									
									<!--end service coupon -->
									<?
								}
								?>
								</tr>
							</table>
						</td>
					</tr>
				</table>
				
				<!-- Coupon CTA -->
				<table width="580" style="margin:0 auto;border-collapse:collapse;font-family:Arial, Helvetica, sans-serif; width:580px;">
					<tr>
						<td style="padding-top:10px;">
							<table width="580" cellspacing="0" cellpadding="0" border="0" style="background-color:#00427a; width:580px;" class="mobileButton">
								<tr>
									<td style="padding: 10px 20px 10px 20px; font-family: Arial, sans-serif; color: #ffffff; font-size: 18px; text-align: center; font-weight:bold;">
										<a href="<?=$couponLink ?>" style="text-decoration: none; color: #ffffff; font-size: 18px; a:visited:#ffffff;font-weight:bold;" target="_blank">FIND SERVICE COUPONS HERE</span></a>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
				<?
				}
				
				
					
				?>
				<table width="580" style="margin:0 auto;border-collapse:collapse;font-family:Arial, Helvetica, sans-serif; width:580px">
					<tr>
						<td>
							<hr>
						</td>
					</tr>
				</table>
				<!-- Dealership Social Links -->
				<table width="300" align="center" style="margin:0 auto;border-collapse:collapse;font-family:Arial, Helvetica, sans-serif; width:300px">
					<tr>
						<td>
							<a href="<?=$dealership['facebook_url'] ?>"><img src="http://www.dickhannah.com/wp-content/uploads/MonthlyEmailTemplate_Facebook.jpg" border="0" width="55" height="55" title="Facebook" alt="Facebook"></a>
						</td>
						<td>
							<a href="<?=$dealership['twitter_url'] ?>"><img src="http://www.dickhannah.com/wp-content/uploads/MonthlyEmailTemplate_Twitter.jpg" border="0" width="55" height="55" title="Twitter" alt="Twitter"></a>
						</td>
						<td>
							<a href="<?=$dealership['googleplus_url'] ?>"><img src="http://www.dickhannah.com/wp-content/uploads/MonthlyEmailTemplate_Google.jpg" border="0" width="55" height="55" title="Google +" alt="Google +"></a>
						</td>
						<td>
							<a href="<?=$dealership['linkedin_url'] ?>"><img src="http://www.dickhannah.com/wp-content/uploads/MonthlyEmailTemplate_Linkedin.jpg" border="0" width="55" height="55" title="Linkedin" alt="Linkedin"></a>
						</td>
						<td>
							<a href="<?=$dealership['youtube_url'] ?>"><img src="http://www.dickhannah.com/wp-content/uploads/MonthlyEmailTemplate_YouTube.jpg" border="0" width="55" height="55" title="You Tube" alt="You Tube"></a>
						</td>
					</tr>
				</table>
				<table width="580" align="center" style="margin:0 auto;border-collapse:collapse;font-family:Arial, Helvetica, sans-serif; width:580px;">
					<tr>
						<!-- Offer Disclaimers -->
						
						<td style="padding: 20px 0; font-size:13px; color:#707070;">
							<? 
							if(strlen($hero_disclosure) > 0){ 
								echo "* ".$hero_disclosure."<br /><br />";
							}
							echo "* A documentary service fee in an amount up to $150 may be added to the sales price of any offer listed<br /.<br /><br />";
							foreach($disclaimers as $dk=>$dv){
								echo $dk." - ".str_replace("^", "'", $dv)."<br /><br />";
							}
							?>  
						</td>
					</tr>
				</table>
				<!--end coupons and lower section-->
			<!--end the main table-->	
			</td>
		</tr>
	</table>
	
</body>
</html>
<?
/////////////////////////////////////////////////////////////////
//main function to create the html output for the front end
/////////////////////////////////////////////////////////////////
function getThisOutput($row, $disclaimerNumber, $disclaimers){
	global $volkswagenFlag;
	global $thisDivisionId;
	
	if($row['ais_Incentive_1'] > 0){
		$iSql = "SELECT * FROM cs_IncentiveInfoNew WHERE cs_IncentinveInfo = ".$row['ais_Incentive_1']." LIMIT 1";
		$iSqlResults = get_results($iSql);
		if(count($iSqlResults)>0){
			$row['aisIncentive1'] = $iSqlResults[0];
		}else{
			$row['aisIncentive1'] = 0; //we've got nothing 
		}
	}
	if($row['ais_Incentive_2'] > 0){
		if($row['ais_Incentive_2']>1){
			
			$iSql = "SELECT * FROM cs_IncentiveInfoNew WHERE cs_IncentinveInfo = ".$row['ais_Incentive_2']." LIMIT 1";
			$iSqlResults = get_results($iSql);
			if(count($iSqlResults)>0){
				$row['aisIncentive2'] = $iSqlResults[0];
			}else{
				$row['aisIncentive2'] = 0; //we've got nothing
			}
			
		}else{ //this is a custom lease
			//$extras = unserialize($row['ais_Incentive_2_Extra']);
			$extras = unserialize(base64_decode($row['ais_Incentive_2_Extra']));			
			$row['aisIncentive2']['monthly'] = $extras['monthly'];
			$row['aisIncentive2']['length'] = $extras['length'];
			$row['aisIncentive2']['first'] = $extras['first'];
			$row['aisIncentive2']['Disclaimer'] = stripslashes($extras['disclosure']);
		}
	}					
	/////////////////////////////////////////////////////////
	//get the vehicle image
	  $query = 'SELECT * FROM model WHERE model_year = "';
	  $query .= $row['ais_Year'];
	  $query .= '" AND model = "';
	  $query .= $row['ais_Model'].'"';
	  if($volkswagenFlag == 1){ //we're on a volkswagen page - alter the query to add the divId and zip
		$query .= ' AND dealership_id = '.$thisDivisionId;
	  }
	  $query .= ' LIMIT 1 ';
	  $imageRes = get_results($query);
				
	   foreach($imageRes as $rowImage){ //we have the raw data list separated by '|'					
			$thisImagePath = dh_upload_path_hardcoded("baseurl", "Model", $rowImage, 'image_file'); 
	   } 
	//////////////////////////////////////////////////////////
	$disclaimerArray = checkDisclaimers($disclaimerNumber, $disclaimers, $row); //check this model and see how many disclaimers we have
	$disclaimers = $disclaimerArray['disclaimers']; //returns the disclaimer array for later use
	$disclaimerNumber = $disclaimerArray['disclaimerNumber']; //returns the current disclaimer number
	$row = $disclaimerArray['row']; //returns updated row with disclaimer info and number added to dataset
	/////////////////////////////////////////////////////////
	$weHaveIncentives = 0; //we have incentives to show
	if( $row['aisIncentive1'] > 0){
		$weHaveIncentives++;
	}
	if( $row['aisIncentive2'] > 0){
		$weHaveIncentives++;
	}
	$imageMargin = "";
	$msrpDifference = 0;
		
		////////////////////////////////////////////////////////////////
		//determine if we have the data to come up with a msrp difference
		if($row['ais_Msrp'] > 0 && $row['ais_Price'] > 0 ){
			$msrpDifference = $row['ais_Msrp'] - ($row['ais_Price'] + $row['ais_Rebate']);
			$msrpDifference = number_format($msrpDifference);
		} 
		//end msrp section
		////////////////////////////////////////////////////////////////
		
	if($weHaveIncentives == 1){ $imageMargin = " <td width=\"90\">&nbsp</td> "; }
	
	$thisOut = '<table cellpadding="0" cellspacing="0">
				<tr>
					<td class="section-title" width="188" align="center" valign="middle" style="font-family: arial,sans-serif; font-size: 14px; color: #000;padding-top:10px;">
						<span style="font-size:18px;color:#00427a;font-weight:bold;">';
	$thisOut .=			'NEW '.$row["ais_Year"].' ';			
		
		if(strlen($row["ais_Model_Override"])>0){
			$thisOut .= $row["ais_Model_Override"];
		}else{
			$thisOut .= $row["ais_Model"].' ';
		}
	
	$thisOut .=			'</span><br>';
	$thisOut .=			'<img src="'.str_replace("dev.", "", $thisImagePath).'" width="173" height="130" border="0" >';
	$thisOut .=	'</td>'.$imageMargin;
	
	$thisOut .=	'<td class="spacer" width="5" style="font-size: 1px;">&nbsp;</td>
					<td class="section-row">';			
	$thisOut .=			'<table cellpadding="0" cellspacing="0">
							<tr>';
	
	if($weHaveIncentives == 0){ //we don't have any incentives registered for this model so we want to display the price info in the 
		$thisOut .=				'<td width="90">&nbsp</td>
								<td width="188" height="144" align="center" valign="top" style="font-family: arial,sans-serif; font-size: 14px; color: #000; width:188px;">';
									
		if($row['ais_Price']>0){ //test to see if we have a price registered for this vehicle - if so, then show it
			$thisOut .=				'<span style="font-size:18px;color:#00427a;font-weight:bold;">Starting at</span><br>
									<span style="font-size:48px;font-weight:bold;color:#ab192d;"><sup style="font-size:28px;">$</sup>'.number_format($row['ais_Price']).'</span><br />';
		}
		if($msrpDifference > 0)	{	
			$thisOut .= '<span style="font-size:18px;line-height:24px;">&nbsp;&nbsp;$'.$msrpDifference.' off MSRP';
			if($row['ais_Rebate']>0) { $thisOut .= ' + $'.number_format($row['ais_Rebate']).' factory rebates'; }
		}
		$thisOut .= "<div style='margin-left:10px; font-size:14px; text-align:left'>".$disclaimerArray['lower']."</div>";
		$thisOut	.=	'</td>';						
	}else{
		$incentiveTdWidth = 188;
	}
			
			////////////////////////////////////////////////////////////////////////
			if(count($row["aisIncentive1"]) > 0 && $row['ais_Incentive_1_Type'] == "Finance"){
				$thisBack = formatIncentive("Finance", $incentiveTdWidth, $row);
				if(!is_null($thisBack)){
					$thisOut .= $thisBack;
				}
			}
			////////////////////////////////////////////////////////////////////////
			if( count($row["aisIncentive2"]) > 0 && strstr($row['ais_Incentive_2_Type'], "Lease")  ){ //we have data for the first Incentive block - populate it
				$thisBack = formatIncentive($row['ais_Incentive_2_Type'], $incentiveTdWidth, $row);
				if(!is_null($thisBack)){
					$thisOut .= $thisBack;
				}				
			}
			////////////////////////////////////////////////////////////////////////
							
	$thisOut .=					'<td class="spacer" width="5" style="font-size: 1px;">&nbsp;</td>
							</tr>';						
	$thisOut .=				'</table>';			
	$thisOut .=		'</td>
				</tr>';
	
	if($weHaveIncentives > 0){ //we had incentive info up top so we need to show the price data now	
			$thisOut .=			'
								<tr>
								<td colspan="4" height="34" align="left" valign="top" style="font-family: arial,sans-serif; font-size: 14px; color: #000;">';
			if($row['ais_Price']>0){ //test to see if we have a price registered for this vehicle - if so, then show it
				$thisOut .=			'<span style="font-size:18px;color:#00427a;font-weight:bold;">Starting at</span><br>
									<span style="font-size:48px;font-weight:bold;color:#ab192d;">&nbsp;<sup style="font-size:28px;">$</sup>'.number_format($row['ais_Price']).'</span>';
			}
									
				////////////////////////////////////////////////////////
			
			if($msrpDifference > 0)	{	
				$thisOut .= '<span style="font-size:18px;line-height:24px;">&nbsp;&nbsp;$'.$msrpDifference.' off MSRP';
				if( $row['ais_Rebate'] > 0 ){ 
					$thisOut .= ' + $'.number_format($row['ais_Rebate']).' factory rebates</span>'; 
				}else{
					$thisOut .= '</span>';
				}
				
			}
			$thisOut .= "<div style='margin-left:40px'>".$disclaimerArray['lower']."</div>";
			$thisOut .=			'</td></tr>';
		}
	
	$thisOut .=	'</table>';
	return array($row['ais_Div_Id'], $thisOut, $thisImagePath, $disclaimerNumber, $disclaimers);
			
}
?>
<?
function formatIncentive($mode, $incentiveTdWidth, $row){
	$output = null; //set up a null flag to return if we don't set it in the meantime by finding the incentive by its id
	if($mode == "Finance" && count($row['aisIncentive1'])>1 ){
		$output = '';
		$output .=		'<td width="'.$incentiveTdWidth.'px" height="144" align="center" valign="top" style="font-family: arial,sans-serif; font-size: 14px; color: #000;">
													<span style="font-size:18px;color:#00427a;font-weight:bold;">Finance offer</span><br>
													<span style="font-size:24px;font-weight:bold;color:#00427a;">'.number_format($row['aisIncentive1']['APR'], 1).'%</span> <span style="color:#00427a;font-size:24px;font-weight:bold;">APR</span><span style="color:#00427a;font-size:18px;font-weight:bold;"><sup>'.$row['aisIncentive1']['ais_Incentive_1_Disc_Number'].'</sup></span><br>
													<span style="font-size:18px;">up to <span style="font-size:24px; font-weight:bold">'.$row['aisIncentive1']['Term'].'</span> months</span><br>
													<span style="font-size:13px;line-height:34px;">ON APPROVED CREDIT</span>
												</td>
												<td class="spacer" width="5" style="font-size: 1px;">&nbsp;</td>';
	}elseif($mode=="Lease" && count($row['aisIncentive2'])>1 ){
		/////
		$output = '';
		$downColorStyle = '';
		$downToShow = $row['aisIncentive2']['Down'];
		if($downToShow == 0){
			$downToShow = $row['aisIncentive2']['FirstPayment'];
		}
		if($downToShow == 0){ 
			$downColorStyle = " style='color:red' "; 
		}
		/////
		$output .=		'<td width="'.$incentiveTdWidth.'px" height="144" align="center" valign="top" style="font-family: arial,sans-serif; font-size: 14px; color: #000;">
							<!-- Offer Title -->
							<span style="font-size:18px;color:#00427a;font-weight:bold;">'.$row['ais_Incentive_2_Type'].' offer</span><br>
							<!-- Offer Details -->
							<span style="font-size:24px;font-weight:bold;color:#00427a;"><sup style="font-size:20px;">$</sup>'.$row['aisIncentive2']['Lease'].'</span> <span style="color:#00427a;font-size:18px;font-weight:bold;">per month<sup>'.$row['aisIncentive2']['ais_Incentive_2_Disc_Number'].'</sup></span><br>
							<span style="font-size:24px; font-weight:bold">'.$row['aisIncentive2']['Term'].'-month lease<br>
							<span style="font-size:24px; font-weight:bold"  '.$downColorStyle.'>$'.number_format($row['aisIncentive2']['Down']).'</span> due at signing</span><br>
							<span style="font-size:13px;line-height:34px;">ON APPROVED CREDIT</span>
						</td>';
	}elseif($mode=="Custom Lease"){
		
		/////
		if( $row['aisIncentive1'] > 0 ){ $incentiveTdWidth = 188; }else{ $incentiveTdWidth = 366; }
		$output = '';
		$downColorStyle = '';
		
		$extras = unserialize(base64_decode($row['ais_Incentive_2_Extra']));
		
		//global $extras;
		
		$downToShow = $extras['down'];
		$downColorStyle = " style='color:red' "; 
		/////
		
		$output .=		'<td width="'.$incentiveTdWidth.'px" height="144" align="center" valign="top" style="font-family: arial,sans-serif; font-size: 14px; color: #000;">';
		$output .=		'
							<!-- Offer Title -->
							<span style="font-size:18px;color:#00427a;font-weight:bold;">Special Lease Offer</span><br>
							<!-- Offer Details -->
							<span style="font-size:24px;font-weight:bold;color:#00427a;"><sup style="font-size:20px;">$</sup>'.number_format($extras['monthly']).'</span> <span style="color:#00427a;font-size:18px;font-weight:bold;">per month<sup>'.$row['aisIncentive2']['ais_Incentive_2_Disc_Number'].'</sup></span><br>
							<span style="font-size:18px;"><span style="font-size:24px; color:#00427a; font-weight:bold">'.$extras['length'].'</span>-month lease<br>
							<span style="font-size:24px; color:#00427a; font-weight:bold"  '.$downColorStyle.'>$'.$extras['first'].'</span> due at signing</span><br>
							<span style="font-size:13px;line-height:34px;">ON APPROVED CREDIT</span>';
		$output .=		$extras['down'];
		$output .=		'</td>';
	}
	return $output;
}

function checkDisclaimers($disclaimerNumber, $disclaimers, $row){ //assembles data structure and disclaimer text. returns array
		
		$lowerDisclaimerText = ''; //placeholder for text bewlow price with VINs and expiration dates
		$today = date("n.j.y"); //get today's date to determine last day in month 
		$exp = "Offer expires ".date('n/t/Y', strtotime($today))."."; 
		
		if($row['ais_Price']> 0 ){ //we have the number of cars available, now loop through that amount to get the VINs for the disclaimer output
			
			if($row['ais_Div_Id'] == 23 ){ //this should be set to 23 for Nissan - this is a special case for them
				if($row['ais_Number']>=2){
					if($row['ais_Number'] == 2){
						$lowerDisclaimerText .= "Two available at this price - ";
					}elseif($row['ais_Number'] > 2){
						$lowerDisclaimerText .= "More than two available at this price ";
						if($row['ais_Number'] < 6) $lowerDisclaimerText .= "-";
					}
				}else{ //default for all makes but Nissan
						$lowerDisclaimerText .= $row['ais_Number']." available at this price - ";
				}
			}else{ //there is only one avaiable
				$lowerDisclaimerText .= $row['ais_Number']." available at this price ";
				if($row['ais_Number'] < 6){ $lowerDisclaimerText .= "-"; }
			} //end Nissan conditional

			if( $row['ais_Number'] < 6)	{ //we have less than 6 VINS so we need to create a disclaimer and list them
									
				for($i=1;$i<= $row['ais_Number']; $i++){
					if($i==1){ $lowerDisclaimerText .= "("; }
							$vinToCheck = "ais_Vin_".$i;
							$lowerDisclaimerText .= $row[$vinToCheck];
							if($i != $row['ais_Number']) $lowerDisclaimerText .= ", "; //we're not at the final VIN yet so add the comma
				} //end for
			$lowerDisclaimerText .= "). ".$exp;
			} //end number if
		
		} //end price if
	if( $row['ais_Incentive_1']> 0 && strlen($row['aisIncentive1']['Disclaimer'])> 0 ){ //we have a disclaimer to list so increment the display number and record the disclaimer data
		$disclaimerNumber++; 
		$row['aisIncentive1']['ais_Incentive_1_Disc_Number'] = $disclaimerNumber;
		$disclaimers[$disclaimerNumber] = $row['aisIncentive1']['Disclaimer'];
	}
	if( $row['ais_Incentive_2']> 0 && strlen($row['aisIncentive2']['Disclaimer'])> 0 ){ //we have a disclaimer to list so increment the display number and record the disclaimer data
		$disclaimerNumber++;
		$row['aisIncentive2']['ais_Incentive_2_Disc_Number'] = $disclaimerNumber;
		$disclaimers[$disclaimerNumber] = $row['aisIncentive2']['Disclaimer'];
	}
	
	$thisReturn = array('lower'=>$lowerDisclaimerText, 'disclaimerNumber'=>$disclaimerNumber, 'disclaimers'=>$disclaimers, 'row'=>$row);
	return $thisReturn;
}

function dh_upload_path_hardcoded($root, $model, $obj, $upload){ //hard-coded to retrieve path for dealership

	if (WP_DEBUG) error_log(' !!! dh_upload_path called with args ['. $root .'|'.$model.'|'.$obj.'|'.$upload.']');
    $wp_upload = wp_upload_dir();
    if (WP_DEBUG) error_log(' !!! wp_upload[root] = '. $wp_upload[$root] );
    $table = dh_table($model);
    if (WP_DEBUG) error_log(' !!! table = '.$table);
    $pk_column = dh_pk_column($model);
	
	$uploadUrls = array(
		'3'=>'chrysler',
		'4'=>'dodge',
		'7'=>'jeep',
		'21'=>'ram'
	);
	
	$uploadKeys = array_keys($uploadUrls);
	if(in_array($obj['dealership_id'], $uploadKeys)){
		$makeName = $uploadUrls[$obj['dealership_id']];
		$uploadFolder = "dickhannah".$makeName.".com/files";
		$path = "http://".$uploadFolder."/dh/{$table}s/{$obj[$pk_column]}/{$obj[$upload]}";

	}else{
		$path = "{$wp_upload[$root]}/dh/{$table}s/{$obj[$pk_column]}/{$obj[$upload]}";
	}
    return $path;
}
?>