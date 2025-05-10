<?php
include('userSettings.php');

IF (ISSET($_POST["Submit"])) {
 
$string = '<?php 
$TZ = "'. $_POST["TZ"]. '";
$advisoryzone  = "'. $_POST["advisoryzone "]. '";
$since    = "'. $_POST["since"]. '";
$defaultlanguage   = "'.$_POST["defaultlanguage"]. '";
$password    = "'.$_POST['password']. '";
$flag   = "'.$_POST["flag"]. '";
$manifestShortName = "'.$_POST["manifestShortName"].'";
?>';
 
$fp = FOPEN("locationSettings.php", "w") or die("Unable to open locationSettings.php file check file permissions !");
FWRITE($fp, $string);
FCLOSE($fp);
 
}
?>
<link href="favicon.ico" rel="shortcut icon" type="image/x-icon">
<link href="favicon.ico" rel="icon" type="image/x-icon">
<style> 
body{font-size:12px;font-family:Arial,Helvetica,sans-serif;color:#777;background:white}h1{color:rgba(86, 95, 103, 1.000);font-size:24px;margin-bottom:10px;font-weight:bold;margin:10px 0}h2{color:rgba(86, 95, 103, 1.000);font-size:20px;margin-bottom:10px;font-weight:bold;margin:10px 0}h3{color:#ccc;font-size:14px;margin-bottom:20px;font-weight:bold;margin:20px 0}.weathersetuptitle{font-size:18px;text-align:center;font-weight:200;font-family:Arial,Helvetica,sans-serif;padding:5px;border:0;background:rgba(67, 58, 80, 1.000);border-radius:5px;color:#fff;width:600px;margin:0 auto;border:0;border:1px solid #777}.theframe1{font-size:14px;font-family:Arial,Helvetica,sans-serif;color:#fff;border:0px solid #777;margin:0 auto;margin-top:10px;margin-bottom:10px;width:1024px;background:0;padding:5px;border-radius:4px}.theframe{font-size:14px;font-family:Arial,Helvetica,sans-serif;color:#fff;border:1px solid rgba(7, 114, 125, 1.000,.4);margin:0 auto;margin-top:10px;margin-bottom:10px;width:960px;background:white;padding:5px;border-radius:4px;-webkit-box-shadow: 4px 7px 20px -5px rgba(0,0,0,0.75);
-moz-box-shadow: 4px 7px 20px -5px rgba(0,0,0,0.75);
box-shadow: 4px 7px 20px -5px rgba(0,0,0,0.75);}.weatheroptions{margin:5px;padding:10px;border-radius:4px;border:1px solid #e9ebf1;border-bottom:18px solid #e9ebf1;width:75%}.weatheroptionssidebar{margin:1px;margin-top:-5px;margin-bottom:-5px;margin-left:-5px;padding:5px;border-radius:4px;border:1px solid #e9ebf1;border-bottom:18px solid #f6f8fc;width:200px;position:relative;float:right;margin:5px;color:#777}.weatheroptionssidebarbottom{margin:1px;margin-top:-145px;margin-left:200px;padding:5px;border-radius:4px;border:1px solid #e9ebf1;border-bottom:18px solid #f6f8fc;width:200px;position:relative;float:right;color:#777}.weatherbottominfo{position:absolute;font-size:12px;color:#777;padding:3px;margin-top:3px}.weatherbottomwarning{position:absolute;font-size:12px;color:#777;padding:3px;margin-top:7px}.weatheroptions .button{background:rgba(240,94,64,1);border-radius:4px;padding:5px;font-size:16px;color:#fff;font-family:Arial,Helvetica,sans-serif;border:0;outline:0;-webkit-appearance:none}.weatheroptions .choose{background:rgba(7, 114, 125, 1.000);border-radius:4px;padding:5px;padding-right:10px;font-size:14px;color:#fff;font-family:Arial,Helvetica,sans-serif;border:0;width:160px;max-width:400px;outline:0;-webkit-appearance:none;text-align:left}.weatheroptions .choose1{background:rgba(7, 114, 125, 1.000);border-radius:3px;padding:5px;padding-right:10px;font-size:14px;color:#fff;font-family:Arial,Helvetica,sans-serif;border:0;width:130px;outline:0;-webkit-appearance:none}.weatheroptions .choose2{background:rgba(86, 95, 103, 1.000);border-radius:3px;padding:5px;padding-right:10px;font-size:14px;color:#fff;font-family:Arial,Helvetica,sans-serif;border:0;width:100px;outline:0;-webkit-appearance:none}.weatheroptions .chooseapi{background:rgba(7, 114, 125, 1.000);border-radius:4px;padding:5px;padding-right:10px;font-size:14px;color:#fff;font-family:Arial,Helvetica,sans-serif;border:0;width:300px;outline:0;-webkit-appearance:none;text-align:left}.weatheroptions .personal{background:rgba(7, 114, 125, 1.000);border-radius:4px;padding:5px;padding-right:10px;font-size:14px;color:#fff;font-family:Arial,Helvetica,sans-serif;border:0;width:99%;outline:0;-webkit-appearance:none;text-align:left}.weatheroptions .stationvalue{background:rgba(86, 95, 103, 1.000);border-radius:3px;padding:5px;font-size:14px;color:#fff;font-family:Arial,Helvetica,sans-serif;border:0;outline:0;display:inline-block;-webkit-appearance:none}.weatheroptions .stationvalue1{background:#777;border-radius:3px;padding:5px;font-size:14px;color:#fff;font-family:Arial,Helvetica,sans-serif;border:0;outline:0;display:inline-block;-webkit-appearance:none}.weathersectiontitle{background:#777;border-radius:4px;padding:5px;font-size:14px;color:#fff;font-family:Arial,Helvetica,sans-serif;border:0;outline:0;margin:5px;display:inline-block;-webkit-appearance:none}.weatheroptions a{font-size:14px;color:rgba(86, 95, 103, 1.000);font-family:Arial,Helvetica,sans-serif;border:0;text-transform:none;outline:0;-webkit-appearance:none}a{font-size:14px;color:rgba(86, 95, 103, 1.000);font-family:Arial,Helvetica,sans-serif;border:0;text-transform:none;outline:0;-webkit-appearance:none}#weatherpopupcontainer{width:960px;margin:auto;padding:30px}p{margin-bottom:20px;line-height:24px}#hover{position:fixed;background:white;width:100%;height:100%;opacity:.6}#weatherpopup{position:fixed;width:600px;height:320px;background:white;left:50%;top:25%;border-radius:5px;padding:60px 0;margin-left:-320px;margin-top:-100px;text-align:center;border:1px solid #e9ebf1;border-bottom:23px solid rgba(40,39,39,0.7);color:#fff;padding:10px}.weatherpopupbottom{margin-top:55px;padding:10px;float:left;color:#fff;position:absolute;font-size:11px}#close{position:absolute;background:white;color:#fff;right:-10px;top:-10px;border-radius:50%;width:30px;height:30px;line-height:30px;text-align:center;font-size:14px;font-weight:bold;font-family:'Arial',Arial,sans-serif;cursor:pointer}body{background:white}.seperator{width:700px;border-top:1px #ddd dotted;margin-top:5px;padding:10px}*{box-sizing:border-box}*:focus{outline:0}.login{margin:0 auto;width:300px;background-color:none}a{font-size:12px;text-transform:none;text-decoration:none;color:#2095a7}a:hover{color:#7bbb28}.login-screen{background-color:none;padding:20px;border-radius:5px;margin:0 auto}.app-title{text-align:center;color:#ccc;background-color:none}.login-form{text-align:center;background-color:none}.control-group{margin-bottom:10px}input{text-align:center;background-color:#777;border:2px solid transparent;border-radius:3px;font-size:16px;font-weight:200;padding:10px 0;width:250px;transition:border .5s;color:#fff;border:2px solid rgba(86, 95, 103, 1.000);box-shadow:none;margin:0 auto;margin-top:10px}input:focus{border:2px solid rgba(86, 95, 103, 1.000);box-shadow:none}.btn{border:2px solid transparent;background:rgba(86, 95, 103, 1.000);color:#fff;font-size:16px;line-height:25px;padding:10px 0;text-decoration:none;text-shadow:none;border-radius:3px;box-shadow:none;transition:.25s;display:block;width:150px;margin:10px;text-align:center;-webkit-appearance:none}.btn:hover{background-color:rgba(86, 95, 103, 1.000)}.login-link{font-size:12px;color:#444;display:block;margin-top:12px}.loginformarea{margin:0 auto;text-align:center}orange{color:rgba(236, 87, 27, 1.000)}green{color:rgba(67, 58, 80, 1.000)}blue{color:rgba(67, 58, 80, 1.000)}img{border-radius:4px;}white{color:#fff}.input-button,.modal-button{font-size:18px;padding:10px 40px}.input-block input,.input-button,.modal-button{font-family:Arial,sans-serif;border:1px solid #ccc;}.icon-button,.input-block input,.input-button,.modal-button{outline:0;cursor:pointer}.modal-button{color:#7d695e;border-radius:5px;background:rgba(230, 232, 239, 1.000);width:120px;text-align:center}.modal-button:hover{border-color:rgba(255,255,255,.2);background:rgba(144,177,42,1);color:#f8f8f8}.input-button{color:#7d695e;border-radius:5px;background:#fff}.input-button:hover{background:rgba(144,177,42,1);color:#fff}.input-label{font-size:11px;text-transform:uppercase;font-family:Arial,sans-serif;font-weight:600;letter-spacing:.7px;color:#8c7569;}.input-block{display:flex;flex-direction:column;padding:10px 10px 8px;border:1px solid #ddd;border-radius:4px;margin-bottom:20px;}.input-block input{color:#fff;font-size:18px;padding:10px 40px;border-radius:5px;background:rgba(144,177,42,1)}.input-block input::-webkit-input-placeholder{color:#ccc;opacity:1}.input-block input:-ms-input-placeholder{color:#ccc;opacity:1}.input-block input::-ms-input-placeholder{color:#ccc;opacity:1}.input-block input::placeholder{color:#ccc;opacity:1}.input-block:focus-within{border-color:#8c7569}.input-block:focus-within .input-label{color:rgba(140,117,105,.8)}.icon-button{position:absolute;right:10px;top:12px;width:32px;height:32px;background:0;padding:0}

</style>
<script src="js/jquery.js"></script>

 </head>
        
    <body>
       
    
       
<div class="loginformarea">
<?php 
	//lets secure the homeweatherstation easy setup ///
function showForm($error="LOGIN"){ 
?> <?php echo $error; ?> 
  
  <div class= "login_screen" style="width:60%;max-width:600px;margin:0 auto;color:rgba(24, 25, 27, 1.000);border:solid 1px grey;padding:10px;border-radius:4px;">  <?php echo 'Your Current PHP version is :<orange> ' . phpversion(), '</orange> <br>(A minimum version of PHP 8.2 is required)'; ?>
  <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="pwd" > 
   Enter Your Password For DivumWX-Lite Location Settings Below
<center> <div class="modal-buttons">
     <input name="passwd" type="password" class="input-button"/>  <input type="submit" name="submit_pwd" value="Login " class="modal-button" /> 
         </form> 
     </center>
      <?php echo "2025-" ;?><?php echo date('Y');?> &copy;</a> DivumWX-<orange>Lite</orange></span></span></span>
      <br><br>
        

  

      
<?php    
} 
?>
</div>


  <div span style="width:auto;margin:0 auto;text-align:center;color:#fff;background:0;font-family:arial;padding:20px;border-radius:4px;"> 
<?php 
$Password = $password; 
if (isset($_POST['submit_pwd'])){    $pass = isset($_POST['passwd']) ? $_POST['passwd'] : '';  
   if ($pass != $Password) { 
      showForm("DivumWX-Lite Location Settings Screen"); 
      exit();      
   } 
} else { 
   showForm("DivumWX-Lite Location Settings Screen"); 
   exit(); 
} 
?>


</div>
<div span style="width:450px;margin:0 auto;margin-top:10px;text-align:center;color:#4a636f;background:0;font-family:arial;padding:20px;border-radius:4px;border:1px solid rgba(74, 99, 111, 0.4);"> 
<img src='img/divumwxLogo2.svg' width='100px'>

<br>

Welcome you have logged into the <br>DivumWX setup screen <?php echo date("M jS Y H:i"); ?>
</span>
</div>
</div></div>
</div></div>
<div class="theframe1">
<div class="theframe">
 
<p>
<form action="" method="post" name="install" id="install">
<div class="weatheroptionssidebar">
<svg id="i-info" viewBox="0 0 32 32" width="10" height="10" fill="rgba(7, 114, 125, 1.000)" stroke="rgba(7, 114, 125, 1.000)" stroke-linecap="round" stroke-linejoin="round" stroke-width="16.25%">
     <path d="M16 14 L16 23 M16 8 L16 10" /><circle cx="16" cy="16" r="14" /></svg> Please setup and password protect this page for later use it is for your privacy and protection.
<br>





<div class="weatherbottominfo">
<svg id="i-checkmark" viewBox="0 0 32 32" width="12" height="12" fill="none" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round" stroke-width="6.25%">
    <path d="M2 20 L12 28 30 4" />
</svg>

check password </div>

</div>

<p>

<div class="weatheroptions">
  <div class= "weathersectiontitle"> 
   <svg id="i-settings" viewBox="0 0 32 32" width="12" height="12" fill="none" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round" stroke-width="6.25%">
    <path d="M13 2 L13 6 11 7 8 4 4 8 7 11 6 13 2 13 2 19 6 19 7 21 4 24 8 28 11 25 13 26 13 30 19 30 19 26 21 25 24 28 28 24 25 21 26 19 30 19 30 13 26 13 25 11 28 8 24 4 21 7 19 6 19 2 Z" />
    <circle cx="16" cy="16" r="4" />
</svg>
  Setup Unique Location Settings Password</div><p>

  
  <div class= "stationvalue">  Set a Password, it is for your privacy & protection</div>
<svg id="i-chevron-right" viewBox="0 0 32 32" width="14" height="14" fill="none" stroke="rgba(86, 95, 103, 1.000)" stroke-linecap="round" stroke-linejoin="round" stroke-width="6.25%">
    <path d="M12 30 L24 16 12 2" />
</svg>
  <input name="password" type="password" id="password" value="<?php echo $password ;?>" class="choose">

   
   </div>
   

    
   
   
  
   
   <div class="weatheroptions">
<div class= "weathersectiontitle">
Choose the default Language to display and use..</div>


<p>
      <div class= "stationvalue"> 
      Template Language (lowercase)</div>
      <svg id="i-chevron-right" viewBox="0 0 32 32" width="14" height="14" fill="none" stroke="#F05E40" stroke-linecap="round" stroke-linejoin="round" stroke-width="6.25%">
    <path d="M12 30 L24 16 12 2" />
</svg><svg id="i-chevron-bottom" viewBox="0 0 32 32" width="10" height="10" fill="#777" stroke="#777" stroke-linecap="round" stroke-linejoin="round" stroke-width="6.25%">
    <path d="M30 12 L16 24 2 12" />
</svg>
      
       <label name="defaultlanguage"></label>
        <select id="defaultlanguage" name="defaultlanguage" class="choose1">           
           <option><?php echo $defaultlanguage ;?></option>

																	<option value="">Select New System Language</option>
																	<option value="cat" <?php if($defaultlanguage == 'cat') echo 'selected'; ?>>Catalan</option>
																	<option value="dk" <?php if($defaultlanguage == 'dk') echo 'selected'; ?>>Danish</option>
																	<option value="dl" <?php if($defaultlanguage == 'dl') echo 'selected'; ?>>German</option>
																	<option value="en" <?php if($defaultlanguage == 'en') echo 'selected'; ?>>English</option>
																	<option value="fr" <?php if($defaultlanguage == 'fr') echo 'selected'; ?>>French</option>
																	<option value="gr" <?php if($defaultlanguage == 'gr') echo 'selected'; ?>>Greek</option>
																	<option value="hu" <?php if($defaultlanguage == 'hu') echo 'selected'; ?>>Hungarian</option>
																	<option value="it" <?php if($defaultlanguage == 'it') echo 'selected'; ?>>Italian</option>
																	<option value="nl" <?php if($defaultlanguage == 'nl') echo 'selected'; ?>>Dutch</option>
																	<option value="no" <?php if($defaultlanguage == 'no') echo 'selected'; ?>>Norwegian</option>
																	<option value="pl" <?php if($defaultlanguage == 'pl') echo 'selected'; ?>>Polish</option>
																	<option value="sp" <?php if($defaultlanguage == 'sp') echo 'selected'; ?>>Spanish</option>
																	<option value="sw" <?php if($defaultlanguage == 'sw') echo 'selected'; ?>>Swedish</option>
																	<option value="tr" <?php if($defaultlanguage == 'tr') echo 'selected'; ?>>Turkish</option>        </select>
        <br>
                                                                                                                                        

                                                                 
 <div class= "stationvalue"> 
      Your Country Flag - (ISO 3166-1 Alpha-2 code) </div>
      <svg id="i-chevron-right" viewBox="0 0 32 32" width="14" height="14" fill="none" stroke="#F05E40" stroke-linecap="round" stroke-linejoin="round" stroke-width="6.25%">
    <path d="M12 30 L24 16 12 2" />
</svg>
  <input name="flag" type="text" id="flag" value="<?php echo strtolower($flag) ;?>" class="chooseapi">
 <div><a style="color:#000000;" href= "https://justcall.io/app/country-code-information.html" title="https://justcall.io/app/country-code-information.html" target="_blank"> check here</a></div>

   </div>
   
 <br>
                                                                    

      

  <div class="weatheroptionssidebar"> try and keep these short dont include full country try a short code
  <br/><br/>
  <svg id="i-alert" viewBox="0 0 32 32" width="14" height="14" fill="none" stroke="rgba(86, 95, 103, 1.000)" stroke-linecap="round" stroke-linejoin="round" stroke-width="6.25%">
    <path d="M16 3 L30 29 2 29 Z M16 11 L16 19 M16 23 L16 25" /> </svg>
    Keep Short Name to a maximum of 6 characters
  </div>
  <div class="weatherbottominfo"></div>
 <div class="weatheroptions">
<div class= "weathersectiontitle">
<svg id="i-location" viewBox="0 0 32 32" width="12" height="12" fill="none" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round" stroke-width="6.25%">
    <circle cx="16" cy="11" r="4" />
    <path d="M24 15 C21 22 16 30 16 30 16 30 11 22 8 15 5 8 10 2 16 2 22 2 27 8 24 15 Z" />
</svg>

Advisory Zone and Station Name</div><p>
  
        <div class= "stationvalue"> 
      Advisory Zone</div>
      <svg id="i-chevron-right" viewBox="0 0 32 32" width="14" height="14" fill="none" stroke="#F05E40" stroke-linecap="round" stroke-linejoin="round" stroke-width="6.25%">
    <path d="M12 30 L24 16 12 2" />
</svg><svg id="i-chevron-bottom" viewBox="0 0 32 32" width="10" height="10" fill="#777" stroke="#777" stroke-linecap="round" stroke-linejoin="round" stroke-width="6.25%">
    <path d="M30 12 L16 24 2 12" />
</svg>
      
       <label name="advisoryzone"></label>
        <select id="advisoryzone" name="advisoryzone" class="choose1">           
           <!--option><?php echo $advisoryzone ;?></option-->

																	<option value="Select Advisory Zone">Select Advisory Zone</option>
																	<option value="uk" <?php if($advisoryzone == 'uk') echo 'selected'; ?>>United Kingdom</option>
																	<option value="na" <?php if($advisoryzone == 'na') echo 'selected'; ?>>North America</option>
																	<option value="eu" <?php if($advisoryzone == 'eu') echo 'selected'; ?>>Europe</option>
																	<option value="au" <?php if($advisoryzone == 'au') echo 'selected'; ?>>Australia</option>
																	<option value="rw" <?php if($advisoryzone == 'rw') echo 'selected'; ?>>Rest of the World</option>
</select>  
        <br>

   <div class= "stationvalue">Short Name</div>
<svg id="i-chevron-right" viewBox="0 0 32 32" width="14" height="14" fill="none" stroke="rgba(86, 95, 103, 1.000)" stroke-linecap="round" stroke-linejoin="round" stroke-width="6.25%">
    <path d="M12 30 L24 16 12 2" />
</svg>

  <input name="manifestShortName" type="text" id="manifestShortName" value="<?php echo $manifestShortName ;?>" class="chooseapi">

   </div>
 <br><br>
   <div class="weatheroptionssidebar">Time Zone , you can check
   <a href="http://php.net/manual/en/timezones.php" title="http://php.net/manual/en/timezones.php" target="_blank"> the official php timezone documented page</a>
   <div class="weatherbottominfo">
<svg id="i-checkmark" viewBox="0 0 32 32" width="12" height="12" fill="none" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round" stroke-width="6.25%">
    <path d="M2 20 L12 28 30 4" />
</svg>


</div>
   </div>
   
   
   
<div class="weatheroptions">
<div class= "stationvalue">TIMEZONE</div>
 <svg id="i-chevron-right" viewBox="0 0 32 32" width="14" height="14" fill="none" stroke="rgba(86, 95, 103, 1.000)" stroke-linecap="round" stroke-linejoin="round" stroke-width="6.25%">
    <path d="M12 30 L24 16 12 2" />
</svg>

 
 <input name="TZ" type="text" id="TZ" value="<?php echo $TZ ;?>" class="choose"> 
  </div>
    

   
<div class="weatheroptions">


    <input type="submit" name="Submit" value="Save Configuration" class="button"><p><span style="color:#777;font-size:12px;padding:5px;line-height:16px;">
  <svg id="i-alert" viewBox="0 0 32 32" width="14" height="14" fill="none" stroke="rgba(86, 95, 103, 1.000)" stroke-linecap="round" stroke-linejoin="round" stroke-width="6.25%">
    <path d="M16 3 L30 29 2 29 Z M16 11 L16 19 M16 23 L16 25" /> </svg> Click "save configuration" if everything looks ok and dont forget to set the password.</span>
  





  </div>
  
  
 
   <p>
   <div class="weatheroptionssidebarbottom"><svg id="i-alert" viewBox="0 0 32 32" width="14" height="14" fill="none" stroke="rgba(86, 95, 103, 1.000)" stroke-linecap="round" stroke-linejoin="round" stroke-width="6.25%">
    <path d="M16 3 L30 29 2 29 Z M16 11 L16 19 M16 23 L16 25" />
</svg>
click save if everything looks ok above and dont forget to set the password.


<div class="weatherbottominfo">
<svg id="i-info" viewBox="0 0 32 32" width="14" height="14" fill="none" stroke="rgba(86, 95, 103, 1.000)" stroke-linecap="round" stroke-linejoin="round" stroke-width="6.25%">
    <path d="M16 14 L16 23 M16 8 L16 10" />
    <circle cx="16" cy="16" r="14" />
</svg>

now check the weather 
<svg id="i-checkmark" viewBox="0 0 32 32" width="12" height="12" fill="none" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round" stroke-width="6.25%">
    <path d="M2 20 L12 28 30 4" />
</svg>
</div>
</div>

<br>
 <br>
    <span style="font-size:12px;color:#777;"><svg id="i-info" viewBox="0 0 32 32" width="12" height="12" fill="none" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round" stroke-width="6.25%">
    <path d="M16 14 L16 23 M16 8 L16 10" />
    <circle cx="16" cy="16" r="14" />
</svg> DivumWX-Lite Location Settings Screen &copy; 2025-<?php echo date('Y');?> Dashboard</span><br>
<center><a href="https://www.divumwx.org" title="https://www.divumwx.org" target="_blank"><img src="img/divumwxLogo2.svg" width="80" /></a></center><br>
 
</form></div> 
</div>