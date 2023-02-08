# Manual Setup of your Dashboard Grid Guide

Whilst the administration section is still under construction, you will have to continue customising DivumWX to suit your needs. This section explains the process of setting up your dashboard grid.

When you open up userSettings.php for editing you will find this setting near the top of the file: -
            
            $themelayout = "5"; //4, 5, tablet
    
    Sets the main module section to tablet (3 rows), 4 rows or 5 rows. Choosing 5 rows will allow you to display all of the large modules. You will also be able to toggle between 4 or 5 rows and the tablet mode via the button top right of your top menu bar (marked D).

Next find the following sections in the userSettins.php file: -

            $position2   = "dvmAirqualityTop.php"; 
            $position3   = "dvmLightningTop.php";

    These are the settings for the two middle positions on the top row of smaller modules. Positions 1 and 4 are fixed and cannot be changed. You can choose any two of the following small modules: -

            dvmAirqualityTop.php
            dvmLightningTop.php
            dvmRainfalMonthYearTop.php
            dvmTemperatureYearTop.php
            dvmWindGustYearTop.php

Next find this section in userSettings.php: -

            $position5   = "dvmTemperatureModule.php";
            $position6   = "dvmForecastModule.php";
            $position7   = "dvmCurrentModule.php";
            $position8   = "dvmWindModule.php";
            $position9   = "dvmRainfallModule.php";
            $position10   = "dvmBarometerModule.php";
            $position11   = "dvmIndoorTemperatureModule.php";
            $position12   = "dvmSolarUvLuxModule.php";
            $position13   = "dvmLightningModule.php";
            $position14   = "dvmAirqualityModule.php";
            $position15   = "dvmWebcamModule.php";
            $position16   = "dvmEarthquakeModule.php";
            $position17   = "dvmEarthDaylightModule.php";
            $position18    = "dvmSolarDialModule.php";
            $position19   = "dvmMoonPhaseModule.php";

    These are the settings for the main grid and you can swap any of the module tiles into any position on the grid (counting from left to right). Postions 5 to 13 are the standard 3 row tablet format. 14 to 16 for a fourth row and 17 to 19 for a fifth row. If you do not wish to use a fourth or fifth row, just make blank entries below your final choice like this for three row: -

            $position14   = "";
            $position15   = "";
            $position16   = "";
            $position17   = "";
            $position18   = "";
            $position19   = "";

    or this for four row: -

            $position17   = "";
            $position18   = "";
            $position19   = "";

