  <div class="divumfooter-container">
    <div class="divumfooter-item">
      <div class="hardwarelogo1"><a href="http://weewx.com" alt="http://weewx.com" title="http://weewx.com">
          <?php echo '<img src="img/icon-weewx.svg" alt="WeeWX" title="WeeWX" width="150px" height="55px"><div class="hardwarelogo1text"></div>';?></a>
      </div>
      <div class="hardwarelogo2">
        <?php echo '<a href="https://https://claydonsweather.org.uk/" title="https://claydonsweather.org.uk/" target="_blank"><br><img src="img/divumLogo.svg" width="40px" alt="https://https://claydonsweather.org.uk/" class="homeweatherstationlogo" ><divumwx>Team DivumWX design in progress 2021-' . date('Y') . '</divumwx></a>';?>
      </div>
      <div class="footertext"><?php echo "System Operational Since " . $divum["since"]; ?></div>
      <div class="footertext"><?php echo $info;?> (<value><?php echo $templateversion;?></value>) <?php echo "WeeWX";?>-(<value><maxred><?php echo $divum["swversion"];?></value>) OS- <yellow><?php echo " " . $weatherhardware . "". $os_version;?></value></div>
      <div class="footertext"><a href="https://www.aerisweather.com/"><img src="img/aerisweather-attribution-h-<?php echo $theme;?>.png" width="75px"></a><br /><a href="https://developer.yr.no/featured-products/forecast/">&nbsp;&nbsp;&nbsp;&nbsp;Meteogram Data by <img src="img/yr.svg" width="14px"></a><br /><a href="https://bas.dev/work/meteocons">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Animated Icons by <img src="img/bm.svg" width="14px"></a>
    </div>
  </div>
</div>