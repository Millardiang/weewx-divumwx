<?php
##############################################################################################
#        ________   __  ___      ___  ____  ____  ___      ___    __   __  ___  ___  ___     #
#       |"      "\ |" \|"  \    /"  |("  _||_ " ||"  \    /"  |  |"  |/  \|  "||"  \/"  |    #
#       (.  ___  :)||  |\   \  //  / |   (  ) : | \   \  //   |  |'  /    \:  | \   \  /     #
#       |: \   ) |||:  | \\  \/. ./  (:  |  | . ) /\\  \/.    |  |: /'        |  \\  \/      #
#       (| (___\ |||.  |  \.    //    \\ \__/ // |: \.        |   \//  /\'    |  /\.  \      #
#       |:       :)/\  |\  \\   /     /\\ __ //\ |.  \    /:  |   /   /  \\   | /  \   \     #
#       (________/(__\_|_)  \__/     (__________)|___|\__/|___|  |___/    \___||___/\___|    #
#                                                                                            #
#     Copyright (C) 2023 Ian Millard, Steven Sheeley, Sean Balfour. All rights reserved      #
#      Distributed under terms of the GPLv3.  See the file LICENSE.txt for your rights.      #
#    Issues for weewx-divumwx skin template are only addressed via the issues register at    #
#                    https://github.com/Millardiang/weewx-divumwx/issues                     #
##############################################################################################
//  Ian Millard 05/11/24 added styling to links for MetOffice warning links                  #
//                                                                                           #
//############################################################################################
error_reporting(0);
$healthFromTo =  " 17 January 2025 at 06:00pm to 21 January 2025 at 09:00am";
$healthUpdated = "Last updated on Thursday, 16 January 2025 at 11:56am";
$healthAlertID = "0";
if($healthAlertID !== "0")
{
//yellow cold
if ($healthAlertID == "c1")
{$healthBackground = "rgb(249,240,199)";//"#e9c143";
$iconColor = "yellow";
$healthAlertColor = "#000000";
$healthAlertlevel = "Yellow Cold Health Alert";
$healthHeadline = "  " . $healthAlertlevel . " for South East England. Effective from". $healthFromTo.". Updated ".$healthUpdated.".";
$healthMessage = "<p>Forecast weather is likely to be cold. It will not affect most people, however:</p>
<ul>
										<li>increased use of healthcare services by vulnerable people</li>
										<li>greater risk to life of vulnerable people</li>
									</ul>";
}
//amber cold
else if ($healthAlertID == "c2")
{$healthBackground = "rgb(232,128,59)";
$healthAlertColor = "#ffffff";
$iconColor = "orange";
$healthAlertlevel = "Amber Cold Health Alert";
$healthHeadline = "  " . $healthAlertlevel . " for South East England. Effective from". $healthFromTo.". Updated ".$healthUpdated.".";
$healthMessage = "<p>Forecast weather is likely to cause significant impacts across health and social care services, including:</p>
<ul>
<li>a rise in deaths, particularly among those aged 65 and over or with health conditions. We may also see impacts on younger age groups</li>
<li>a likely increase in demand for health services</li>
<li>temperatures inside places like hospitals, care homes, and clinics dropping below the levels recommended for assessing health risks</li>
<li>challenges keeping indoor temperatures at the recommended 18°C leading to more risk to vulnerable people</li>
<li>staffing issues due to external factors (such as travel delays)</li>
<li>other sectors starting to observe impacts (such as transport and energy)</li>
</ul>";
} 
//red cold
else if ($healthAlertID == "c3")
{$healthBackground = "rgb(169,34,23)";
$iconColor = "red"; 
$healthAlertColor = "#ffffff";
$healthAlertlevel = "Red Cold Health Alert";
$healthHeadline = "  " . $healthAlertlevel . " for South East England. Effective from". $healthFromTo.". Updated ".$healthUpdated.".";
$healthMessage = "<p>likely to cause extreme impacts across health and social care services, including:</p>
<ul>
<li>Even healthy people are more likely to be unwell or die because of the weather.</li>
<li>a likely increase in demand for health services</li>
<li>temperatures inside places like hospitals, care homes, and clinics dropping below the levels recommended for assessing health risks</li>
<li>challenges keeping indoor temperatures at the recommended 18°C leading to more risk to vulnerable people</li>
<li>staffing issues due to external factors (such as travel delays)</li>
<li>other sectors starting to observe impacts (such as transport and energy)</li>
</ul>";
}
//yellow heat
else if ($healthAlertID == "h1")
{$healthBackground = "rgb(249,240,199)";
$iconColor = "yellow";
$healthAlertColor = "#000000";
$healthAlertlevel = "Yellow Heat Health Alert";
$healthHeadline = "  " . $healthAlertlevel . " for South East England. Effective from". $healthFromTo.". Updated ".$healthUpdated.".";
$healthMessage = "<p>Forecast weather is likely to be hot. It will not affect most people, however:</p>
<ul>
<li>it might affect people who are very old, young, disabled or unwell</li>
<!--li>a rise in deaths, particularly among those aged 65 and over or with health conditions. We may also see impacts on younger age groups</li>
<li>a likely increase in demand for health services</li>
<li>temperatures inside places like hospitals, care homes, and clinics dropping below the levels recommended for assessing health risks</li>
<li>challenges keeping indoor temperatures at the recommended 18°C leading to more risk to vulnerable people</li>
<li>staffing issues due to external factors (such as travel delays)</li>
<li>other sectors starting to observe impacts (such as transport and energy)</li-->
</ul>";
}
//amber heat
else if ($healthAlertID == "h2")
{$healthBackground = "rgb(232,128,59)";
$iconColor = "orange";
$healthAlertColor = "#ffffff";
$healthAlertlevel = "Amber Heat Health Alert";
$healthHeadline = "  " . $healthAlertlevel . " for South East England. Effective from". $healthFromTo.". Updated ".$healthUpdated.".";
$healthMessage = "<p>Forecast weather is likely to cause significant impacts across health and social care services, including:</p>
<ul>
<li>a rise in deaths, particularly among those aged 65 and over or with health conditions. We may also see impacts on younger age groups</li>
<li>a likely increase in demand for health services</li>
<li>temperatures inside places like hospitals, care homes, and clinics dropping below the levels recommended for assessing health risks</li>
<li>challenges keeping indoor temperatures at the recommended 18°C leading to more risk to vulnerable people</li>
<li>staffing issues due to external factors (such as travel delays)</li>
<li>other sectors starting to observe impacts (such as transport and energy)</li>
</ul>";
} 
//red heat
else if ($healthAlertID == "h3")
{$healthBackground = "rgb(169,34,23)";
$iconColor = "red";
$healthAlertColor = "#ffffff";
$healthAlertlevel = "Red Heat Health Alert";
$healthHeadline = "  " . $healthAlertlevel . " for South East England. Effective from". $healthFromTo.". Updated ".$healthUpdated.".";
$healthMessage = "<p>Forecast weather is likely to cause significant impacts across health and social care services, including:</p>
<ul>
<li>a rise in deaths, particularly among those aged 65 and over or with health conditions. We may also see impacts on younger age groups</li>
<li>a likely increase in demand for health services</li>
<li>temperatures inside places like hospitals, care homes, and clinics dropping below the levels recommended for assessing health risks</li>
<li>challenges keeping indoor temperatures at the recommended 18°C leading to more risk to vulnerable people</li>
<li>staffing issues due to external factors (such as travel delays)</li>
<li>other sectors starting to observe impacts (such as transport and energy)</li>
</ul>";
} 
echo '<html>

      <section>
      <div class="alertbar" style="margin-bottom:4px;padding-bottom:10px;background-color:'.$healthBackground .';color:'.$healthAlertColor.';border-radius:5px;border: transparent 4px;">
      <div class="alert-text-box" style="padding-left:20px;padding-right:20px;display:flex;margin: 0 auto;">
		<div class="post" style="font-weight:500; font-size:16px; color:'.$healthAlertColor.';"><img src="img/coldHealth'.$iconColor.'.svg"style="margin-bottom:-10px; width:40px;">'.$healthHeadline.' 
			
			<span class="more" style="padding-top:-20px;display:none; font-size:16px;"><p>'.$healthMessage .'.</p></span>

			<more-button class="read">More</more-button>
		</div></div></div>
	</section>	

</html>';
}      


