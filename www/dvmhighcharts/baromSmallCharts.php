<?php
include ('../fixedSettings.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <script src="scripts/jquery-1.12.4.min.js"></script>
    <script src="scripts/jquerry-ui-1.12.1.min.js"></script>
    <script src="scripts/highstock.js"></script>
    <script src="scripts/highcharts-more.js"></script>
    <script src="scripts/windbarb.js"></script>
    <script src="scripts/boost.js"></script>
    <script src="scripts/exporting.js"></script>
    <script src="scripts/divumwx-<?php echo $theme;?>.js" type="text/javascript"></script>
    <script src="scripts/plots_config.js" type="text/javascript"></script>
    <script src="scripts/plots.js" type="text/javascript"></script>
    <script src="scripts/convert_units.js" type="text/javascript"></script>
    <script src="../languages/translations.js" type="text/javascript"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script>
    <script type="text/javascript">


        window.onload = function() {
            theme = 'black';
            var vars = {};
            window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {vars[key.replace(/%27/g,"")] = value.replace(/%27/g,"");});
            document.title = vars['chart'] + " chart";
            if(vars['chart'] != 'windroseplot'){
                $("#plot_div").css("height", 600);
                (function(vars){new plot_js({temp:vars['temp'], pressure:vars['pressure'], wind:vars['wind'], rain:vars['rain']}, vars['chart'], vars['span'], "plot_div");}(vars));
            }else{
                $("#plot_div").css("display", "none");
                $("#plot_div1").css("display","inline-block");
                $("#plot_div2").css("display","inline-block");
                $("#plot_div1").css("height", 500);
                $("#plot_div2").css("height", 500);
                (function(vars){new plot_js({temp:vars['temp'], pressure:vars['pressure'], wind:vars['wind'], rain:vars['rain']}, vars['chart'], vars['span'], "plot_div1");}(vars));
                vars['chart'] = 'windrosegustplot';
                (function(vars){new plot_js({temp:vars['temp'], pressure:vars['pressure'], wind:vars['wind'], rain:vars['rain']}, vars['chart'], vars['span'], "plot_div2");}(vars));
           }
        }


    </script>
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/base/jquery-ui.css">
    <style>body
-webkit-border-radius:4px;	-moz-border-radius:4px;	-o-border-radius:4px;	-ms-border-radius:4px;border-radius:4px;border:solid RGBA(84, 85, 86, 1.00) 2px;	width:167vh;height:80vh;}
.divumwxdarkbrowser{position:relative;background:0;width:103.3%;max-height:30px;margin:auto;margin-top:-15px;margin-left:-20px;border-top-left-radius:5px;border-top-right-radius:5px;padding-top:45px;background-image:radial-gradient(circle,#EB7061 6px,transparent 8px),radial-gradient(circle,#F5D160 6px,transparent 8px),radial-gradient(circle,#81D982 6px,transparent 8px),radial-gradient(circle,rgba(97,106,114,1) 2px,transparent 2px),radial-gradient(circle,rgba(97,106,114,1) 2px,transparent 2px),radial-gradient(circle,rgba(97,106,114,1) 2px,transparent 2px),linear-gradient(to bottom,rgba(59,60,63,0.4) 40px,transparent 0);background-position:left top,left top,left top,right top,right top,right top,0 0;background-size:50px 45px,90px 45px,130px 45px,50px 30px,50px 45px,50px 60px,100%;background-repeat:no-repeat,no-repeat}.divumwxdarkbrowser[url]:after{content:attr(url);color:#aaa;font-size:14px;position:absolute;left:0;right:0;top:0;padding:2px 15px;margin:11px 50px 0 90px;border-radius:3px;background:rgba(97, 106, 114, 0.3);height:20px;box-sizing:border-box;font-family:Arial, Helvetica, sans-serif}
</style>
  </head>
  <body>
  
 
       <div style="width:auto;">  
       <div id="plot_div"  style="width:100%;}"></div>
       <div id="plot_div1" style="width:49.6%;display:none;"></div>
       <div id="plot_div2" style="width:49.6%;display:none;"></div>
       </div>
</body> 

</html>

