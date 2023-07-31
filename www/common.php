<?php
  include('userSettings.php');
  date_default_timezone_set($TZ);
  mb_internal_encoding('UTF-8');
  mb_http_output('UTF-8');
  mb_http_input('G');
  mb_language('uni');
  mb_regex_encoding('UTF-8');
  ob_start('mb_output_handler');

  if(isSet($_GET['lang'])){
  $lang = $_GET['lang'];
  $_SESSION['lang'] = $lang;
  setcookie("lang", $lang, time() +3600);
  }else if(isSet($_SESSION['lang'])){
  $lang = $_SESSION['lang'];
  }else if(isSet($_COOKIE['lang'])){
  $lang = $_COOKIE['lang'];
  }else{
  $lang = $defaultlanguage;
  }
  $lang_file = 'lang.' . $lang . '.php';
  include_once 'languages/'.$lang_file;
?>
