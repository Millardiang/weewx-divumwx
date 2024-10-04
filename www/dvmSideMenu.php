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
?>
<!-- Side Menu -->
 <input type="checkbox" class="sidebarmenu" id="sidebarmenu"/>
  <label for="sidebarmenu" class="sidebarIconToggle">
    <div class="menuspinner menucross part-1"></div>
    <div class="menuspinner menuhorizontal"></div>
    <div class="menuspinner menucross part-2"></div>
  </label>
  <div id="divumwxsidebarMenu">
    <ul class="divumwxsidebarMenuInner">
      <br/><br/><br/>
     <li class="header">
        <a href="index.php" title="WEATHERSTATION HOME PAGE">
          <menumarkergreen></menumarkergreen> HOME
        </a>
      </li>
          
      <li class="header">ADMIN</li>
        <li><a href="./admin/index.php" title="DvM Admin"><menumarkergreen></menumarkergreen> DvM Admin</a></li>
      <!--li class="header">UI THEME</li>
      <li>
        <a href="<?php echo ($theme == 'dark') ? '?theme=light' : '?theme=dark'; ?>">
          <?php if ($theme == 'dark'){
            echo '<menumarkerlight></menumarkerlight> Light Theme';
          }else{
            echo '<menumarkerbluegrey></menumarkerbluegrey> Dark Theme';
          }?></a>
      </li-->
      <li class="header">UNITS</li>
      <?php if ($units != Null && $units != 'default'){
        echo '<li>
          <a href="./?units=default"><menumarkerred></menumarkerred> Default Units';
            if ($tempunit == 'F'){
              echo '<topbarimperialf>°F</topbarimperialf>';
            }else{
              echo '<topbarmetricc>°C</topbarmetricc>';
            }?></a>
        </li>
      <?php
      }
      if ($units != 'us'){
        echo '<li>
          <a href="./?units=us"><menumarkerorange></menumarkerorange> US Customary <topbarimperialf>°F</topbarimperialf></a>
        </li>';
      }
      if ($units != 'metric'){
        echo '<li>
          <a href="./?units=metric"><menumarkerblue></menumarkerblue> Metric <topbarmetricc>°C</topbarmetricc></a>
        </li>';
      }
      if ($units != 'uk'){
        echo '<li>
          <a href="./?units=uk"><menumarkeryellow></menumarkeryellow> UK (mph) <topbarmetricc>°C</topbarmetricc></a>
        </li>';
      }
      if ($units != 'scandinavia'){
        echo '<li>
          <a href="./?units=scandinavia"><menumarkerred></menumarkerred> Scandinavia (m/s) <topbarmetricc>°C</topbarmetricc></a>
        </li>';
      }
      if ($units != 'ca'){
        echo '<li>
          <a href="./?units=ca"><menumarkerred></menumarkerred> Canada (kPa) <topbarmetricc>°C</topbarmetricc></a>
        </li>';
      }
       if ($units != 'kts'){
        echo '<li>
          <a href="./?units=kts"><menumarkerred></menumarkerred> Metar (kts) <topbarmetricc>°C</topbarmetricc></a>
        </li>';
      }
      if ($extralinks == 'yes') {
        echo '<li class="header sub">LINKS <img class="menuimg" src="img/arrowiconlink.svg" alt=""/>';
        echo '<ul>';
        if ($linkWU == 'yes') {
          echo '<li>
                  <a href="' . (($linkWUNewDash == 'yes' || empty($linkWUNewDash)) ? 'https://www.wunderground.com/dashboard/pws/' : 'https://www.wunderground.com/personal-weather-station/dashboard?id=') . $WUid . '" title="' . $WUid . ' on Weather Underground" target="_blank"><img class="menuimg" src="img/wulogo.svg" style="width:30px" alt=""/>' . $WUid . ' on WU</a>
                </li>';
        }
        if (!empty($linkCWOPID)){
          echo '<li>
                  <a href="https://weather.gladstonefamily.net/site/' . $linkCWOPID . '" title="' . $linkCWOPID . ' on CWOP" target="_blank"><img class="menuimg" src="img/arrowiconlink.svg" alt=""/>' . $linkCWOPID . ' on CWOP</a>
                </li>';
        }
        if (!empty($linkFindUID)) {
          echo '<li>
                  <a href="http://www.findu.com/cgi-bin/wxpage.cgi?call=' . $linkFindUID . '&last=48" title="' . $linkFindUID . ' on Findu.com" target="_blank"><img class="menuimg" src="img/arrowiconlink.svg" alt=""/>' . $linkFindUID . ' on FindU.com</a>
                </li>';
        }
        if (($linkNOAA == 'yes') && (!empty($linkCWOPID) && (empty($linkNOAAID)))) {
          echo '<li>
                  <a href="https://www.wrh.noaa.gov/mesowest/getobext.php?wfo=lox&sid=' . $linkCWOPID . '" title="' . $linkCWOPID . ' on NOAA Meso West" target="_blank"><img class="menuimg" src="img/noaa.svg" style="max-width:30px" alt=""/>' . $linkCWOPID . ' on NOAA</a>
                </li>';
        }
        if ($linkNOAA == 'yes' && !empty($linkNOAAID)) {
          echo '<li>
                  <a href="https://www.wrh.noaa.gov/mesowest/getobext.php?wfo=lox&sid=' . $linkNOAAID . '" title="' . $linkNOAAID . ' on NOAA Meso West" target="_blank"><img class="menuimg" src="img/noaa.svg" style="max-width:30px" alt=""/>' . $linkNOAAID . ' on NOAA</a>
                </li>';
        }
        if ($linkMADIS == 'yes' && !empty($linkCWOPID)) {
          echo '<li>
                  <a href="https://madis-data.ncep.noaa.gov/MadisSurface/?CenterLAT=' . $lat . '&CenterLON=' . $lon . '&Zoom=11.00&StationID=' . $linkCWOPID . '" title="' . $linkCWOPID . ' on MADIS Map" target="_blank"><img class="menuimg" src="img/noaa.svg" style="max-width:30px" alt=""/>' . $linkCWOPID . ' on NOAA MADIS Map</a>
                </li>';
        }
        if (($linkMesoWest == 'yes') && (!empty($linkCWOPID) && (empty($linkMesoWestID)))) {
          echo '<li>
                  <a href="https://mesowest.utah.edu/cgi-bin/droman/meso_base.cgi?stn=' . $linkCWOPID . '" title="' . $linkCWOPID . ' on Meso West" target="_blank"><img class="menuimg" src="img/mesowest.svg" alt=""/>' . $linkCWOPID . ' on Meso West</a>
                </li>';
        }
        if ($linkMesoWest == 'yes' && !empty($linkMesoWestID)) {
          echo '<li>
                  <a href="https://mesowest.utah.edu/cgi-bin/droman/meso_base.cgi?stn=' . $linkMesoWestID . '" title="' . $linkMesoWestID . ' on Meso West" target="_blank"><img class="menuimg" src="img/mesowest.svg" alt=""/>' . $linkMesoWestID . ' on Meso West</a>
                </li>';
        }
        if (!empty($linkWeatherCloudID)) {
          echo '<li>
                  <a href="https://app.weathercloud.net/' . $linkWeatherCloudID . '" title="View on WeatherCloud" target="_blank"><img class="menuimg" src="img/weathercloud.svg" style="width:21px" alt=""/>View on WeatherCloud</a>
                </li>';
        }
        if (!empty($linkWindyID)) {
          echo '<li>
                  <a href="https://www.windy.com/station/pws-' . $linkWindyID . '?' . $lat . ',' . $lon . ',8" title="View on Windy.com" target="_blank"><img class="menuimg" src="img/windy.svg" style="width:21px" alt=""/>View on Windy.com</a>
                </li>';
        }
        if (!empty($linkAWEKASID)) {
          echo '<li>
                  <a href="https://www.awekas.at/en/instrument.php?id=' . $linkAWEKASID . '" title="View on AWEKAS" target="_blank"><img class="menuimg" src="img/awekas.svg" alt=""/>View on AWEKAS</a>
               </li>';
        }
        if (!empty($linkAmbientWeatherID)) {
          echo '<li>
                    <a href="https://dashboard.ambientweather.net/devices/public/' . $linkAmbientWeatherID . '" title="Ambient weather" target= "_blank"><img class="menuimg" src="img/ambientweather.svg" alt=""/>View on Ambient weather</a>
                </li>';
        }
        if (!empty($linkPWSWeatherID)) {
          echo '<li>
                  <a href="https://www.pwsweather.com/obs/' . $linkPWSWeatherID . '.html" title="PWS Weather" target="_blank"><img style="background-color:white" class="menuimg" src="img/pwslogo.svg" alt=""/>View on PWS Weather</a>
                </li>';
        }
        if (!empty($linkMetOfficeID)) {
          echo '<li>
                    <a href="http://wow.metoffice.gov.uk/observations/details?site_id=' . $linkMetOfficeID . '" title="MetOffice/WoW" target="_blank"><img class="menuimg" src="img/metoffice.svg" alt=""/>View on MetOffice/WoW</a>
                </li>';
        }
        if (!empty($linkCustom1Title)) {
          echo '<li>
                  <a href="' . $linkCustom1URL . '" title="' . $linkCustom1Title . '" target="_blank">
                      <img class="menuimg" src="img/arrowiconlink.svg" alt=""/>
                      ' . $linkCustom1Title . '
                    </a>
                  </li>';
        }
        if (!empty($linkCustom2Title) && !empty($linkCustom2URL)) {
          echo '<li>
                  <a href="' . $linkCustom2URL . '" title="' . $linkCustom2Title . '" target="_blank"><img class="menuimg" src="img/arrowiconlink.svg" alt=""/>' . $linkCustom2Title . '</a>
                </li>';
        }
        echo '</ul>';
      }
      echo '<li class="header">EXTRAS</li>';
      if ($weatherflowoption == "yes") {
          echo '<li>
                  <a href="https://tempestwx.com/map/' . $lat . '/' . $lon . '/' . $weatherflowmapzoom . '" data-lity title="see your weather station on the official WeatherFlow map"><menumarkerblue></menumarkerblue> WeatherFlow Map</a>
                </li>';
      }
      if (!empty($webcamurl) && $webcamurl != ' ' && $webcamurl != 'Null' && $webcamurl != 'null') {
          echo '<li>
                  <a href="dvmWebcamPopup.php" data-lity title="Weather Station Webcam"><menumarkeryellow></menumarkeryellow> Web Cam</a>
                </li>';
      }
      echo '<li>
              <a href="dvmBioPopup.php" data-lity title="Weather Station Owner Contact Card Info"><menumarkerorange></menumarkerorange> Contact Card</a>
            </li>
            <li>
              <a href="stationinfo.php" data-lity title="Weather Station Hardware Info"><menumarkerred></menumarkerred> Hardware Info</a>
            </li>
            <li>
              <a href="dvmMenuCheckVariable.php" data-lity title="Check Variables"><menumarkerorange></menumarkerorange> Variable Check Lists</a>
            </li>';
      if (!empty($extraLinkTitle) && !empty($extraLinkURL) && !empty($extraLinkColor)) {
          echo '<li>
                  <a href="' . $extraLinkURL . '" title="' . $extraLinkTitle . '" target="_blank">';
          if ($extraLinkColor == 'white') {
              echo '<menumarkerlight></menumarkerlight>';
          } else if ($extraLinkColor == 'red') {
              echo '<menumarkerred></menumarkerred>';
          } else if ($extraLinkColor == 'grey') {
              echo '<menumarkerbluegrey></menumarkerbluegrey>';
          } else if ($extraLinkColor == 'green') {
              echo '<menumarkergreen></menumarkergreen>';
          } else if ($extraLinkColor == 'yellow') {
              echo '<menumarkeryellow></menumarkeryellow>';
          } else if ($extraLinkColor == 'blue') {
              echo '<menumarkerblue></menumarkerblue>';
          } else {
              echo '<menumarkerorange></menumarkerorange>';
          }
          echo $extraLinkTitle . '</a></li>';
      }
      if ($sbLang == "yes") {
          echo '<li class="header">' . $lang["language"] . '</li>
                <li class="flagstop">
                  <a href="index.php?lang=en">English</a><br />
                  <a href="index.php?lang=dk">Danish</a><br />
                  <a href="index.php?lang=gr">Greek</a><br />
                  <a href="index.php?lang=it">Italian</a><br />
                  <a href="index.php?lang=fr">French</a><br />
                  <a href="index.php?lang=de">German</a><br />
                  <a href="index.php?lang=nl">Dutch</a><br />
                  <a href="index.php?lang=cat">Catalan</a><br />
                  <a href="index.php?lang=sp">Spanish</a><br />
                  <a href="index.php?lang=tr">Turkish</a><br />
                  <a href="index.php?lang=hu">Hungary</a><br />
                  <a href="index.php?lang=pl">Polish</a><br />
                  <a href="index.php?lang=no">Norwegian</a><br />
                </li><br /><br />';
      }
      echo '<li class="header">MISC</li>
            <li>
              <a href="https://chrisalemany.ca/2021/02/24/installing-the-weather34-skin-on-weewx-with-remote-web-server-2021-edition/" title="Remote Setup" target="_blank"><menumarkerbluegrey></menumarkerbluegrey> Remote Setup Guide</a>
            </li>
            <li>
              <a href="team_divumwx/index.html" title="weewx-DivumWX on Github" target="_blank"><menumarkerbluegrey></menumarkerbluegrey> Download DivumWX template</a>
            </li>
            <li>
              <a href="https://steepleian.github.io/weewx-Weather34/" title="Web Services Setup Page" target="_blank"><menumarkerbluegrey></menumarkerbluegrey> Web Services Setup Page</a>
            </li>
            <li class="header">The DivumWX Team</li>
            <li class="flagstop">
              <a href="mailto:steepleian@btinternet.com" title="Email Steepleian for Support" target="_blank">Ian Millard (Steepleian)</a><br />
              <a href="#">Steven Sheeley (Rayvenhaus)</a><br />
              <a href="#">Sean Balfour</a><br />
            </li><br /><br />';
      if (!empty($USAWeatherFinder)) {
          echo '<li>
                    <a href="https://usaweatherfinder.com/index.php?a=stats&u=' . $USAWeatherFinder . '" title="' . $USAWeatherFinder . '\'s Weather Finder" target="_blank"><img src="https://usaweatherfinder.com/button.php?u=' . $USAWeatherFinder . '" alt="USA Weather Finder" border="0" /></a>
                </li>';
      }
      ?>
    </ul>
  </div>
</div>