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
include('dvmCombinedData.php');
?>
<style>
  body {
  	overflow: hidden;
    background-color:var(--col-1);
  }  

	.demo {
		border: 1px solid #aaa;
		border-collapse: collapse;
		padding: 50px;
        font-family: arial, helvetica, verdana, sans-serif;
        font-size: 10px;
        margin-bottom: 50px; /*whatever distance you want between the two tables*/
        margin-top: 50px;
        width: 100%;
	}
	.demo th {
		border: 1px solid #aaa;
		padding: 5px;
        color: #aaa;
        
	}
	.demo td {
		border: 1px solid #aaa;
		padding: 10px;
		background: var(--col-1);
        text-align: center;
	}
    .demo td#CELL1 {
		background-color: transparent;
        color: #aaa;
	}
    .demo td#CELL2 {
		background-color: #9cff9c;
	}
    .demo td#CELL3 {
		background-color: #31ff00;
	}
    .demo td#CELL4 {
		background-color: #31cf00;
	}
    .demo td#CELL5 {
		background-color: #ff0;
	}
    .demo td#CELL6 {
		background-color: #ffcf00;
	}
    .demo td#CELL7 {
		background-color: #ff9a00;
	}
    .demo td#CELL8 {
		background-color: #ff6464;
	}
    .demo td#CELL9 {
		background-color: red;
        color: white;
	}
    .demo td#CELL10 {
		background-color: #900;
        color: white;
	}
    .demo td#CELL11 {
		background-color: #ce30ff;
        color: white;
	}
   
</style>
<table class="demo">
 <caption style="text-align:left; color:#aaa"><small>The World AQI Indices are based on the US EPA (United States Environmental Protection Agency) Scale, latest 24 hour running mean for the current day.</small></p></caption>
  
  <tbody>
	<tr>
        <th id="CELL1">WORLD (US EPA) AQI PM<sub>2.5</sub></th>
		<th id="CELL2">Good</th>
		<th id="CELL3">Moderate</th>
		<th id="CELL4">Unhealthy for Sensitive Groups</th>
		<th id="CELL5">Unhealthy</th>
		<th id="CELL6">Very Unhealthy</th>
		<th id="CELL7">Hazardous</th>
		
		
	</tr>
	
	<tr>
		<td id="CELL1"><b>Range<b></td>
		<td id="CELL3">0-50</td>
		<td id="CELL6">51-100</td>
		<td id="CELL7">101-150</td>
		<td id="CELL9">151-200</td>
		<td id="CELL11">201-300</td>
		<td id="CELL10">300</td>
		
	</tr>
	<tr> 	 	 	 	 	 	 	 	 	
		<td id="CELL1"><b>µg/m³<b></td>
		<td id="CELL3">0-12</td>
		<td id="CELL6">12-35</td>
		<td id="CELL7">35-55</td>
		<td id="CELL9">55-150</td>
		<td id="CELL11">150-250</td>
		<td id="CELL10">250</td>
		
	</tr>
	</tbody>
</table>

<table class="demo">
  <caption style="text-align:left; color:#aaa"><small>The Defra (UK Government Department for Environment and Rural Affairs) DAQI Indices are based on the daily mean concentration for historical data, latest 24 hour running mean for the current day.</small></p></caption>
  	
	<thead>
	<tr>
    <th id="CELL1">UK DAQI</th>
		<th id="CELL2">Low</th>
		<th id="CELL3">Low</th>
		<th id="CELL4">Low</th>
		<th id="CELL5">Moderate</th>
		<th id="CELL6">Moderate</th>
		<th id="CELL7">Moderate</th>
		<th id="CELL8">High</th>
		<th id="CELL9">High</th>
		<th id="CELL10">High</th>
		<th id="CELL11">Very High</th>
	</tr>
	</thead>
	<tbody>
	<tr>
		<td id="CELL1"><b>Band<b></td>
		<td id="CELL2">1</td>
		<td id="CELL3">2</td>
		<td id="CELL4">3</td>
		<td id="CELL5">4</td>
		<td id="CELL6">5</td>
		<td id="CELL7">6</td>
		<td id="CELL8">7</td>
		<td id="CELL9">8</td>
		<td id="CELL10">9</td>
		<td id="CELL11">10</td>
	</tr>
	<tr>
		<td id="CELL1"><b>PM<sub>2.5 </sub>µg/m³<b></td>
		<td id="CELL2">0-11</td>
		<td id="CELL3">12-23</td>
		<td id="CELL4">24-35</td>
		<td id="CELL5">36-41</td>
		<td id="CELL6">42-47</td>
		<td id="CELL7">48-53</td>
		<td id="CELL8">54-58</td>
		<td id="CELL9">59-64</td>
		<td id="CELL10">65-70</td>
		<td id="CELL11">71 or more</td>
	</tr>
    <tr> 	 	 	 	 	 	 	 	 	
		<td id="CELL1"><b>PM<sub>10 </sub>µg/m³<b></td>
		<td id="CELL2">0-16</td>
		<td id="CELL3">17-33</td>
		<td id="CELL4">34-50</td>
		<td id="CELL5">51-58</td>
		<td id="CELL6">59-66</td>
		<td id="CELL7">67-75</td>
		<td id="CELL8">76-83</td>
		<td id="CELL9">84-91</td>
		<td id="CELL10">92-100</td>
		<td id="CELL11">101 or more</td>
	</tr>      
	</tbody>

	<table class="demo">
 <caption style="text-align:left; color:#aaa"><small>The European CAQI (Common Air Quality Index) Indices are based on the daily mean concentration for historical data, latest 24 hour running mean for the current day.</small></p></caption>
  
  <tbody>
	<tr>
    <th id="CELL1">Europe (CAQI)</th>
		<th id="CELL2">Very Low</th>
		<th id="CELL3">Low</th>
		<th id="CELL4">Medium</th>
		<th id="CELL5">High</th>
		<th id="CELL6">Very High</th>		
	</tr>
	<tr>
		<td id="CELL1"><b>Range<b></td>
		<td id="CELL3">0-25</td>
		<td id="CELL6">25-50</td>
		<td id="CELL7">50-75</td>
		<td id="CELL9">75-100</td>
		<td id="CELL11">100 or more</td>		
	</tr>
	<tr> 	 	 	 	 	 	 	 	 	
		<td id="CELL1"><b>PM<sub>2.5 </sub>µg/m³<b></td>
		<td id="CELL3">0-15</td>
		<td id="CELL6">15-30</td>
		<td id="CELL7">30-55</td>
		<td id="CELL9">55-110</td>
		<td id="CELL11">110 or more</td>	
	</tr>
	<tr> 	 	 	 	 	 	 	 	 	
		<td id="CELL1"><b>PM<sub>10 </sub>µg/m³<b></td>
		<td id="CELL3">0-25</td>
		<td id="CELL6">25-50</td>
		<td id="CELL7">50-90</td>
		<td id="CELL9">90-180</td>
		<td id="CELL11">180 or more</td>
	</tr> 
	</tbody>


 